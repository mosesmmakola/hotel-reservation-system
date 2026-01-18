<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'hotel_reservation_system');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "<p>Sorry, we are experiencing technical issues. Please try again later.</p>";
    exit();
}

// Handle form submission
$registration_success = false;
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];

    // Use prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("INSERT INTO Guests (first_name, last_name, email, password, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $phone);
    
    if ($stmt->execute()) {
        $registration_success = true;
    } else {
        $error_message = "Error: " . $stmt->error;
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
    <title>Guest Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 style="text-align:center;">Register as a Guest</h1>
    <nav style="text-align:center;">
        <a href="guest_login.php">Go back</a>
    </nav>
    <hr>
    <div class="main-container">
    
    <!-- Display success or error message -->
    <?php if ($registration_success): ?>
        <p style="color: green; text-align: center;">Registration successful. <a href='guest_login.php'>Login here</a></p>
    <?php elseif ($error_message): ?>
        <p style="color: red; text-align: center;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <!-- Registration form -->
    <form action="" method="POST">
        <div>
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>
        <div>
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required>
        </div>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="guest_login.php">Login here</a></p>
    </div>

    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
        <p>Contact: 063 461 3389 | Email: <a href="mailto:paradisehotel@pdh.ac.za" style="color: #fff;">paradisehotel@pdh.ac.za</a></p>
    </footer>
</body>
</html>