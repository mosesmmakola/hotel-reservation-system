<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hotel_reservation_system');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "<p>Sorry, we are experiencing technical issues. Please try again later.</p>";
    exit();
}

// Check if the reservation ID is set in the URL
if (isset($_GET['reservation_id'])) {
    $reservation_id = intval($_GET['reservation_id']);
    
    // Query to fetch reservation details using the reservation ID
    $stmt = $conn->prepare("SELECT * FROM reservations WHERE reservation_id = ?");
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $reservation_result = $stmt->get_result();

    if ($reservation_result && $reservation = $reservation_result->fetch_assoc()) {
        // Reservation details retrieved
        $guest_id = $reservation['guest_id'];
        
        // Query to fetch guest details using the guest ID
        $guest_sql = "SELECT first_name, last_name FROM guests WHERE guest_id = $guest_id";
        $guest_result = $conn->query($guest_sql);
        
        if ($guest_result && $guest = $guest_result->fetch_assoc()) {
            $first_name = $guest['first_name'];
            $last_name = $guest['last_name'];
        } else {
            echo "<p>No guest details found for this reservation.</p>";
        }
    } else {
        echo "<p>No reservation found with the provided ID.</p>";
    }

    $stmt->close();
} else {
    echo "<p>Invalid reservation ID.</p>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Reservation Details</h1>
        <nav>
            <a href="browse_rooms.php">Go back to Browse Rooms</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="confirmation-container">
        <?php if (isset($reservation)): ?>
            <h2>Reservation ID: <?php echo $reservation['reservation_id']; ?></h2>
            <p>Guest Name: <?php echo $first_name . " " . $last_name; ?></p>
            <p>Room Number: <?php echo $reservation['room_id']; ?></p>
            <p>Check-in Date: <?php echo $reservation['check_in_date']; ?></p>
            <p>Check-out Date: <?php echo $reservation['check_out_date']; ?></p>
            <p>Total Price: <?php echo $reservation['total_price']; ?></p>
        <?php else: ?>
            <p>No reservation details available.</p>
        <?php endif; ?>
    </div>
    <footer>
            <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
            <p>Contact: 063 461 3389 | Email: <a href="mailto:paradisehotel@pdh.ac.za" style="color: #fff;">paradisehotel@pdh.ac.za</a></p>
    </footer>
</body>
</html>