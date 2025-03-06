<?php
// Start the session
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search query
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Fetch rooms with search functionality
$sql = "SELECT * FROM rooms WHERE location LIKE ? OR description LIKE ?";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("ss", $search_param, $search_param);
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
</head>
<body>

    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="logo">
            <a href="user_dashboard.php">Home</a>
        </div>
        <div class="search-bar">
            <form action="user_dashboard.php" method="GET" style="display: flex;">
                <input type="text" name="search" placeholder="Search rooms..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="nav-links">
            <a href="#about-section">About Us</a>
            <a href="add_room.php">Add Your Room</a>
            <a href="index.php">Logout</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Available Rooms Section -->
        <h2>Trending now</h2>
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
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <p><strong>Monthly Rent:</strong> $<?php echo htmlspecialchars($row['rent']); ?></p>
                            <p><strong>Number of Rooms:</strong> <?php echo htmlspecialchars($row['number_of_rooms']); ?></p>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No rooms found matching your search.</p>
            <?php endif; ?>
        </div>

        <br><br><br><br>
    </div>
    <div id="about-section">
    <div class="about-container">
        <div class="about-text">
            <h2>About Kothamandu</h2>
            <p>
                Welcome to <strong>Kothamandu.com</strong>, your ultimate destination for finding the perfect rental rooms in Nepal! 
                Whether you’re a student, professional, or traveler, our platform connects you with verified landlords and 
                helps you discover comfortable and affordable accommodations with ease. Our user-friendly interface allows 
                you to filter searches based on location, price, and amenities, ensuring you find a home that meets your needs. 
                Join thousands of satisfied users and let Kothamandu simplify your room-hunting experience today!
            </p>
        </div>
        <div class="about-image">
            <img src="images.jpg" alt="Kothamandu Room Rentals">
        </div>
    </div>
</div>

        <!-- Footer Section -->
        <div class="footer">
        <p>&copy; 2025 Kothamandu.com | All Rights Reserved</p>
        <p>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Contact Us</a>
        </p>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>