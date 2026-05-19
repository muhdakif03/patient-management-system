<?php
$required_role = 'patient';
include('../config/session.php');
include('../config/db_connect.php');

$msg = "";
$error = "";

// Handle Booking Submission
if(isset($_POST['book_appt'])) {
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $patient_id = $_SESSION['user_id'];
    
    $full_datetime = $date . ' ' . $time;

    // Double-Check: Server-side validation to prevent race conditions
    $check_sql = "SELECT * FROM appointment WHERE doctor_id='$doctor_id' AND appointment_date='$full_datetime' AND status != 'Cancelled'";
    if(mysqli_num_rows(mysqli_query($conn, $check_sql)) > 0) {
        $error = "Error: This slot was just taken by another patient. Please choose another.";
    } else {
        $sql = "INSERT INTO appointment (doctor_id, patient_id, appointment_date, status) VALUES ('$doctor_id', '$patient_id', '$full_datetime', 'Pending')";
        if(mysqli_query($conn, $sql)) {
            $msg = "Appointment booked successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Fetch Doctors
$doctors = [];
$query = "SELECT doctor_id, name FROM doctor";
$result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($result)) {
    $doctors[] = $row;
}

include('../includes/header.php');
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-calendar-plus me-2"></i>Book an Appointment</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Select Doctor</label>
                            <select name="doctor_id" id="doctorSelect" class="form-select bg-light" required onchange="fetchSlots()">
                                <option value="">-- Choose a Doctor --</option>
                                <?php foreach($doctors as $doc): ?>
                                    <option value="<?php echo $doc['doctor_id']; ?>">
                                        Dr. <?php echo $doc['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Date</label>
                            <input type="date" name="date" id="dateSelect" class="form-select bg-light" min="<?php echo date('Y-m-d'); ?>" required onchange="fetchSlots()">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small">Available Time Slots</label>
                            <select name="time" id="timeSelect" class="form-select bg-light" required>
                                <option value="">-- Select Date First --</option>
                            </select>
                        </div>

                        <button type="submit" name="book_appt" class="btn btn-primary w-100 fw-bold shadow-sm mt-2">
                            Confirm Appointment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function to fetch available slots via AJAX
function fetchSlots() {
    var doctorId = document.getElementById('doctorSelect').value;
    var dateVal = document.getElementById('dateSelect').value;
    var timeSelect = document.getElementById('timeSelect');

    // Only fetch if both Doctor and Date are selected
    if(doctorId && dateVal) {
        timeSelect.innerHTML = "<option>Loading slots...</option>";

        // Create Form Data
        var formData = new FormData();
        formData.append('doctor_id', doctorId);
        formData.append('date', dateVal);

        // Fetch API
        fetch('get_slots.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            timeSelect.innerHTML = data;
        })
        .catch(error => {
            console.error('Error:', error);
            timeSelect.innerHTML = "<option>Error loading slots</option>";
        });
    } else {
        timeSelect.innerHTML = "<option value=''>-- Select Date First --</option>";
    }
}
</script>

<?php include('../includes/footer.php'); ?>