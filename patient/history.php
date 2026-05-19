<?php
$required_role = 'patient';
include('../config/session.php');
include('../config/db_connect.php');
include('../includes/header.php');

$patient_id = $_SESSION['user_id'];
?>

<div class="container-fluid">
    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold text-dark"><i class="fas fa-file-medical-alt me-2"></i>My Medical History</h5>
        </div>
        <div class="card-body px-4">
            <div class="table-responsive">
                <table class="table table-hover table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Doctor</th>
                            <th>Diagnosis</th>
                            <th>Treatment / Prescription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT r.*, d.name as doc_name 
                                  FROM patient_record r 
                                  JOIN doctor d ON r.doctor_id = d.doctor_id 
                                  WHERE r.patient_id='$patient_id' 
                                  ORDER BY r.date_created DESC";
                        $result = mysqli_query($conn, $query);

                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td><span class='badge bg-light text-dark border'>".date('M d, Y', strtotime($row['date_created']))."</span></td>";
                                echo "<td class='fw-bold text-primary'>Dr. ".$row['doc_name']."</td>";
                                echo "<td>".$row['diagnosis']."</td>";
                                echo "<td>".$row['treatment']."</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No medical records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>