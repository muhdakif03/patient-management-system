<?php
$required_role = 'doctor';
include('../config/session.php');
include('../config/db_connect.php');

$doc_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// Handle New Medical Record Submission
if(isset($_POST['add_record'])) {
    $patient_id = $_POST['patient_id'];
    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $treatment = mysqli_real_escape_string($conn, $_POST['treatment']);
    
    // Vitals
    $bp = mysqli_real_escape_string($conn, $_POST['bp']);
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    $temp = mysqli_real_escape_string($conn, $_POST['temp']);
    $pulse = mysqli_real_escape_string($conn, $_POST['pulse']);
    
    $sql = "INSERT INTO patient_record (patient_id, doctor_id, diagnosis, treatment, blood_pressure, weight, temperature, pulse) 
            VALUES ('$patient_id', '$doc_id', '$diagnosis', '$treatment', '$bp', '$weight', '$temp', '$pulse')";
            
    if(mysqli_query($conn, $sql)) {
        if(function_exists('log_activity')) log_activity($conn, "Add Medical Record", "Added record for Patient ID: $patient_id");
        $msg = "Detailed medical record added successfully.";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// --- SEARCH & PAGINATION ---
$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where_sql = "";
if($search != "") {
    $where_sql = "WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR patient_id LIKE '%$search%'";
}

// Count Total
$count_res = mysqli_query($conn, "SELECT count(*) as total FROM patient $where_sql");
$total_rows = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_rows / $limit);

include('../includes/header.php');
?>

<div class="container-fluid">
    <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-file-medical me-2"></i>Patient Medical Records</h5>
                <small class="text-muted">Total Patients: <?php echo $total_rows; ?></small>
            </div>
            
            <form class="d-flex" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search patient..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if($search): ?>
                        <a href="records.php" class="btn btn-outline-secondary" title="Clear"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card-body px-4">
            <div class="table-responsive">
                <table class="table table-hover table-custom align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient Name</th>
                            <th>Gender</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM patient $where_sql LIMIT $start, $limit";
                        $result = mysqli_query($conn, $query);
                        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>#".$row['patient_id']."</td>";
                                echo "<td>
                                        <div class='fw-bold'>".$row['name']."</div>
                                        <div class='small text-muted'>".$row['email']."</div>
                                      </td>";
                                echo "<td>".$row['gender']."</td>";
                                echo "<td class='text-end'>
                                        <a href='view_patient.php?id=".$row['patient_id']."' class='btn btn-outline-info btn-sm me-1'>
                                            <i class='fas fa-history'></i> History
                                        </a>
                                        
                                        <button type='button' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#recordModal".$row['patient_id']."'>
                                            <i class='fas fa-plus'></i> Add
                                        </button>
                                      </td>";
                                
                                // MODAL (Kept inside loop)
                                echo "
                                <div class='modal fade' id='recordModal".$row['patient_id']."' tabindex='-1'>
                                    <div class='modal-dialog modal-lg'> <div class='modal-content'>
                                            <form method='POST'>
                                                <div class='modal-header'>
                                                    <h5 class='modal-title'>New Record: ".$row['name']."</h5>
                                                    <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                                </div>
                                                <div class='modal-body text-start'>
                                                    <input type='hidden' name='patient_id' value='".$row['patient_id']."'>
                                                    
                                                    <h6 class='text-primary fw-bold mb-3'>Vital Signs</h6>
                                                    <div class='row mb-3'>
                                                        <div class='col-md-3'>
                                                            <label class='form-label small'>Blood Pressure</label>
                                                            <input type='text' name='bp' class='form-control' placeholder='120/80'>
                                                        </div>
                                                        <div class='col-md-3'>
                                                            <label class='form-label small'>Weight (kg)</label>
                                                            <input type='number' step='0.1' name='weight' class='form-control' placeholder='70.0'>
                                                        </div>
                                                        <div class='col-md-3'>
                                                            <label class='form-label small'>Temp (°C)</label>
                                                            <input type='number' step='0.1' name='temp' class='form-control' placeholder='36.5'>
                                                        </div>
                                                        <div class='col-md-3'>
                                                            <label class='form-label small'>Pulse (bpm)</label>
                                                            <input type='number' name='pulse' class='form-control' placeholder='80'>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <h6 class='text-primary fw-bold mb-3'>Medical Details</h6>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Diagnosis</label>
                                                        <textarea name='diagnosis' class='form-control' rows='2' required></textarea>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Treatment / Prescription</label>
                                                        <textarea name='treatment' class='form-control' rows='3' required></textarea>
                                                    </div>
                                                </div>
                                                <div class='modal-footer'>
                                                    <button type='submit' name='add_record' class='btn btn-success'>Save Medical Record</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No patients found.</td></tr>";
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