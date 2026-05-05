<?php
  if(session_status()===PHP_SESSION_NONE)
    session_start();
?>

<?php
  function renderHeader($pageTitle = "Court Connect", $logged, $register) {
    if(isset($_SESSION['role']))
        if($_SESSION['role']){
          if ($_SESSION['role']==="admin") {
             $link1="summary";
          }
          $logged="logout";
          $register="profile";
        }
      $ucLogged=ucfirst($logged);
      $ucRegister=ucfirst($register);
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
    <div class="contact-bar">
      <div class="contact-left">
        📞 (404) 345-9022 | ✉ dillsantana888@gmail.com
      </div>
      <div class="contact-right">
        Follow us: Instagram | Facebook
      </div>
    </div>
    <header class="main-header">
      <div class="logo-title">
        <img src="img/placeholder.jpg" alt="Court Connect Logo" class="logo">
        <span class="site-title">Court Connect</span>
      </div>

      <nav class="nav-tabs">
        <a href="dashboard.php" class="<?php echo ($pageTitle == 'Dashboard') ? 'active' : ''; ?>">Dashboard</a>
        <a href="events.php" class="<?php echo ($pageTitle == 'Events') ? 'active' : ''; ?>">Events</a>
        <a href="leaderboard.php" class="<?php echo ($pageTitle == 'Leaderboard') ? 'active' : ''; ?>">Leaderboard</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <a href="event_admin.php" class="<?php echo ($pageTitle == 'Event Admin') ? 'active' : ''; ?>">Admin</a>
        <?php endif; ?>
        <a href="<?php echo $register; ?>.php" class="<?php echo ($pageTitle == 'Register') ? 'active' : ''; ?>"><?php echo $ucRegister; ?></a>
        <a href="<?php echo $logged; ?>.php" class="<?php echo ($pageTitle == 'Login') ? 'active' : ''; ?>"><?php echo $ucLogged; ?></a>
      </nav>
    </header>

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