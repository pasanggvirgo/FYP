<?php
// Start session securely
session_start();
session_regenerate_id(true);

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize search query
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Prepare and execute query in one statement
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
        if (!empty($photo_path) && file_exists(realpath($photo_path))) {
            unlink(realpath($photo_path)); // Delete the photo file
        }
        $message = "Room deleted successfully.";
    } else {
        $error = "Error: Could not delete the room.";
    }
    $stmt->close();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Fetch rooms based on search query
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
    <title>Admin Dashboard - Room Management</title>
    <link rel="stylesheet" href="dashboard.css">
   
    <script>
        function confirmDelete(roomId) {
            if (confirm("Are you sure you want to delete this room?")) {
                window.location.href = "admin_dashboard.php?delete=" + roomId;
            }
        }
    </script>
</head>
<body>

    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="logo">
            <a href="admin_dashboard.php">Admin Dashboard</a>
        </div>
        <div class="search-bar">
            <form action="admin_dashboard.php" method="GET" style="display: flex;">
                <input type="text" name="search" placeholder="Search rooms..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="nav-links">
            <a href="add_room.php">Add Room</a>
            <a href="admin_dashboard.php?logout=true" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>Manage Rooms</h1>

        <?php if (isset($message)): ?>
            <p class="success"><?php echo $message; ?></p>
        <?php elseif (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <div class="room-cards">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="room-card">
                        <a href="room_details.php?id=<?php echo $row['id']; ?>">
                            <?php if (!empty($row['photo']) && file_exists(realpath($row['photo']))): ?>
                                <img src="<?php echo htmlspecialchars($row['photo']); ?>" alt="Room Photo">
                            <?php else: ?>
                                <img src="default_room.jpg" alt="Default Room Photo">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($row['location']); ?></h3>
                            <p><strong>Monthly Rent:</strong> $<?php echo htmlspecialchars($row['rent']); ?></p>
                            <p><strong>Number of Rooms:</strong> <?php echo htmlspecialchars($row['number_of_rooms']); ?></p>
                        </a>
                        <div class="action-buttons">
                            <a href="edit_room.php?id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                            <a href="#" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="delete-btn">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No rooms available.</p>
            <?php endif; ?>
        </div>
    </div>
    
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
