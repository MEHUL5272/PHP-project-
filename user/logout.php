<?php

session_start();

// Clear all session data
$_SESSION = [];

// Destroy session
session_destroy();

// Redirect to main home page
header("Location: ../index.php");
exit;

?>