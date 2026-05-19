<?php
// Function to record system activities securely
function log_activity($conn, $action, $description) {
    
    // 1. Identify the User
    // If they are logged in, grab their ID and Name from the session
    if(isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        $user_name = $_SESSION['name'];
    } else {
        // If not logged in (e.g., Login Page or System Task), record as "Guest/System"
        $user_id = 0;
        $role = 'guest';
        $user_name = 'Guest User';
    }

    // 2. Identify Metadata
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // 3. Insert into Database (Using Prepared Statements for Security)
    $stmt = $conn->prepare("INSERT INTO system_logs (user_id, role, user_name, action, description, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("isssss", $user_id, $role, $user_name, $action, $description, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}
?>