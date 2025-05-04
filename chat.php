<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch favorite count for navbar
$fav_count = 0;
$fav_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM favorites WHERE user_id = ?");
$fav_stmt->bind_param("i", $user_id);
$fav_stmt->execute();
$fav_result = $fav_stmt->get_result();
if ($row = $fav_result->fetch_assoc()) {
    $fav_count = $row['total'];
}
$fav_stmt->close();

// Validate receiver
if (!isset($_GET['receiver_id'])) {
    die("No user selected for chat.");
}

$receiver_id = intval($_GET['receiver_id']);

// Fetch receiver's username
$receiver_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$receiver_stmt->bind_param("i", $receiver_id);
$receiver_stmt->execute();
$receiver_result = $receiver_stmt->get_result();
$receiver_username = "User"; // fallback
if ($row = $receiver_result->fetch_assoc()) {
    $receiver_username = htmlspecialchars($row['username']);
}
$receiver_stmt->close();

// Send message
if (isset($_POST['send_message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message);
        $stmt->execute();
        $stmt->close();
        header("Location: chat.php?receiver_id=$receiver_id");
        exit();
    }
}


// Fetch chat history
$stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY timestamp ASC
");
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        body {
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
       
        .chat-container {
            width: 90%;
            max-width: 700px;
            margin: 50px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            min-height: 600px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .chat-box {
            flex: 1;
            overflow-y: auto;
            padding-right: 10px;
            margin-bottom: 20px;
        }
        .message {
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 70%;
            word-wrap: break-word;
            position: relative;
        }
        .sent {
            background-color: #007BFF;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .received {
            background-color: #f1f1f1;
            color: #333;
            margin-right: auto;
            text-align: left;
        }
        .message small {
            display: block;
            font-size: 11px;
            margin-top: 5px;
            color: #ddd;
        }
        form {
            display: flex;
            margin-top: 10px;
        }
        input[type="text"] {
            flex: 1;
            padding: 10px 15px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
        }
        button {
            padding: 10px 20px;
            border: none;
            background: #28a745;
            color: white;
            border-radius: 20px;
            cursor: pointer;
            margin-left: 10px;
            width:90px;
        }
        button:hover {
            background: #218838;
        }
    </style>
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

<div class="chat-container">
<h2>Chatting with <?php echo $receiver_username; ?></h2>

    <div class="chat-box">
        <?php if ($messages->num_rows > 0): ?>
            <?php while ($row = $messages->fetch_assoc()): ?>
                <div class="message <?php echo ($row['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                    <?php echo htmlspecialchars($row['message']); ?>
                    <small><?php echo date('d M Y, h:i A', strtotime($row['timestamp'])); ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; color: #aaa;">No messages yet.</div>
        <?php endif; ?>
    </div>

    <form method="POST">
        <input type="text" name="message" placeholder="Type a message..." required>
        <button type="submit" name="send_message">Send</button>
    </form>
</div>

</body>
</html>
