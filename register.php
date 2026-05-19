<?php
session_start();
include('config/db_connect.php');

$msg = "";
$error = "";

if (isset($_POST['register_btn'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validation: Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // 2. Validation: Check if email already exists using a Prepared Statement
        // (Combining tables securely using UNION in a prepared query)
        $check_stmt = $conn->prepare("
            SELECT email FROM patient WHERE email = ? 
            UNION 
            SELECT email FROM doctor WHERE email = ? 
            UNION 
            SELECT email FROM admin WHERE email = ?
        ");
        $check_stmt->bind_param("sss", $email, $email, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            // 3. Hash Password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 4. Insert into Patient Table using a Prepared Statement
            $medical_history = "";
            $insert_stmt = $conn->prepare("INSERT INTO patient (name, email, password, gender, phone_number, medical_history) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssss", $name, $email, $hashed_password, $gender, $phone, $medical_history);

            if ($insert_stmt->execute()) {
                $msg = "Registration successful! You can now login.";
            } else {
                $error = "Error: Something went wrong. Please try again.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - PMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .register-card { max-width: 500px; margin: 50px auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="register-card">
        <h3 class="text-center mb-4 fw-bold">Patient Registration</h3>
        
        <?php if($msg): ?>
            <div class="alert alert-success">
                <?php echo $msg; ?> <br>
                <a href="login.php" class="fw-bold">Click here to Login</a>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select...</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" name="register_btn" class="btn btn-success w-100 py-2 fw-bold">Register Account</button>
        </form>

        <div class="mt-3 text-center">
            <p class="text-muted">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
        </div>
    </div>
</div>

</body>
</html>