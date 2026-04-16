<?php
require_once 'pageFormat.php';
require_once 'db_connect.php';

// ── Auth guard ────────────────────────────────────────────────────────
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'event_admin'])) {
    header('Location: login.php');
    exit;
}

$adminID = $_SESSION['user']['user_id'] ?? 0;

// ── Helpers ───────────────────────────────────────────────────────────

function fullName(array $user): string {
    return trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
}

// Fetch all rows from a mysqli result as an associative array
function fetchAll(mysqli_result $result): array {
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    return $rows;
}

// ─────────────────────────────────────────────────────────────────────
// AJAX / POST ACTION HANDLER
// ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    switch ($action) {

        // ── EVENTS ───────────────────────────────────────────────────
        case 'create_event': {
            $cap = $_POST['max_capacity'] !== '' ? (int)$_POST['max_capacity'] : null;
            $stmt = $conn->prepare("INSERT INTO events
                (title, event_type, description, event_date, start_time, end_time, location, max_capacity, created_by, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssiis',
                $_POST['title'],
                $_POST['event_type'],
                $_POST['description'],
                $_POST['event_date'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['location'],
                $cap,
                $adminID,
                $_POST['status']
            );
            $stmt->execute();
            echo json_encode(['ok' => true, 'id' => $conn->insert_id]);
            break;
        }
        case 'edit_event': {
            $cap = $_POST['max_capacity'] !== '' ? (int)$_POST['max_capacity'] : null;
            $id  = (int)$_POST['event_id'];
            $stmt = $conn->prepare("UPDATE events SET
                title=?, event_type=?, description=?,
                event_date=?, start_time=?, end_time=?,
                location=?, max_capacity=?, status=?
                WHERE event_id=?");
            $stmt->bind_param('sssssssisi',
                $_POST['title'],
                $_POST['event_type'],
                $_POST['description'],
                $_POST['event_date'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['location'],
                $cap,
                $_POST['status'],
                $id
            );
            $stmt->execute();
            echo json_encode(['ok' => true]);
            break;
        }
        case 'delete_event': {
            $id = (int)$_POST['event_id'];
            $stmt = $conn->prepare("DELETE FROM events WHERE event_id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            echo json_encode(['ok' => true]);
            break;
        }

        // ── MATCH RESULTS ─────────────────────────────────────────────
        case 'get_matches': {
            $eid = (int)$_POST['event_id'];
            $stmt = $conn->prepare("
                SELECT m.match_id, m.opponent_school, m.user_score, m.opponent_score,
                       m.result, m.approval_status, m.submitted_at, m.approved_at,
                       u.first_name, u.last_name
                FROM matches m
                JOIN users u ON u.user_id = m.user_id
                WHERE m.event_id = ?
                ORDER BY m.submitted_at DESC");
            $stmt->bind_param('i', $eid);
            $stmt->execute();
            $matches = fetchAll($stmt->get_result());
            foreach ($matches as &$m) $m['player_name'] = fullName($m);
            echo json_encode(['ok' => true, 'matches' => $matches]);
            break;
        }
        case 'approve_match': {
            $id = (int)$_POST['match_id'];
            $stmt = $conn->prepare("UPDATE matches SET approval_status='approved',
                approved_by=?, approved_at=NOW() WHERE match_id=?");
            $stmt->bind_param('ii', $adminID, $id);
            $stmt->execute();
            echo json_encode(['ok' => true]);
            break;
        }
        case 'reject_match': {
            $id = (int)$_POST['match_id'];
            $stmt = $conn->prepare("UPDATE matches SET approval_status='rejected',
                approved_by=?, approved_at=NOW() WHERE match_id=?");
            $stmt->bind_param('ii', $adminID, $id);
            $stmt->execute();
            echo json_encode(['ok' => true]);
            break;
        }

        // ── RSVP LIST ─────────────────────────────────────────────────
        case 'get_rsvps': {
            $eid = (int)$_POST['event_id'];
            $stmt = $conn->prepare("
                SELECT u.first_name, u.last_name, u.email, r.status, r.responded_at
                FROM rsvps r
                JOIN users u ON u.user_id = r.user_id
                WHERE r.event_id = ?
                ORDER BY r.status, u.last_name, u.first_name");
            $stmt->bind_param('i', $eid);
            $stmt->execute();
            $rows = fetchAll($stmt->get_result());
            foreach ($rows as &$r) $r['name'] = fullName($r);
            echo json_encode(['ok' => true, 'rsvps' => $rows]);
            break;
        }

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    }
    exit;
}

// ── PAGE DATA ─────────────────────────────────────────────────────────
$events = fetchAll($conn->query("SELECT * FROM events ORDER BY event_date DESC, start_time DESC"));

renderHeader("Event Admin");
?>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700&family=Barlow:wght@400;500&display=swap');

  :root {
    --c0: #0d1b2a; --c1: #1b263b; --c2: #415a77; --c3: #778da9;
    --accent: #e0a020; --accent2: #4fc3f7;
    --danger: #e05555; --success: #4caf7d;
    --text: #e0e8f0; --subtext: #9fb3c8;
    --radius: 6px;
    --font-head: 'Barlow Condensed', sans-serif;
    --font-body: 'Barlow', sans-serif;
  }

  .ea-wrap { font-family: var(--font-body); color: var(--text); max-width: 1280px; margin: 0 auto; }

  .ea-page-title {
    font-family: var(--font-head); font-size: 2.2rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase; color: var(--accent);
    border-left: 4px solid var(--accent); padding-left: 14px; margin-bottom: 28px;
  }

  .ea-card { background: var(--c1); border: 1px solid var(--c2); border-radius: var(--radius); margin-bottom: 28px; overflow: hidden; }
  .ea-card-header { display: flex; align-items: center; justify-content: space-between; background: var(--c2); padding: 12px 18px; cursor: pointer; user-select: none; }
  .ea-card-header h2 { font-family: var(--font-head); font-size: 1.15rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: white; }
  .ea-card-header .chevron { font-size: 0.9rem; transition: transform .25s; }
  .ea-card-header.collapsed .chevron { transform: rotate(-90deg); }
  .ea-card-body { padding: 20px 18px; }
  .ea-card-body.hidden { display: none; }

  .ea-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
  .ea-table th { background: var(--c0); color: var(--c3); font-family: var(--font-head); letter-spacing: 1px; text-transform: uppercase; padding: 10px 12px; text-align: left; font-size: 0.78rem; }
  .ea-table td { padding: 10px 12px; border-bottom: 1px solid #1e2f44; vertical-align: top; }
  .ea-table tr:last-child td { border-bottom: none; }
  .ea-table tr:hover td { background: rgba(65,90,119,.25); }

  .ea-form { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin-bottom: 16px; }
  .ea-form.wide { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
  .ea-form label { font-size: 0.78rem; color: var(--subtext); font-family: var(--font-head); letter-spacing: 1px; text-transform: uppercase; display: block; margin-bottom: 4px; }
  .ea-form input, .ea-form select, .ea-form textarea {
    width: 100%; background: var(--c0); border: 1px solid var(--c2); border-radius: var(--radius);
    color: var(--text); padding: 8px 10px; font-family: var(--font-body); font-size: 0.9rem; outline: none; transition: border-color .2s;
  }
  .ea-form input:focus, .ea-form select:focus, .ea-form textarea:focus { border-color: var(--accent2); }

  .ea-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border: none; border-radius: var(--radius); font-family: var(--font-head); font-size: 0.88rem; font-weight: 600; letter-spacing: .5px; text-transform: uppercase; cursor: pointer; transition: filter .15s, transform .1s; }
  .ea-btn:hover { filter: brightness(1.15); }
  .ea-btn:active { transform: scale(.97); }
  .ea-btn-primary { background: var(--accent); color: #0d1b2a; }
  .ea-btn-info    { background: var(--accent2); color: #0d1b2a; }
  .ea-btn-success { background: var(--success); color: #0d1b2a; }
  .ea-btn-danger  { background: var(--danger); color: white; }
  .ea-btn-ghost   { background: transparent; border: 1px solid var(--c2); color: var(--text); }
  .ea-btn-sm { padding: 4px 10px; font-size: .78rem; }

  .badge { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: .72rem; font-family: var(--font-head); letter-spacing: .5px; text-transform: uppercase; font-weight: 600; }
  .badge-pending     { background: #3a2e10; color: var(--accent); }
  .badge-approved    { background: #163324; color: var(--success); }
  .badge-rejected    { background: #331616; color: var(--danger); }
  .badge-completed   { background: #163324; color: var(--success); }
  .badge-upcoming    { background: #1a2233; color: var(--accent2); }
  .badge-cancelled   { background: #331616; color: var(--danger); }
  .badge-win         { background: #163324; color: var(--success); }
  .badge-loss        { background: #331616; color: var(--danger); }
  .badge-Yes         { background: #163324; color: var(--success); }
  .badge-No          { background: #331616; color: var(--danger); }
  .badge-Maybe       { background: #1a2233; color: var(--accent2); }
  .badge-practice    { background: #1f2b1a; color: #a5d6a7; }
  .badge-tournament  { background: #2a261a; color: #ffe082; }
  .badge-social      { background: #1a2233; color: var(--accent2); }

  .event-select-bar { display: flex; align-items: flex-end; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
  .event-select-bar > div { flex: 1; max-width: 340px; }

  .divider { height: 1px; background: var(--c2); margin: 16px 0; }
  .row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
  .empty-state { color: var(--subtext); font-size: .88rem; padding: 16px 0; text-align: center; }
  .round-group-header td { background: var(--c0); color: var(--accent); font-family: var(--font-head); letter-spacing: 1px; text-transform: uppercase; font-size: .8rem; padding: 8px 12px; }

  #ea-toast { position: fixed; bottom: 28px; right: 28px; background: var(--c2); color: white; padding: 12px 22px; border-radius: var(--radius); font-family: var(--font-head); font-size: .95rem; letter-spacing: .5px; opacity: 0; transform: translateY(10px); transition: opacity .3s, transform .3s; z-index: 9999; pointer-events: none; max-width: 320px; }
  #ea-toast.show { opacity: 1; transform: translateY(0); }
  #ea-toast.toast-ok  { border-left: 4px solid var(--success); }
  #ea-toast.toast-err { border-left: 4px solid var(--danger); }

  .ea-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 2000; align-items: center; justify-content: center; }
  .ea-modal-overlay.open { display: flex; }
  .ea-modal { background: var(--c1); border: 1px solid var(--c2); border-radius: var(--radius); padding: 28px; width: 90%; max-width: 640px; max-height: 90vh; overflow-y: auto; position: relative; }
  .ea-modal h3 { font-family: var(--font-head); font-size: 1.2rem; letter-spacing: 1px; text-transform: uppercase; color: var(--accent); margin-bottom: 20px; }
  .ea-modal-close { position: absolute; top: 12px; right: 16px; background: none; border: none; color: var(--subtext); font-size: 1.3rem; cursor: pointer; }
</style>

<div class="ea-wrap">
  <div class="ea-page-title">Event Admin Panel</div>

  <!-- ████ SECTION 1 — MANAGE EVENTS ████ -->
  <div class="ea-card">
    <div class="ea-card-header" onclick="toggleSection('events')">
      <h2>Manage Events</h2><span class="chevron">▼</span>
    </div>
    <div class="ea-card-body" id="body-events">
      <div id="event-form-wrap">
        <h3 style="font-family:var(--font-head);font-size:1rem;letter-spacing:1px;text-transform:uppercase;color:var(--accent2);margin-bottom:12px;" id="event-form-title">New Event</h3>
        <input type="hidden" id="ef-id">
        <div class="ea-form wide">
          <div><label>Title</label><input type="text" id="ef-title" placeholder="e.g. Club Practice"></div>
          <div><label>Type</label>
            <select id="ef-type">
              <option value="practice">Practice</option>
              <option value="tournament">Tournament</option>
              <option value="social">Social</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div><label>Date</label><input type="date" id="ef-date"></div>
          <div><label>Start Time</label><input type="time" id="ef-start"></div>
          <div><label>End Time</label><input type="time" id="ef-end"></div>
          <div><label>Location</label><input type="text" id="ef-loc" placeholder="Campus Courts…"></div>
          <div><label>Max Capacity</label><input type="number" id="ef-cap" min="1" placeholder="Leave blank for unlimited"></div>
          <div><label>Status</label>
            <select id="ef-status">
              <option value="upcoming">Upcoming</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div><label style="font-size:.78rem;color:var(--subtext);font-family:var(--font-head);letter-spacing:1px;text-transform:uppercase;display:block;margin-bottom:4px;">Description</label>
          <textarea id="ef-desc" rows="2" style="width:100%;background:var(--c0);border:1px solid var(--c2);border-radius:var(--radius);color:var(--text);padding:8px 10px;font-family:var(--font-body);font-size:.9rem;outline:none;resize:vertical;" placeholder="Optional event description"></textarea>
        </div>
        <div style="display:flex;gap:10px;margin-top:12px;">
          <button class="ea-btn ea-btn-primary" onclick="submitEvent()">Save Event</button>
          <button class="ea-btn ea-btn-ghost"   onclick="resetEventForm()">Clear</button>
        </div>
      </div>
      <div class="divider"></div>
      <div style="overflow-x:auto;">
        <table class="ea-table">
          <thead><tr><th>ID</th><th>Title</th><th>Type</th><th>Date</th><th>Time</th><th>Location</th><th>Cap.</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="events-tbody">
            <?php foreach ($events as $ev): ?>
            <tr id="erow-<?= $ev['event_id'] ?>">
              <td><?= $ev['event_id'] ?></td>
              <td><?= htmlspecialchars($ev['title']) ?></td>
              <td><span class="badge badge-<?= $ev['event_type'] ?>"><?= $ev['event_type'] ?></span></td>
              <td><?= $ev['event_date'] ?></td>
              <td><?= substr($ev['start_time'],0,5) ?>–<?= substr($ev['end_time'],0,5) ?></td>
              <td><?= htmlspecialchars($ev['location']) ?></td>
              <td><?= $ev['max_capacity'] ?? '∞' ?></td>
              <td><span class="badge badge-<?= $ev['status'] ?>"><?= $ev['status'] ?></span></td>
              <td>
                <div class="row-actions">
                  <button class="ea-btn ea-btn-ghost ea-btn-sm" onclick="editEvent(<?= $ev['event_id'] ?>, <?= htmlspecialchars(json_encode($ev)) ?>)">Edit</button>
                  <button class="ea-btn ea-btn-danger ea-btn-sm" onclick="deleteEvent(<?= $ev['event_id'] ?>)">Delete</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (empty($events)): ?><p class="empty-state">No events yet.</p><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ████ SECTION 2 — RSVP LISTS ████ -->
  <div class="ea-card">
    <div class="ea-card-header" onclick="toggleSection('rsvp')">
      <h2>RSVP Lists</h2><span class="chevron">▼</span>
    </div>
    <div class="ea-card-body" id="body-rsvp">
      <div class="event-select-bar">
        <div>
          <label style="font-size:.78rem;color:var(--subtext);font-family:var(--font-head);letter-spacing:1px;text-transform:uppercase;display:block;margin-bottom:4px;">Event</label>
          <select id="rsvp-event-select"><?= eventOptions($events) ?></select>
        </div>
        <button class="ea-btn ea-btn-info" onclick="loadRSVPs()">Load RSVPs</button>
      </div>
      <div id="rsvp-results"><p class="empty-state">Select an event to view RSVPs.</p></div>
    </div>
  </div>

  <!-- ████ SECTION 3 — MATCH RESULTS ████ -->
  <div class="ea-card">
    <div class="ea-card-header" onclick="toggleSection('matches')">
      <h2>Match Results</h2><span class="chevron">▼</span>
    </div>
    <div class="ea-card-body" id="body-matches">
      <div class="event-select-bar" style="margin-bottom:20px;">
        <div>
          <label style="font-size:.78rem;color:var(--subtext);font-family:var(--font-head);letter-spacing:1px;text-transform:uppercase;display:block;margin-bottom:4px;">Event</label>
          <select id="match-event-select"><?= eventOptions($events) ?></select>
        </div>
        <button class="ea-btn ea-btn-info" onclick="loadMatches()">Load Results</button>
      </div>
      <div id="matches-results"><p class="empty-state">Select an event to view submitted results.</p></div>
    </div>
  </div>

</div><!-- /.ea-wrap -->

<!-- ████ EDIT EVENT MODAL ████ -->
<div class="ea-modal-overlay" id="edit-modal">
  <div class="ea-modal">
    <button class="ea-modal-close" onclick="closeModal('edit-modal')">✕</button>
    <h3>Edit Event</h3>
    <input type="hidden" id="em-id">
    <div class="ea-form wide">
      <div><label>Title</label><input type="text" id="em-title"></div>
      <div><label>Type</label>
        <select id="em-type">
          <option value="practice">Practice</option><option value="tournament">Tournament</option>
          <option value="social">Social</option><option value="other">Other</option>
        </select>
      </div>
      <div><label>Date</label><input type="date" id="em-date"></div>
      <div><label>Start Time</label><input type="time" id="em-start"></div>
      <div><label>End Time</label><input type="time" id="em-end"></div>
      <div><label>Location</label><input type="text" id="em-loc"></div>
      <div><label>Max Capacity</label><input type="number" id="em-cap" min="1" placeholder="Unlimited"></div>
      <div><label>Status</label>
        <select id="em-status">
          <option value="upcoming">Upcoming</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </div>
    <div style="margin-top:10px;"><label style="font-size:.78rem;color:var(--subtext);font-family:var(--font-head);letter-spacing:1px;text-transform:uppercase;display:block;margin-bottom:4px;">Description</label>
      <textarea id="em-desc" rows="2" style="width:100%;background:var(--c0);border:1px solid var(--c2);border-radius:var(--radius);color:var(--text);padding:8px 10px;font-family:var(--font-body);font-size:.9rem;outline:none;resize:vertical;"></textarea>
    </div>
    <div style="display:flex;gap:10px;margin-top:16px;">
      <button class="ea-btn ea-btn-primary" onclick="saveEdit()">Save Changes</button>
      <button class="ea-btn ea-btn-ghost"   onclick="closeModal('edit-modal')">Cancel</button>
    </div>
  </div>
</div>

<div id="ea-toast"></div>

<script>
// ── UTILITIES ─────────────────────────────────────────────────────────
function toast(msg, ok = true) {
  const t = document.getElementById('ea-toast');
  t.textContent = msg;
  t.className = 'show ' + (ok ? 'toast-ok' : 'toast-err');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.className = '', 3200);
}

async function post(data) {
  const fd = new FormData();
  for (const [k, v] of Object.entries(data)) fd.append(k, v);
  const r = await fetch('event_admin.php', { method: 'POST', body: fd });
  return r.json();
}

function toggleSection(id) {
  const body = document.getElementById('body-' + id);
  body.classList.toggle('hidden');
  body.previousElementSibling.classList.toggle('collapsed');
}

function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function escHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmtTime(str) {
  if (!str) return '—';
  const d = new Date(str.replace(' ','T'));
  return isNaN(d) ? str : d.toLocaleString([], { dateStyle:'medium', timeStyle:'short' });
}

// ── EVENTS ────────────────────────────────────────────────────────────
function resetEventForm() {
  ['ef-id','ef-title','ef-date','ef-start','ef-end','ef-loc','ef-cap','ef-desc']
    .forEach(id => document.getElementById(id).value = '');
  document.getElementById('ef-type').value   = 'practice';
  document.getElementById('ef-status').value = 'upcoming';
  document.getElementById('event-form-title').textContent = 'New Event';
}

async function submitEvent() {
  const id    = document.getElementById('ef-id').value;
  const title = document.getElementById('ef-title').value.trim();
  if (!title) { toast('Title is required', false); return; }
  const res = await post({
    action:       id ? 'edit_event' : 'create_event',
    event_id:     id,
    title,
    event_type:   document.getElementById('ef-type').value,
    description:  document.getElementById('ef-desc').value,
    event_date:   document.getElementById('ef-date').value,
    start_time:   document.getElementById('ef-start').value,
    end_time:     document.getElementById('ef-end').value,
    location:     document.getElementById('ef-loc').value,
    max_capacity: document.getElementById('ef-cap').value,
    status:       document.getElementById('ef-status').value,
  });
  if (res.ok) { toast(id ? 'Event updated!' : 'Event created!'); setTimeout(() => location.reload(), 800); }
  else toast('Error saving event', false);
}

function editEvent(id, ev) {
  document.getElementById('em-id').value    = id;
  document.getElementById('em-title').value = ev.title;
  document.getElementById('em-type').value  = ev.event_type;
  document.getElementById('em-date').value  = ev.event_date;
  document.getElementById('em-start').value = ev.start_time?.slice(0,5) ?? '';
  document.getElementById('em-end').value   = ev.end_time?.slice(0,5)   ?? '';
  document.getElementById('em-loc').value   = ev.location;
  document.getElementById('em-cap').value   = ev.max_capacity ?? '';
  document.getElementById('em-status').value = ev.status;
  document.getElementById('em-desc').value  = ev.description ?? '';
  document.getElementById('edit-modal').classList.add('open');
}

async function saveEdit() {
  const id = document.getElementById('em-id').value;
  const res = await post({
    action:       'edit_event',
    event_id:     id,
    title:        document.getElementById('em-title').value,
    event_type:   document.getElementById('em-type').value,
    description:  document.getElementById('em-desc').value,
    event_date:   document.getElementById('em-date').value,
    start_time:   document.getElementById('em-start').value,
    end_time:     document.getElementById('em-end').value,
    location:     document.getElementById('em-loc').value,
    max_capacity: document.getElementById('em-cap').value,
    status:       document.getElementById('em-status').value,
  });
  if (res.ok) { toast('Event updated!'); setTimeout(() => location.reload(), 800); }
  else toast('Error updating', false);
}

async function deleteEvent(id) {
  if (!confirm('Delete this event and all its data?')) return;
  const res = await post({ action: 'delete_event', event_id: id });
  if (res.ok) { document.getElementById('erow-' + id)?.remove(); toast('Event deleted'); }
  else toast('Error deleting', false);
}

// ── RSVP ──────────────────────────────────────────────────────────────
async function loadRSVPs() {
  const eid = document.getElementById('rsvp-event-select').value;
  if (!eid) { toast('Select an event first', false); return; }
  const res = await post({ action: 'get_rsvps', event_id: eid });
  const wrap = document.getElementById('rsvp-results');
  if (!res.ok || !res.rsvps.length) { wrap.innerHTML = '<p class="empty-state">No RSVPs found.</p>'; return; }
  const rows = res.rsvps.map(r => `<tr>
    <td>${escHtml(r.name)}</td>
    <td>${escHtml(r.email)}</td>
    <td><span class="badge badge-${escHtml(r.status)}">${escHtml(r.status)}</span></td>
    <td>${r.responded_at ? fmtTime(r.responded_at) : '—'}</td>
  </tr>`).join('');
  wrap.innerHTML = `<div style="overflow-x:auto;"><table class="ea-table">
    <thead><tr><th>Name</th><th>Email</th><th>RSVP</th><th>Responded</th></tr></thead>
    <tbody>${rows}</tbody></table></div>`;
}

// ── MATCHES ───────────────────────────────────────────────────────────
async function loadMatches() {
  const eid = document.getElementById('match-event-select').value;
  if (!eid) { toast('Select an event first', false); return; }
  const res = await post({ action: 'get_matches', event_id: eid });
  const wrap = document.getElementById('matches-results');
  if (!res.ok || !res.matches.length) {
    wrap.innerHTML = '<p class="empty-state">No submitted results found for this event.</p>';
    return;
  }

  const rows = res.matches.map(m => `
    <tr id="mrow-${m.match_id}">
      <td>${m.match_id}</td>
      <td>${escHtml(m.player_name)}</td>
      <td>${escHtml(m.opponent_school) || '—'}</td>
      <td>${m.user_score} – ${m.opponent_score}</td>
      <td><span class="badge badge-${m.result}">${escHtml(m.result) || '—'}</span></td>
      <td>${m.submitted_at ? fmtTime(m.submitted_at) : '—'}</td>
      <td><span class="badge badge-${m.approval_status}">${m.approval_status}</span></td>
      <td><div class="row-actions">
        ${m.approval_status === 'pending' ? `
          <button class="ea-btn ea-btn-success ea-btn-sm" onclick="approveMatch(${m.match_id})">Approve</button>
          <button class="ea-btn ea-btn-danger  ea-btn-sm" onclick="rejectMatch(${m.match_id})">Reject</button>
        ` : ''}
      </div></td>
    </tr>`).join('');

  wrap.innerHTML = `<div style="overflow-x:auto;"><table class="ea-table">
    <thead><tr>
      <th>ID</th><th>Player</th><th>Opponent</th><th>Score</th>
      <th>Result</th><th>Submitted</th><th>Status</th><th>Actions</th>
    </tr></thead>
    <tbody>${rows}</tbody>
  </table></div>`;
}

async function approveMatch(id) {
  const res = await post({ action: 'approve_match', match_id: id });
  if (res.ok) { toast('Result approved!'); loadMatches(); }
  else toast('Error', false);
}

async function rejectMatch(id) {
  if (!confirm('Reject this result?')) return;
  const res = await post({ action: 'reject_match', match_id: id });
  if (res.ok) { toast('Result rejected'); loadMatches(); }
  else toast('Error', false);
}

</script>

<?php renderFooter(); ?>

<?php
// ── HELPER: event <option> list ───────────────────────────────────────
function eventOptions(array $events): string {
    $html = '<option value="">-- choose event --</option>';
    foreach ($events as $ev) {
        $html .= sprintf(
            '<option value="%d">%s (%s)</option>',
            $ev['event_id'],
            htmlspecialchars($ev['title']),
            $ev['event_date']
        );
    }
    return $html;
}
?>
