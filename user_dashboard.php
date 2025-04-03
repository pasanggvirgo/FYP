<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$location = isset($_GET['location']) ? trim($_GET['location']) : "";
$property_type = isset($_GET['property_type']) ? trim($_GET['property_type']) : "";
$price = isset($_GET['price']) ? trim($_GET['price']) : "";
$room_id = isset($_GET['room_id']) ? trim($_GET['room_id']) : "";

$sql = "SELECT * FROM rooms WHERE (location LIKE ? OR description LIKE ? OR id LIKE ? )";
$params = ["%$search%", "%$search%", "%$room_id%"];
$types = "sss";

if (!empty($location)) {
    $sql .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}
if (!empty($property_type)) {
    $sql .= " AND property_type = ?";
    $params[] = $property_type;
    $types .= "s";
}
if (!empty($price)) {
    $sql .= " AND rent <= ?";
    $params[] = $price;
    $types .= "i";
}
if (!empty($room_id)) {
    $sql = "SELECT * FROM rooms WHERE id = ?";
    $params = [$room_id];  // Only pass room_id to look for exact matches.
    $types = "i";  // Use 'i' for integers in the prepared statement
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
            <a class="nav-links" href="#about-section">About Us</a>
            <a class="nav-links" href="add_room.php">✚ Add Room</a>
            <a class="nav-links" href="favorites.php">❤️ WishList</a> 
            <?php if ($user_id): ?>
                <a class="nav-links" href="index.php">👤 Logout</a>
            <?php else: ?>
                <a class="nav-links" href="index.php">👤 Log in</a>
            <?php endif; ?>
        </div>
    </div>

    <img id="bgimg" src="bg.jpg" alt="Background Image">

    <div class="search-bar-container">
        <form action="user_dashboard.php" method="GET" class="search-form">
            <input type="text" name="location" placeholder="📍 Location" value="<?php echo htmlspecialchars($location); ?>">

            <select id="selectproperty " name="property_type">
                <option value="">🏠Property Type</option>
                <option value="1 BHK" <?php if ($property_type == "1 BHK") echo "selected"; ?>>1 BHK</option>
                <option value="2 BHK" <?php if ($property_type == "2 BHK") echo "selected"; ?>>2 BHK</option>
                <option value="3 BHK" <?php if ($property_type == "3 BHK") echo "selected"; ?>>3 BHK</option>
                <option value="Commercial Property" <?php if ($property_type == "Commercial Property") echo "selected"; ?>>Commercial Property</option>
                <option value="Shutters" <?php if ($property_type == "Shutters") echo "selected"; ?>>Shutters</option>
            </select>

            <input type="number" name="price" placeholder="💰 Max Price" min="1" value="<?php echo htmlspecialchars($price); ?>">
            <input type="text" name="room_id" placeholder="🏠 Room ID" value="<?php echo htmlspecialchars($room_id); ?>">
            <button type="submit">🔍 Search</button>
        </form>
    </div>
    <div class="carousel">
    <div class="carousel-inner">
        <img src="slider1.png" alt="Slide 1">
        <img src="slider2.png" alt="Slide 2">
        <img src="slider3.png" alt="Slide 3">
    </div>
</div>

    <div class="container">
        <h1>Trending now....</h1>
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
    document.addEventListener("DOMContentLoaded", function () {
        let index = 0;
        const slides = document.querySelectorAll(".carousel-inner img");
        function showSlide() {
            slides.forEach((slide, i) => {
                slide.style.display = i === index ? "block" : "none";
            });
            index = (index + 1) % slides.length;
        }
        setInterval(showSlide, 3000); // Change every 3 seconds
        showSlide();
    });

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
