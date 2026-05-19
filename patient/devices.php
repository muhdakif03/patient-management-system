<?php
include('../config/session.php');
include('../config/db_connect.php');

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$msg = "";

// --- HANDLE REMOVE ACTION ---
if(isset($_GET['remove_id'])) {
    $device_id = mysqli_real_escape_string($conn, $_GET['remove_id']);
    
    $check_sql = "SELECT token FROM trusted_devices WHERE device_id='$device_id' AND user_id='$user_id' AND role='$role'";
    $check_res = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_res) > 0) {
        $row = mysqli_fetch_assoc($check_res);
        $token_to_remove = $row['token'];

        // 2. Delete from Database
        $del_sql = "DELETE FROM trusted_devices WHERE device_id='$device_id'";
        if(mysqli_query($conn, $del_sql)) {
            $msg = "Device removed successfully.";

            // 3. Check if we just deleted the CURRENT device's cookie
            if(isset($_COOKIE['remember_me']) && $_COOKIE['remember_me'] === $token_to_remove) {
                setcookie("remember_me", "", time() - 3600, "/");
            }
        }
    }
}

include('../includes/header.php');
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-laptop-medical me-2"></i>Trusted Devices</h5>
                    <p class="text-muted small">Manage devices that can log in without an OTP code.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    
                    <?php if($msg): ?>
                        <div class="alert alert-success"><?php echo $msg; ?></div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Device Name</th>
                                    <th>Trust Created</th>
                                    <th class="text-end">Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM trusted_devices WHERE user_id='$user_id' AND role='$role' ORDER BY created_at DESC";
                                $result = mysqli_query($conn, $query);
                                
                                $current_token = isset($_COOKIE['remember_me']) ? $_COOKIE['remember_me'] : '';

                                if(mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $is_current = ($row['token'] === $current_token);
                                        $date = date('M d, Y h:i A', strtotime($row['created_at']));
                                        
                                        // Icon logic
                                        $icon = "fa-desktop";
                                        if(stripos($row['device_name'], 'Phone') !== false || stripos($row['device_name'], 'Android') !== false || stripos($row['device_name'], 'iPhone') !== false) {
                                            $icon = "fa-mobile-alt";
                                        }

                                        echo "<tr>";
                                        echo "<td>
                                                <div class='fw-bold text-primary'><i class='fas $icon me-2'></i>".$row['device_name']."</div>
                                              </td>";
                                        echo "<td class='text-muted small'>$date</td>";
                                        echo "<td class='text-end'>";
                                        if($is_current) {
                                            echo "<span class='badge bg-success'>Current Device</span>";
                                        } else {
                                            echo "<span class='badge bg-light text-dark border'>Trusted</span>";
                                        }
                                        echo "</td>";
                                        echo "<td class='text-end'>
                                                <a href='devices.php?remove_id=".$row['device_id']."' 
                                                   class='btn btn-sm btn-outline-danger' 
                                                   onclick='return confirm(\"Remove this device? You will need OTP to login on it next time.\")'>
                                                    Remove
                                                </a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No trusted devices found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>