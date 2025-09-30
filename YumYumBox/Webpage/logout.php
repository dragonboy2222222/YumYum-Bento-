<?php
session_start();
session_unset();  // remove all session variables
session_destroy(); // destroy the session

// Redirect to homepage (index.php)
header("Location: index.php");
exit;
