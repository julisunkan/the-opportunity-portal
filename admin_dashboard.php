<?php
require_once 'header.php';
require_once 'db_connect.php';
require_once 'error_handler.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    log_message("Unauthorized access attempt to admin_dashboard.php. User ID: " . ($_SESSION['user_id'] ?? 'Not set'), 'WARNING');
    header("Location: admin_login.php");
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    try {
        switch ($_POST['action']) {
            case 'delete_user':
                $user_id = $_POST['user_id'];
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                log_admin_action($_SESSION['user_id'], 'delete_user', "Deleted user ID: $user_id");
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                exit();
            case 'delete_job':
                $job_id = $_POST['job_id'];
                $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
                $stmt->bind_param("i", $job_id);
                $stmt->execute();
                log_admin_action($_SESSION['user_id'], 'delete_job', "Deleted job ID: $job_id");
                echo json_encode(['success' => true, 'message' => 'Job deleted successfully']);
                exit();
        }
    } catch (Exception $e) {
        log_message("Error in admin action: " . $e->getMessage(), 'ERROR');
        echo json_encode(['success' => false, 'message' => 'An error occurred while performing the action']);
        exit();
    }
}

// Fetch users
$users_result = $conn->query("SELECT id, username, email, user_type FROM users ORDER BY id DESC LIMIT 10");

// Fetch jobs
$jobs_result = $conn->query("SELECT j.id, j.title, e.company_name, j.created_at FROM jobs j JOIN employers e ON j.employer_id = e.id ORDER BY j.created_at DESC LIMIT 10");

// Fetch recent applications
$applications_result = $conn->query("SELECT a.id, j.title, c.first_name, c.last_name, a.status, a.applied_at FROM applications a JOIN jobs j ON a.job_id = j.id JOIN candidates c ON a.candidate_id = c.id ORDER BY a.applied_at DESC LIMIT 10");
?>

<h2>Admin Dashboard</h2>

<div id="admin-actions">
    <h3>Recent Users</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Action</th>
        </tr>
        <?php while ($user = $users_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo $user['user_type']; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" value="<?php echo  $user['id']; ?>">
                        <button type="submit" class="delete-user">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3>Recent Jobs</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Company</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
        <?php while ($job = $jobs_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $job['id']; ?></td>
                <td><?php echo htmlspecialchars($job['title']); ?></td>
                <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                <td><?php echo $job['created_at']; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="action" value="delete_job">
                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                        <button type="submit" class="delete-job">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3>Recent Applications</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Job Title</th>
            <th>Candidate Name</th>
            <th>Status</th>
            <th>Applied At</th>
        </tr>
        <?php while ($application = $applications_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $application['id']; ?></td>
                <td><?php echo htmlspecialchars($application['title']); ?></td>
                <td><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></td>
                <td><?php echo $application['status']; ?></td>
                <td><?php echo $application['applied_at']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<h3>Admin Actions</h3>
<ul>
    <li><a href="admin_user_management.php">Manage Users</a></li>
    <li><a href="admin_job_management.php">Manage Jobs</a></li>
    <li><a href="admin_application_management.php">Manage Applications</a></li>
    <li><a href="admin_reports.php">View Reports</a></li>
</ul>

<?php include 'footer.php'; ?>