<?php
include('../config/db_connect.php');

if(isset($_POST['doctor_id']) && isset($_POST['date'])) {
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];
    
    // 1. Get Day of Week
    $day_of_week = date('l', strtotime($date));

    // 2. Fetch Doctor's Schedule & Existing Appointments
    $query = "SELECT availability_schedule FROM doctor WHERE doctor_id='$doctor_id'";
    $doc_result = mysqli_query($conn, $query);
    $doc_row = mysqli_fetch_assoc($doc_result);
    
    // 3. Fetch Booked Slots for this specific Date
    $booked_query = "SELECT DATE_FORMAT(appointment_date, '%H:%i') as time_slot 
                     FROM appointment 
                     WHERE doctor_id='$doctor_id' 
                     AND DATE(appointment_date) = '$date' 
                     AND status != 'Cancelled'";
    $booked_result = mysqli_query($conn, $booked_query);
    
    $booked_slots = [];
    while($b = mysqli_fetch_assoc($booked_result)) {
        $booked_slots[] = $b['time_slot'];
    }

    // 4. Generate Slots
    $schedule = json_decode($doc_row['availability_schedule'], true);
    $output = "<option value=''>-- Select Time --</option>";

    if(isset($schedule[$day_of_week]) && $schedule[$day_of_week] !== 'Closed') {
        $parts = explode(' - ', $schedule[$day_of_week]);
        $start_time = strtotime($parts[0]);
        $end_time = strtotime($parts[1]);
        $duration = 30 * 60;

        // Loop from Start to End
        for($current = $start_time; $current < $end_time; $current += $duration) {
            $slot = date('H:i', $current);
            $display_slot = date('h:i A', $current);

            // Check if slot is in the booked array
            if(in_array($slot, $booked_slots)) {
                $output .= "<option value='' disabled class='text-danger'>$display_slot (Booked)</option>";
            } else {
                $output .= "<option value='$slot'>$display_slot</option>";
            }
        }
    } else {
        $output = "<option value=''>Doctor is Unavailable on $day_of_week</option>";
    }

    echo $output;
}
?>