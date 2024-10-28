<?php
require_once 'db_connect.php';
require_once 'error_handler.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'candidate') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $job_id = $_POST['job_id'];
        $candidate_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO applications (job_id, candidate_id, status) VALUES (?, ?, 'applied')");
        $stmt->bind_param("ii", $job_id, $candidate_id);

        if ($stmt->execute()) {
            $application_id = $stmt->insert_id;
            log_message("New application submitted. Job ID: $job_id, Candidate ID: $candidate_id");
            echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
        } else {
            throw new Exception("Failed to submit application");
        }
    } catch (Exception $e) {
        log_message("Error submitting application: " . $e->getMessage(), 'ERROR');
        echo json_encode(['success' => false, 'message' => 'An error occurred while submitting your application']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}