<?php
$required_role = 'admin';
include('../config/session.php');
include('../config/db_connect.php');

$admin_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// Handle Profile Update
if(isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Password Update
    if(!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE admin SET name='$name', email='$email', password='$password' WHERE admin_id='$admin_id'";
    } else {
        $sql = "UPDATE admin SET name='$name', email='$email' WHERE admin_id='$admin_id'";
    }

    if(mysqli_query($conn, $sql)) {
        $msg = "Profile updated successfully!";
        $_SESSION['name'] = $name;
    } else {
        $error = "Error updating profile: " . mysqli_error($conn);
    }
}

// Fetch Current Data
$query = "SELECT * FROM admin WHERE admin_id='$admin_id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

include('../includes/header.php');
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-user-cog me-2"></i>My Profile</h5>
                </div>
                <div class="card-body px-4 pb-5">
                    <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary small">Full Name</label>
                                <input type="text" name="name" class="form-control bg-light" value="<?php echo $row['name']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-secondary small">Email Address</label>
                                <input type="email" name="email" class="form-control bg-light" value="<?php echo $row['email']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">New Password (Leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control bg-light">
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" name="update_profile" class="btn btn-primary px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>