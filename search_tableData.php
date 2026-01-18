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

// Check if the form is submitted
if (isset($_POST['search_data']) && isset($_GET['table_name'])) {
    $table_name = $_GET['table_name'];
    $column_name = $_POST['column_name'];
    $search_value = $_POST['search_value'];

    // Check if the table and column exist
    $table_check_query = "SHOW TABLES LIKE '$table_name'";
    $table_check_result = mysqli_query($conn, $table_check_query);
    
    if (mysqli_num_rows($table_check_result) > 0) {
        // Verify that the column exists in the table
        $column_check_query = "SHOW COLUMNS FROM `$table_name` LIKE '$column_name'";
        $column_check_result = mysqli_query($conn, $column_check_query);
        
        if (mysqli_num_rows($column_check_result) > 0) {
            // If table and column exist, search for the specific value
            $search_query = "SELECT * FROM `$table_name` WHERE `$column_name` LIKE '%$search_value%'";
            $search_result = mysqli_query($conn, $search_query);

            if ($search_result && mysqli_num_rows($search_result) > 0) {
                echo "<header>
                <h2>Search Results for '$search_value' in '$column_name' of '$table_name'</h2>
                <nav>
                    <a href='display_tableData.php?table_name=" . urlencode($table_name) . "'>Go Back</a>
                </nav>
                </header>";
                echo "<table border='1'>";

                // Display table column headers
                $columns = mysqli_fetch_fields($search_result);
                echo "<tr>";
                foreach ($columns as $column) {
                    echo "<th>" . $column->name . "</th>";
                }
                echo "<th>Edit</th><th>Delete</th>"; 
                echo "</tr>";

                // Display matching rows
                while ($row = mysqli_fetch_assoc($search_result)) {
                    echo "<tr>";
                    foreach ($row as $column_name => $data) {
                        echo "<td>" . $data . "</td>";
                    }
                    // Provide Edit and Delete options
                    $primary_key_value = reset($row);  // Assuming the first column is the primary key
                    echo "<td><a href='edit_tableData.php?table_name=" . urlencode($table_name) . "&pk_value=" . urlencode($primary_key_value) . "'>Edit</a></td>";
                    echo "<td><a href='delete_tableData.php?table_name=" . urlencode($table_name) . "&pk_value=" . urlencode($primary_key_value) . "' onclick=\"return confirm('Are you sure you want to delete this row?');\">Delete</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<div class='main2-container'></div>";
            } else {
                echo "No results found for '$search_value' in column '$column_name'. <a href='display_tableData.php?table_name=" . urlencode($table_name) . "'>Back to Table Data</a>";
            }
        } else {
            echo "The column '$column_name' does not exist in the table '$table_name'. <a href='display_tableData.php?table_name=" . urlencode($table_name) . "'>Back to Table Data</a>";
        }
    }
} else {
    echo "Invalid request. <a href='display_tableData.php?table_name=" . urlencode($table_name) . "'>Back to Table Data</a>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search table data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main2-container"></div>
    <div class="main2-container"></div>
    <div class="main2-container"></div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>