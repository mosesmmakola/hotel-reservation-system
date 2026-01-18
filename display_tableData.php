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

// Check if a table name is passed as a GET parameter
if (isset($_GET['table_name'])) {
    $table_name = $_GET['table_name'];

    // Fetch all data from the selected table
    $sql = "SELECT * FROM " . $table_name;
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) >= 0){
    echo "<header>
        <h2>Data for Table: " . $table_name . "</h2>
        <nav>
            <a href='manage_tables.php'>Go back to Table Management</a>
        </nav>
    </header>";
    echo "<table border='1'>";
    
        // Fetch and display column headers
        $columns = mysqli_fetch_fields($result);
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<th>" . $column->name . "</th>";
        }
        echo "<th>Edit</th><th>Delete</th>"; // Extra columns for Edit and Delete links
        echo "</tr>";

        // Fetch and display rows of data
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            foreach ($row as $column_name => $data) {
                echo "<td>" . $data . "</td>";
            }
            // Add an Edit link and a Delete link
            $primary_key_value = reset($row);  // Assuming first column is the primary key
            echo "<td><a href='edit_tableData.php?table_name=" . urlencode($table_name) . "&pk_value=" . urlencode($primary_key_value) . "'>Edit</a></td>";
            echo "<td><a href='delete_tableData.php?table_name=" . urlencode($table_name) . "&pk_value=" . urlencode($primary_key_value) . "' onclick=\"return confirm('Are you sure you want to delete this row?');\">Delete</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No data found or table doesn't exist. <a href='manage_tables.php'>Go back to Table Management</a>";
    }
} else {
    echo "No table selected. <a href='manage_tables.php'>Go back</a>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Table Data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-container">
<!-- Form to add data to a table -->
<form action="insert_data.php?table_name=<?php echo urlencode($table_name); ?>" method="post">
    <h2>Add Data to Table</h2>
    <button type="submit" name="get_columns">Do Data Insertion</button>
</form>
<hr>
<form action="search_tableData.php?table_name=<?php echo urlencode($table_name); ?>" method="post">
    <h2>Search Table Data</h2>
    <input type="text" name="column_name" placeholder="Column Name" required>
    <input type="text" name="search_value" placeholder="Search Value" required>
    <button type="submit" name="search_data">Search Data</button>
</form>
</div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>