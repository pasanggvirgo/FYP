<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
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

// Fetch favorite rooms
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
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    </div><br><br><br>
    <h1>My Favorite Rooms</h1>
    <div class="room-cards">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="room-card">
                <a href="room_details.php?id=<?php echo $row['id']; ?>">
                    <?php 
                    // Decode the JSON array of photos
                    $photos = json_decode($row['photos'], true);
                    if (!empty($photos) && isset($photos[0])) {
                        // Display the first photo
                        $first_photo = htmlspecialchars($photos[0]);
                        echo "<img src='$first_photo' alt='Room Photo'>";
                    } else {
                        // Default photo if no photos are available
                        echo "<img src='default-room.jpg' alt='Default Room Photo'>";
                    }
                    ?>
                    <h3><?php echo "ID ".htmlspecialchars($row['id']).", ".htmlspecialchars($row['location']).", ".htmlspecialchars($row['property_type']); ?></h3>
                    <p><strong>📍 Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                    <p style="color:red;"><strong>💰 Monthly Rent:</strong> Rs.<?php echo htmlspecialchars($row['rent']); ?></p>
                    <p><strong>🏠Property Type:</strong> <?php echo htmlspecialchars($row['property_type']); ?></p>
                </a>

                <button class="favorite-btn" data-room-id="<?php echo $row['id']; ?>">
                    <?php
                    $fav_stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND room_id = ?");
                    $fav_stmt->bind_param("ii", $user_id, $row['id']);
                    $fav_stmt->execute();
                    $fav_result = $fav_stmt->get_result();
                    $is_favorite = $fav_result->num_rows > 0;
                    echo $is_favorite ? "❌ Remove from Favorites" : "❤️ Add to Favorites";
                    ?>
                </button>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No favorite rooms found.</p>
        <?php endif; ?>
    </div>

<script>
$(document).ready(function() {
    $(".favorite-btn").click(function() {
        var button = $(this);
        var roomId = button.data("room-id");

        $.ajax({
            url: "favorite_handler.php",
            type: "POST",
            data: { room_id: roomId, action: "remove" },
            dataType: "json",
            success: function(response) {
                if (response.status === "removed") {
                    button.closest(".room-card").fadeOut();
                }
            },
            error: function() {
                alert("An error occurred. Please try again.");
            }
        });
    });
});
</script>

</body>
</html>
