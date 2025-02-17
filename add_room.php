<?php
// Start session
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error messages
$error = null;
$success = null;

// Handle form submission to add a new room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $location = $_POST['location'];
    $rent = $_POST['rent'];
    $number_of_rooms = $_POST['number_of_rooms'];
    $description = $_POST['description'];
    $photo_path = null;

    // Handle file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $target_dir = "uploads/";
        // Ensure the target directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $photo_path = $target_dir . basename($_FILES['photo']['name']);
        $imageFileType = strtolower(pathinfo($photo_path, PATHINFO_EXTENSION));

        // Validate file type and size
        $check = getimagesize($_FILES['photo']['tmp_name']);
        if ($check === false) {
            $error = "The uploaded file is not a valid image.";
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error = "Only JPG, JPEG, PNG, and GIF formats are allowed.";
        } elseif ($_FILES['photo']['size'] > 5000000) { // 5MB limit
            $error = "The file size must not exceed 5MB.";
        } elseif (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
            $error = "Failed to upload the photo. Check file permissions.";
        }
    }

    // Insert data into the database if no errors
    if (!isset($error)) {
        $sql = "INSERT INTO rooms (location, rent, number_of_rooms, description, photo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdiss", $location, $rent, $number_of_rooms, $description, $photo_path);

        if ($stmt->execute()) {
            $success = "Room added successfully!";
        } else {
            $error = "Database error: Could not add the room.";
        }
        $stmt->close();
    }
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

        <!-- Display success or error messages -->
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <!-- Add Room Form -->
        <form action="add_room.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>
            </div>
            <div class="form-group">
                <label for="rent">Monthly Rent:</label>
                <input type="number" id="rent" name="rent" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="number_of_rooms">Number of Rooms:</label>
                <input type="number" id="number_of_rooms" name="number_of_rooms" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="photo">Room Photo:</label>
                <input required type="file" id="photo" name="photo" accept="image/*">
            </div>
            <button type="submit" name="add_room">Add Room</button>
        </form>

        <!-- Back to Dashboard Button -->
        <div>
            <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
