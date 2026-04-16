<?php
session_start();
?>

<?php
  // pageFormat.php - shared layout wrapper
  function renderHeader($pageTitle = "Court Connect") {
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/style.css">
  </head>

  <body>
    <!-- CONTACT BAR (STICKY TOP) -->
    <div class="contact-bar">
      <div class="contact-left">
        📞 (123) 456-7890 | ✉ info@courtconnect.com
      </div>
      <div class="contact-right">
        Follow us: Instagram | Facebook
      </div>
    </div>

    <!-- MAIN HEADER (STICKY BELOW CONTACT BAR) -->
    <header class="main-header">
      <div class="logo-title">
        <img src="img/placeholder.jpg" alt="Court Connect Logo" class="logo">
        <span class="site-title">Court Connect</span>
      </div>

      <nav class="nav-tabs">
        <a href="dashboard.php" class="<?php echo ($pageTitle == 'Dashboard') ? 'active' : ''; ?>">Dashboard</a>
        <a href="events.php" class="<?php echo ($pageTitle == 'Events') ? 'active' : ''; ?>">Events</a>
        <a href="leaderboard.php" class="<?php echo ($pageTitle == 'Leaderboard') ? 'active' : ''; ?>">Leaderboard</a>
        <a href="register.php" class="<?php echo ($pageTitle == 'Register') ? 'active' : ''; ?>">Register</a>
        <a href="login.php" class="<?php echo ($pageTitle == 'Login') ? 'active' : ''; ?>">Login</a>
      </nav>
    </header>

    <!-- PAGE CONTENT WRAPPER -->
    <main class="page-content">
      <?php
        }

        function renderFooter() {
      ?>
    </main>

    <footer class="footer">
        <p>© <?php echo date("Y"); ?> Court Connect. All rights reserved.</p>
    </footer>

    <script src="js/script.js"></script>
  </body>
</html>

<?php
}
?>