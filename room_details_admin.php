<?php

session_start();
include('smtp/PHPMailerAutoload.php');

// Database connection
$conn = new mysqli("localhost", "root", "", "FYP");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message
$error = null;

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
// Retrieve room ID from URL parameter
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $room_id = intval($_GET['id']);

    // Fetch room details from database
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc(); // ✅ this line fetches the room data
    } else {
        $error = "Room not found.";
    }
    

    $stmt->close();
} else {
    $error = "Invalid room ID.";
}


if (isset($_POST['book_appointment'])) {
    $your_email = $_POST['your_email'];
    $your_contact = $_POST['your_contact'];
    $seller_email = $_POST['seller_email'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sherpapasang877@gmail.com';
        $mail->Password = 'rlch gavl pjwy svsd'; // Consider moving to env variable
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('sherpapasang877@gmail.com', 'kothamandu.com');
        $mail->addAddress($seller_email);
        $mail->Subject = 'New Appointment Request from kothamandu.com';
        $mail->Body = "Hello,\n\nA user has requested an appointment for your listed room.\n\nContact Details:\nEmail: $your_email\nPhone: $your_contact\n\nPlease respond to the user to confirm.\n\nThank you,\nKothamandu Team";

        $mail->send();
        echo "<script>alert('Appointment request sent to the seller!');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Failed to send email: {$mail->ErrorInfo}');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 800px;
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
            border-radius: 10px;
        }
        .slides {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }
        .slide {
            flex-shrink: 0;
            width: 100%;
            position: relative;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
        }
        .slide img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Dots Styling */
        .dots {
            text-align: center;
            margin-top: 10px;
        }
        .dot {
            height: 12px;
            width: 12px;
            margin: 0 5px;
            background-color: #ddd;
            border-radius: 50%;
            display: inline-block;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .dot.active {
            background-color: white;
            border: 2px solid #007BFF;
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
    <h1>Room Details</h1>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php else: ?>
        <div class="room-details">
            <!-- Image Slider -->
            <?php 
            $photos = json_decode($room['photos'], true);
            if (!empty($photos)) {
                echo '<div class="slider">';
                echo '<div class="slides">';
                foreach ($photos as $photo) {
                    echo '<div class="slide"><img src="' . htmlspecialchars($photo) . '" alt="Room Photo"></div>';
                }
                echo '</div>';

                // Add Dots
                echo '<div class="dots">';
                foreach ($photos as $index => $photo) {
                    echo '<span class="dot" onclick="goToSlide(' . $index . ')"></span>';
                }
                echo '</div>';
                echo '</div>';
            } else {
                echo "<p>No photos available.</p>";
            }
            ?>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($room['location1']); ?></p>

            <p><strong>Detailed Location:</strong> <?php echo htmlspecialchars($room['location']); ?></p>
            <p><strong>Monthly Rent:</strong> Rs.<?php echo number_format($room['rent'], 2); ?></p>
            <p><strong>Property Type:</strong> <?php echo htmlspecialchars($room['property_type']); ?></p>
            <p><strong>Furnishing Status:</strong> <?php echo htmlspecialchars($room['furnishing_status']); ?></p>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($room['description'])); ?></p>

            <p><strong>Seller contact:</strong> <?php echo nl2br(htmlspecialchars($room['seller_contact'])); ?></p>
            <?php $locationEncoded = urlencode($room['location']); ?>
            <?php
                    $status = htmlspecialchars($room['availability']);
                    $color = ($status === 'Available') ? 'green' : 'red';
                    
                    ?>
                    <p><strong>Availability:</strong> <span style="color: <?php echo $color; ?>;"><?php echo $status; ?></span></p>

                    <!-- Google Map Embed -->
                    <iframe 
                        width="100%" 
                        height="300" 
                        frameborder="0" 
                        style="border:0; border-radius: 8px; margin-top: 10px;" 
                        src="https://www.google.com/maps/embed/v1/place?key=AIzaSyCIADFY8ATJhZgpNnoOi-Ks5UxdhW4XVAc&q=<?php echo $locationEncoded; ?>" 
                        allowfullscreen>
                    </iframe>

        </div>

    <?php endif; ?>
   



</div>

                </div>

<script>
    let currentIndex = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = slides.length;

    function showSlide(index) {
        slides.forEach(slide => slide.style.display = 'none');
        dots.forEach(dot => dot.classList.remove('active'));

        slides[index].style.display = 'block';
        dots[index].classList.add('active');
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalSlides;
        showSlide(currentIndex);
    }

    showSlide(currentIndex);
    setInterval(nextSlide, 3000);
</script>

</body>
</html>
