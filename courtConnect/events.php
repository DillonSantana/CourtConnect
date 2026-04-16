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
        renderHeader("Events");
    ?>

    <h1>Upcoming Events</h1>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Participants</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Spring Tennis Tournament</td>
                    <td>April 15, 2024</td>
                    <td>Central Park Courts</td>
                    <td>32 Players</td>
                </tr>
                <tr>
                    <td>Summer Tennis Camp</td>
                    <td>June 10-14, 2024</td>
                    <td>Riverside Tennis Club</td>
                    <td>20 Players</td>
                </tr>
                <tr>
                    <td>Autumn Doubles League</td>
                    <td>September 5 - November 30, 2024</td>
                    <td>Downtown Sports Complex</td>
                    <td>16 Teams</td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php
        renderFooter();
    ?>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>