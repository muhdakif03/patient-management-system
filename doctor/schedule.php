<?php
$required_role = 'doctor';
include('../config/session.php');
include('../config/db_connect.php');

$doc_id = $_SESSION['user_id'];
$msg = "";

// 1. Handle Form Submission
if(isset($_POST['save_schedule'])) {
    $schedule_data = [];
    
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    foreach($days as $day) {
        if(isset($_POST['active'][$day])) {
            $start = $_POST['start'][$day];
            $end = $_POST['end'][$day];
            
            $schedule_data[$day] = "$start - $end";
        } else {
            $schedule_data[$day] = "Closed";
        }
    }

    $json_schedule = mysqli_real_escape_string($conn, json_encode($schedule_data));

    $sql = "UPDATE doctor SET availability_schedule='$json_schedule' WHERE doctor_id='$doc_id'";
    if(mysqli_query($conn, $sql)) {
        $msg = "Availability updated successfully!";
    }
}

// 2. Fetch Current Schedule
$query = "SELECT availability_schedule FROM doctor WHERE doctor_id='$doc_id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

$current_schedule = json_decode($row['availability_schedule'], true);
if(!$current_schedule) {
    $current_schedule = array_fill_keys(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], 'Closed');
}

include('../includes/header.php');
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-clock me-2"></i>Weekly Availability</h5>
                    <p class="text-muted small">Set your working hours for the week.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                    
                    <form method="POST">
                        <div class="list-group mb-4">
                            <?php 
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach($days as $day): 
                                // Parse saved data (e.g., "09:00 - 17:00")
                                $is_active = ($current_schedule[$day] !== 'Closed');
                                $start_val = '';
                                $end_val = '';
                                
                                if($is_active) {
                                    // Split "09:00 - 17:00" into two parts
                                    $parts = explode(' - ', $current_schedule[$day]);
                                    $start_val = $parts[0] ?? '09:00';
                                    $end_val = $parts[1] ?? '17:00';
                                }
                            ?>
                            <div class="list-group-item py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="active[<?php echo $day; ?>]" 
                                                   id="switch_<?php echo $day; ?>" 
                                                   <?php echo $is_active ? 'checked' : ''; ?> 
                                                   onchange="toggleTimes('<?php echo $day; ?>')">
                                            <label class="form-check-label fw-bold" for="switch_<?php echo $day; ?>"><?php echo $day; ?></label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-9" id="times_<?php echo $day; ?>" style="display: <?php echo $is_active ? 'flex' : 'none'; ?>;">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">From</span>
                                            <input type="time" name="start[<?php echo $day; ?>]" class="form-control" value="<?php echo $start_val ?: '09:00'; ?>">
                                            <span class="input-group-text bg-light">To</span>
                                            <input type="time" name="end[<?php echo $day; ?>]" class="form-control" value="<?php echo $end_val ?: '17:00'; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" name="save_schedule" class="btn btn-primary px-5 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTimes(day) {
    var checkbox = document.getElementById('switch_' + day);
    var timeDiv = document.getElementById('times_' + day);
    if(checkbox.checked) {
        timeDiv.style.display = 'flex';
    } else {
        timeDiv.style.display = 'none';
    }
}
</script>

<?php include('../includes/footer.php'); ?>