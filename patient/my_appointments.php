<?php
$required_role = 'patient';
include('../config/session.php');
include('../config/db_connect.php');

$patient_id = $_SESSION['user_id'];
$msg = "";

// Handle Cancellation
if(isset($_GET['cancel_id'])) {
    $app_id = $_GET['cancel_id'];
    
    $check_sql = "SELECT * FROM appointment WHERE appointment_id='$app_id' AND patient_id='$patient_id'";
    if(mysqli_num_rows(mysqli_query($conn, $check_sql)) > 0) {
        mysqli_query($conn, "UPDATE appointment SET status='Cancelled' WHERE appointment_id='$app_id'");
        $msg = "Appointment cancelled successfully.";
    }
}

include('../includes/header.php');
?>

<div class="container-fluid">
    <?php if($msg) echo "<div class='alert alert-warning'>$msg</div>"; ?>

    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold text-dark"><i class="fas fa-calendar-alt me-2"></i>My Appointments</h5>
        </div>
        <div class="card-body px-4">
            <div class="table-responsive">
                <table class="table table-hover table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Doctor</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT a.*, d.name as doc_name 
                                  FROM appointment a 
                                  JOIN doctor d ON a.doctor_id = d.doctor_id 
                                  WHERE a.patient_id='$patient_id' 
                                  ORDER BY a.appointment_date DESC";
                        $result = mysqli_query($conn, $query);

                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $date_obj = new DateTime($row['appointment_date']);
                                $is_future = $date_obj > new DateTime();
                                
                                $status_badge = match($row['status']) {
                                    'Pending' => 'bg-warning text-dark',
                                    'Confirmed' => 'bg-primary',
                                    'Completed' => 'bg-success',
                                    'Cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };

                                echo "<tr>";
                                echo "<td class='fw-bold'>".$date_obj->format('M d, Y h:i A')."</td>";
                                echo "<td>Dr. ".$row['doc_name']."</td>";
                                echo "<td><span class='badge $status_badge rounded-pill'>".$row['status']."</span></td>";
                                echo "<td class='text-end'>";
                                
                                // Only show Cancel button if Future AND not already cancelled/completed
                                if($is_future && ($row['status'] == 'Pending' || $row['status'] == 'Confirmed')) {
                                    echo "<a href='my_appointments.php?cancel_id=".$row['appointment_id']."' 
                                             class='btn btn-sm btn-outline-danger' 
                                             onclick='return confirm(\"Are you sure you want to cancel?\")'>
                                             <i class='fas fa-ban me-1'></i> Cancel
                                          </a>";
                                } else {
                                    echo "<span class='text-muted small'>-</span>";
                                }
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No appointments found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>