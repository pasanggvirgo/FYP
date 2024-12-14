<?php
// Start session
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // Default value will be 'user'

    // Validate input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Database connection (update with your database credentials)
        $conn = new mysqli("localhost", "root", "", "fyp");

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Insert user data into database
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            // Redirect to signup page with success message
            header("Location: signup.php?signup_success=1");
            exit();
        } else {
            $error = "Error: Could not complete signup. Please try again.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Renting - Signup</title>
    <link rel="stylesheet" href="script.css">
</head>
<body>
    <div class="signup-container">
        <h2>Signup Page</h2>

        <!-- Success Message -->
        <?php if (isset($_GET['signup_success']) && $_GET['signup_success'] == 1): ?>
            <p class="success">Signup successful! Go to login page. You can now log in.</p>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="/fyp/signup.php" method="POST">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Gmail:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Set Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <input type="hidden" id="role" name="role" value="user">
            <div class="form-group">
                <button type="submit">Sign Up</button>
            </div>
        </form>
        <div class="form-group">
            <a href="index.php" class="login-link">Back to Login</a>
        </div>
    </div>
</body>
</html>
