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

// Get the table name from the URL
if (isset($_GET['table_name'])) {
    $table_name = mysqli_real_escape_string($conn, $_GET['table_name']);
} else {
    die("No table selected!");
}

// Handle form submission for adding a column
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $column_name = mysqli_real_escape_string($conn, $_POST['column_name']);
    $data_type = mysqli_real_escape_string($conn, $_POST['data_type']);
    $varchar_length = isset($_POST['varchar_length']) ? intval($_POST['varchar_length']) : '';
    $enum_values = isset($_POST['enum_values']) ? mysqli_real_escape_string($conn, $_POST['enum_values']) : '';
    $constraints = isset($_POST['constraints']) ? $_POST['constraints'] : [];

    // Build the column definition
    if ($data_type == 'VARCHAR') {
        $column_definition = "$data_type($varchar_length)";
    } elseif ($data_type == 'ENUM') {
        // Ensure ENUM values are in the correct format
        $enum_values = "'" . implode("','", explode(',', $enum_values)) . "'";
        $column_definition = "ENUM($enum_values)";
    } else {
        $column_definition = $data_type;
    }

    // Append constraints to the column definition
    foreach ($constraints as $constraint) {
        if ($constraint == 'PRIMARY KEY') {
            $column_definition .= " PRIMARY KEY AUTO_INCREMENT";
        } elseif ($constraint == 'UNIQUE') {
            $column_definition .= " UNIQUE";
        } elseif ($constraint == 'NOT NULL') {
            $column_definition .= " NOT NULL";
        } elseif ($constraint == 'FOREIGN KEY') {
            $fk_reference_table = mysqli_real_escape_string($conn, $_POST['fk_reference_table']);
            $fk_reference_column = mysqli_real_escape_string($conn, $_POST['fk_reference_column']);

            // Add foreign key constraint later in the query
            $foreign_key_query = "ALTER TABLE `$table_name` ADD CONSTRAINT `fk_$column_name` FOREIGN KEY (`$column_name`) REFERENCES `$fk_reference_table`(`$fk_reference_column`)";
        }
    }

    // SQL query to add a new column
    $add_column_query = "ALTER TABLE `$table_name` ADD `$column_name` $column_definition";

    // Execute the query for adding the column
    if (mysqli_query($conn, $add_column_query)) {
        echo "Column '$column_name' added successfully to table '$table_name'.";

        // Execute the foreign key constraint query if applicable
        if (isset($foreign_key_query)) {
            if (mysqli_query($conn, $foreign_key_query)) {
                echo " Foreign key constraint added successfully.";
            } else {
                error_log(" Error adding foreign key: " . mysqli_error($conn));
                echo " Error adding foreign key: ";
            }
        }
    } else {
        error_log("Error adding column " . mysqli_error($conn));
        echo "Error adding column ";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Column</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h2>Add Column to Table: <?php echo $table_name; ?></h2>
        <nav>
            <a href="manage_tables.php">Back to Table Management</a>
        </nav>
    </header>

    <div class="main-container">
        <form method="POST" action="">
            <label>Column Name:</label>
            <input type="text" name="column_name" required><br>

            <label>Data Type:</label>
            <select name="data_type" onchange="toggleEnum(this.value)">
                <option value="INT">Integer</option>
                <option value="VARCHAR">Characters</option>
                <option value="DECIMAL">Decimal</option>
                <option value="BOOLEAN">Boolean</option>
                <option value="ENUM">Enum</option>
                <option value="DATE">DATE</option>
            </select><br>

            <div id="varchar-length" style="display:none;">
                <label>Number of Characters (for VARCHAR):</label>
                <input type="number" name="varchar_length" placeholder="50"><br>
            </div>

            <div id="enum-values" style="display:none;">
                <label>Enum Values (comma separated):</label>
                <input type="text" name="enum_values" placeholder="value1, value2, value3"><br>
            </div>

            <label>Constraints:</label><br>
            <input type="checkbox" name="constraints[]" value="PRIMARY KEY"> Primary Key<br>
            <input type="checkbox" name="constraints[]" value="UNIQUE"> Unique<br>
            <input type="checkbox" name="constraints[]" value="NOT NULL"> Not Null<br>
            <input type="checkbox" name="constraints[]" value="FOREIGN KEY" onchange="toggleFK()"> Foreign Key<br>

            <div id="fk-options" style="display:none;">
                <label>Reference Table:</label>
                <input type="text" name="fk_reference_table" placeholder="Enter reference table"><br>

                <label>Reference Column:</label>
                <input type="text" name="fk_reference_column" placeholder="Enter reference column"><br>
            </div>

            <button type="submit">Add Column</button>
        </form>
    </div>

    <script>
    function toggleEnum(value) {
        var varcharField = document.getElementById('varchar-length');
        var enumField = document.getElementById('enum-values');
        
        if (value == 'VARCHAR') {
            varcharField.style.display = 'block';
            enumField.style.display = 'none';
        } else if (value == 'ENUM') {
            varcharField.style.display = 'none';
            enumField.style.display = 'block';
        } else {
            varcharField.style.display = 'none';
            enumField.style.display = 'none';
        }
    }

    function toggleFK() {
        var fkOptions = document.getElementById('fk-options');
        if (fkOptions.style.display === 'none') {
            fkOptions.style.display = 'block';
        } else {
            fkOptions.style.display = 'none';
        }
    }
    </script>

    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>

