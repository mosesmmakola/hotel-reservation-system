<?php
session_start();
$guest_id = isset($_SESSION['guest_id']) ? $_SESSION['guest_id'] : null; 
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : null;

if (!$guest_id || !$room_id) {
    echo "Invalid guest or room ID.";
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
    $check_in_date = $_POST['check_in_date'];
    $check_out_date= $_POST['check_out_date'];

    // Calculate price based on stay duration
    $stmt = $conn->prepare("SELECT price FROM Rooms WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($room = $result->fetch_assoc()) {
        $price_per_night = $room['price'];

        $total_price = $price_per_night * ((strtotime($check_out_date) - strtotime($check_in_date)) / 86400);

        // Insert into Reservations
        $stmt = $conn->prepare("INSERT INTO Reservations (guest_id, room_id, check_in_date, check_out_date, total_price) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $guest_id, $room_id, $check_in_date, $check_out_date, $total_price);
        
        if ($stmt->execute()) {
            $reservation_id = $stmt->insert_id;  // Get the last inserted ID
            $_SESSION['reservation_id'] = $reservation_id; // Save reservation_id in session
            
            $stmt = $conn->prepare("UPDATE Rooms SET availability = 'reserved' WHERE room_id = ?");
            $stmt->bind_param("i", $room_id);
            $stmt->execute();
            
            header("Location: confirmation.php");
            exit();
        } else {
            echo "Error: Reservation Unsuccessful.";
        }
    } else {
        echo "Please try again later.";
        header("Location: browse_rooms.php");
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Room</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Make a Reservation</h1>
        <nav>
            <a href="browse_rooms.php">Go back to Browse Rooms</a>
        </nav>
    </header>
    <div class="main-container">
        <form action="" method="POST">
            <label for="check_in_date">Check-in Date:</label>
            <input type="date" id="check_in_date" name="check_in_date" required>

            <label for="check_out_date">Check-out Date:</label>
            <input type="date" id="check_out_date" name="check_out_date" required>

            <button type="submit" class="btn">Reserve</button>
        </form>
    </div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
        <p>Contact: 063 461 3389 | Email: <a href="mailto:paradisehotel@pdh.ac.za" style="color: #fff;">paradisehotel@pdh.ac.za</a></p>
    </footer>
</body>
</html>
