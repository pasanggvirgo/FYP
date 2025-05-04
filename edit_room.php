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
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit();
}
$fav_count = 0;
if ($user_id) {
    $fav_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM favorites WHERE user_id = ?");
    $fav_stmt->bind_param("i", $user_id);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    if ($row = $fav_result->fetch_assoc()) {
        $fav_count = $row['total'];
    }
    $fav_stmt->close();
}
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
    $location1 = trim($_POST['location1']);
    $rent = floatval($_POST['rent']);
    $property_type = trim($_POST['property_type']);
    $furnishing_status = trim($_POST['furnishing_status']);
    $seller_contact = trim($_POST['seller_contact']);
    $description = trim($_POST['description']);
    
    // Handle file uploads
    $photo_paths = json_decode($room['photos'], true) ?? [];
    if (!empty($_FILES['photos']['name'][0])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            $photo_name = basename($_FILES['photos']['name'][$key]);
            $photo_path = $target_dir . $photo_name;
            $imageFileType = strtolower(pathinfo($photo_path, PATHINFO_EXTENSION));

            // Validate file
            $check = getimagesize($tmp_name);
            if ($check !== false && in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif']) && $_FILES['photos']['size'][$key] <= 5000000) {
                if (move_uploaded_file($tmp_name, $photo_path)) {
                    $photo_paths[] = $photo_path; // Save new file path
                } else {
                    $error = "Failed to upload some images.";
                }
            } else {
                $error = "Invalid file type or size exceeded.";
            }
        }
    }

    // Update database if no errors
    if (!$error) {
        $photo_json = json_encode($photo_paths);
        $sql = "UPDATE rooms SET location = ?,location1=?, rent = ?, property_type = ?, furnishing_status = ?, contact_info = ?, description = ?, photos = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsssssi", $location,$location1, $rent, $property_type, $furnishing_status, $contact_info, $description, $photo_json, $room_id);

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
<div class="main-container">

<div class="navbar">
        <div class="logo">
            <a href="user_dashboard.php"><img id="homeimg" class="icon" src="house.png"></a>
        </div>
        <div class="nav-links">
            <a class="nav-links" href="my_chats.php">💬 My Chats</a>
            <a class="nav-links" href="add_room.php">✚ Add Room</a>
            <?php if ($user_id): ?>
                <a class="nav-links" href="favorites.php">Favourites <span style="color:red;">(<?php echo $fav_count; ?>)</span></a>
                <a class="nav-links" href="myuploads.php">My Uploads</a>
                <a class="nav-links" href="index.php">👤 Logout</a>
            <?php else: ?>
                <a class="nav-links" href="index.php">👤 Log in</a>
            <?php endif; ?>
        </div>
    </div>
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
                <label for="location1">Location:</label>
                <input type="text" id="location1" name="location1" value="<?php echo htmlspecialchars($room['location1']); ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Detailed Location:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($room['location']); ?>" required>
            </div>

            <div class="form-group">
                <label for="rent">Monthly Rent:</label>
                <input type="number" id="rent" name="rent" step="0.01" value="<?php echo $room['rent']; ?>" required>
            </div>

            <div class="form-group">
                <label for="property_type">Property Type:</label>
                <select id="property_type" name="property_type" required>
                    <option value="">Select Property Type</option>
                    <option value="1 BHK" <?php if ($room['property_type'] == "1 BHK") echo "selected"; ?>>1 BHK</option>
                    <option value="2 BHK" <?php if ($room['property_type'] == "2 BHK") echo "selected"; ?>>2 BHK</option>
                    <option value="3 BHK" <?php if ($room['property_type'] == "3 BHK") echo "selected"; ?>>3 BHK</option>
                    <option value="Commercial Property" <?php if ($room['property_type'] == "Commercial Property") echo "selected"; ?>>Commercial Property</option>
                </select>
            </div>

            <div class="form-group">
                <label for="furnishing_status">Furnishing Status:</label>
                <select id="furnishing_status" name="furnishing_status">
                    <option value="">Select Furnishing Status</option>
                    <option value="Furnished" <?php if ($room['furnishing_status'] == "Furnished") echo "selected"; ?>>Furnished</option>
                    <option value="Semi-Furnished" <?php if ($room['furnishing_status'] == "Semi-Furnished") echo "selected"; ?>>Semi-Furnished</option>
                    <option value="Unfurnished" <?php if ($room['furnishing_status'] == "Unfurnished") echo "selected"; ?>>Unfurnished</option>
                </select>
            </div>

            <div class="form-group">
                <label for="contact_info">Owner Contact:</label>
                <input type="text" id="seller_contact" name="seller_contact" value="<?php echo htmlspecialchars($room['seller_contact']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($room['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="photos">Room Photos (upload multiple):</label>
                <input type="file" id="photos" name="photos[]" accept="image/*" multiple>
                <div class="photo-preview">
                    <?php 
                    $photos = json_decode($room['photos'], true);
                    if (!empty($photos)) {
                        foreach ($photos as $photo) {
                            echo "<img src='" . htmlspecialchars($photo) . "' alt='Room Photo' style='max-width: 100px; border-radius: 8px; margin-right: 5px;'>";
                        }
                    }
                    ?>
                </div>
            </div>

            <button type="submit" name="edit_room">Update Room</button>
        </form>

        <!-- Back to Dashboard Button -->
        <div>
            <a href="user_dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>
                </div>
</body>
</html>

<?php
$conn->close();
?>
