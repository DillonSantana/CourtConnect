<?php
  session_start();
  $_SESSION = array();  // Clear all session variables
  setcookie(session_name(), '', time() - 254100, '/');  // Delete the session cookie
  session_destroy();    // Destroy the session
  header("Location: ./login.php");
?>
