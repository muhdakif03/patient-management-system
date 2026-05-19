<?php
$required_role = 'doctor';
include('../config/session.php');
include('../config/db_connect.php');

if(!isset($_GET['id'])) {
    header("Location: records.php");
    exit();
}

$patient_id = mysqli_real_escape_string($conn, $_GET['id']);

// 1. Fetch Patient Details
$pat_sql = "SELECT * FROM patient WHERE patient_id='$patient_id'";
$pat_res = mysqli_query($conn, $pat_sql);
$patient = mysqli_fetch_assoc($pat_res);

if(!$patient) {
    echo "Patient not found.";
    exit();
}

include('../includes/header.php');
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <a href="records.php" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1"><?php echo $patient['name']; ?></h4>
                        <p class="text-muted mb-0">
                            <i class="fas fa-envelope me-2"></i><?php echo $patient['email']; ?> | 
                            <i class="fas fa-phone mx-2"></i><?php echo $patient['phone_number']; ?> | 
                            <span class="badge bg-light text-dark border ms-2"><?php echo $patient['gender']; ?></span>
                        </p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Patient ID</small>
                        <h3 class="text-primary fw-bold">#<?php echo $patient['patient_id']; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-history me-2"></i>Medical History Timeline</h5>
                </div>
                <div class="card-body px-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Attending Doctor</th>
                                    <th>Diagnosis</th>
                                    <th>Treatment</th>
                                    <th>Vitals</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hist_sql = "SELECT r.*, d.name as doctor_name 
                                             FROM patient_record r 
                                             JOIN doctor d ON r.doctor_id = d.doctor_id 
                                             WHERE r.patient_id='$patient_id' 
                                             ORDER BY r.date_created DESC"; 
                                $hist_res = mysqli_query($conn, $hist_sql);

                                if(mysqli_num_rows($hist_res) > 0) {
                                    while($row = mysqli_fetch_assoc($hist_res)) {
                                        $date = date('M d, Y', strtotime($row['date_created']));
                                        
                                        echo "<tr>";
                                        echo "<td class='fw-bold'>$date</td>";
                                        echo "<td>Dr. ".$row['doctor_name']."</td>";
                                        echo "<td>".$row['diagnosis']."</td>";
                                        echo "<td><small>".$row['treatment']."</small></td>";
                                        echo "<td>
                                                <div class='small'>BP: <b>".$row['blood_pressure']."</b></div>
                                                <div class='small'>Temp: <b>".$row['temperature']."°C</b></div>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No medical history records found for this patient.</td></tr>";
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