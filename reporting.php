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

    // Calculate current occupancy rate
    $total_rooms_query = "SELECT COUNT(room_id) AS total_rooms FROM rooms";
    $occupied_rooms_query = "SELECT COUNT(DISTINCT room_id) AS occupied_rooms FROM rooms WHERE availability = 'checked-in' OR availability = 'reserved'";
    
    $total_rooms_result = mysqli_query($conn, $total_rooms_query);
    $occupied_rooms_result = mysqli_query($conn, $occupied_rooms_query);
    
    $total_rooms = mysqli_fetch_assoc($total_rooms_result)['total_rooms'];
    $occupied_rooms = mysqli_fetch_assoc($occupied_rooms_result)['occupied_rooms'];
    
    $occupancy_rate = ($occupied_rooms / $total_rooms) * 100;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
    // Calculate total revenue for the specified period
    $total_revenue_sql = "
        SELECT SUM(total_price) AS total_revenue 
        FROM reservations 
        WHERE payment_status = 'paid' 
        AND check_in_date >= '$start_date' 
        AND check_out_date <= '$end_date'
    ";
    $total_revenue_result = $conn->query($total_revenue_sql);
    $total_revenue = $total_revenue_result->fetch_assoc()['total_revenue'];

    // If revenue was generated
    if ($total_revenue === null) {
        $total_revenue = 0; // Handle case when there is no revenue
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Reporting</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <header>
        <h1>Hotel Reporting</h1>
        <nav>
            <a href="admin_dashboard.php">Back to Dashboard</a>
        </nav>
    </header>
    <div class="main-container">
        <h2>Occupancy Rate</h2>
        <p>Rooms Currently Booked: <?php echo $occupied_rooms; ?></p>
        <p>Occupancy Rate: <?php echo round($occupancy_rate, 2); ?>%</p>
        <br><hr>

        <h2>Revenue Report</h2>
        <form action="reporting.php" method="POST">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required><br><br>
            
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required><br><br>
            
            <button type="submit">Generate Revenue</button>
        </form>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <h3>Report Results</h3>
            <p>Total Revenue from <?php echo $start_date; ?> to <?php echo $end_date; ?>
            <b>: R<?php echo number_format($total_revenue, 2); ?><b></p>
        <?php endif; ?>
    </div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>
