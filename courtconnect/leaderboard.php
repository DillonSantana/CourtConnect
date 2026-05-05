<?php
    require_once "db_connect.php";
    $conn = connectDB();

    if (session_status() === PHP_SESSION_NONE) session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['opponent_name'])) {
        header('Content-Type: application/json');
        $us  = (int)$_POST['user_score'];
        $os  = (int)$_POST['opponent_score'];
        $res = $us > $os ? 'win' : ($us < $os ? 'loss' : 'tie');
        $eid = $_POST['event_id'] !== '' ? (int)$_POST['event_id'] : null;
        $stmt = $conn->prepare("INSERT INTO matches
            (user_id, event_id, opponent_school, user_score, opponent_score, result, submitted_by, submitted_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())");
        $stmt->execute([
            $_SESSION['user_id'], $eid, trim($_POST['opponent_name']),
            $us, $os, $res, $_SESSION['user_id']
        ]);
        echo json_encode(['ok' => true, 'result' => $res]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch'])) {
        header('Content-Type: application/json');
        $rows = $conn->query("
            SELECT u.name,
                   SUM(result='win')  AS wins,
                   SUM(result='loss') AS losses,
                   SUM(result='tie')  AS ties,
                   COUNT(*)           AS played,
                   ROUND(100.0 * SUM(result='win') / COUNT(*), 1) AS pct
            FROM matches m
            JOIN users u ON u.user_id = m.user_id
            WHERE m.approval_status = 'approved'
            GROUP BY m.user_id
            ORDER BY wins DESC, pct DESC
        ")->fetchAll();
        echo json_encode($rows);
        exit;
    }

    $standings = $conn->query("
        SELECT u.name,
               SUM(result='win')  AS wins,
               SUM(result='loss') AS losses,
               SUM(result='tie')  AS ties,
               COUNT(*)           AS played,
               ROUND(100.0 * SUM(result='win') / COUNT(*), 1) AS pct
        FROM matches m
        JOIN users u ON u.user_id = m.user_id
        WHERE m.approval_status = 'approved'
        GROUP BY m.user_id
        ORDER BY wins DESC, pct DESC
    ")->fetchAll();

    $events = $conn->query("
        SELECT event_id, title, event_date FROM events ORDER BY event_date DESC
    ")->fetchAll();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .leaderboard-container, .events-container { width: 100%; overflow-x: auto; }
        .table th, .table td { white-space: nowrap; }
    </style>
    <title>Leaderboard</title>
  </head>
  <body>
    <?php
        include("pageFormat.php");
        renderHeader("Leaderboard", "login", "register");
    ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 mb-4">
                <div class="leaderboard-container">
                    <h2>🎾 Current Leaderboard</h2>
                    <table class="table table-striped mt-3">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Player</th>
                                <th>W</th>
                                <th>L</th>
                                <th>Win%</th>
                            </tr>
                        </thead>
                        <tbody id="standings-body">
                            <?php if (empty($standings)): ?>
                                <tr><td colspan="5">No approved results yet.</td></tr>
                            <?php else: ?>
                                <?php
                                    $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
                                    foreach ($standings as $i => $r):
                                        $rank = $i + 1;
                                ?>
                                <tr>
                                    <td><?= $medals[$rank] ?? $rank ?></td>
                                    <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                                    <td style="color: green;"><strong><?= $r['wins'] ?></strong></td>
                                    <td style="color: red;"><?= $r['losses'] ?></td>
                                    <td><?= $r['pct'] ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <small class="text-muted">Updates automatically every 30 seconds.</small>
                </div>
            </div>

            <div class="col-lg-5 mb-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="events-container">
                    <h2>📝 Submit a Result</h2>
                    <p class="text-muted" style="font-size: 0.9rem;">Results are pending until approved by an admin.</p>
                    <div class="form-group mt-3">
                        <label>Opponent Name</label>
                        <input type="text" id="opp-name" class="form-control" placeholder="e.g. Jane Smith">
                    </div>
                    <div class="form-group">
                        <label>Related Event (optional)</label>
                        <select id="event-id" class="form-control">
                            <option value="">-- None --</option>
                            <?php foreach ($events as $e): ?>
                            <option value="<?= $e['event_id'] ?>"><?= htmlspecialchars($e['title']) ?> (<?= $e['event_date'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Score (You – Opponent)</label>
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <input type="number" id="my-score"  class="form-control" style="width: 80px;" min="0" value="0">
                            <span>–</span>
                            <input type="number" id="opp-score" class="form-control" style="width: 80px;" min="0" value="0">
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block mt-2" onclick="submitScore()">Submit Result</button>
                    <p id="msg" class="mt-2"></p>
                </div>
                <?php else: ?>
                <div class="events-container">
                    <h2>📝 Submit a Result</h2>
                    <p class="mt-3"><a href="login.php">Log in</a> to submit a match result.</p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script src="js/leaderboard.js"></script>

    <?php renderFooter(); ?>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>