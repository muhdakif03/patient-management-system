<?php
$required_role = 'admin';
include('../config/session.php');
include('../config/db_connect.php');
include('../includes/header.php');

// --- SEARCH & PAGINATION ---
$limit = 10; // Show more rows for reports
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where_sql = "";
if($search != "") {
    $where_sql = "WHERE p.name LIKE '%$search%' OR d.name LIKE '%$search%' OR a.status LIKE '%$search%'";
}

// 1. Count Total Rows
$count_sql = "SELECT count(*) as total 
              FROM appointment a 
              JOIN patient p ON a.patient_id = p.patient_id
              JOIN doctor d ON a.doctor_id = d.doctor_id
              $where_sql";
$count_res = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_rows / $limit);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h2 class="text-dark fw-bold">Hospital Report</h2>
            <p class="text-muted mb-0">Total Appointments Found: <?php echo $total_rows; ?></p>
        </div>
        
        <div class="d-flex gap-2">
            <form class="d-flex" method="GET">
                <input type="text" name="search" class="form-control me-2" placeholder="Patient, Doctor, Status..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-primary" type="submit">Search</button>
            </form>
            <button onclick="window.print()" class="btn btn-primary shadow-sm"><i class="fas fa-print me-2"></i> Print</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">Appointment Summary</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Patient Name</th>
                                    <th>Doctor Assigned</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // 2. Fetch Data with Limit
                                $query = "SELECT a.appointment_date, a.status, p.name as pname, d.name as dname 
                                          FROM appointment a 
                                          JOIN patient p ON a.patient_id = p.patient_id
                                          JOIN doctor d ON a.doctor_id = d.doctor_id
                                          $where_sql
                                          ORDER BY a.appointment_date DESC 
                                          LIMIT $start, $limit";
                                $result = mysqli_query($conn, $query);

                                if(mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $status_color = $row['status'] == 'Confirmed' ? 'text-primary' : ($row['status'] == 'Completed' ? 'text-success' : 'text-warning');
                                        if($row['status'] == 'Cancelled') $status_color = 'text-danger';

                                        echo "<tr>";
                                        echo "<td>".date('M d, Y h:i A', strtotime($row['appointment_date']))."</td>";
                                        echo "<td>".$row['pname']."</td>";
                                        echo "<td>Dr. ".$row['dname']."</td>";
                                        echo "<td class='fw-bold $status_color'>".$row['status']."</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center py-5 text-muted'>No appointments found matching your search.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($total_pages > 1): ?>
                    <nav class="mt-4 d-print-none"> <ul class="pagination justify-content-center">
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
    </div>
</div>

<style>
@media print {
    .sidebar, .btn, .navbar, .d-print-none, form { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    body { background-color: white; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<?php include('../includes/footer.php'); ?>