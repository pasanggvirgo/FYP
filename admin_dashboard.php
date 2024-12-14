<?php
// Start session
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission to add a new room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $location = $_POST['location'];
    $rent = $_POST['rent'];
    $number_of_rooms = $_POST['number_of_rooms'];
    $description = $_POST['description'];

    $sql = "INSERT INTO rooms (location, rent, number_of_rooms, description) VALUES ( ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdis", $location, $rent, $number_of_rooms, $description);

    if ($stmt->execute()) {
        $message = "Room added successfully.";
    } else {
        $error = "Error: Could not add the room.";
    }
    $stmt->close();
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

        <h2>Add Room</h2>
        <form action="admin_dashboard.php" method="POST">
          
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>
            </div>
            <div class="form-group">
                <label for="rent">Monthly Rent:</label>
                <input type="number" id="rent" name="rent" required>
            </div>
            <div class="form-group">
                <label for="number_of_rooms">Number of Rooms:</label>
                <input type="number" id="number_of_rooms" name="number_of_rooms" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <button type="submit" name="add_room">Add Room</button>
        </form>

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
