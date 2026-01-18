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

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_table'])) {
        header("Location: create_table.php");
        exit();
    }

    if (isset($_POST['delete_table'])) {
    $table_name = mysqli_real_escape_string($conn, $_POST['table_name']);
    $sql = "DROP TABLE `$table_name`";
    
    try {
        if (mysqli_query($conn, $sql)) {
            echo "Table '$table_name' deleted successfully!";
        } else {
            // Check for specific errors like parent table violations
            $error_code = mysqli_errno($conn);
            if ($error_code == 1451) {
                // Error code 1451 indicates a foreign key constraint violation (i.e., parent table)
                echo "Error: Deletion of parent table not allowed due to existing foreign key constraints.";
            } else {
                // General error message
                echo "Error: Deleting table unsuccessful.";
            }
        }
    } catch (Exception $e) {
        echo "Exception caught: " . $e->getMessage();
    }
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tables</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Manage Tables</h1>
        <nav>
            <a href="admin_dashboard.php">Back to Dashboard</a>
        </nav>
    </header>
    <div class="main-container">
<!-- Form to create a new table -->
<form action = "" method="post">
    <h2>Create a New Table</h2>
    <button type="submit" name="create_table">Create Table</button>
</form>
<hr>
<?php
// Fetch list of tables from the database
$sql = "SHOW TABLES";
$result = mysqli_query($conn, $sql);

?>
<!-- Section to display the list of tables -->
<h2>Existing Tables</h2>
<?php
if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10' cellspacing='0'>";
    echo "<tr>
            <th>Table Name</th>
            <th>Browse</th>
            <th>Add Column</th>
            <th>Delete</th>
          </tr>";
    
    while ($row = mysqli_fetch_row($result)) {
        $table_name = $row[0];
        
        // Display each table name and options in the table
        echo "<tr>";
        echo "<td>" . htmlspecialchars($table_name) . "</td>"; // Table Name
        
        // Browse option - Link to display table data
        echo "<td><a href='display_tableData.php?table_name=" . urlencode($table_name) . "'>Browse</a></td>";
        
        // Add Column option - Link to add column page
        echo "<td><a href='add_column.php?table_name=" . urlencode($table_name) . "'>Add Column</a></td>";
        
        // Delete option
        echo "<td>
                <form action='' method='POST' style='display:inline;'>
                    <input type='hidden' name='table_name' value='" . htmlspecialchars($table_name) . "'>
                    <button type='submit' name='delete_table'>Delete</button>
                </form>
              </td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No tables found.";
}
?>

</div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>