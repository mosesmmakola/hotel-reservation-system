<?php
session_start();

// Ensure the user has admin/staff role
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff') {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hotel_reservation_system');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "<p>Sorry, we are experiencing technical issues. Please try again later.</p>";
    exit();
}

// Check in guest
if (isset($_POST['check_in'])) {
    $reservation_id = $_POST['reservation_id'];
    $room_id = $_POST['room_id'];

    // Mark as checked in and make the room unavailable
    $sql = "UPDATE reservations SET checked_in = 1 WHERE reservation_id = $reservation_id";
    $conn->query($sql);

    $sql = "UPDATE rooms SET availability = 'checked-in' WHERE room_id = $room_id";
    $conn->query($sql);

    echo "Guest checked in successfully!";
}

// Check out guest
if (isset($_POST['check_out'])) {
    $reservation_id = $_POST['reservation_id'];
    $room_id = $_POST['room_id'];

    // Mark as checked out and make the room available again
    $sql = "UPDATE reservations SET checked_out = 1 WHERE reservation_id = $reservation_id";
    $conn->query($sql);

    $sql = "UPDATE rooms SET availability = 'available' WHERE room_id = $room_id";
    $conn->query($sql);

    echo "Guest checked out successfully!";
}

// Fetch bookings where the guest hasn't checked in yet or is currently checked in
$bookings = $conn->query("SELECT reservations.reservation_id, reservations.guest_id, reservations.room_id, reservations.check_in_date, 
                                 reservations.check_out_date, reservations.checked_in, reservations.checked_out, 
                                 guests.first_name AS first_name, guests.last_name AS last_name, rooms.room_id 
                          FROM reservations 
                          JOIN guests ON reservations.guest_id = guests.guest_id 
                          JOIN rooms ON reservations.room_id = rooms.room_id 
                          WHERE reservations.checked_in = 0 OR reservations.checked_in = 1 AND reservations.checked_out = 0");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Check-in Management</h1>
        <nav>
            <a href="admin_dashboard.php">Back to Dashboard</a>
            <a href="available_rooms.php">Available Rooms</a>
        </nav>
    </header>
    <table>
        <tr>
            <th>Reservation ID</th>
            <th>Room ID</th>
            <th>Guest Name</th>
            <th>Check-in Date</th>
            <th>Check-out Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while ($booking = $bookings->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $booking['reservation_id']; ?></td>
            <td><?php echo $booking['room_id']; ?></td>
            <td><?php echo $booking['first_name'] . " " . $booking['last_name']; ?></td>
            <td><?php echo $booking['check_in_date']; ?></td>
            <td><?php echo $booking['check_out_date']; ?></td>
            <td>
                <?php 
                if ($booking['checked_in'] == 0) {
                    echo "Not checked in";
                } elseif ($booking['checked_out'] == 0) {
                    echo "Checked in";
                } else {
                    echo "Checked out";
                }
                ?>
            </td>
            <td>
                <!-- Check-in button -->
                <?php if ($booking['checked_in'] == 0) { ?>
                    <form action = "" method="POST">
                        <input type="hidden" name="reservation_id" value="<?php echo $booking['reservation_id']; ?>">
                        <input type="hidden" name="room_id" value="<?php echo $booking['room_id']; ?>">
                        <button type="submit" name="check_in">Check In</button>
                    </form>
                <?php } ?>
                
                <!-- Check-out button -->
                <?php if ($booking['checked_in'] == 1 && $booking['checked_out'] == 0) { ?>
                    <form action="" method="POST">
                        <input type="hidden" name="reservation_id" value="<?php echo $booking['reservation_id']; ?>">
                        <input type="hidden" name="room_id" value="<?php echo $booking['room_id']; ?>">
                        <button type="submit" name="check_out">Check Out</button>
                    </form>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>
    <div class="main2-container"></div>
    <div class="main2-container"></div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>