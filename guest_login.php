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
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare statement to prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM Guests WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $guest = $result->fetch_assoc();
        if ($password == $guest['password']) {
            $_SESSION['guest_id'] = $guest['guest_id'];
            header("Location: browse_rooms.php");
        } else {
            echo "Invalid email or password. Please try again.";
        }
    } else {
        echo "Guest not found. Please try again.";
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
    <title>Guest Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 style="text-align:center;">Guest Login</h1>
    <nav style="text-align:center;">
        <a href="index.php">Exit to Home</a>
    </nav><hr>
    <div class="main-container">
    <form action="" method="POST">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
        <p>Contact: 063 461 3389 | Email: <a href="mailto:paradisehotel@pdh.ac.za" style="color: #fff;">paradisehotel@pdh.ac.za</a></p>
    </footer>
</body>
</html>