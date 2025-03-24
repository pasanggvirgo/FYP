<?php
session_start();

include('smtp/PHPMailerAutoload.php');

$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Input validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email exists
        $check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username or email already exists. Please choose a different one.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));

            // Insert user into the database
            $sql = "INSERT INTO users (username, email, password, role, token, is_verified) VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $role, $token);

            if ($stmt->execute()) {
                $verification_link = "http://localhost/fyp/verify.php?token=$token";
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'sherpapasang877@gmail.com';
                    $mail->Password = 'rlch gavl pjwy svsd';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('sherpapasang877@gmail.com', 'Kothamandu.com');
                    $mail->addAddress($email, $username);
                    $mail->Subject = 'Email Verification';
                    $mail->Body = "Click the link below to verify your email:\n$verification_link";

                    if ($mail->send()) {
                        echo "<script>alert('Verification link sent on your mail. please verify to login.'); window.location='index.php';</script>";
                        exit();
                    } else {
                        $error = "Could not send verification email.";
                    }
                } catch (Exception $e) {
                    $error = "Email error: " . $mail->ErrorInfo;
                }
            } else {
                $error = "Error: Could not complete signup.";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}

$conn->close();
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
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="signup.php" method="POST">
            <div class="form-group">
                <label for="username">UserName:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
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
            <div class="form-group">
                <label for="role">Sign Up As:</label>
                <select id="role" name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Sign Up</button>
            </div>
            
        </form>
        <div id="loginbutton " class="form-group">
               <a href="index.php"> <button>Back to login Page</button></a>
            </div>
    </div>
    
</body>
</html>
