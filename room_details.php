<?php
// Start session
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message
$error = null;

// Retrieve room ID from URL parameter
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $room_id = $_GET['id'];

    // Fetch room details from database
    $sql = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc();
    } else {
        $error = "Room not found.";
    }

    $stmt->close();
} else {
    $error = "Invalid room ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ecf1e7;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #dae7da; /* Light green for softer look */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .room-details {
            text-align: center;
            padding: 20px;
            background-color:rgb(221, 243, 213);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        .room-details p {
            font-size: 16px;
            color: #333;
            margin: 10px 0;
        }
        .room-details strong {
            color: #007BFF;
        }
        .room-photo {
            width: 100%;
            max-width: 500px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .error {
            color: #f44336;
            text-align: center;
            font-weight: bold;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            text-align: center;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Room Details</h1>

        <!-- Display error message if any -->
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php else: ?>
            <!-- Display room details -->
            <div class="room-details">
                <!-- Display room photo -->
                <?php if ($room['photo']): ?>
                    <img src="<?php echo htmlspecialchars($room['photo']); ?>" alt="Room Photo" class="room-photo">
                <?php else: ?>
                    <p>No photo available.</p>
                <?php endif; ?>

                <p><strong>Location:</strong> <?php echo htmlspecialchars($room['location']); ?></p>
                <p><strong>Monthly Rent:</strong> $<?php echo number_format($room['rent'], 2); ?></p>
                <p><strong>Number of Rooms:</strong> <?php echo $room['number_of_rooms']; ?></p>
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($room['description'])); ?></p>
            </div>
        <?php endif; ?>

        <!-- Back to Dashboard Button -->
        <div>
            <a href="user_dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
