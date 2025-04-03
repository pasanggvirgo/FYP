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
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 25px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            font-size: 28px;
            color: #222;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .room-details {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            text-align: left;
        }
        .room-details p {
            font-size: 16px;
            line-height: 1.6;
            margin: 10px 0;
        }
        .room-details strong {
            color: #007BFF;
        }

        /* Slider Styling */
        .slider {
            position: relative;
            max-width: 600px;
            margin: 20px auto;
            overflow: hidden;
        }
        .slides {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }
        .slide {
            flex-shrink: 0; /* Prevent slides from shrinking */
            width: 100%;
            /* Enforcing a fixed aspect ratio, e.g., 16:9 */
            position: relative;
            padding-top: 56.25%; /* Aspect ratio 16:9 (height is 56.25% of width) */
        }
        .slide img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures the image maintains the aspect ratio */
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .slider .prev, .slider .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }
        .slider .prev {
            left: 10px;
        }
        .slider .next {
            right: 10px;
        }

        /* Error Message */
        .error {
            color: #f44336;
            font-weight: bold;
            font-size: 16px;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 500;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s ease-in-out;
            border: none;
            cursor: pointer;
        }
        .btn-back {
            background: #007BFF;
        }
        .btn-back:hover {
            background: #0056b3;
        }
        .btn-contact {
            background: #28a745;
            margin-left: 10px;
        }
        .btn-contact:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Room Details</h1>

        <!-- Display error message if any -->
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php else: ?>
            <div class="room-details">
                <!-- Slider for room photos -->
                <?php 
                // Decode the JSON array of photos
                $photos = json_decode($room['photos'], true);

                if (!empty($photos)) {
                    echo '<div class="slider">';
                    echo '<div class="slides">';
                    foreach ($photos as $photo) {
                        echo '<div class="slide"><img src="' . htmlspecialchars($photo) . '" alt="Room Photo"></div>';
                    }
                    echo '</div>';
                    echo '<button class="prev">❮</button>';
                    echo '<button class="next">❯</button>';
                    echo '</div>';
                } else {
                    echo "<p>No photos available.</p>";
                }
                ?>
                
                <p><strong>Location:</strong> 🌍 <?php echo htmlspecialchars($room['location']); ?></p>
                <p><strong>Monthly Rent:</strong> 💵 Rs.<?php echo number_format($room['rent'], 2); ?></p>
                <p><strong>Seller Contact:</strong> 📞 <?php echo htmlspecialchars($room['seller_contact']); ?></p>
                <p><strong>Property Type:</strong> 🏠 <?php echo htmlspecialchars($room['property_type']); ?></p>
                <p><strong>Furnishing Status:</strong> 🛋️ <?php echo htmlspecialchars($room['furnishing_status']); ?></p>
                <p><strong>Description:</strong> ✨ <?php echo nl2br(htmlspecialchars($room['description'])); ?></p>

            </div>
        <?php endif; ?>

        <!-- Buttons -->
        <div>
            <a href="user_dashboard.php" class="btn btn-back">Back to Dashboard</a>
            <?php if (!$error): ?>
                <a href="tel:<?php echo htmlspecialchars($room['seller_contact']); ?>" class="btn btn-contact">Call Seller</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // JavaScript to control the slider functionality
        let currentIndex = 0;
        const slides = document.querySelectorAll('.slide');
        const totalSlides = slides.length;

        // Show the first image
        showSlide(currentIndex);

        document.querySelector('.next').addEventListener('click', function() {
            if (currentIndex < totalSlides - 1) {
                currentIndex++;
            } else {
                currentIndex = 0;
            }
            showSlide(currentIndex);
        });

        document.querySelector('.prev').addEventListener('click', function() {
            if (currentIndex > 0) {
                currentIndex--;
            } else {
                currentIndex = totalSlides - 1;
            }
            showSlide(currentIndex);
        });

        function showSlide(index) {
            // Hide all slides
            slides.forEach(slide => slide.style.display = 'none');
            // Show the current slide
            slides[index].style.display = 'block';
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
