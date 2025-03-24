<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT rooms.* FROM rooms JOIN favorites ON rooms.id = favorites.room_id WHERE favorites.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Favorites</title>
</head>
<body>
    <h1>My Favorite Rooms</h1>
    <div class="room-cards">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="room-card">
                <a href="room_details.php?id=<?php echo $row['id']; ?>">
                    <img src="<?php echo $row['photo'] ?: 'default-room.jpg'; ?>" alt="Room Photo">
                    <h3><?php echo htmlspecialchars($row['location']); ?></h3>
                    <p><strong>Monthly Rent:</strong> $<?php echo htmlspecialchars($row['rent']); ?></p>
                    <p><strong>Number of Rooms:</strong> <?php echo htmlspecialchars($row['number_of_rooms']); ?></p>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
