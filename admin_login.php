<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hotel_reservation_system');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "<p>Sorry, we are experiencing technical issues. Please try again later.</p>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare statement to prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($password == $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            header("Location: admin_dashboard.php");
        } else {
            echo "Invalid username or password. Please try again.";
        }
    } else {
        echo "User not found. Try again.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin/Staff Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 style="text-align:center;">Admin/Staff Login</h1>
    <nav style="text-align:center;">
        <a href="index.php">Exit to Home</a>
    </nav><hr>
    <div class="main-container">
    <form action="" method="POST">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    </div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
        <p>Contact: 063 461 3389 | Email: <a href="mailto:paradisehotel@pdh.ac.za" style="color: #fff;">paradisehotel@pdh.ac.za</a></p>
    </footer>
</body>
</html>
