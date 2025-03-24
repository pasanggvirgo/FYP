<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if (!$conn) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : null;

if ($user_id && $room_id) {
    $check_stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND room_id = ?");
    $check_stmt->bind_param("ii", $user_id, $room_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND room_id = ?");
        $stmt->bind_param("ii", $user_id, $room_id);
        $stmt->execute();
        echo json_encode(["status" => "removed"]);
    } else {
        $stmt = $conn->prepare("INSERT INTO favorites (user_id, room_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $room_id);
        $stmt->execute();
        echo json_encode(["status" => "added"]);
    }
}
$conn->close();
?>
