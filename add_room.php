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

    $sql = "INSERT INTO rooms (location, rent, number_of_rooms, description) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdis", $location, $rent, $number_of_rooms, $description);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php"); // Redirect back to dashboard
        exit();
    } else {
        $error = "Error: Could not add the room.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Add Room</h1>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Add Room Form -->
        <form action="add_room.php" method="POST">
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

        <!-- Back to Dashboard Button -->
        <div style="margin-top: 20px;">
            <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

