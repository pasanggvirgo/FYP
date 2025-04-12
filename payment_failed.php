<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8d7da;
            text-align: center;
            padding: 50px;
        }
        .container {
            max-width: 500px;
            background: white;
            padding: 20px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #721c24;
        }
        p {
            color: #721c24;
            font-size: 18px;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: 0.3s;
        }
        .btn-retry {
            background-color: #dc3545;
            color: white;
        }
        .btn-retry:hover {
            background-color: #c82333;
        }
        .btn-back {
            background-color: #007bff;
            color: white;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>❌ Payment Failed</h1>
        <p>Unfortunately, your Esewa payment was unsuccessful.</p>
        <p>Please try again or contact support if the issue persists.</p>
        
        <a href="esewa_payment.php" class="btn btn-retry">🔄 Retry Payment</a>
        <a href="user_dashboard.php" class="btn btn-back">🏠 Back to Dashboard</a>
    </div>

</body>
</html>
