<?php
session_start();
include('smtp/PHPMailerAutoload.php');

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = null;
$success = null;

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to add a room.");
}

$user_id = $_SESSION['user_id'];
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

$user_query = "SELECT username, email FROM users WHERE id=?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

$user_email = $user['email'];
$username = $user['username'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $location = $_POST['location'];
    $location1 = $_POST['location1'];
    $rent = $_POST['rent'];
    $description = $_POST['description'];
    $seller_contact = $_POST['seller_contact'];
    $property_type = $_POST['property_type'];
    $furnishing_status = $_POST['furnishing_status'];
    $photo_paths = [];

    // Handle multiple file uploads
    if (isset($_FILES['photos']) && count($_FILES['photos']['name']) > 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        foreach ($_FILES['photos']['name'] as $key => $photo_name) {
            $tmp_name = $_FILES['photos']['tmp_name'][$key];
            $imageFileType = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
            $unique_name = $target_dir . uniqid() . "." . $imageFileType;

            // Validate file
            $check = getimagesize($tmp_name);
            if ($check === false) {
                $error = "One of the uploaded files is not a valid image.";
                break;
            } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = "Only JPG, JPEG, PNG, and GIF formats are allowed.";
                break;
            } elseif ($_FILES['photos']['size'][$key] > 5000000) {
                $error = "Each file must not exceed 5MB.";
                break;
            } elseif (move_uploaded_file($tmp_name, $unique_name)) {
                $photo_paths[] = $unique_name;
            } else {
                $error = "Failed to upload some images.";
                break;
            }
        }
    }

    // Insert data into the database if no errors
    if (!$error) {
        $photo_json = json_encode($photo_paths);

        $sql = "INSERT INTO rooms (location,location1, rent, description, photos, user_id, seller_contact, property_type, furnishing_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsssiss", $location,$location1, $rent, $description, $photo_json, $user_id, $seller_contact, $property_type, $furnishing_status);

        if ($stmt->execute()) {
            $success = "Room added successfully for verification!";

            // Send Email Notification
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'sherpapasang877@gmail.com';
                $mail->Password = 'rlch gavl pjwy svsd';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('sherpapasang877@gmail.com', 'kothamandu.com');
                $mail->addAddress($user_email, $username);
                $mail->Subject = 'Room Successfully Uploaded';
                $mail->Body = "Dear $username,\n\nYour room has been successfully uploaded to our platform!\n\nLocation: $location\nRent: $$rent per month\n\nThank you for using our service!\n\nBest regards,\nkothamandu.com";

                if ($mail->send()) {
                    $success .= " An email has been sent to confirm your room upload.";
                } else {
                    $error = "Room added, but email could not be sent.";
                }
            } catch (Exception $e) {
                $error = "Email error: " . $mail->ErrorInfo;
            }
        } else {
            $error = "Database error: Could not add the room.";
        }
        $stmt->close();
    }
}
?>

<!-- HTML STARTS HERE -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Room</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Use the same styles as your current code — skipped here for brevity */
        /* Include the previous CSS here */
    </style>
</head>
<body>
<div class="main-container">
    <div class="navbar">
        <div class="logo">
            <a href="admin_dashboard.php"><img id="homeimg" class="icon" src="house.png"></a>
        </div>
        <div class="nav-links">
            <a class="nav-links" href="add_room_admin.php">✚ Add Room</a>
            <?php if ($user_id): ?>
                <a class="nav-links" href="my_uploads_admin.php">Uploads</a>
                <a class="nav-links" href="index.php">👤 Logout</a>
            <?php else: ?>
                <a class="nav-links" href="index.php">👤 Log in</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h1>Add Room</h1>

        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>

        <form action="add_room.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
                <label for="location1">Location:</label>
                <input type="text" placeholder="E.g, Kapan" id="location1" name="location1" required>
            </div>
            <div class="form-group">
                <label for="location">Detailed Location with Landmark:</label>
                <input type="text" placeholder="E.g, Venus Public School, Jorpati" id="location" name="location" required>
            </div>
            

            <div class="form-group">
                <label for="rent">Monthly Rent:</label>
                <input type="number" id="rent" name="rent" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="seller_contact">Seller Contact Number:</label>
                <input type="text" id="seller_contact" name="seller_contact" required>
            </div>

            <div class="form-group">
                <label for="property_type">Property Type:</label>
                <select id="property_type" name="property_type" required>
                    <option value="1 BHK">1 BHK</option>
                    <option value="2 BHK">2 BHK</option>
                    <option value="3 BHK">3 BHK</option>
                    <option value="Shutters">Shutters</option>
                    <option value="Commercial Property">Commercial Property</option>
                </select>
            </div>

            <div class="form-group">
                <label for="furnishing_status">Furnishing Status:</label>
                <select id="furnishing_status" name="furnishing_status" required>
                    <option value="Furnished">Furnished</option>
                    <option value="Semi-Furnished">Semi-Furnished</option>
                    <option value="Unfurnished">Unfurnished</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="2" required></textarea>
            </div>

            
            <div class="form-group">
                <label for="photos">Room Photos (Multiple):</label>
                <input type="file" id="photos" name="photos[]" accept="image/*" multiple required>
            </div>

            <button type="submit" name="add_room">Add Room</button>
        </form>

        <div>
            <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>

<?php $conn->close(); ?>
