<?php
session_start();

// Database connection 
$conn = new mysqli('localhost', 'root', '', 'hotel_reservation_system');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo "<p>Sorry, we are experiencing technical issues. Please try again later.</p>";
    exit();
}

$sql = "SELECT * FROM Rooms WHERE availability = 'available'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Rooms</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Browse Available Rooms</h1>
        <nav>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="main2-container">
    <section>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="room">
                <img src="images/<?php echo $row['photo']; ?>" alt="<?php echo $row['room_type']; ?>">
                <h2>Room <?php echo $row['room_id']; ?></h2>
                <p>Price: R<?php echo $row['price']; ?></p>
                <a href="reserve.php?room_id=<?php echo $row['room_id']; ?>" class="btn">Reserve Now</a>
                <hr><hr>
            </div>
        <?php endwhile; ?>
    </section>
    </div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
        <p>Contact: 063 461 3389 | Email: <a href="mailto:paradisehotel@pdh.ac.za" style="color: #fff;">paradisehotel@pdh.ac.za</a></p>
    </footer>
</body>
</html>