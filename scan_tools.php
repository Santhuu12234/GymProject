<?php
session_start(); // Start the session
$equipmentInfo = "";
$status = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['qr_data'])) {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "gym_qr_db";

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        $_SESSION['equipmentInfo'] = "Connection failed: " . $conn->connect_error;
    } else {
        $scannedContent = $_POST['qr_data'];
        $stmt = $conn->prepare("SELECT description FROM qrcodes WHERE qr_content = ?");
        $stmt->bind_param("s", $scannedContent);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($description);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            $_SESSION['equipmentInfo'] = $description;
        } else {
            $_SESSION['equipmentInfo'] = "Equipment is not available";
        }

        $stmt->close();
        $conn->close();
    }

    // Redirect to about_tool.php (not .html)
    header("Location: about_tool.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Code Scanner</title>
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

        #video-container {
            width: 300px;
            height: 300px;
            position: relative;
            overflow: hidden;
            border: 5px solid #333;
            border-radius: 10px;
        }

        video#preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #output {
            font-size: 18px;
            font-weight: bold;
            color: green;
            margin-top: 20px;
            text-align: center;
        }

        #hidden-form {
            display: none;
        }
        .navbar {
            background-color:rgb(45, 45, 45);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            height: 65px;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
        }
        .navbar .logo {
            color:rgb(195, 195, 195);
            font-size: 24px;
            font-weight: bold;
        }
        /* Align links to the right */

    </style>
</head>
<body>
<nav class="navbar">
        <div class="logo"><i class="fa-solid fa-heartbeat" style="font-size: 25px;"></i> Men's Gym</div>
    </nav>

    <h2>QR Code Scanner</h2>
    <div id="video-container">
        <video id="preview" autoplay muted></video>
    </div>
    <div id="output">Scan a QR Code...</div>

    <!-- Hidden form for POST -->
    <form id="hidden-form" method="POST">
        <input type="hidden" name="qr_data" id="qr_data">
    </form>

    <script type="module">
        import QrScanner from 'https://cdn.jsdelivr.net/npm/qr-scanner@1.4.2/qr-scanner.min.js';

        const video = document.getElementById('preview');
        const output = document.getElementById('output');
        const qrInput = document.getElementById('qr_data');
        const form = document.getElementById('hidden-form');

        const scanner = new QrScanner(video, result => {
            output.textContent = 'Scanned QR Code: ' + result;
            qrInput.value = result.data ?? result;
            scanner.stop();
            setTimeout(() => {
                form.submit();
            }, 1000);
        }, {
            highlightScanRegion: true,
            highlightCodeOutline: true
        });

        scanner.start();
    </script>

</body>
</html>
