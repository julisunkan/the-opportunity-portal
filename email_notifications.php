<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure you've installed PHPMailer via Composer

function send_email($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com'; // Replace with your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_username@example.com'; // Replace with your email
        $mail->Password   = 'your_password'; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('noreply@opportunityportal.com', 'The Opportunity Portal');
        $mail->addAddress($to);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function notify_application_status_change($application_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT a.status, c.email, j.title, e.company_name 
                            FROM applications a 
                            JOIN candidates c ON a.candidate_id = c.id 
                            JOIN jobs j ON a.job_id = j.id 
                            JOIN employers e ON j.employer_id = e.id 
                            WHERE a.id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();

    $to = $application['email'];
    $subject = "Application Status Update - {$application['title']}";
    $body = "Dear Candidate,<br><br>
             Your application status for the position of {$application['title']} at {$application['company_name']} 
             has been updated to: {$application['status']}.<br><br>
             Please log in to your account for more details.<br><br>
             Best regards,<br>
             The Opportunity Portal Team";

    return send_email($to, $subject, $body);
}

function notify_new_application($job_id, $candidate_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT e.email, j.title, c.first_name, c.last_name 
                            FROM jobs j 
                            JOIN employers e ON j.employer_id = e.id 
                            JOIN candidates c ON c.id = ? 
                            WHERE j.id = ?");
    $stmt->bind_param("ii", $candidate_id, $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();

    $to = $application['email'];
    $subject = "New Application Received - {$application['title']}";
    $body = "Dear Employer,<br><br>
             A new application has been received for the position of {$application['title']}.<br><br>
             Candidate Name: {$application['first_name']} {$application['last_name']}<br><br>
             Please log in to your account to review the application.<br><br>
             Best regards,<br>
             The Opportunity Portal Team";

    return send_email($to, $subject, $body);
}