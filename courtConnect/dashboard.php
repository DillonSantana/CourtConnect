<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Court Connect</title>
  </head>
  <body>
    <?php
        include("pageFormat.php");
        renderHeader("Dashboard");
    ?>

    <div class="slideshow-container">
        <!-- Slides -->
        <div class="slide fade">
            <img src="img/tennis1.jpg" alt="Slide 1">
        </div>

        <div class="slide fade">
            <img src="img/tennis2.jpg" alt="Slide 2">
        </div>

        <div class="slide fade">
            <img src="img/tennis3.jpg" alt="Slide 3">
        </div>

        <!-- Center Header -->
        <div class="overlay-text">
            Welcome to Court Connect
        </div>
    </div>

    <div class="box-container">
        <div class="events-container">
            <h2>Upcoming Events</h2>
            <div class="event-card">
                <h3>Spring Tennis Tournament</h3>
                <p>Date: April 15, 2024</p>
                <p>Location: Central Park Courts</p>
            </div>

            <div class="event-card">
                <h3>Summer Tennis Camp</h3>
                <p>Date: June 10-14, 2024</p>
                <p>Location: Riverside Tennis Club</p>
            </div>

            <div class="event-card">
                <h3>Autumn Doubles League</h3>
                <p>Date: September 5 - November 30, 2024</p>
                <p>Location: Downtown Sports Complex</p>
            </div>
        </div>

        <div class="leaderboard-container">
            <h2>Current Leaderboard</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Player Name</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Alice Smith</td>
                        <td>1500</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Bob Johnson</td>
                        <td>1400</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Charlie Lee</td>
                        <td>1300</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php
        renderFooter();
    ?>

    <script src="./js/slides.js"></script>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>