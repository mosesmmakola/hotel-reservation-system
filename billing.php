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

// Process Payment
if (isset($_POST['process_payment'])) {
    $reservation_id = $_POST['reservation_id'];
    $amount_paid = $_POST['amount_paid'];
    $payment_method = $_POST['payment_method'];
    
    // Insert payment record into payments table
    $sql = "INSERT INTO payments (reservation_id, amount_paid, payment_date, payment_method) 
            VALUES ('$reservation_id', '$amount_paid', CURRENT_DATE(), '$payment_method')";
    if ($conn->query($sql)) {
        // Update payment_status in reservations table to 'paid'
        $sql_update = "UPDATE reservations SET payment_status = 'paid' WHERE reservation_id = '$reservation_id'";
        $conn->query($sql_update);

        echo "Payment processed successfully!";
    } else {
        echo "Error processing payment: " . $conn->error;
    }
}

// Fetch Payments for listing
$payments = $conn->query("SELECT payments.*, guests.first_name AS first_name, guests.last_name AS last_name
                          FROM payments
                          LEFT JOIN reservations ON payments.reservation_id = reservations.reservation_id
                          LEFT JOIN guests ON reservations.guest_id = guests.guest_id")
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing and Payments</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Billing and Payments</h1>
        <nav>
            <a href="admin_dashboard.php">Back to Dashboard</a>
        </nav>
    </header>
    <div class="main-container">
    <form action = "" method="POST">
        <select name="reservation_id" required>
            <?php
            // Fetch reservations along with guest name and email for dropdown
            $reservations = $conn->query("SELECT reservations.reservation_id, guests.first_name AS first_name, guests.last_name AS last_name, guests.email AS guest_email, reservations.total_price
                                        FROM reservations
                                        JOIN guests ON reservations.guest_id = guests.guest_id
                                        WHERE reservations.payment_status != 'paid'");
            while ($row = $reservations->fetch_assoc()) {
                echo "<option value='" . $row['reservation_id'] . "'>
                | Reservation " . $row['reservation_id'] . " || " . $row['first_name'] . " " . $row['last_name'] . " || " . $row['guest_email'] . " || " . $row['total_price'] . " |
                </option>";
            }
            ?>
        </select>
        <input type="number" name="amount_paid" placeholder="Amount Paid" required>
        <!-- Payment Method Dropdown -->
        <select name="payment_method">
            <option value="card">Card</option>
            <option value="cash">Cash</option>
            <option value="online">Online</option>
        </select>
        <!--input type="date" name="payment_date"><-- Schedule remove-->
        <button type="submit" name="process_payment">Process Payment</button>
    </form>
    </div>
    <h2>Payments List</h2>
    <table>
        <tr>
            <th>Payment ID</th>
            <th>Reservation ID</th>
            <th>Guest Name</th>
            <th>Amount Paid</th>
            <th>Payment Method</th>
            <th>Payment Date</th>
        </tr>
        <?php while ($payment = $payments->fetch_assoc()): ?>
        <tr>
            <td><?php echo $payment['payment_id']; ?></td>
            <td><?php echo $payment['reservation_id']; ?></td>
            <td><?php echo $payment['first_name'] . " " . $payment['last_name']; ?></td>
            <td><?php echo $payment['amount_paid']; ?></td>
            <td><?php echo $payment['payment_method']; ?></td>
            <td><?php echo $payment['payment_date']; ?></td>
        </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <div class="main2-container"></div>
    <footer>
        <p>&copy; 2024 Paradise Hotel | All rights reserved.</p>
    </footer>
</body>
</html>
