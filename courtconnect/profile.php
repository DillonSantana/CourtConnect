<?php
require_once 'pageFormat.php';
require_once 'db_connect.php';

$conn = connectDB();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$userID = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'upload_photo': {
            $file = $_FILES['photo'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['ok'=>false,'error'=>'Upload failed']); break;
            }
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array($file['type'], $allowed)) {
                echo json_encode(['ok'=>false,'error'=>'Invalid file type']); break;
            }
            if ($file['size'] > 2 * 1024 * 1024) {
                echo json_encode(['ok'=>false,'error'=>'File too large (max 2MB)']); break;
            }
            $dir = __DIR__ . '/img/uploads/profilepics/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $userID . '.' . $ext;
            foreach (glob($dir . 'user_' . $userID . '.*') as $old) unlink($old);
            if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
                echo json_encode(['ok'=>false,'error'=>'Could not save file']); break;
            }
            $path = 'img/uploads/profilepics/' . $filename;
            $conn->prepare("UPDATE users SET profile_pic=:p WHERE user_id=:id")
                 ->execute([':p'=>$path,':id'=>$userID]);
            echo json_encode(['ok'=>true,'path'=>$path]);
            break;
        }
        default:
            echo json_encode(['ok'=>false,'error'=>'Unknown action']);
    }
    exit;
}

$user = $conn->prepare("SELECT user_id, name, email, role, profile_pic FROM users WHERE user_id=:id");
$user->execute([':id' => $userID]);
$user = $user->fetch();
if (!$user) { header('Location: login.php'); exit; }

$wl = $conn->prepare("SELECT SUM(result='win') AS wins, SUM(result='loss') AS losses FROM matches WHERE user_id=:id AND approval_status='approved'");
$wl->execute([':id' => $userID]);
$wl     = $wl->fetch();
$wins   = (int)($wl['wins']   ?? 0);
$losses = (int)($wl['losses'] ?? 0);

$history = $conn->prepare("SELECT match_id, opponent_school, user_score, opponent_score, result, approval_status, submitted_at FROM matches WHERE user_id=:id ORDER BY submitted_at DESC");
$history->execute([':id' => $userID]);
$history = $history->fetchAll();

$picSrc = !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : null;

renderHeader("Profile", "logout", "profile");
?>
<link rel="stylesheet" href="css/profile.css">

<div class="pf-wrap">
  <div class="pf-page-title">My Profile</div>
  <div class="pf-card">
    <div class="pf-card-header">Account</div>
    <div class="pf-card-body">
      <div class="pf-center">

        <div class="pf-avatar-wrap">
          <?php if ($picSrc): ?>
            <img src="<?= $picSrc ?>" alt="Profile picture" class="pf-avatar" id="pf-avatar-img">
          <?php else: ?>
            <div class="pf-avatar-placeholder" id="pf-avatar-placeholder"><?= mb_substr($user['name'], 0, 1) ?></div>
          <?php endif; ?>
          <button class="pf-upload-btn" onclick="document.getElementById('pf-file-input').click()" title="Change photo">✎</button>
          <input type="file" id="pf-file-input" accept="image/*" style="display:none" onchange="uploadPhoto(this)">
        </div>

        <div style="text-align:center">
          <div class="pf-name"><?= htmlspecialchars($user['name']) ?></div>
          <div class="pf-role"><?= htmlspecialchars($user['role']) ?></div>
          <div class="pf-email"><?= htmlspecialchars($user['email']) ?></div>
        </div>

        <div class="pf-stats">
          <div class="pf-stat pf-stat-win">
            <div class="pf-stat-num"><?= $wins ?></div>
            <div class="pf-stat-label">Wins</div>
          </div>
          <div class="pf-stat">
            <div class="pf-stat-num" style="color:var(--subtext)"><?= $wins + $losses ?></div>
            <div class="pf-stat-label">Matches</div>
          </div>
          <div class="pf-stat pf-stat-loss">
            <div class="pf-stat-num"><?= $losses ?></div>
            <div class="pf-stat-label">Losses</div>
          </div>
        </div>

      </div>
    </div>
  </div>
  <div class="pf-card">
    <div class="pf-card-header">Match History</div>
    <div class="pf-card-body" style="padding:0">
      <?php if (empty($history)): ?>
        <p class="empty-state">No matches on record yet.</p>
      <?php else: ?>
      <div class="pf-table-wrap">
        <table class="pf-table">
          <thead><tr><th>Opponent</th><th>Score</th><th>Result</th><th>Submitted</th><th>Approval</th></tr></thead>
          <tbody>
            <?php foreach ($history as $m): ?>
            <tr>
              <td><?= htmlspecialchars($m['opponent_school']) ?></td>
              <td><?= $m['user_score'] ?> – <?= $m['opponent_score'] ?></td>
              <td><span class="badge badge-<?= $m['result'] ?>"><?= $m['result'] ?></span></td>
              <td><?= $m['submitted_at'] ? date('M j, Y', strtotime($m['submitted_at'])) : '—' ?></td>
              <td><span class="badge badge-<?= $m['approval_status'] ?>"><?= $m['approval_status'] ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<div id="pf-toast"></div>
<script src="js/profile.js"></script>

<?php renderFooter(); ?>
