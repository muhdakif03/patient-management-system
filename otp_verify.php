<?php
session_start();
include('config/db_connect.php');

$error = "";

// Security: Kick user out if they didn't come from login.php
if (!isset($_SESSION['temp_email'])) {
    header("Location: login.php");
    exit(0);
}

if (isset($_POST['verify_btn'])) {
    $entered_otp = $_POST['otp_code'];
    $email = $_SESSION['temp_email'];
    $role = $_SESSION['temp_role'];

    // 1. Query to get the saved OTP and Expiry using Prepared Statements
    $stmt = $conn->prepare("SELECT * FROM $role WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $current_time = date("Y-m-d H:i:s");

    // 2. STRICTOR VALIDATION LOGIC (Using === instead of ==)
    if ($user && $entered_otp === $user['otp_code']) {
        if ($user['otp_expiry'] > $current_time) {
            // Success! Set real session variables
            $_SESSION['user_id'] = $user[$role . '_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $role;

            // [AUDIT LOG]
            if (function_exists('log_activity')) {
                log_activity($conn, "Login", "User logged in via OTP Verification");
            }

            // --- MULTI-DEVICE TRUST LOGIC ---
            $ua = $_SERVER['HTTP_USER_AGENT'];
            $device_name = "Unknown Device";
            
            if (strpos($ua, 'Windows') !== false) $device_name = "Windows PC";
            elseif (strpos($ua, 'Mac') !== false) $device_name = "Mac";
            elseif (strpos($ua, 'Android') !== false) $device_name = "Android Phone";
            elseif (strpos($ua, 'iPhone') !== false) $device_name = "iPhone";
            
            if (strpos($ua, 'Chrome') !== false) $device_name .= " (Chrome)";
            elseif (strpos($ua, 'Firefox') !== false) $device_name .= " (Firefox)";
            elseif (strpos($ua, 'Safari') !== false && strpos($ua, 'Chrome') === false) $device_name .= " (Safari)";
            elseif (strpos($ua, 'Edge') !== false) $device_name .= " (Edge)";

            $token = bin2hex(random_bytes(32)); 
            $id_column = $role . "_id";
            $user_id = $user[$id_column];
            
            // Prepared statement for inserting trusted devices
            $insert_stmt = $conn->prepare("INSERT INTO trusted_devices (user_id, role, device_name, token) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("isss", $user_id, $role, $device_name, $token);
            $insert_stmt->execute();
            $insert_stmt->close();
            
            setcookie("remember_me", $token, time() + (86400 * 30), "/");

            // --- CLEANUP using Prepared Statement ---
            $update_stmt = $conn->prepare("UPDATE $role SET otp_code=NULL, otp_expiry=NULL WHERE email=?");
            $update_stmt->bind_param("s", $email);
            $update_stmt->execute();
            $update_stmt->close();

            unset($_SESSION['temp_email']);
            unset($_SESSION['temp_role']);

            // Redirect based on Role
            if ($role == 'admin') {
                header("Location: admin/index.php");
            } elseif ($role == 'doctor') {
                header("Location: doctor/index.php");
            } else {
                header("Location: patient/index.php");
            }
            exit(0);

        } else {
            $error = "OTP has expired. Please try logging in again.";
        }
    } else {
        $error = "Invalid OTP Code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP - PMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .login-card { max-width: 400px; margin: 100px auto; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); background: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="login-card">
        <h4 class="text-center mb-3">Two-Factor Authentication</h4>
        <p class="text-muted text-center">We sent a code to <strong><?php echo $_SESSION['temp_email']; ?></strong></p>
        <p class="text-center small text-primary">New device detected. Please verify your identity.</p>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="otp_verify.php" method="POST">
            <div class="mb-3">
                <label>Enter 6-Digit Code</label>
                <input type="text" name="otp_code" class="form-control text-center" maxlength="6" style="font-size: 24px; letter-spacing: 5px;" required>
            </div>
            <button type="submit" name="verify_btn" class="btn btn-success w-100">Verify & Trust Device</button>
        </form>
    </div>
</div>

</body>
</html>