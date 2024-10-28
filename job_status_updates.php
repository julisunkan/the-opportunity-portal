<?php
require_once 'header.php';
require_once 'db_connect.php';
require_once 'email_notifications.php';
require_once 'error_handler.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'employer') {
    log_message("Unauthorized access attempt to job_status_updates.php. User ID: " . ($_SESSION['user_id'] ?? 'Not set'), 'WARNING');
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $application_id = $_POST['application_id'];
        $new_status = $_POST['new_status'];

        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("si", $new_status, $application_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        if (!notify_application_status_change($application_id)) {
            log_message("Failed to send email notification for application status change. Application ID: $application_id", 'WARNING');
        }

        log_message("Application status updated. Application ID: $application_id, New Status: $new_status");
        $success = "Application status updated successfully";
    } catch (Exception $e) {
        log_message("Error updating application status: " . $e->getMessage(), 'ERROR');
        $error = "Error updating application status. Please try again later.";
    }
}

try {
    $stmt = $conn->prepare("SELECT a.*, j.title, c.first_name, c.last_name FROM applications a 
                            JOIN jobs j ON a.job_id = j.id 
                            JOIN candidates c ON a.candidate_id = c.id 
                            WHERE j.employer_id = (SELECT id FROM employers WHERE user_id = ?)
                            ORDER BY a.applied_at DESC");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
} catch (Exception $e) {
    log_message("Error fetching job applications: " . $e->getMessage(), 'ERROR');
    echo display_error("An error occurred while fetching job applications. Please try again later.");
    include 'footer.php';
    exit();
}
?>

<h2>Job Applications</h2>
<?php 
if (isset($success)) echo "<p class='success'>$success</p>";
if (isset($error)) echo display_error($error);
?>
<table>
    <tr>
        <th>Job Title</th>
        <th>Candidate Name</th>
        <th>Applied Date</th>
        <th>Current Status</th>
        <th>Update Status</th>
    </tr>
    <?php while ($application = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($application['title']); ?></td>
            <td><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></td>
            <td><?php echo date('Y-m-d', strtotime($application['applied_at'])); ?></td>
            <td><?php echo ucfirst($application['status']); ?></td>
            <td>
                <form action="" method="post">
                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                    <select name="new_status">
                        <option value="applied" <?php echo $application['status'] == 'applied' ? 'selected' : ''; ?>>Applied</option>
                        <option value="under review" <?php echo $application['status'] == 'under review' ? 'selected' : ''; ?>>Under Review</option>
                        <option value="interviewed" <?php echo $application['status'] == 'interviewed' ? 'selected' : ''; ?>>Interviewed</option>
                        <option value="offered" <?php echo $application['status'] == 'offered' ? 'selected' : ''; ?>>Offered</option>
                        <option value="hired" <?php echo $application['status'] == 'hired' ? 'selected' : ''; ?>>Hired</option>
                        <option value="rejected" <?php echo $application['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<?php include 'footer.php'; ?>