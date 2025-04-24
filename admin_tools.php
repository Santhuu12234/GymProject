<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "gym_qr_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $qrContent = $_POST['qr_content'] ?? '';
    $description = $_POST['description'] ?? '';
    $imagePath = '';

    if (empty($qrContent)) {
        die("QR content is required.");
    }

    if (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = basename($_FILES['qr_image']['name']);
        $targetFile = $uploadDir . uniqid() . '_' . $filename;

        if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        } else {
            die("Failed to upload image.");
        }
    }

    $stmt = $conn->prepare("INSERT INTO qrcodes (qr_content, description, image_path) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $qrContent, $description, $imagePath);

    if ($stmt->execute()) {
        echo "QR Code content and image saved successfully!";
    } else {
        echo "Failed to save data.";
    }

    $stmt->close();
}

$conn->close();
?>

<!-- HTML + JS -->
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Upload QR Code</title>
</head>
<body>
    <h2>Upload QR Code Image and Details</h2>
    <form method="POST" enctype="multipart/form-data" id="qrForm">
        <label>QR Image:</label><br>
        <input type="file" name="qr_image" id="qrImage" accept="image/*" required><br><br>

        <label>QR Content (auto-filled):</label><br>
        <input type="text" name="qr_content" id="qr_content" readonly required><br><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4" cols="30" required></textarea><br><br>

        <button type="submit" id="submitBtn" disabled>Submit</button>
    </form>

    <script type="module">
        import QrScanner from "https://cdn.jsdelivr.net/npm/qr-scanner@1.4.2/qr-scanner.min.js";

        const qrInput = document.getElementById("qrImage");
        const qrContentField = document.getElementById("qr_content");
        const submitBtn = document.getElementById("submitBtn");

        qrInput.addEventListener("change", async () => {
            const file = qrInput.files[0];
            if (!file) return;

            const result = await QrScanner.scanImage(file, { returnDetailedScanResult: true })
                .catch(() => null);

            if (result && result.data) {
                qrContentField.value = result.data;
                submitBtn.disabled = false;
            } else {
                alert("Failed to detect QR content. Please try another image.");
                qrContentField.value = '';
                submitBtn.disabled = true;
            }
        });
    </script>
</body>
</html>
