<?php

session_start();

// Clear all admin session data
unset($_SESSION["admin_id"]);
unset($_SESSION["admin_name"]);
unset($_SESSION["admin_email"]);

// Redirect to admin login
header("Location: admin_login.php");
exit;

?>