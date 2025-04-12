<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit();
}
$fav_count = 0;
if ($user_id) {
    $fav_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM favorites WHERE user_id = ?");
    $fav_stmt->bind_param("i", $user_id);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    if ($row = $fav_result->fetch_assoc()) {
        $fav_count = $row['total'];
    }
    $fav_stmt->close();
}

// Fetch rooms uploaded by the user
$sql = "SELECT * FROM rooms WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Uploads</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="main-container">
<div class="navbar">
        <div class="logo">
            <a href="user_dashboard.php"><img id="homeimg" class="icon" src="house.png"></a>
        </div>
        <div class="nav-links">
            <a class="nav-links" href="#about-section">About Us</a>
            <a class="nav-links" href="add_room.php">✚ Add Room</a>
            <?php if ($user_id): ?>
                <a class="nav-links" href="favorites.php">❤️ Favourites <span style="color:red;">(<?php echo $fav_count; ?>)</span></a>
                <a class="nav-links" href="myuploads.php">📂 My Uploads</a>
                <a class="nav-links" href="index.php">👤 Logout</a>
            <?php else: ?>
                <a class="nav-links" href="index.php">👤 Log in</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h1>My Uploaded Rooms</h1>
        <div class="room-cards">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="room-card">
                        <a href="room_details.php?id=<?php echo $row['id']; ?>">
                            <?php 
                            $photos = json_decode($row['photos'], true);
                            $first_photo = !empty($photos) && isset($photos[0]) ? htmlspecialchars($photos[0]) : 'default-room.jpg';
                            ?>
                            <img src="<?php echo $first_photo; ?>" alt="Room Photo">
                            <h3><?php echo "ID ".htmlspecialchars($row['id']).", ".htmlspecialchars($row['location']).", ".htmlspecialchars($row['property_type']); ?></h3>
                            <p><strong>📍 Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <p style="color:red;"><strong>💰 Monthly Rent:</strong> Rs.<?php echo htmlspecialchars($row['rent']); ?></p>
                            <p><strong>🏠Property Type:</strong> <?php echo htmlspecialchars($row['property_type']); ?></p>
                        </a>
                        <div class="action-buttons">
                            <a href="edit_room.php?id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                            <a href="#" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="delete-btn">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You haven't uploaded any rooms yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 Kothamandu.com | All Rights Reserved</p>
    </div>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
