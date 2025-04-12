<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? null;
$location = $_GET['location'] ?? "";
$property_type = $_GET['property_type'] ?? "";
$price = $_GET['price'] ?? "";
$room_id = $_GET['room_id'] ?? "";
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Handle verification toggle
if (isset($_GET['toggle_verify']) && is_numeric($_GET['toggle_verify'])) {
    $id = intval($_GET['toggle_verify']);
    $sql = "UPDATE rooms SET verified = NOT verified WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch rooms with search filter
// Fetch rooms with search filter and order from newest to oldest
$sql = "SELECT * FROM rooms WHERE location LIKE ? OR description LIKE ? ORDER BY id DESC";
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="main-container">
    <H1> Welcome to Admin dashboard.</H2>

    <div class="search-bar-container">
        <form action="user_dashboard.php" method="GET" class="search-form">
            <input type="text" name="location" placeholder="📍 Location" value="<?php echo htmlspecialchars($location); ?>">

            <select id="selectproperty " name="property_type">
                <option value="">🏠Property Type</option>
                <option value="1 BHK" <?php if ($property_type == "1 BHK") echo "selected"; ?>>1 BHK</option>
                <option value="2 BHK" <?php if ($property_type == "2 BHK") echo "selected"; ?>>2 BHK</option>
                <option value="3 BHK" <?php if ($property_type == "3 BHK") echo "selected"; ?>>3 BHK</option>
                <option value="Commercial Property" <?php if ($property_type == "Commercial Property") echo "selected"; ?>>Commercial Property</option>
                <option value="Shutters" <?php if ($property_type == "Shutters") echo "selected"; ?>>Shutters</option>
            </select>

            <input type="number" name="price" placeholder="💰 Max Price" min="1" value="<?php echo htmlspecialchars($price); ?>">
            <input type="text" name="room_id" placeholder="🏠 Room ID" value="<?php echo htmlspecialchars($room_id); ?>">
            <button type="submit">🔍 Search</button>
            <button type="button" id="clear-filters">Clear Filter</button> 
        </form>
    </div>

    <div class="container">
        <h1>Manage Rooms</h1>
        <div class="room-cards">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="room-card">
                        <a href="room_details.php?id=<?php echo $row['id']; ?>">
                            <?php 
                            $photos = json_decode($row['photos'], true);
                            if (!empty($photos) && isset($photos[0])) {
                                echo "<img src='" . htmlspecialchars($photos[0]) . "' alt='Room Photo'>";
                            } else {
                                echo "<img src='default-room.jpg' alt='Default Room Photo'>";
                            }
                            ?>
                            <h3><?php echo "ID " . htmlspecialchars($row['id']) . ", " . htmlspecialchars($row['location']) . ", " . htmlspecialchars($row['property_type']); ?></h3>
                            <p><strong>📍 Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <p style="color:red;"><strong>💰 Monthly Rent:</strong> Rs.<?php echo htmlspecialchars($row['rent']); ?></p>
                            <p><strong>🏠 Property Type:</strong> <?php echo htmlspecialchars($row['property_type']); ?></p>
                        </a>
                        <div class="action-buttons">
                            <a href="admin_dashboard.php?toggle_verify=<?php echo $row['id']; ?>" class="verify-btn">
                                <?php echo $row['verified'] ? '✅ Verified' : '❌ Unverified'; ?>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No rooms found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
