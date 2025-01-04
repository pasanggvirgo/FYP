<?php
// Start session
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Fetch the photo path before deletion
    $sql = "SELECT photo FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($photo_path);
    $stmt->fetch();
    $stmt->close();

    // Delete the room
    $sql = "DELETE FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($photo_path && file_exists($photo_path)) {
            unlink($photo_path); // Delete the photo file from the server
        }
        $message = "Room deleted successfully.";
    } else {
        $error = "Error: Could not delete the room.";
    }
    $stmt->close();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy(); // Destroy the session
    header("Location: index.php"); // Redirect to login page
    exit();
}

// Fetch all rooms
$sql = "SELECT * FROM rooms";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Room Management</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>

        <!-- Logout Button -->
        <form action="admin_dashboard.php" method="GET" style="text-align: right;">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>

        <?php if (isset($message)): ?>
            <p class="success"><?php echo $message; ?></p>
        <?php elseif (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Add Room Button -->
        <div style="margin-bottom: 20px;">
            <a href="add_room.php" class="add-room-btn">Add Room</a>
        </div>

        <!-- List of Rooms -->
        <h2>Manage Rooms</h2>
        <div class="room-cards">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="room-card">
                        <?php if (!empty($row['photo']) && file_exists($row['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($row['photo']); ?>" alt="Room Photo" class="room-photo">
                        <?php else: ?>
                            <img src="default_room.jpg" alt="Default Photo" class="room-photo">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($row['location']); ?></h3>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                        <p><strong>Monthly Rent:</strong> $<?php echo htmlspecialchars($row['rent']); ?></p>
                        <p><strong>Number of Rooms:</strong> <?php echo htmlspecialchars($row['number_of_rooms']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                        <a href="admin_dashboard.php?delete=<?php echo $row['id']; ?>" class="delete-btn">Delete</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No rooms available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
