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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize input
    $table_name = mysqli_real_escape_string($conn, $_POST['table_name']);
    $columns = intval($_POST['columns']);
    $column_names = $_POST['column_names'];
    $data_types = $_POST['data_types'];
    $constraints = $_POST['constraints'];
    $enum_values = $_POST['enum_values'];
    
    $primary_keys = [];
    $foreign_keys = [];

    // Start building the SQL query
    $query = "CREATE TABLE `$table_name` (";
    $column_defs = [];

    for ($i = 0; $i < $columns; $i++) {
        $column_name = mysqli_real_escape_string($conn, $column_names[$i]);
        $data_type = mysqli_real_escape_string($conn, $data_types[$i]);
    
        // Check for VARCHAR and append the length
        if ($data_type == 'VARCHAR') {
            $varchar_length = intval($_POST['varchar_length'][$i]);
            $column_def = "`$column_name` VARCHAR($varchar_length)";
        } elseif ($data_type == 'ENUM') {
            $enum_values_formatted = "'" . implode("','", explode(',', $enum_values[$i])) . "'";
            $column_def = "`$column_name` ENUM($enum_values_formatted)";
        } else {
            $column_def = "`$column_name` $data_type";
        }
        
        // Check for constraints
        if (isset($constraints[$i])) {
            foreach ($constraints[$i] as $constraint) {
                if ($constraint == 'PRIMARY KEY') {
                    $column_def .= " AUTO_INCREMENT";
                    $primary_keys[] = "`$column_name`";
                }
                if ($constraint == 'UNIQUE') {
                    $column_def .= " UNIQUE";
                }
                if ($constraint == 'NOT NULL') {
                    $column_def .= " NOT NULL";
                }
                if ($constraint == 'FOREIGN KEY') {
                    $fk_table = mysqli_real_escape_string($conn, $_POST['fk_table'][$i]);
                    $fk_column = mysqli_real_escape_string($conn, $_POST['fk_column'][$i]);
                    $foreign_keys[] = "FOREIGN KEY (`$column_name`) REFERENCES `$fk_table`(`$fk_column`)";
                }
            }
        }
        $column_defs[] = $column_def;
    }
    
    // Add primary keys to the query
    if (!empty($primary_keys)) {
        $column_defs[] = "PRIMARY KEY (" . implode(", ", $primary_keys) . ")";
    }

    // Add foreign keys to the query
    if (!empty($foreign_keys)) {
        $column_defs = array_merge($column_defs, $foreign_keys);
    }

    // Finalize the query
    $query .= implode(", ", $column_defs) . ");";

    // Execute the query and handle feedback
    if ($conn->query($query) === TRUE) {
        echo "Table '$table_name' created successfully.";
    } else {
        echo "Error: creating table unsuccessful";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Table</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Create Table</h1>
        <nav>
            <a href="manage_tables.php">Back to Table Management</a>
        </nav>
    </header>

<div class="main-container">
    <form action="" method="POST">
        <label for="table_name">Table Name:</label>
        <input type="text" id="table_name" name="table_name" required><br><br>

        <label for="columns">Number of Columns:</label>
        <input type="number" id="columns" name="columns" min="1" required><br><br>

        <div id="column-container"></div>

        <button type="button" onclick="addColumns()">Add Columns</button>
        <button type="submit">Create Table</button>
    </form>
</div>

<script>
function addColumns() {
    var columnCount = document.getElementById('columns').value;
    var container = document.getElementById('column-container');
    container.innerHTML = ''; // Clear previous input fields

    for (let i = 0; i < columnCount; i++) {
        let columnConfig = `
            <div class="column-config">
                <h3>Column ${i + 1}</h3>
                <label>Column Name:</label>
                <input type="text" name="column_names[]" required><br>
                
                <label>Data Type:</label>
                <select name="data_types[]" onchange="toggleDataType(${i}, this.value)" required>
                    <option value="INT">Integer</option>
                    <option value="VARCHAR">Characters</option>
                    <option value="DECIMAL">Decimal</option>
                    <option value="BOOLEAN">Boolean</option>
                    <option value="ENUM">Enum</option>
                    <option value="DATE">DATE</option>
                </select><br>

                <div id="varchar-length-${i}" style="display:none;">
                    <label>Number of Characters (for VARCHAR):</label>
                    <input type="number" name="varchar_length[]" min="1" max="255" value="50"><br>
                </div>

                <div id="enum-values-${i}" style="display:none;">
                    <label>Enum Values (comma separated):</label>
                    <input type="text" name="enum_values[]" placeholder="value1, value2, value3"><br>
                </div>

                <div class="constraints-group">
                    <input type="checkbox" name="constraints[${i}][]" value="PRIMARY KEY">
                    <label>PRIMARY KEY</label>
                </div>

                <div class="constraints-group">
                    <input type="checkbox" name="constraints[${i}][]" value="UNIQUE">
                    <label>UNIQUE</label>
                </div>

                <div class="constraints-group">
                    <input type="checkbox" name="constraints[${i}][]" value="NOT NULL">
                    <label>NOT NULL</label>
                </div>
    
                <div class="constraints-group">
                    <input type="checkbox" name="constraints[${i}][]" value="FOREIGN KEY" onchange="toggleFK(${i})">
                    <label>FOREIGN KEY</label>
                </div>

                <div id="fk-fields-${i}" style="display:none;">
                    <label>Referenced Table: </label>
                    <input type="text" name="fk_table[]" placeholder="Enter reference table"><br>
                    <label>Referenced Column: </label>
                    <input type="text" name="fk_column[]" placeholder="Enter reference column"><br>
                </div>
            </div>
        `;
        container.innerHTML += columnConfig;
    }
}

function toggleDataType(index, value) {
    var varcharLengthField = document.getElementById('varchar-length-' + index);
    var enumField = document.getElementById('enum-values-' + index);

    if (value == 'VARCHAR') {
        varcharLengthField.style.display = 'block';
        enumField.style.display = 'none';
    } else if (value == 'ENUM') {
        enumField.style.display = 'block';
        varcharLengthField.style.display = 'none';
    } else {
        varcharLengthField.style.display = 'none';
        enumField.style.display = 'none';
    }
}

function toggleFK(index) {
    var fkFields = document.getElementById('fk-fields-' + index);
    fkFields.style.display = fkFields.style.display === 'none' ? 'block' : 'none';
}
</script>

    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>
