<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Court Connect</title>
  </head>
  <body>
    <?php
        include("pageFormat.php");
        renderHeader("Dashboard", "login", "register");
    ?>

    <?php
        require_once "db_connect.php";
        $conn = connectDB();

        $events = $conn->query("
            SELECT title, event_date, location
            FROM events
            WHERE event_date >= CURDATE()
            ORDER BY event_date ASC
            LIMIT 3
        ")->fetchAll();

        $standings = $conn->query("
            SELECT u.name,
                   SUM(result='win')  AS wins,
                   SUM(result='loss') AS losses,
                   COUNT(*)           AS played
            FROM matches m
            JOIN users u ON u.user_id = m.user_id
            WHERE m.approval_status = 'approved'
            GROUP BY m.user_id
            ORDER BY wins DESC
            LIMIT 3
        ")->fetchAll();
    ?>

    <div class="slideshow-container">
        <div class="slide fade">
            <img src="img/tennis12.jpg" alt="Slide 1">
        </div>

        <div class="slide fade">
            <img src="img/tennis22.jpg" alt="Slide 2">
        </div>

        <div class="slide fade">
            <img src="img/tennis32.jpg" alt="Slide 3">
        </div>

        <div class="overlay-text">
            Welcome to Court Connect
        </div>
    </div>

    <div class="box-container">
        <div class="events-container">
            <h2>Upcoming Events</h2>
            <?php if (empty($events)): ?>
                <p>No upcoming events.</p>
            <?php else: ?>
                <?php foreach ($events as $e): ?>
                <div class="event-card">
                    <h3><?= htmlspecialchars($e['title']) ?></h3>
                    <p>Date: <?= date('F j, Y', strtotime($e['event_date'])) ?></p>
                    <p>Location: <?= htmlspecialchars($e['location']) ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="leaderboard-container">
            <h2>Current Leaderboard</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Player Name</th>
                        <th>W</th>
                        <th>L</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($standings)): ?>
                        <tr><td colspan="4">No results yet.</td></tr>
                    <?php else: ?>
                        <?php
                            $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
                            foreach ($standings as $i => $r):
                                $rank = $i + 1;
                        ?>
                        <tr>
                            <td><?= $medals[$rank] ?? $rank ?></td>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><?= $r['wins'] ?></td>
                            <td><?= $r['losses'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
        renderFooter();
    ?>

    <script src="./js/slides.js"></script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>