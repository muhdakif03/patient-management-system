<?php
$required_role = 'admin';
include('../config/session.php');
include('../config/db_connect.php');

$msg = "";
$error = "";

// 1. Handle Add Doctor
if(isset($_POST['add_doctor'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO doctor (name, email, password, phone_number) VALUES ('$name', '$email', '$password', '$phone')";
    if(mysqli_query($conn, $sql)) {
        if(function_exists('log_activity')) log_activity($conn, "Add Doctor", "Added Dr. $name");
        $msg = "New Doctor registered successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// 2. Handle Edit Doctor
if(isset($_POST['edit_doctor'])) {
    $id = $_POST['doctor_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    if(!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE doctor SET name='$name', email='$email', phone_number='$phone', password='$password' WHERE doctor_id='$id'";
    } else {
        $sql = "UPDATE doctor SET name='$name', email='$email', phone_number='$phone' WHERE doctor_id='$id'";
    }

    if(mysqli_query($conn, $sql)) {
        if(function_exists('log_activity')) log_activity($conn, "Edit Doctor", "Updated Dr. $name");
        $msg = "Doctor details updated!";
    } else {
        $error = "Error updating: " . mysqli_error($conn);
    }
}

// 3. Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM doctor WHERE doctor_id='$id'");
    header("Location: doctors.php?deleted=1");
    exit();
}

// --- SEARCH & PAGINATION ---
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where_sql = "";
if($search != "") {
    $where_sql = "WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR doctor_id LIKE '%$search%'";
}

// Count Total
$count_res = mysqli_query($conn, "SELECT count(*) as total FROM doctor $where_sql");
$total_rows = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_rows / $limit);

include('../includes/header.php');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-primary"><i class="fas fa-user-plus me-2"></i>Register New Doctor</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if($msg) echo "<div class='alert alert-success py-2'>$msg</div>"; ?>
                    <?php if($error) echo "<div class='alert alert-danger py-2'>$error</div>"; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Full Name</label>
                            <input type="text" name="name" class="form-control bg-light" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Email Address</label>
                            <input type="email" name="email" class="form-control bg-light" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Phone Number</label>
                            <input type="text" name="phone" class="form-control bg-light" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Password</label>
                            <input type="password" name="password" class="form-control bg-light" required>
                        </div>
                        <button type="submit" name="add_doctor" class="btn btn-primary w-100 fw-bold shadow-sm">Create Account</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="fw-bold text-dark mb-0">Medical Staff Directory</h5>
                    
                    <form class="d-flex" method="GET">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
                
                <div class="card-body px-4">
                    <table class="table table-hover table-custom align-middle">
                        <thead>
                            <tr>
                                <th class="ps-3">Doctor</th>
                                <th>Contact</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM doctor $where_sql LIMIT $start, $limit";
                            $result = mysqli_query($conn, $query);
                            
                            if(mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td class='ps-3'>
                                            <div class='fw-bold'>Dr. ".$row['name']."</div>
                                            <div class='small text-muted'>ID: #".$row['doctor_id']."</div>
                                          </td>";
                                    echo "<td>
                                            <div class='small'><i class='fas fa-envelope me-1'></i> ".$row['email']."</div>
                                            <div class='small'><i class='fas fa-phone me-1'></i> ".$row['phone_number']."</div>
                                          </td>";
                                    echo "<td class='text-end pe-3'>
                                            <button class='btn btn-sm btn-outline-primary me-1' data-bs-toggle='modal' data-bs-target='#editDoc".$row['doctor_id']."'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <a href='doctors.php?delete=".$row['doctor_id']."' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Are you sure?\")'>
                                                <i class='fas fa-trash'></i>
                                            </a>
                                          </td>";
                                    
                                    // Modal code preserved here...
                                    echo "
                                    <div class='modal fade' id='editDoc".$row['doctor_id']."' tabindex='-1'>
                                        <div class='modal-dialog'>
                                            <div class='modal-content'>
                                                <form method='POST'>
                                                    <div class='modal-header'>
                                                        <h5 class='modal-title'>Edit Dr. ".$row['name']."</h5>
                                                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                                    </div>
                                                    <div class='modal-body text-start'>
                                                        <input type='hidden' name='doctor_id' value='".$row['doctor_id']."'>
                                                        <div class='mb-3'>
                                                            <label>Name</label>
                                                            <input type='text' name='name' class='form-control' value='".$row['name']."' required>
                                                        </div>
                                                        <div class='mb-3'>
                                                            <label>Email</label>
                                                            <input type='email' name='email' class='form-control' value='".$row['email']."' required>
                                                        </div>
                                                        <div class='mb-3'>
                                                            <label>Phone</label>
                                                            <input type='text' name='phone' class='form-control' value='".$row['phone_number']."' required>
                                                        </div>
                                                        <div class='mb-3'>
                                                            <label>New Password (Leave blank to keep)</label>
                                                            <input type='password' name='password' class='form-control' placeholder='*******'>
                                                        </div>
                                                    </div>
                                                    <div class='modal-footer'>
                                                        <button type='submit' name='edit_doctor' class='btn btn-success'>Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center py-4 text-muted'>No doctors found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                    <?php if($total_pages > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center pagination-sm">
                            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>">Prev</a>
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

<?php include('../includes/footer.php'); ?>