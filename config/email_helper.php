<?php
define('DEVELOPMENT_MODE', true); 

function send_system_email($to, $subject, $body) {
    
    // 1. DEVELOPMENT MODE
    if (DEVELOPMENT_MODE) {
        $log_file = dirname(__DIR__) . '/otp_logs.txt';
        $entry = "TIME: " . date("Y-m-d H:i:s") . " | TO: $to | SUBJECT: $subject | $body\n";
        file_put_contents($log_file, $entry, FILE_APPEND);
        return true;
    }
    
    // 2. LIVE MODE
    else {
        require_once dirname(__DIR__) . '/libs/PHPMailer/Exception.php';
        require_once dirname(__DIR__) . '/libs/PHPMailer/PHPMailer.php';
        require_once dirname(__DIR__) . '/libs/PHPMailer/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            
            // YOUR CREDENTIALS
            $mail->Username   = 'YOUR_EMAIL@gmail.com'; 
            $mail->Password   = 'YOUR_APP_PASSWORD';
            
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('muhdakif031@gmail.com', 'PMS System');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body); 

            $mail->send();
            return true; 

        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false; 
        }
    }
}
?>