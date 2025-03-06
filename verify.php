<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fyp");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if the token is present in the URL
if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];
$error = "";

// Check if the token exists and if the user is not already verified
$sql = "SELECT * FROM users WHERE token=? AND is_verified=0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    if (isset($_POST['verify'])) {
        // Update the user's verification status
        $update = "UPDATE users SET is_verified=1 WHERE token=?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            echo "<script>alert('Verification successful! Redirecting to homepage...'); window.location='index.php';</script>";
            exit();
        } else {
            $error = "Error updating verification status.";
        }
    }
} else {
    $error = "Invalid or expired token.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 100px;
        }
        .container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.2);
        }
        .error {
            color: red;
            font-weight: bold;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php else: ?>
            <form method="POST">
                <button type="submit" name="verify">Click here to verify</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
