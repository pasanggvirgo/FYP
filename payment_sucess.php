<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['room_id']) && is_numeric($_GET['room_id'])) {
    $room_id = intval($_GET['room_id']);

    // Update payment status
    $stmt = $conn->prepare("UPDATE rooms SET payment_status = 'paid' WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stmt->close();

    echo "Payment Successful! Contact details are now visible.";
    echo "<br><a href='room_details.php?id=$room_id'>Go back to room</a>";
} else {
    echo "Invalid Payment!";
}
?>
