<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS - Medical Portal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="sidebar">
    <h4><i class="fas fa-heartbeat me-2"></i>PMS</h4>
    
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <a href="../admin/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
        <a href="../admin/doctors.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'doctors.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-md me-2"></i> Manage Doctors
        </a>
        <a href="../admin/patients.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'patients.php' ? 'active' : ''; ?>">
            <i class="fas fa-users me-2"></i> Manage Patients
        </a>
        <a href="../admin/reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line me-2"></i> Reports
        </a>
        <a href="../admin/profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-circle me-2"></i> My Profile
        </a>
        <a href="../admin/devices.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'devices.php' ? 'active' : ''; ?>">
            <i class="fas fa-laptop-medical me-2"></i> Trusted Devices
        </a>
    <?php endif; ?>

    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'doctor'): ?>
        <a href="../doctor/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-columns me-2"></i> Dashboard
        </a>
        <a href="../doctor/appointments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check me-2"></i> My Appointments
        </a>
        <a href="../doctor/schedule.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'active' : ''; ?>">
            <i class="fas fa-clock me-2"></i> Availability
        </a>
        <a href="../doctor/records.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'records.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-medical me-2"></i> Patient Records
        </a>
        <a href="../doctor/profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-circle me-2"></i> My Profile
        </a>
        <a href="../doctor/devices.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'devices.php' ? 'active' : ''; ?>">
            <i class="fas fa-laptop-medical me-2"></i> Trusted Devices
        </a>
    <?php endif; ?>

    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'patient'): ?>
        <a href="../patient/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-home me-2"></i> Dashboard
        </a>
        <a href="../patient/book.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'book.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-plus me-2"></i> Book Appointment
        </a>
        <a href="../patient/my_appointments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'my_appointments.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check me-2"></i> My Appointments
        </a>
        <a href="../patient/history.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-medical-alt me-2"></i> Medical History
        </a>
        <a href="../patient/profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-circle me-2"></i> My Profile
        </a>
        <a href="../patient/devices.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'devices.php' ? 'active' : ''; ?>">
            <i class="fas fa-laptop-medical me-2"></i> Trusted Devices
        </a>
    <?php endif; ?>

    <a href="../logout.php" class="mt-5 text-danger">
        <i class="fas fa-sign-out-alt me-2"></i> Logout
    </a>
</div>

<div class="main-content">