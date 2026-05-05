<?php
session_start();
require_once 'db_connect.php';

$conn = connectDB();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); exit;
}

require_once 'pageFormat.php';

$adminID = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $eid    = (int)($_POST['event_id']  ?? 0);
    $mid    = (int)($_POST['match_id']  ?? 0);
    $cap    = ($_POST['max_capacity'] ?? '') !== '' ? (int)$_POST['max_capacity'] : null;

    switch ($action) {
        case 'create_event':
            $s = $conn->prepare("INSERT INTO events (title,event_type,description,event_date,start_time,end_time,location,max_capacity,created_by) VALUES (:t,:et,:d,:ed,:st,:en,:l,:c,:b)");
            $s->execute([':t'=>$_POST['title'],':et'=>$_POST['event_type'],':d'=>$_POST['description']??'',':ed'=>$_POST['event_date'],':st'=>$_POST['start_time'],':en'=>$_POST['end_time'],':l'=>$_POST['location'],':c'=>$cap,':b'=>$adminID]);
            echo json_encode(['ok'=>true,'id'=>$conn->lastInsertId()]); break;

        case 'edit_event':
            $s = $conn->prepare("UPDATE events SET title=:t,event_type=:et,description=:d,event_date=:ed,start_time=:st,end_time=:en,location=:l,max_capacity=:c WHERE event_id=:id");
            $s->execute([':t'=>$_POST['title'],':et'=>$_POST['event_type'],':d'=>$_POST['description']??'',':ed'=>$_POST['event_date'],':st'=>$_POST['start_time'],':en'=>$_POST['end_time'],':l'=>$_POST['location'],':c'=>$cap,':id'=>$eid]);
            echo json_encode(['ok'=>true]); break;

        case 'delete_event':
            $conn->prepare("DELETE FROM events WHERE event_id=:id")->execute([':id'=>$eid]);
            echo json_encode(['ok'=>true]); break;

        case 'get_matches':
            $s = $conn->prepare("SELECT m.match_id,m.opponent_school,m.user_score,m.opponent_score,m.result,m.approval_status,m.submitted_at,u.name FROM matches m JOIN users u ON u.user_id=m.user_id WHERE m.event_id=:eid ORDER BY m.submitted_at DESC");
            $s->execute([':eid'=>$eid]);
            $rows = $s->fetchAll();
            foreach ($rows as &$r) $r['player_name'] = $r['name'];
            echo json_encode(['ok'=>true,'matches'=>$rows]); break;

        case 'approve_match':
        case 'reject_match':
            $status = $action === 'approve_match' ? 'approved' : 'rejected';
            $conn->prepare("UPDATE matches SET approval_status=:s,approved_by=:b,approved_at=NOW() WHERE match_id=:id")->execute([':s'=>$status,':b'=>$adminID,':id'=>$mid]);
            echo json_encode(['ok'=>true]); break;

        case 'get_rsvps':
            $s = $conn->prepare("SELECT u.name,u.email,r.status,r.responded_at FROM rsvps r JOIN users u ON u.user_id=r.user_id WHERE r.event_id=:eid ORDER BY r.status,u.name");
            $s->execute([':eid'=>$eid]);
            echo json_encode(['ok'=>true,'rsvps'=>$s->fetchAll()]); break;

        default:
            echo json_encode(['ok'=>false,'error'=>'Unknown action']);
    }
    exit;
}

$events = $conn->query("SELECT * FROM events ORDER BY event_date DESC,start_time DESC")->fetchAll();

function eOpts(array $events): string {
    $o = '<option value="">-- choose event --</option>';
    foreach ($events as $e) $o .= sprintf('<option value="%d">%s (%s)</option>', $e['event_id'], htmlspecialchars($e['title']), $e['event_date']);
    return $o;
}

renderHeader("Event Admin", "logout", "profile");
?>
<link rel="stylesheet" href="css/event_admin.css">

<div class="ea-wrap">
  <div class="ea-page-title">Event Admin Panel</div>

  <div class="ea-card">
    <div class="ea-card-header" onclick="toggleSection('events')"><h2>Manage Events</h2><span class="chevron">▼</span></div>
    <div class="ea-card-body" id="body-events">
      <h3 class="ea-form-title" id="event-form-title">New Event</h3>
      <input type="hidden" id="ef-id">
      <div class="ea-form">
        <div><label>Title</label><input type="text" id="ef-title" placeholder="e.g. Club Practice"></div>
        <div><label>Type</label><select id="ef-type"><option value="practice">Practice</option><option value="tournament">Tournament</option><option value="social">Social</option><option value="other">Other</option></select></div>
        <div><label>Date</label><input type="date" id="ef-date"></div>
        <div><label>Start Time</label><input type="time" id="ef-start"></div>
        <div><label>End Time</label><input type="time" id="ef-end"></div>
        <div><label>Location</label><input type="text" id="ef-loc" placeholder="Campus Courts…"></div>
        <div><label>Max Capacity</label><input type="number" id="ef-cap" min="1" placeholder="Unlimited"></div>
      </div>
      <div><label class="ea-label">Description</label><textarea id="ef-desc" class="ea-textarea" rows="2" placeholder="Optional"></textarea></div>
      <div class="ea-actions">
        <button class="ea-btn ea-btn-primary" onclick="submitEvent()">Save Event</button>
        <button class="ea-btn ea-btn-ghost" onclick="resetForm()">Clear</button>
      </div>
      <div class="divider"></div>
      <div style="overflow-x:auto">
        <table class="ea-table">
          <thead><tr><th>ID</th><th>Title</th><th>Type</th><th>Date</th><th>Time</th><th>Location</th><th>Cap.</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($events as $ev): ?>
            <tr id="erow-<?= $ev['event_id'] ?>">
              <td><?= $ev['event_id'] ?></td>
              <td><?= htmlspecialchars($ev['title']) ?></td>
              <td><span class="badge badge-<?= $ev['event_type'] ?>"><?= $ev['event_type'] ?></span></td>
              <td><?= $ev['event_date'] ?></td>
              <td><?= substr($ev['start_time'],0,5) ?>–<?= substr($ev['end_time'],0,5) ?></td>
              <td><?= htmlspecialchars($ev['location']) ?></td>
              <td><?= $ev['max_capacity'] ?? '∞' ?></td>
              <td class="row-actions">
                <button class="ea-btn ea-btn-ghost ea-btn-sm" onclick="editEvent(<?= $ev['event_id'] ?>,<?= htmlspecialchars(json_encode($ev)) ?>)">Edit</button>
                <button class="ea-btn ea-btn-danger ea-btn-sm" onclick="deleteEvent(<?= $ev['event_id'] ?>)">Delete</button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($events)): ?><tr><td colspan="8" class="empty-state">No events yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="ea-card">
    <div class="ea-card-header" onclick="toggleSection('rsvp')"><h2>RSVP Lists</h2><span class="chevron">▼</span></div>
    <div class="ea-card-body" id="body-rsvp">
      <div class="event-select-bar">
        <div><label class="ea-label">Event</label><select id="rsvp-event-select"><?= eOpts($events) ?></select></div>
        <button class="ea-btn ea-btn-info" onclick="loadRSVPs()">Load RSVPs</button>
      </div>
      <div id="rsvp-results"><p class="empty-state">Select an event to view RSVPs.</p></div>
    </div>
  </div>

  <div class="ea-card">
    <div class="ea-card-header" onclick="toggleSection('matches')"><h2>Match Results</h2><span class="chevron">▼</span></div>
    <div class="ea-card-body" id="body-matches">
      <div class="event-select-bar">
        <div><label class="ea-label">Event</label><select id="match-event-select"><?= eOpts($events) ?></select></div>
        <button class="ea-btn ea-btn-info" onclick="loadMatches()">Load Results</button>
      </div>
      <div id="matches-results"><p class="empty-state">Select an event to view submitted results.</p></div>
    </div>
  </div>

</div>
<div id="ea-toast"></div>
<script src="js/event_admin.js"></script>

<?php renderFooter(); ?>
