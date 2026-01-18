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

// Step 1: Get table columns and their data types based on the table name
if (isset($_POST['get_columns']) && isset($_GET['table_name'])) {
    // Get the table name from the URL
    $table_name = $_GET['table_name'];

    // SQL query to get the column names and data types for the specified table
    $query = "SHOW COLUMNS FROM $table_name";
    $result = mysqli_query($conn, $query);

    // Check if the query was successful
    if ($result) {
        // Start building the form for data insertion
        echo "<header>
                <h2>Insert Data into $table_name</h2>
                <nav>
                    <br><a href='display_tableData.php?table_name=" . urlencode($table_name) . "'>Back to Table Data</a>
                </nav>
                </header>";
                echo "<table border='1'>";
        echo "<div class='main-container'>";
        echo "<form action='' method='post'>";
        
        // Loop through each column and generate an input field based on the data type
        while ($row = mysqli_fetch_assoc($result)) {
            $column_name = $row['Field'];
            $column_type = $row['Type'];
            $is_nullable = $row['Null'] == 'YES';

            echo "<label>$column_name:</label>";

            // Generate appropriate input type based on column type
            if (strpos($column_type, 'int') !== false) {
                // Integer fields
                echo "<input type='number' name='data[$column_name]' placeholder='Enter value for $column_name'" . ($is_nullable ? "" : "") . "><br><br>";
            } elseif (strpos($column_type, 'varchar') !== false || strpos($column_type, 'text') !== false) {
                // Text fields
                echo "<input type='text' name='data[$column_name]' placeholder='Enter value for $column_name'" . ($is_nullable ? "" : " required") . "><br><br>";
            } elseif (strpos($column_type, 'date') !== false) {
                // Date fields
                echo "<input type='date' name='data[$column_name]' placeholder='Enter date for $column_name'" . ($is_nullable ? "" : " required") . "><br><br>";
            } elseif (strpos($column_type, 'enum') !== false) {
                // Enum fields: Extract enum values
                preg_match("/enum\((.*)\)/", $column_type, $matches);
                $enum_values = explode(",", str_replace("'", "", $matches[1]));
                echo "<select name='data[$column_name]'>";
                foreach ($enum_values as $value) {
                    echo "<option value='$value'>$value</option>";
                }
                echo "</select><br><br>";
            } elseif (strpos($column_type, 'tinyint(1)') !== false) {
                // Boolean fields (tinyint(1))
                echo "<input type='checkbox' name='data[$column_name]' value='1'><br><br>";
            } elseif (strpos($column_type, 'decimal') !== false || strpos($column_type, 'float') !== false || strpos($column_type, 'double') !== false) {
                // Decimal, float, or double fields
                echo "<input type='number' step='0.01' name='data[$column_name]' placeholder='Enter value for $column_name'" . ($is_nullable ? "" : " required") . "><br><br>";
            } else {
                // Default text input for any other types
                echo "<input type='text' name='data[$column_name]' placeholder='Enter value for $column_name'" . ($is_nullable ? "" : " required") . "><br><br>";
            }
        }
        
        // Hidden field to pass the table name to the next step
        echo "<input type='hidden' name='table_name' value='$table_name'>";
        
        // Submit button to insert the data
        echo "<button type='submit' name='insert_data'>Insert Data</button>";
        echo "</form>";
    } else {
        echo "Error fetching columns: " . mysqli_error($conn);
    }
    echo "</div>";
}

// Step 2: Insert the data into the table
if (isset($_POST['insert_data'])) {
    // Get the table name and data from the form submission
    $table_name = $_POST['table_name'];
    $data = $_POST['data'];

    // Prepare the SQL query for insertion
    $columns = implode(", ", array_keys($data));
    $values = implode(", ", array_map(function ($value) use ($conn) {
        // Special handling for checkbox (boolean)
        return "'" . mysqli_real_escape_string($conn, ($value === 'on') ? '1' : $value) . "'";
    }, $data));

    // SQL query to insert the data into the specified table
    $insert_query = "INSERT INTO $table_name ($columns) VALUES ($values)";

    // Execute the insert query
    if (mysqli_query($conn, $insert_query)) {
        header("refresh:3;url='display_tableData.php?table_name=" . urlencode($table_name) . ""); // Redirect after 3 seconds
        echo "<b>Data inserted successfully into $table_name. You will be redirected back to table data.</b><br>";
    } else {
        echo "Error: inserting data unsuccessful. ";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert table data</title>
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