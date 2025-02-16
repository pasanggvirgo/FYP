<?php
// Start the session
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all rooms using prepared statements for better security
$sql = "SELECT * FROM rooms";
$stmt = $conn->prepare($sql);
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
        <div class="nav-links">
            <a href="add_room.php">Add Room</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <h1>Welcome to User Dashboard</h1>

        <!-- Available Rooms Section -->
        <h2>Available Rooms</h2>
        <div class="room-cards">
        

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    
                    <div class="room-card">
                    <a href="roomdetails.php" >
                        <!-- Display room photo -->
                        <?php if (!empty($row['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($row['photo']); ?>" alt="Room Photo">
                        <?php else: ?>
                            <img src="default-room.jpg" alt="Default Room Photo">
                        <?php endif; ?>
                        
                        <!-- Room details -->
                        <h3><?php echo htmlspecialchars($row['location']); ?></h3>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                        <p><strong>Monthly Rent:</strong> $<?php echo htmlspecialchars($row['rent']); ?></p>
                        <p><strong>Number of Rooms:</strong> <?php echo htmlspecialchars($row['number_of_rooms']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                        </a></div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No rooms available at the moment.</p>
            <?php endif; ?>
            </div>

        <!-- Add Room Button -->
        <div>
            <a href="add_room.php" class="add-room-btn">Add Your Room</a>
        </div>
    </div>

</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>
