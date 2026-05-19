<?php
$file = 'otp_logs.txt';

// Handle Clear Action
if(isset($_GET['clear'])) {
    file_put_contents($file, "");
    header("Location: view_logs.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Debug Logs</title>
    <meta http-equiv="refresh" content="5"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #333; color: #fff; padding: 20px; font-family: monospace; }
        .log-box { background: #000; padding: 20px; border-radius: 5px; border: 1px solid #444; height: 80vh; overflow-y: auto; color: #00ff00; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">OTP Logs</h4>
        <a href="view_logs.php?clear=1" class="btn btn-sm btn-danger fw-bold">Clear Logs</a>
    </div>

    <div class="log-box">
        <?php
        if(file_exists($file)) {
            $content = file_get_contents($file);
            echo nl2br(htmlspecialchars($content));
        } else {
            echo "<span class='text-muted'>No OTPs generated yet...</span>";
        }
        ?>
    </div>
    <p class="text-center text-muted mt-2 small">Auto-refreshing every 5 seconds...</p>
</div>

<script>
    var logBox = document.querySelector('.log-box');
    logBox.scrollTop = logBox.scrollHeight;
</script>

</body>
</html>