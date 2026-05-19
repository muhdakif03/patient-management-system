<?php
$required_role = 'doctor';
include('../config/session.php');
include('../config/db_connect.php');
include('../includes/header.php');

$doc_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// --- 1. STATISTICS ---
// Today's Appointments
$query_today = "SELECT count(*) as total FROM appointment WHERE doctor_id='$doc_id' AND DATE(appointment_date) = '$today'";
$today_count = mysqli_fetch_assoc(mysqli_query($conn, $query_today))['total'];

// Pending Appointments
$query_pending = "SELECT count(*) as total FROM appointment WHERE doctor_id='$doc_id' AND status='Pending'";
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, $query_pending))['total'];

// Total Patients Treated (Unique patients in records)
$query_treated = "SELECT count(DISTINCT patient_id) as total FROM patient_record WHERE doctor_id='$doc_id'";
$treated_count = mysqli_fetch_assoc(mysqli_query($conn, $query_treated))['total'];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-dark fw-bold">Doctor Dashboard</h2>
            <p class="text-muted mb-0">Overview for <?php echo date('l, F j, Y'); ?></p>
        </div>
        <div>
            <a href="records.php" class="btn btn-primary shadow-sm"><i class="fas fa-plus me-2"></i>New Medical Record</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card bg-gradient-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Appointments Today</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $today_count; ?></h2>
                    </div>
                    <i class="fas fa-calendar-day fa-4x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card bg-gradient-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Pending Requests</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $pending_count; ?></h2>
                    </div>
                    <i class="fas fa-hourglass-half fa-4x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card bg-gradient-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Patients Treated</h6>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $treated_count; ?></h2>
                    </div>
                    <i class="fas fa-user-md fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-clock me-2"></i>Today's Schedule</h5>
                </div>
                <div class="card-body px-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query_sched = "SELECT a.*, p.name 
                                                FROM appointment a 
                                                JOIN patient p ON a.patient_id = p.patient_id 
                                                WHERE a.doctor_id='$doc_id' AND DATE(a.appointment_date) = '$today'
                                                ORDER BY a.appointment_date ASC";
                                $res_sched = mysqli_query($conn, $query_sched);

                                if(mysqli_num_rows($res_sched) > 0) {
                                    while($row = mysqli_fetch_assoc($res_sched)) {
                                        $time = date('h:i A', strtotime($row['appointment_date']));
                                        $status_color = $row['status'] == 'Completed' ? 'bg-success' : ($row['status'] == 'Confirmed' ? 'bg-primary' : 'bg-warning');
                                        
                                        echo "<tr>";
                                        echo "<td class='fw-bold text-dark'>$time</td>";
                                        echo "<td>".$row['name']."</td>";
                                        echo "<td><span class='badge $status_color rounded-pill'>".$row['status']."</span></td>";
                                        echo "<td>
                                                <a href='appointments.php' class='btn btn-sm btn-outline-secondary'>View</a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center text-muted py-3'>No appointments scheduled for today.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body px-4">
                    <ul class="list-group list-group-flush">
                        <?php
                        // Fetch last 5 records added by this doctor
                        $query_rec = "SELECT r.*, p.name 
                                      FROM patient_record r 
                                      JOIN patient p ON r.patient_id = p.patient_id 
                                      WHERE r.doctor_id='$doc_id' 
                                      ORDER BY r.record_id DESC LIMIT 5";
                        $res_rec = mysqli_query($conn, $query_rec);

                        if(mysqli_num_rows($res_rec) > 0) {
                            while($rec = mysqli_fetch_assoc($res_rec)) {
                                echo "<li class='list-group-item d-flex justify-content-between align-items-center px-0 py-3'>";
                                echo "<div>
                                        <div class='fw-bold'>".$rec['name']."</div>
                                        <div class='small text-muted text-truncate' style='max-width: 150px;'>".$rec['diagnosis']."</div>
                                      </div>";
                                echo "<span class='badge bg-light text-dark'>".date('M d', strtotime($rec['date_created']))."</span>";
                                echo "</li>";
                            }
                        } else {
                            echo "<li class='list-group-item text-center text-muted'>No recent records found.</li>";
                        }
                        ?>
                    </ul>
                    <div class="mt-3 text-center">
                        <a href="records.php" class="small text-decoration-none fw-bold">View All Records <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>