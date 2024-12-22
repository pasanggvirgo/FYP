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
    $sql = "DELETE FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
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
        <div>
            <a href="add_room.php" class="add-room-btn">Add Room</a>
        </div>

        <!-- List of Rooms -->
        <h2>Manage Rooms</h2>
        <div class="room-cards">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="room-card">
                    <h3><?php echo htmlspecialchars($row['location']); ?></h3>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                    <p><strong>Monthly Rent:</strong> $<?php echo htmlspecialchars($row['rent']); ?></p>
                    <p><strong>Number of Rooms:</strong> <?php echo htmlspecialchars($row['number_of_rooms']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                    <a href="admin_dashboard.php?delete=<?php echo $row['id']; ?>" class="delete-btn">Delete</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
