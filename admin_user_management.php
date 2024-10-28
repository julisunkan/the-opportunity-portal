<?php
require_once 'header.php';
require_once 'db_connect.php';
require_once 'error_handler.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    log_message("Unauthorized access attempt to admin_user_management.php. User ID: " . ($_SESSION['user_id'] ?? 'Not set'), 'WARNING');
    header("Location: admin_login.php");
    exit();
}

// Handle user management actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'delete_user':
                $user_id = $_POST['user_id'];
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                log_admin_action($_SESSION['user_id'], 'delete_user', "Deleted user ID: $user_id");
                $success = "User deleted successfully";
                break;
            case 'change_user_type':
                $user_id = $_POST['user_id'];
                $new_type = $_POST['new_type'];
                $stmt = $conn->prepare("UPDATE users SET user_type = ? WHERE id = ?");
                $stmt->bind_param("si", $new_type, $user_id);
                $stmt->execute();
                log_admin_action($_SESSION['user_id'], 'change_user_type', "Changed user ID: $user_id to type: $new_type");
                $success = "User type changed successfully";
                break;
        }
    } catch (Exception $e) {
        log_message("Error in admin user management: " . $e->getMessage(), 'ERROR');
        $error = "An error occurred while performing the action. Please try again.";
    }
}

// Fetch users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$users_result = $conn->query("SELECT id, username, email, user_type FROM users ORDER BY id DESC LIMIT $offset, $per_page");
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_pages = ceil($total_users / $per_page);

?>

<h2>User Management</h2>
<?php 
if (isset($success)) echo "<p class='success'>$success</p>";
if (isset($error)) echo display_error($error);
?>

<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>User Type</th>
        <th>Actions</th>
    </tr>
    <?php while ($user = $users_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo $user['user_type']; ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display: inline;">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <button type="submit">Delete</button>
                </form>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="change_user_type">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <select name="new_type">
                        <option value="candidate" <?php echo $user['user_type'] == 'candidate' ? 'selected' : ''; ?>>Candidate</option>
                        <option value="employer" <?php echo $user['user_type'] == 'employer' ? 'selected' : ''; ?>>Employer</option>
                        <option value="admin" <?php echo $user['user_type'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                    <button type="submit">Change Type</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<!-- Pagination -->
<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" <?php echo ($page == $i) ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
    <?php endfor; ?>
</div>

<p><a href="admin_dashboard.php">Back to Admin Dashboard</a></p>

<?php include  'footer.php'; ?>