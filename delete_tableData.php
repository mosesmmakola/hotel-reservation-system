<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error_message'] = "Access Denied: You don't have permission to access the page.";
    header("refresh:3;url=admin_dashboard.php"); // Redirect after 3 seconds
    echo "Access Denied: You will be redirected to the dashboard.";
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hotel_reservation_system');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "<p>Sorry, we are experiencing technical issues. Please try again later.</p>";
    exit();
}

// Check if table name and primary key value are passed
if (isset($_GET['table_name']) && isset($_GET['pk_value'])) {
    $table_name = $_GET['table_name'];
    $pk_value = $_GET['pk_value'];

    // Fetch primary key column (assume it's the first column)
    $sql = "SHOW KEYS FROM " . $table_name . " WHERE Key_name = 'PRIMARY'";
    $primary_key_result = mysqli_query($conn, $sql);
    $primary_key_column = mysqli_fetch_assoc($primary_key_result)['Column_name'];

    // Delete the row from the table
    $delete_query = "DELETE FROM " . $table_name . " WHERE " . $primary_key_column . " = '" . $pk_value . "'";

    if (mysqli_query($conn, $delete_query)) {
        header("refresh:3;url='display_tableData.php?table_name=" . urlencode($table_name) . ""); // Redirect after 3 seconds
        echo "<b>Row deleted successfully. You will be redirected back to table data.</b><br>";
    } else {
        echo "Error: deleting row unsuccessful ";
        exit();
    }
} else {
    echo "Invalid request.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete table data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <footer style= "position:fixed;">
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>