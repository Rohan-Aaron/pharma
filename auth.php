<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Optional: Check user role for specific pages
function requireRole($requiredRole) {
    if ($_SESSION['role'] !== $requiredRole) {
        header("Location: unauthorized.php");
        exit();
    }
}
?>