<?php
session_start();

// Ensure the user is logged in and has admin/staff role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff') {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Welcome to the Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="checkin_management.php">Check-in Management</a></li>
                <li><a href="manage_tables.php">Manage Tables</a></li>
                <li><a href="billing.php">Billing & Payments</a></li>
                <li><a href="reporting.php">View Reports</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="main-container">
    <?php
    // Display access denied message if it exists
    if (isset($_SESSION['error_message'])) {
        echo "<p style='color:red;'>" . $_SESSION['error_message'] . "</p>";
        unset($_SESSION['error_message']); // Clear the message after displaying it
    }
    ?>
    <section class="dashboard-options">
        <h2>Dashboard Options</h2>
        <hr>
            <div class="dashboard-item">
                <h3>Check-in Management</h3>
                <p>View and manage check-ins and check-outs.</p>
                <a href="checkin_management.php" class="btn">Go to Check-in Management</a>
            </div><br>
            <div class="dashboard-item">
                <h3>Table Management</h3>
                <p>Create, update, or delete tables and manage their data.</p>
                <a href="manage_tables.php" class="btn">Go to Table Management</a>
            </div><br>
            <div class="dashboard-item">
                <h3>Billing & Payments</h3>
                <p>Manage billing information and payment records for guests.</p>
                <a href="billing.php" class="btn">Go to Billing</a>
            </div><br>
            <div class="dashboard-item">
                <h3>Reports</h3>
                <p>View occupancy and revenue reports for the hotel.</p>
                <a href="reporting.php" class="btn">View Reports</a>
            </div>
        </div>
    </section>
    </div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>
