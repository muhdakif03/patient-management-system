<?php
$required_role = 'patient';
include('../config/session.php');
include('../config/db_connect.php');
include('../includes/header.php');

$patient_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// 1. Get Next Upcoming Appointment
$query_next = "SELECT a.*, d.name as doctor_name 
               FROM appointment a 
               JOIN doctor d ON a.doctor_id = d.doctor_id 
               WHERE a.patient_id='$patient_id' AND a.appointment_date >= NOW() AND a.status != 'Cancelled'
               ORDER BY a.appointment_date ASC LIMIT 1";
$next_appt = mysqli_fetch_assoc(mysqli_query($conn, $query_next));

// 2. Get latest Vitals from Medical History
$query_vitals = "SELECT * FROM patient_record WHERE patient_id='$patient_id' ORDER BY date_created DESC LIMIT 1";
$vitals = mysqli_fetch_assoc(mysqli_query($conn, $query_vitals));

// 3. Count Total Visits
$query_recs = "SELECT count(*) as total FROM patient_record WHERE patient_id='$patient_id'";
$total_recs = mysqli_fetch_assoc(mysqli_query($conn, $query_recs))['total'];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-dark fw-bold">My Health Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, <?php echo $_SESSION['name']; ?></p>
        </div>
        <a href="book.php" class="btn btn-primary shadow-sm"><i class="fas fa-plus me-2"></i>Book Appointment</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="stat-card bg-gradient-primary h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase mb-3 opacity-75">Next Appointment</h6>
                        <?php if($next_appt): ?>
                            <h3 class="fw-bold mb-1"><?php echo date('M d, Y', strtotime($next_appt['appointment_date'])); ?></h3>
                            <p class="lead mb-0"><?php echo date('h:i A', strtotime($next_appt['appointment_date'])); ?></p>
                            <div class="mt-3 badge bg-white text-primary">
                                <i class="fas fa-user-md me-1"></i> Dr. <?php echo $next_appt['doctor_name']; ?>
                            </div>
                        <?php else: ?>
                            <h4 class="mb-0">No upcoming visits</h4>
                            <p class="small opacity-75">Schedule a checkup today.</p>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-calendar-check fa-4x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card bg-gradient-success h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Medical Records</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $total_recs; ?></h2>
                        <p class="mb-0 opacity-75">Total checkups completed</p>
                    </div>
                    <i class="fas fa-file-medical fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between">
                    <h5 class="fw-bold text-dark"><i class="fas fa-heartbeat me-2"></i>Recent Vital Signs</h5>
                    <?php if($vitals): ?>
                        <span class="text-muted small">Recorded on: <?php echo date('M d, Y', strtotime($vitals['date_created'])); ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if($vitals): ?>
                        <div class="row text-center mt-2">
                            <div class="col-3 border-end">
                                <h6 class="text-muted text-uppercase small mb-2">Blood Pressure</h6>
                                <h4 class="fw-bold text-primary mb-0"><?php echo $vitals['blood_pressure'] ? $vitals['blood_pressure'] : '--'; ?></h4>
                                <small class="text-muted">mmHg</small>
                            </div>
                            <div class="col-3 border-end">
                                <h6 class="text-muted text-uppercase small mb-2">Heart Rate</h6>
                                <h4 class="fw-bold text-danger mb-0"><?php echo $vitals['pulse'] ? $vitals['pulse'] : '--'; ?></h4>
                                <small class="text-muted">bpm</small>
                            </div>
                            <div class="col-3 border-end">
                                <h6 class="text-muted text-uppercase small mb-2">Weight</h6>
                                <h4 class="fw-bold text-success mb-0"><?php echo $vitals['weight'] ? $vitals['weight'] : '--'; ?></h4>
                                <small class="text-muted">kg</small>
                            </div>
                            <div class="col-3">
                                <h6 class="text-muted text-uppercase small mb-2">Temperature</h6>
                                <h4 class="fw-bold text-warning mb-0"><?php echo $vitals['temperature'] ? $vitals['temperature'] : '--'; ?></h4>
                                <small class="text-muted">°C</small>
                            </div>
                        </div>
                        <div class="alert alert-light mt-4 mb-0 border">
                            <strong><i class="fas fa-stethoscope me-2"></i>Latest Diagnosis:</strong>
                            <?php echo $vitals['diagnosis']; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-notes-medical fa-3x text-muted mb-3 opacity-25"></i>
                            <p class="text-muted">No vitals recorded yet. They will appear here after your first doctor visit.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark">Quick Actions</h5>
                </div>
                <div class="card-body px-4">
                    <div class="d-grid gap-3">
                        <a href="book.php" class="btn btn-outline-primary py-3 text-start">
                            <i class="fas fa-calendar-plus me-3"></i> Book Appointment
                        </a>
                        <a href="my_appointments.php" class="btn btn-outline-dark py-3 text-start">
                            <i class="fas fa-calendar-alt me-3"></i> My Appointments
                        </a>
                        <a href="history.php" class="btn btn-outline-secondary py-3 text-start">
                            <i class="fas fa-file-medical-alt me-3"></i> View Medical History
                        </a>
                        <a href="profile.php" class="btn btn-outline-info py-3 text-start">
                            <i class="fas fa-user-cog me-3"></i> Account Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>