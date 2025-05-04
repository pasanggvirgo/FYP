<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
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
    <!-- noUiSlider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.7.0/dist/nouislider.min.css">
    <!-- noUiSlider JS -->
    <script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.0/dist/nouislider.min.js"></script>
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
    <H1> Welcome to Admin dashboard.</H2>
    <div class="search-bar-container">
        <form action="admin_dashboard.php" method="GET" class="search-form">
            <input type="text" name="location" placeholder="📍 Location" value="<?php echo htmlspecialchars($location); ?>">

            <select id="selectproperty " name="property_type">
                <option value="">🏠Property Type</option>
                <option value="1 BHK" <?php if ($property_type == "1 BHK") echo "selected"; ?>>1 BHK</option>
                <option value="2 BHK" <?php if ($property_type == "2 BHK") echo "selected"; ?>>2 BHK</option>
                <option value="3 BHK" <?php if ($property_type == "3 BHK") echo "selected"; ?>>3 BHK</option>
                <option value="Commercial Property" <?php if ($property_type == "Commercial Property") echo "selected"; ?>>Commercial Property</option>
                <option value="Shutters" <?php if ($property_type == "Shutters") echo "selected"; ?>>Shutters</option>
            </select>

            <div class="price-slider-container" style="display: flex; text-align: center; flex-direction: column; gap: 0px; max-width: 400px;">
                <label for="price-range">💰 Price Range:</label>
                
                <div id="price-slider" style="width: 180px;"></div>

                <div id="priceLabel">
                    Rs. <span id="min-price-display">0</span> - Rs. <span id="max-price-display">100000</span>
                </div>

                <!-- Hidden inputs for form submission -->
                <input type="hidden" name="min_price" id="minPriceInput">
                <input type="hidden" name="max_price" id="maxPriceInput">
            </div>


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
                        <a href="room_details_admin.php?id=<?php echo $row['id']; ?>">
                            <?php 
                            $photos = json_decode($row['photos'], true);
                            if (!empty($photos) && isset($photos[0])) {
                                echo "<img src='" . htmlspecialchars($photos[0]) . "' alt='Room Photo'>";
                            } else {
                                echo "<img src='default-room.jpg' alt='Default Room Photo'>";
                            }
                            ?>
                            <h3><?php echo "ID " . htmlspecialchars($row['id']) . ", " . htmlspecialchars($row['location1']) . ", " . htmlspecialchars($row['property_type']); ?></h3>
                            <p style="color:red;"><strong>💰 Monthly Rent:</strong> Rs.<?php echo htmlspecialchars($row['rent']); ?></p>
                            <p><strong>🏠 Property Type:</strong> <?php echo htmlspecialchars($row['property_type']); ?></p>
                        </a>
                        <?php
                    $status = htmlspecialchars($row['availability']);
                    $color = ($status === 'Available') ? 'green' : 'red';
                    ?>
                    <p> <span style="color: <?php echo $color; ?>;"><?php echo $status; ?></span></p>
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
<script>
        const minFromURL = <?php echo isset($_GET['min_price']) ? $_GET['min_price'] : 0; ?>;
    const maxFromURL = <?php echo isset($_GET['max_price']) ? $_GET['max_price'] : 100000; ?>;

    const priceSlider = document.getElementById('price-slider');
    noUiSlider.create(priceSlider, {
        start: [minFromURL, maxFromURL],
        connect: true,
        step: 1000,
        range: {
            min: 0,
            max: 100000
        },
        format: {
            to: value => Math.round(value),
            from: value => Number(value)
        }
    });

    const minPriceDisplay = document.getElementById('min-price-display');
    const maxPriceDisplay = document.getElementById('max-price-display');
    const minPriceInput = document.getElementById('minPriceInput');
    const maxPriceInput = document.getElementById('maxPriceInput');

    priceSlider.noUiSlider.on('update', function (values) {
        minPriceDisplay.textContent = values[0];
        maxPriceDisplay.textContent = values[1];
        minPriceInput.value = values[0];
        maxPriceInput.value = values[1];
    });
    function updateSliderLabel() {
        const min = document.getElementById('minPrice').value;
        const max = document.getElementById('maxPrice').value;
        document.getElementById('priceLabel').innerText = `Rs. ${min} - Rs. ${max}`;
    }

    // Ensure correct order if user sets max lower than min
    const form = document.querySelector('.search-form');
    form.addEventListener('submit', function (e) {
        const minInput = document.getElementById('minPrice');
        const maxInput = document.getElementById('maxPrice');
        if (parseInt(minInput.value) > parseInt(maxInput.value)) {
            // Swap values
            [minInput.value, maxInput.value] = [maxInput.value, minInput.value];
        }
    });
</script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
