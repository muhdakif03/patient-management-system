<?php
$required_role = 'admin';
include('../config/session.php');
include('../config/db_connect.php');

$msg = "";

// 1. Handle Edit Patient
if(isset($_POST['edit_patient'])) {
    $id = $_POST['patient_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    
    if(!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE patient SET name='$name', email='$email', phone_number='$phone', gender='$gender', password='$password' WHERE patient_id='$id'";
    } else {
        $sql = "UPDATE patient SET name='$name', email='$email', phone_number='$phone', gender='$gender' WHERE patient_id='$id'";
    }

    if(mysqli_query($conn, $sql)) {
        // Log Activity
        if(function_exists('log_activity')) {
            log_activity($conn, "Edit Patient", "Updated Patient ID: $id");
        }
        $msg = "Patient updated successfully!";
    }
}

// 2. Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM patient WHERE patient_id='$id'");
    header("Location: patients.php");
    exit();
}

// --- 3. SEARCH & PAGINATION LOGIC ---
$limit = 5; // Rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build Query Condition
$where_sql = "";
if($search != "") {
    $where_sql = "WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR phone_number LIKE '%$search%'";
}

// Get Total Records (for pagination numbers)
$count_sql = "SELECT count(*) as total FROM patient $where_sql";
$count_res = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_rows / $limit);

include('../includes/header.php');
?>

<div class="container-fluid">
    <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-users me-2"></i>Registered Patients</h5>
                <small class="text-muted">Total: <?php echo $total_rows; ?></small>
            </div>
            
            <form class="d-flex" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search name, email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if($search): ?>
                        <a href="patients.php" class="btn btn-outline-secondary" title="Clear Search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card-body px-4">
            <div class="table-responsive">
                <table class="table table-hover table-custom align-middle">
                    <thead>
                        <tr>
                            <th class="ps-3">Patient</th>
                            <th>Contact</th>
                            <th>Gender</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch Data with Limit
                        $query = "SELECT * FROM patient $where_sql LIMIT $start, $limit";
                        $result = mysqli_query($conn, $query);
                        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td class='ps-3'>
                                        <div class='fw-bold'>".$row['name']."</div>
                                        <div class='small text-muted'>ID: #".$row['patient_id']."</div>
                                      </td>";
                                echo "<td>
                                        <div class='small'><i class='fas fa-envelope me-1'></i> ".$row['email']."</div>
                                        <div class='small'><i class='fas fa-phone me-1'></i> ".$row['phone_number']."</div>
                                      </td>";
                                echo "<td><span class='badge bg-light text-dark border'>".$row['gender']."</span></td>";
                                echo "<td class='text-end pe-3'>
                                        <button class='btn btn-sm btn-outline-primary me-1' data-bs-toggle='modal' data-bs-target='#editPat".$row['patient_id']."'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <a href='patients.php?delete=".$row['patient_id']."' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Are you sure?\")'>
                                            <i class='fas fa-trash'></i>
                                        </a>
                                      </td>";
                                
                                // EDIT MODAL
                                echo "
                                <div class='modal fade' id='editPat".$row['patient_id']."' tabindex='-1'>
                                    <div class='modal-dialog'>
                                        <div class='modal-content'>
                                            <form method='POST'>
                                                <div class='modal-header'>
                                                    <h5 class='modal-title'>Edit ".$row['name']."</h5>
                                                    <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                                </div>
                                                <div class='modal-body text-start'>
                                                    <input type='hidden' name='patient_id' value='".$row['patient_id']."'>
                                                    <div class='mb-3'>
                                                        <label>Name</label>
                                                        <input type='text' name='name' class='form-control' value='".$row['name']."' required>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label>Email</label>
                                                        <input type='email' name='email' class='form-control' value='".$row['email']."' required>
                                                    </div>
                                                    <div class='row'>
                                                        <div class='col-6 mb-3'>
                                                            <label>Phone</label>
                                                            <input type='text' name='phone' class='form-control' value='".$row['phone_number']."' required>
                                                        </div>
                                                        <div class='col-6 mb-3'>
                                                            <label>Gender</label>
                                                            <select name='gender' class='form-select'>
                                                                <option value='Male' ".($row['gender']=='Male'?'selected':'').">Male</option>
                                                                <option value='Female' ".($row['gender']=='Female'?'selected':'').">Female</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label>Reset Password (Optional)</label>
                                                        <input type='password' name='password' class='form-control' placeholder='*******'>
                                                    </div>
                                                </div>
                                                <div class='modal-footer'>
                                                    <button type='submit' name='edit_patient' class='btn btn-success'>Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No patients found matching your search.</td></tr>";
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