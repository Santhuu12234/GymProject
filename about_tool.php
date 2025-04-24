<?php
session_start();
$equipmentInfo = $_SESSION['equipmentInfo'] ?? 'No information available';
session_unset(); // Optional: clear session after use
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Equipment Info</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(195, 195, 195);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .info-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.4);
            text-align: center;
            max-width: 600px;
        }
        h2 {
            color: #333;
        }
    </style>
</head>
<body>

<div class="info-box">
    <h2>Equipment Information</h2>
    <p><?php echo htmlspecialchars($equipmentInfo); ?></p>
</div>

</body>
</html>
