<?php
// Start session
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get room ID from request
$room_id = $_GET['room_id'] ?? null;

if (!$room_id || !is_numeric($room_id)) {
    die("Invalid room ID.");
}

// Fetch room details
$stmt = $conn->prepare("SELECT rent FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Room not found.");
}

$room = $result->fetch_assoc();
$stmt->close();

// Set payment details
$amount = 5; // Base amount (e.g., rent price)
$serviceCharge = 0; // Service charge (if applicable)
$deliveryCharge = 0; // Delivery charge (if applicable)
$taxAmount = 0; // Tax amount (if applicable)
$totalAmount = $amount + $serviceCharge + $deliveryCharge + $taxAmount; // Total payable amount

// eSewa Merchant Code (Replace with your actual code)
$merchantCode = "YourMerchantCode";

// Success and Failure URLs
$successUrl = "http://localhost/FYP/esewa_success.php";
$failureUrl = "http://localhost/FYP/esewa_failed.php";

// Redirect to eSewa payment gateway
header("Location: https://uat.esewa.com.np/epay/main?"
    . "amt=$amount"
    . "&psc=$serviceCharge"
    . "&pdc=$deliveryCharge"
    . "&txAmt=$taxAmount"
    . "&tAmt=$totalAmount"
    . "&pid=$room_id"
    . "&scd=$merchantCode"
    . "&su=$successUrl"
    . "&fu=$failureUrl");

exit;
?>
