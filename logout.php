<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'staff') {
            $redirectPage = "guest_login.php";
        } elseif ($_SESSION['role'] == 'guest') {
            $redirectPage = "index.php";
        }
    }
    // Clear session
    session_unset();
    session_destroy();
    // Redirect to the appropriate page
    header("Location: $redirectPage");
    exit();
} else {
    // If the user is not logged in, redirect to a generic page
    header("Location: index.php");
    exit();
}
?>
