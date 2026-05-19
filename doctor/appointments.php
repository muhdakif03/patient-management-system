<?php
$required_role = 'doctor';
include('../config/session.php');
include('../config/db_connect.php');

$doc_id = $_SESSION['user_id'];

// --- ACTION HANDLERS ---
if(isset($_GET['confirm_id'])) {
    $app_id = $_GET['confirm_id'];
    mysqli_query($conn, "UPDATE appointment SET status='Confirmed' WHERE appointment_id='$app_id' AND doctor_id='$doc_id'");
    if(function_exists('log_activity')) log_activity($conn, "Confirm Appointment", "Confirmed Appt ID: $app_id");
    header("Location: appointments.php");
    exit();
}

if(isset($_GET['reject_id'])) {
    $app_id = $_GET['reject_id'];
    mysqli_query($conn, "UPDATE appointment SET status='Cancelled' WHERE appointment_id='$app_id' AND doctor_id='$doc_id'");
    if(function_exists('log_activity')) log_activity($conn, "Reject Appointment", "Cancelled Appt ID: $app_id");
    header("Location: appointments.php");
    exit();
}

if(isset($_GET['complete_id'])) {
    $app_id = $_GET['complete_id'];
    mysqli_query($conn, "UPDATE appointment SET status='Completed' WHERE appointment_id='$app_id' AND doctor_id='$doc_id'");
    if(function_exists('log_activity')) log_activity($conn, "Complete Appointment", "Completed Appt ID: $app_id");
    header("Location: appointments.php");
    exit();
}

// --- SEARCH & PAGINATION ---
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Base Where Clause (Always restricted to this doctor)
$where_sql = "WHERE a.doctor_id='$doc_id'";

if($search != "") {
    $where_sql .= " AND (p.name LIKE '%$search%' OR a.status LIKE '%$search%' OR p.phone_number LIKE '%$search%')";
}

// Count Total
$count_sql = "SELECT count(*) as total FROM appointment a JOIN patient p ON a.patient_id = p.patient_id $where_sql";
$count_res = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_rows / $limit);

include('../includes/header.php');
?>

<div class="container-fluid">
    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-calendar-check me-2"></i>My Appointments</h5>
                <small class="text-muted">Total: <?php echo $total_rows; ?></small>
            </div>

            <form class="d-flex" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search patient or status..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if($search): ?>
                        <a href="appointments.php" class="btn btn-outline-secondary" title="Clear"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card-body px-4">
            <div class="table-responsive">
                <table class="table table-hover table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Patient Name</th>
                            <th>Status</th>
                            <th class="text-end" style="min-width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT a.*, p.name as pname, p.phone_number 
                                  FROM appointment a 
                                  JOIN patient p ON a.patient_id = p.patient_id 
                                  $where_sql 
                                  ORDER BY a.appointment_date DESC 
                                  LIMIT $start, $limit";
                        $result = mysqli_query($conn, $query);

                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $date = date('M d, Y', strtotime($row['appointment_date']));
                                $time = date('h:i A', strtotime($row['appointment_date']));
                                
                                $status_badge = match($row['status']) {
                                    'Pending' => 'bg-warning text-dark',
                                    'Confirmed' => 'bg-primary',
                                    'Completed' => 'bg-success',
                                    'Cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };

                                echo "<tr>";
                                echo "<td>
                                        <div class='fw-bold text-dark'>$date</div>
                                        <div class='small text-muted'>$time</div>
                                      </td>";
                                echo "<td>
                                        <div class='fw-bold'>".$row['pname']."</div>
                                        <div class='small text-muted'><i class='fas fa-phone me-1'></i>".$row['phone_number']."</div>
                                      </td>";
                                echo "<td><span class='badge $status_badge rounded-pill'>".$row['status']."</span></td>";
                                
                                echo "<td class='text-end'>";
                                if($row['status'] == 'Pending') {
                                    echo "<a href='appointments.php?confirm_id=".$row['appointment_id']."' class='btn btn-sm btn-success me-2' title='Accept'>
                                            <i class='fas fa-check'></i>
                                          </a>";
                                    echo "<a href='appointments.php?reject_id=".$row['appointment_id']."' class='btn btn-sm btn-outline-danger' title='Decline' onclick='return confirm(\"Decline this appointment?\")'>
                                            <i class='fas fa-times'></i>
                                          </a>";
                                } 
                                else if ($row['status'] == 'Confirmed') {
                                    echo "<a href='appointments.php?complete_id=".$row['appointment_id']."' class='btn btn-sm btn-dark' title='Mark as Done'>
                                            Mark Completed <i class='fas fa-arrow-right ms-1'></i>
                                          </a>";
                                } 
                                else {
                                    echo "<span class='text-muted small'>-</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center text-muted py-4'>No appointments found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php if($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>">Previous</a>
                    </li>
                    <?php for($i=1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>