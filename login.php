<?php
session_start();
include('config/db_connect.php');
include('config/email_helper.php');

$error = "";

// --- MANUAL LOGIN ---
if (isset($_POST['login_btn'])) {
    $email = $_POST['email']; 
    $password = $_POST['password'];

    $roles = ['admin', 'doctor', 'patient'];
    $found_user = false;

    foreach ($roles as $role) {
        // 1. Check if email exists
        $stmt = $conn->prepare("SELECT * FROM $role WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // 2. Verify Password
            if (password_verify($password, $user['password'])) {
                $found_user = true;
                
                // 3. Check 'trusted_devices' Table for MFA Skip
                $is_trusted = false;
                
                if(isset($_COOKIE['remember_me'])) {
                    $token_from_cookie = $_COOKIE['remember_me'];
                    $user_id = $user[$role . '_id'];
                    
                    $trust_stmt = $conn->prepare("SELECT * FROM trusted_devices WHERE user_id = ? AND role = ? AND token = ? LIMIT 1");
                    $trust_stmt->bind_param("iss", $user_id, $role, $token_from_cookie);
                    $trust_stmt->execute();
                    $trust_res = $trust_stmt->get_result();
                    
                    if($trust_res->num_rows > 0) {
                        $is_trusted = true;
                    }
                    $trust_stmt->close();
                }

                if ($is_trusted) {
                    $_SESSION['user_id'] = $user[$role.'_id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $role;
                    
                    if(function_exists('log_activity')) log_activity($conn, "Login", "User logged in via Trusted Device (Skipped MFA)");

                    header("Location: $role/index.php");
                    exit(0);

                } else {
                    $otp = rand(100000, 999999);
                    $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
                    
                    $id_column = $role . "_id";
                    $user_id = $user[$id_column];
                    
                    // Save OTP
                    $update_stmt = $conn->prepare("UPDATE $role SET otp_code = ?, otp_expiry = ? WHERE $id_column = ?");
                    $update_stmt->bind_param("ssi", $otp, $expiry, $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    send_system_email($email, "Your OTP Code", "Your login OTP is: $otp");

                    $_SESSION['temp_email'] = $email;
                    $_SESSION['temp_role'] = $role;
                    
                    header("Location: otp_verify.php");
                    exit(0);
                }
            }
        }
        $stmt->close();
    }

    if (!$found_user) {
        $error = "Invalid Email or Password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - PMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .login-card { max-width: 400px; margin: 100px auto; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold">PMS Login</h3>
            <p class="text-muted small">Secure Hospital System</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
                <div class="text-end mt-1">
                    <a href="forgot_password.php" class="small text-decoration-none text-muted">Forgot Password?</a>
                </div>
            </div>
            
            <button type="submit" name="login_btn" class="btn btn-primary w-100 py-2 fw-bold">Login</button>
        </form>
        
        <div class="mt-4 text-center border-top pt-3">
            <a href="register.php" class="text-decoration-none">Register as Patient</a>
        </div>
    </div>
</div>

</body>
</html>