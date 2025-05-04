<?php
session_start();
$conn = new mysqli("localhost", "root", "", "FYP");

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';


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

// Received messages (with optional search)
$received_stmt = $conn->prepare("
    SELECT m.id, m.message, m.timestamp, m.is_read, u.username AS sender_name, u.id AS sender_id
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.receiver_id = ? AND u.username LIKE ?
    ORDER BY m.timestamp DESC
");
$received_stmt->bind_param("is", $user_id, $search);
$received_stmt->execute();
$received_messages = $received_stmt->get_result();

// Sent messages (with optional search)
$sent_stmt = $conn->prepare("
    SELECT m.id, m.message, m.timestamp, u.username AS receiver_name, u.id AS receiver_id
    FROM messages m
    JOIN users u ON m.receiver_id = u.id
    WHERE m.sender_id = ? AND u.username LIKE ?
    ORDER BY m.timestamp DESC
");
$sent_stmt->bind_param("is", $user_id, $search);
$sent_stmt->execute();
$sent_messages = $sent_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>📨 My Messages</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        body {
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
       
        .messages-container {
            width: 90%;
            max-width: 1000px;
            margin: 50px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .section-title {
            font-size: 22px;
            margin-top: 40px;
            margin-bottom: 20px;
            color: #4caf50;
            border-bottom: 2px solid #4caf50;
            padding-bottom: 5px;
        }
        .message {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        .message:hover {
            background: #f1f1f1;
        }
        .sender, .receiver {
            font-weight: bold;
            color: #007BFF;
            margin-bottom: 5px;
        }
        .timestamp {
            font-size: 12px;
            color: #888;
            margin-bottom: 10px;
        }
        .text {
            font-size: 15px;
            color: #555;
        }
        .no-messages {
            text-align: center;
            color: #999;
            margin-top: 20px;
        }
        .unread {
            font-weight: bold;
            background: #e9f7ef;
            border-left: 4px solid #4caf50;
        }
        a.message-link {
            text-decoration: none;
            color: inherit;
            display: block;
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
            <a class="nav-links" href="my_chats.php">💬 My Chats</a>
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

    <div class="messages-container">
    <form method="get" style="text-align:center; margin-bottom: 20px;">
    <input type="text" name="search" placeholder="🔍 Search by username" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="padding: 10px; width: 60%; max-width: 400px; border-radius: 5px; border: 1px solid #ccc;">
    <button type="submit" style="padding: 10px 20px; border: none; background-color:#3FB8AF; color: white; border-radius: 5px; width:100px; display: inline; ">Search</button>
</form>

        <h2>📨 My Messages</h2>

        <div class="section-title">Received Messages</div>
        <?php if ($received_messages->num_rows > 0): ?>
            <?php while ($row = $received_messages->fetch_assoc()): ?>
                <a class="message-link" href="chat.php?receiver_id=<?php echo $row['sender_id']; ?>&sender_id=<?php echo $user_id; ?>">
                    <div class="message <?php echo $row['is_read'] == 0 ? 'unread' : ''; ?>">
                        <div class="sender">From: <?php echo htmlspecialchars($row['sender_name']); ?></div>
                        <div class="timestamp"><?php echo date('d M Y, h:i A', strtotime($row['timestamp'])); ?></div>
                        <div class="text"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-messages">No received messages yet.</div>
        <?php endif; ?>

        <div class="section-title">Sent Messages</div>
        <?php if ($sent_messages->num_rows > 0): ?>
            <?php while ($row = $sent_messages->fetch_assoc()): ?>
                <a class="message-link" href="chat.php?receiver_id=<?php echo $row['receiver_id']; ?>&sender_id=<?php echo $user_id; ?>">
                    <div class="message">
                        <div class="receiver">To: <?php echo htmlspecialchars($row['receiver_name']); ?></div>
                        <div class="timestamp"><?php echo date('d M Y, h:i A', strtotime($row['timestamp'])); ?></div>
                        <div class="text"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-messages">No sent messages yet.</div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
