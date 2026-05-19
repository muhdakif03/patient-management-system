<?php
session_start();
include('config/db_connect.php');
include('config/email_helper.php'); // Load Email Engine

$msg = "";
$error = "";

if (isset($_POST['reset_request'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $roles = ['admin', 'doctor', 'patient'];
    $found = false;

    foreach ($roles as $role) {
        $check_sql = "SELECT * FROM $role WHERE email='$email' LIMIT 1";
        $result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($result) > 0) {
            $found = true;
            $user = mysqli_fetch_assoc($result);
            
            // 1. Generate Secure Token
            $token = bin2hex(random_bytes(32));
            
            // 2. Set Expiry
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // 3. Save to Database
            $id_col = $role . "_id";
            $user_id = $user[$id_col];
            
            $update_sql = "UPDATE $role SET reset_token='$token', reset_expiry='$expiry' WHERE $id_col='$user_id'";
            
            if (mysqli_query($conn, $update_sql)) {
                // 4. Send Email via Central Helper
                $reset_link = "http://localhost/pms_project/reset_password.php?token=$token&role=$role";
                
                $subject = "Password Reset Request";
                $message = "Click the following link to reset your password: " . $reset_link;

                send_system_email($email, $subject, $message);

                $msg = "A reset link has been sent to your email address.";
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
            break;
        }
    }

    if (!$found) {
        $error = "We could not find an account with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - PMS</title>
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
            <h3 class="fw-bold">Forgot Password?</h3>
            <p class="text-muted small">Enter your email to receive a reset link.</p>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-success">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="name@example.com">
            </div>

            <button type="submit" name="reset_request" class="btn btn-primary w-100 py-2 fw-bold">Send Reset Link</button>
        </form>

        <div class="mt-4 text-center border-top pt-3">
            <a href="login.php" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
        </div>
    </div>
</div>

</body>
</html>