<?php
require_once 'db_connect.php';
require_once 'error_handler.php';

header('Content-Type: application/json');

try {
    $query = isset($_GET['q']) ? $_GET['q'] : '';
    $stmt = $conn->prepare("SELECT j.id, j.title, j.location, e.company_name 
                            FROM jobs j 
                            JOIN employers e ON j.employer_id = e.id 
                            WHERE j.title LIKE ? OR j.description LIKE ? OR e.company_name LIKE ?
                            LIMIT 10");
    $search_param = "%$query%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $jobs = [];
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    
    echo json_encode($jobs);
} catch (Exception $e) {
    log_message("Error in job search: " . $e->getMessage(), 'ERROR');
    echo json_encode(['error' => 'An error occurred while searching for jobs.']);
}