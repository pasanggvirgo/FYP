<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
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




$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$location = isset($_GET['location']) ? trim($_GET['location']) : "";
$property_type = isset($_GET['property_type']) ? trim($_GET['property_type']) : "";
$room_id = isset($_GET['room_id']) ? trim($_GET['room_id']) : "";
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : null;

$params = [];
$types = "";
$sql = "SELECT * FROM rooms WHERE verified = 1 AND availability = 'Available'";

if (!empty($search)) {
    $sql .= " AND (location LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if (!empty($location)) {
    $sql .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}

if (!empty($property_type)) {
    $sql .= " AND property_type = ?";
    $params[] = $property_type;
    $types .= "s";
}

if (!empty($room_id)) {
    $sql .= " AND id = ?";
    $params[] = $room_id;
    $types .= "i";
}

if (!is_null($min_price)) {
    $sql .= " AND rent >= ?";
    $params[] = $min_price;
    $types .= "i";
}

if (!is_null($max_price)) {
    $sql .= " AND rent <= ?";
    $params[] = $max_price;
    $types .= "i";
}

// If no filters applied
if (empty($search) && empty($location) && empty($property_type) && empty($room_id) && is_null($min_price) && is_null($max_price)) {
    $sql .= " ORDER BY rent ASC LIMIT 8";
}



$stmt = $conn->prepare($sql);

if (!empty($params) && !empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- noUiSlider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.7.0/dist/nouislider.min.css">
    <!-- noUiSlider JS -->
    <script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.0/dist/nouislider.min.js"></script>

</head>
<body>
<div class="main-container">
    <div class="navbar">
        <div class="logo">
            <a href="user_dashboard.php"><img id="homeimg" class="icon" src="house.png"></a>
        </div>
        <div class="nav-links">
        <a class="nav-links" href="my_chats.php">💬 My Chats </a>
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

    <img id="bgimg" src="bg.jpg" alt="Background Image">

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
    <div class="carousel">
    <div class="carousel-inner">
        <img src="slider1.png" alt="Slide 1">
        <img src="slider2.png" alt="Slide 2">
    </div>
</div>

    <div class="container">
    <h1>
    <?php if (!empty($search) || !empty($location) || !empty($property_type) || !empty($price) || !empty($room_id)): ?>
        Your Search Results (<?php echo $result->num_rows; ?> found)
    <?php else: ?>
        Most Affordable Rooms in Kathmandu....
    <?php endif; ?>
</h1>

        <div class="room-cards">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="room-card">
            <?php if ($user_id): ?>
    <a href="room_details.php?id=<?php echo $row['id']; ?>">
<?php else: ?>
    <a href="#" class="login-required">
<?php endif; ?>

                    <?php 
                    // Decode the JSON array of photos
                    $photos = json_decode($row['photos'], true);
                    if (!empty($photos) && isset($photos[0])) {
                        // Display the first photo
                        $first_photo = htmlspecialchars($photos[0]);
                        echo "<img src='$first_photo' alt='Room Photo'>";
                    } else {
                        // Default photo if no photos are available
                        echo "<img src='default-room.jpg' alt='Default Room Photo'>";
                    }
                    ?>
                    <h3><?php echo "ID ".htmlspecialchars($row['id']).", ".htmlspecialchars($row['location1']).", ".htmlspecialchars($row['property_type']); ?></h3>
                    <p style="color:#3FB8AF;"><strong>💰 Monthly Rent:</strong> Rs.<?php echo htmlspecialchars($row['rent']); ?></p>
                    <p><strong>🏠Property Type:</strong> <?php echo htmlspecialchars($row['property_type']); ?></p>
                    <?php
                    $status = htmlspecialchars($row['availability']);
                    $color = ($status === 'Available') ? '#3FB8AF' : 'red';
                    ?>
                    <p> <span style="color: <?php echo $color; ?>;"><?php echo $status; ?></span></p>

                </a>

                <button class="favorite-btn" data-room-id="<?php echo $row['id']; ?>">
                    <?php
                    $fav_stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND room_id = ?");
                    $fav_stmt->bind_param("ii", $user_id, $row['id']);
                    $fav_stmt->execute();
                    $fav_result = $fav_stmt->get_result();
                    $is_favorite = $fav_result->num_rows > 0;
                    echo $is_favorite ? "❌ Remove from Favorites" : "❤️ Add to Favorites";
                    ?>
                </button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No rooms found matching your search.</p>
    <?php endif; ?>
</div>

       
    </div>

    <div id="about-section">
        <div class="about-container">
            <div class="about-text">
                <h2>About Kothamandu</h2>
                <p>
                    Welcome to <strong>Kothamandu.com</strong>, your ultimate destination for finding the perfect rental rooms in Nepal!...
                </p>
            </div>
            <div class="about-image">
                <img src="images.jpg" alt="Kothamandu Room Rentals">
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 Kothamandu.com | All Rights Reserved</p>

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


    document.addEventListener("DOMContentLoaded", function () {
    // Carousel setup
    let index = 0;
    const slides = document.querySelectorAll(".carousel-inner img");
    function showSlide() {
        slides.forEach((slide, i) => {
            slide.style.display = i === index ? "block" : "none";
        });
        index = (index + 1) % slides.length;
    }
    setInterval(showSlide, 1500);
    showSlide();

    // Login alert for room cards
    document.querySelectorAll(".login-required").forEach(el => {
        el.addEventListener("click", function (e) {
            e.preventDefault();
            alert("Please log in to view room details.");
        });
    });
});


$(document).ready(function() {
    $(".favorite-btn").click(function() {
        var button = $(this);
        var roomId = button.data("room-id");

        $.ajax({
            url: "favorite_handler.php",
            type: "POST",
            data: { room_id: roomId },
            dataType: "json",
            success: function(response) {
                if (response.status === "added") {
                    button.text("❌ Remove from Favorites");
                } else if (response.status === "removed") {
                    button.text("❤️ Add to Favorites");
                }
            },
            error: function() {
                alert("An error occurred. Please try again.");
            }
        });
    });
});

document.getElementById("clear-filters").addEventListener("click", function() {
        window.location.href = "user_dashboard.php"; // Reload page without search parameters
    });

</script>

</body>
</html>

<?php
// Close database connection
$stmt->close();
$conn->close();
?>
