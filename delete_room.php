<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? null;
$room_id = $_GET['id'] ?? null;

if ($user_id && $room_id) {
    // 1. Delete any favorites linked to this room
    $delFav = $conn->prepare("DELETE FROM favorites WHERE room_id = ?");
    $delFav->bind_param("i", $room_id);
    $delFav->execute();
    $delFav->close();

    // 2. Now delete the room itself
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $room_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: myuploads.php");
exit();
?>
