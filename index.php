<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Special Styling just for Landing Page */
        .hero-section {
            background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%);
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        .feature-icon {
            font-size: 3rem;
            color: #4ca1af;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-heartbeat me-2"></i>Patient Management System</a>
            <div class="d-flex">
                <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                <a href="register.php" class="btn btn-primary">Patient Registration</a>
            </div>
        </div>
    </nav>

    <div class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Welcome to PMS</h1>
            <a href="login.php" class="btn btn-lg btn-light text-dark fw-bold px-5 shadow">Get Started</a>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 p-4">
                    <i class="fas fa-calendar-check feature-icon"></i>
                    <h5 class="fw-bold">Easy Scheduling</h5>
                    <p class="text-muted">Book appointments with doctors in seconds based on real-time availability.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 p-4">
                    <i class="fas fa-file-medical feature-icon"></i>
                    <h5 class="fw-bold">Digital Records</h5>
                    <p class="text-muted">Securely store and access medical history, diagnosis, and prescriptions.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 p-4">
                    <i class="fas fa-shield-alt feature-icon"></i>
                    <h5 class="fw-bold">Secure Access</h5>
                    <p class="text-muted">Your data is protected with Multi-Factor Authentication (MFA) and encryption.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?>PMS. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>