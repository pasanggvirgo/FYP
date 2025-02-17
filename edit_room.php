<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Validate and sanitize room ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid room ID.");
}

$room_id = intval($_GET['id']); // Ensure ID is an integer

// Fetch room details
$sql = "SELECT * FROM rooms WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();
$stmt->close();

if (!$room) {
    die("Room not found. ID: " . $room_id);
}

// Initialize error and success messages
$error = null;
$success = null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_room'])) {
    $location = trim($_POST['location']);
    $rent = floatval($_POST['rent']);
    $number_of_rooms = intval($_POST['number_of_rooms']);
    $description = trim($_POST['description']);
    $photo_path = $room['photo']; // Keep existing photo if not replaced

    // Handle file upload if a new image is provided
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $photo_name = basename($_FILES['photo']['name']);
        $photo_path = $target_dir . $photo_name;
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

    // Update database if no errors
    if (!$error) {
        $sql = "UPDATE rooms SET location = ?, rent = ?, number_of_rooms = ?, description = ?, photo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdissi", $location, $rent, $number_of_rooms, $description, $photo_path, $room_id);

        if ($stmt->execute()) {
            $success = "Room updated successfully!";
            $stmt->close();
        } else {
            $error = "Database error: Could not update the room.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Edit Room</h1>

        <!-- Display success or error messages -->
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <!-- Edit Room Form -->
        <form action="edit_room.php?id=<?php echo $room_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($room['location']); ?>" required>
            </div>
            <div class="form-group">
                <label for="rent">Monthly Rent:</label>
                <input type="number" id="rent" name="rent" step="0.01" value="<?php echo $room['rent']; ?>" required>
            </div>
            <div class="form-group">
                <label for="number_of_rooms">Number of Rooms:</label>
                <input type="number" id="number_of_rooms" name="number_of_rooms" value="<?php echo $room['number_of_rooms']; ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($room['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="photo">Room Photo (leave blank to keep existing photo):</label>
                <input type="file" id="photo" name="photo" accept="image/*">
                <?php if ($room['photo']): ?>
                    <p>Current Photo:</p>
                    <img src="<?php echo htmlspecialchars($room['photo']); ?>" alt="Room Photo" style="max-width: 200px; border-radius: 8px;">
                <?php endif; ?>
            </div>
            <button type="submit" name="edit_room">Update Room</button>
        </form>

        <!-- Back to Dashboard Button -->
        <div>
            <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
