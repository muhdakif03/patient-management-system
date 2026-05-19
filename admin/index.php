<?php
$required_role = 'admin';
include('../config/session.php');
include('../config/db_connect.php');
include('../includes/header.php');

// 1. Fetch Counters
$total_docs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as total FROM doctor"))['total'];
$total_pats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as total FROM patient"))['total'];
$total_apps = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as total FROM appointment"))['total'];

// 2. Fetch Recent Appointments (Limit 5)
$query_apps = "SELECT a.*, p.name as pname, d.name as dname 
               FROM appointment a 
               JOIN patient p ON a.patient_id = p.patient_id 
               JOIN doctor d ON a.doctor_id = d.doctor_id 
               ORDER BY a.appointment_date DESC LIMIT 5";
$res_apps = mysqli_query($conn, $query_apps);

// 3. Fetch New Patients (Limit 5)
$query_new_pats = "SELECT * FROM patient ORDER BY patient_id DESC LIMIT 5";
$res_new_pats = mysqli_query($conn, $query_new_pats);
?>

<div class="container-fluid">
    <h2 class="mb-4 text-dark fw-bold">Hospital Overview</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card bg-gradient-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Doctors</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $total_docs; ?></h2>
                    </div>
                    <i class="fas fa-user-md fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-gradient-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Patients</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $total_pats; ?></h2>
                    </div>
                    <i class="fas fa-procedures fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-gradient-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Appointments</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $total_apps; ?></h2>
                    </div>
                    <i class="fas fa-calendar-check fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-clock me-2"></i>Recent Appointments</h5>
                </div>
                <div class="card-body px-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($res_apps)): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo date('M d, h:i A', strtotime($row['appointment_date'])); ?></td>
                                    <td><?php echo $row['pname']; ?></td>
                                    <td>Dr. <?php echo $row['dname']; ?></td>
                                    <td>
                                        <?php 
                                            $color = match($row['status']) {
                                                'Confirmed' => 'text-primary',
                                                'Completed' => 'text-success',
                                                'Cancelled' => 'text-danger',
                                                default => 'text-warning'
                                            };
                                        ?>
                                        <span class="fw-bold <?php echo $color; ?>"><?php echo $row['status']; ?></span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="reports.php" class="btn btn-sm btn-outline-secondary">View All Appointments</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-user-plus me-2"></i>Newest Patients</h5>
                </div>
                <div class="card-body px-4">
                    <ul class="list-group list-group-flush">
                        <?php while($pat = mysqli_fetch_assoc($res_new_pats)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-3 text-success">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?php echo $pat['name']; ?></div>
                                    <div class="small text-muted"><?php echo $pat['email']; ?></div>
                                </div>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                    <div class="text-center mt-3">
                        <a href="patients.php" class="btn btn-sm btn-outline-secondary">Manage Patients</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>