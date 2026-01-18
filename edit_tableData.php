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
    $table_name = mysqli_real_escape_string($conn, $_GET['table_name']);
    $pk_value = mysqli_real_escape_string($conn, $_GET['pk_value']);

    // Fetch primary key column (assume it's the first column)
    $sql = "SHOW KEYS FROM `$table_name` WHERE Key_name = 'PRIMARY'";
    $primary_key_result = mysqli_query($conn, $sql);
    if ($primary_key_result && $primary_key_row = mysqli_fetch_assoc($primary_key_result)) {
        $primary_key_column = $primary_key_row['Column_name'];

        // Fetch the row data to edit
        $sql = "SELECT * FROM `$table_name` WHERE `$primary_key_column` = '" . $pk_value . "'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);

        // Fetch column structure
        $columns_query = "SHOW COLUMNS FROM `$table_name`";
        $columns_result = mysqli_query($conn, $columns_query);

        if ($row && $columns_result) {
            // Form to edit data
            echo "<header>
            <h2>Edit Data for Table: " . $table_name . "</h2>
    <nav>
        <a href='display_tableData.php?table_name=" . urlencode($table_name) . "'>Back to Table Data</a>
    </nav>
    </header>
    <div class='main-container'>
    <form action='' method='POST'>";

            // Loop through the table columns and create inputs based on column types
            while ($column = mysqli_fetch_assoc($columns_result)) {
                $column_name = $column['Field'];
                $column_type = $column['Type'];
                $is_nullable = $column['Null'] == 'YES';
                $value = isset($row[$column_name]) ? $row[$column_name] : '';
        
                echo "<label for='$column_name'>$column_name:</label>";
        
                // Generate appropriate input type based on column type
                if (strpos($column_type, 'int') !== false) {
                    echo "<input type='number' name='data[$column_name]' value='" . htmlspecialchars($value) . "' placeholder='Enter value for $column_name'" . ($is_nullable ? "" : " required") . "><br><br>";
                } elseif (strpos($column_type, 'varchar') !== false || strpos($column_type, 'text') !== false) {
                    echo "<input type='text' name='data[$column_name]' value='" . htmlspecialchars($value) . "' placeholder='Enter value for $column_name'" . ($is_nullable ? "" : " required") . "><br><br>";
                } elseif (strpos($column_type, 'date') !== false) {
                    echo "<input type='date' name='data[$column_name]' value='" . htmlspecialchars($value) . "' placeholder='Enter date for $column_name'" . ($is_nullable ? "" : " required") . "><br><br>";
                } elseif (strpos($column_type, 'enum') !== false) {
                    preg_match("/enum\((.*)\)/", $column_type, $matches);
                    $enum_values = explode(",", str_replace("'", "", $matches[1]));
                    echo "<select name='data[$column_name]'>";
                    foreach ($enum_values as $enum_value) {
                        $selected = $value == $enum_value ? "selected" : "";
                        echo "<option value='$enum_value' $selected>$enum_value</option>";
                    }
                    echo "</select><br><br>";
                } elseif (strpos($column_type, 'tinyint(1)') !== false) {
                    $checked = $value == '1' ? "checked" : "";
                    echo "<input type='checkbox' name='data[$column_name]' value='1' $checked><br><br>";
                } elseif (strpos($column_type, 'decimal') !== false || strpos($column_type, 'float') !== false || strpos($column_type, 'double') !== false) {
                    echo "<input type='number' step='0.01' name='data[$column_name]' value='" . htmlspecialchars($value) . "' placeholder='Enter value for $column_name'" . ($is_nullable ? "" : " required") . "><br><br>";
                } else {
                    echo "<input type='text' name='data[$column_name]' value='" . htmlspecialchars($value) . "' placeholder='Enter value for $column_name'" . ($is_nullable ? "" : " required") . "><br><br>";
                }
            }
        
            echo "<input type='hidden' name='table_name' value='" . htmlspecialchars($table_name) . "'>";
            echo "<input type='hidden' name='primary_key' value='" . htmlspecialchars($primary_key_column) . "'>";
            echo "<input type='hidden' name='pk_value' value='" . htmlspecialchars($pk_value) . "'>";
            echo "<button type='submit' name='save_changes'>Save Changes</button>";
            echo "</form>
            </div>";
        } else {
            echo "Row or columns not found.";
        }
    } else {
        echo "Primary key not found.";
    }   
}

// Handle form submission to update the table
if (isset($_POST['save_changes'])) {
    $table_name = mysqli_real_escape_string($conn, $_POST['table_name']);
    $primary_key_column = mysqli_real_escape_string($conn, $_POST['primary_key']);
    $pk_value = mysqli_real_escape_string($conn, $_POST['pk_value']);
    $data = $_POST['data'];

    // Construct the update query
    $update_parts = [];
    foreach ($data as $column_name => $value) {
        $update_parts[] = "`" . mysqli_real_escape_string($conn, $column_name) . "` = '" . mysqli_real_escape_string($conn, $value) . "'";
    }
    $update_query = "UPDATE `$table_name` SET " . implode(', ', $update_parts) . " WHERE `$primary_key_column` = '" . $pk_value . "'";

    
    if (mysqli_query($conn, $update_query)) {
        $notification = "Update Successful. You will be redirected in 5 seconds.";
        header("refresh:5;url=display_tableData.php?table_name=" . urlencode($table_name)); // Redirect after 5 seconds
    } else {
        $notification = "Error: updating data unsuccessful ";
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit table data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class='main2-container'>
        <!-- Display notification at the top -->
        <?php if (!empty($notification)): ?>
            <div class='notification' style='color: green; font-weight: bold;'>
                <?php echo $notification; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="main2-container"></div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>