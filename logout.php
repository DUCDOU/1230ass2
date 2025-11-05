<?php
require_once 'functions.php';

// Destroy the session
logout();

// Redirect to login page
header('Location: index.php');
exit;
?>