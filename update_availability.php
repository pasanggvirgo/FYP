<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? null;
$room_id = $_POST['room_id'] ?? null;
$availability = $_POST['availability'] ?? null;

if ($user_id && $room_id && in_array($availability, ['Available', 'Rented'])) {
    // Update availability only if the room belongs to the user
    $stmt = $conn->prepare("UPDATE rooms SET availability = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $availability, $room_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// Redirect back to my uploads page
header("Location: myuploads.php");
exit();
?>
