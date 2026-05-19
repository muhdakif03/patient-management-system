<?php
session_start();

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit(0);
}

// 2. Check Role (Enforce Access Control)
if (isset($required_role) && $_SESSION['role'] != $required_role) {
    // If a patient tries to access admin page, send them back to login
    header("Location: ../login.php");
    exit(0);
}
?>