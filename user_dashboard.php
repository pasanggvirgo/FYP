<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$location = isset($_GET['location']) ? trim($_GET['location']) : "";
$rooms = isset($_GET['rooms']) ? trim($_GET['rooms']) : "";
$price = isset($_GET['price']) ? trim($_GET['price']) : "";

$sql = "SELECT * FROM rooms WHERE (location LIKE ? OR description LIKE ?)";
$params = ["%$search%", "%$search%"];
$types = "ss";

if (!empty($location)) {
    $sql .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}
if (!empty($rooms)) {
    $sql .= " AND number_of_rooms = ?";
    $params[] = $rooms;
    $types .= "i";
}
if (!empty($price)) {
    $sql .= " AND rent <= ?";
    $params[] = $price;
    $types .= "i";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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
            <a href="#about-section">About Us</a>
            <a href="add_room.php">✚Add Room</a>
            <a href="favorites.php">❤️WishList</a> 
            <a href="index.php">Logout</a>
        </div>
    </div>

    <img id="bgimg" src="bg1.jpg" alt="Background Image">

    <div class="search-bar-container">
        <form action="user_dashboard.php" method="GET" class="search-form">
            <input type="text" name="location" placeholder="📍 Location" value="<?php echo htmlspecialchars($location); ?>">
            <input type="number" name="rooms" placeholder="Number of Rooms" min="1" value="<?php echo htmlspecialchars($rooms); ?>">
            <input type="number" name="price" placeholder="💰 Max Price" min="1" value="<?php echo htmlspecialchars($price); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="container">
        <h1>Trending now....</h1>
        <div class="room-cards">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="room-card">
                        <a href="room_details.php?id=<?php echo $row['id']; ?>">
                            <?php if (!empty($row['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($row['photo']); ?>" alt="Room Photo">
                            <?php else: ?>
                                <img src="default-room.jpg" alt="Default Room Photo">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($row['location']); ?></h3>
                            <p><strong>📍Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <p style="color:red;"><strong>💰Monthly Rent:</strong> $<?php echo htmlspecialchars($row['rent']); ?></p>
                            <p><strong>Number of Rooms:</strong> <?php echo htmlspecialchars($row['number_of_rooms']); ?></p>
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
                <p>No rooms found matching your search.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="about-section">
        <div class="about-container">
            <div class="about-text">
                <h2>About Kothamandu</h2>
                <p>
                    Welcome to <strong>Kothamandu.com</strong>, your ultimate destination for finding the perfect rental rooms in Nepal!...
                </p>
            </div>
            <div class="about-image">
                <img src="images.jpg" alt="Kothamandu Room Rentals">
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 Kothamandu.com | All Rights Reserved</p>
        <p>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Contact Us</a>
        </p>
    </div>

</div>

<script>
$(document).ready(function() {
    $(".favorite-btn").click(function() {
        var button = $(this);
        var roomId = button.data("room-id");

        $.ajax({
            url: "favorite_handler.php",
            type: "POST",
            data: { room_id: roomId },
            dataType: "json",
            success: function(response) {
                if (response.status === "added") {
                    button.text("❌ Remove from Favorites");
                } else if (response.status === "removed") {
                    button.text("❤️ Add to Favorites");
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

<?php
// Close database connection
$stmt->close();
$conn->close();
?>
