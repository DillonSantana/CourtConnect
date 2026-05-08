<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Events</title>
  </head>
  <body>
    <?php
        include("pageFormat.php");
        renderHeader("Events", "login", "register");
    ?>

    <h1>Upcoming Events</h1><br>
    <div id="calendar"></div>
    <div id="eventModal" class="modal">

    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle"></h2>
        <p><strong>Description:</strong>
            <span id="modalDescription"></span>
        </p>
        <p><strong>Start:</strong>
            <span id="modalStart"></span>
        </p>
        <p><strong>End:</strong>
            <span id="modalEnd"></span>
        </p>
        <p><strong>Location:</strong>
            <span id="modalLocation"></span>
        </p>
        <p><strong>Capacity:</strong>
            <span id="modalCapacity"></span>
        </p><br>
        <p><strong>RSVP?</strong></p>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="button-group">
            <button id="yesBtn">Yes</button>
            <button id="maybeBtn">Maybe</button>
            <button id="noBtn">No</button>
        </div>
        <?php else: ?>
            <p class="login-message"><a href="login.php">Log in to RSVP</a></p>
        <?php endif; ?>
        <p id="rsvpMessage" class="rsvp-message"></p>
    </div>

    </div>

    <?php
        renderFooter();
    ?>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <script src="js/calendar.js"></script>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>