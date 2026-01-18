<?php
session_start();

if (!isset($_SESSION['reservation_id'])) {
    die("No reservation found.");
}

$reservation_id = $_SESSION['reservation_id'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hotel_reservation_system');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "<p>Sorry, we are experiencing technical issues. Please try again later.</p>";
    exit();
}

// Query to fetch reservation details using the reservation ID
$stmt = $conn->prepare("SELECT * FROM reservations WHERE reservation_id = ?");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$reservation_result = $stmt->get_result();

if ($reservation_result && $reservation = $reservation_result->fetch_assoc()) {
    // Reservation details retrieved
    $guest_id = $reservation['guest_id'];
    // Query to fetch guest details using the guest ID
    $guest_sql = "SELECT  email FROM guests WHERE guest_id = $guest_id";
    $guest_result = $conn->query($guest_sql);
    
    if ($guest_result && $guest = $guest_result->fetch_assoc()) {
        // Guest email retrieved
        $email = $guest['email'];

        /* Code to send reservation details to the above email.
        <h2>Reservation ID: <?php echo $reservation['reservation_id']; ?></h2>
            <p>Guest Name: <?php echo $first_name . " " . $last_name; ?></p>
            <p>Room Number: <?php echo $reservation['room_id']; ?></p>
            <p>Check-in Date: <?php echo $reservation['check_in_date']; ?></p>
            <p>Check-out Date: <?php echo $reservation['check_out_date']; ?></p>
            <p>Total Price: <?php echo $reservation['total_price']; ?></p>*/
        
    } else {
        echo "<p>No guest email found for this reservation.</p>";
    }
} else {
    echo "<p>No reservation found with the provided ID.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Reservation Confirmed</h1>
        <nav>
            <a href="browse_rooms.php">Go back to Browse Rooms</a>
        </nav>
    </header>
    
    <div class="confirmation-container">
        <h2>Thank You for Booking with Paradise Hotel!</h2>
        <p>Your reservation has been successfully submitted.</p>
        <p>Details Sent to <?php echo "$email" ?></p>
        <a href="view_reservation.php?reservation_id=<?php echo urlencode($reservation_id); ?>" class="btn">View Reservation Details</a>
    </div>
    
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
        <p>Contact: 063 461 3389 | Email: <a href="mailto:paradisehotel@pdh.ac.za" style="color: #fff;">paradisehotel@pdh.ac.za</a></p>
    </footer>
</body>
</html>

