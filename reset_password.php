<?php
session_start();
include('config/db_connect.php');

$msg = "";
$error = "";
$show_form = false;

// 1. VERIFY TOKEN
if (isset($_GET['token']) && isset($_GET['role'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $role = mysqli_real_escape_string($conn, $_GET['role']);
    
    // Validate Role to prevent SQL Injection
    if(!in_array($role, ['admin', 'doctor', 'patient'])) {
        die("Invalid role specified.");
    }

    $current_time = date("Y-m-d H:i:s");
    
    // Check if token exists and is NOT expired
    $check_sql = "SELECT * FROM $role WHERE reset_token='$token' AND reset_expiry > '$current_time' LIMIT 1";
    $result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($result) > 0) {
        $show_form = true;
    } else {
        $error = "This password reset link is invalid or has expired.";
    }
} else if (!isset($_POST['reset_password_btn'])) {
    header("Location: login.php");
    exit(0);
}

// 2. PROCESS NEW PASSWORD
if (isset($_POST['reset_password_btn'])) {
    $token = mysqli_real_escape_string($conn, $_POST['token']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
        $show_form = true;
    } else {
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update Password & Clear Token
        $sql = "UPDATE $role SET password='$hashed_password', reset_token=NULL, reset_expiry=NULL WHERE reset_token='$token'";
        
        if (mysqli_query($conn, $sql)) {
            $msg = "Password reset successful! You can now login.";
            $show_form = false;
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - PMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .auth-card { max-width: 450px; margin: 80px auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold">Reset Password</h3>
            <p class="text-muted small">Create a new secure password.</p>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-success text-center">
                <?php echo $msg; ?> <br>
                <a href="login.php" class="fw-bold mt-2 d-inline-block">Go to Login</a>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($show_form): ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? $_POST['token']); ?>">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($_GET['role'] ?? $_POST['role']); ?>">

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>

            <button type="submit" name="reset_password_btn" class="btn btn-success w-100 py-2 fw-bold">Update Password</button>
        </form>
        <?php endif; ?>
        
        <?php if(!$show_form && !$msg): ?>
            <div class="text-center mt-3">
                <a href="forgot_password.php" class="btn btn-outline-primary btn-sm">Request New Link</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>