<?php
require_once 'header.php';
require_once 'db_connect.php';
require_once 'error_handler.php';

session_start();

if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, username, password, user_type FROM users WHERE username = ? AND user_type = 'admin'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
    } catch (Exception $e) {
        log_message("Error in admin login: " . $e->getMessage(), 'ERROR');
        $error = "An error occurred. Please try again later.";
    }
}
?>

<h2>Admin Login</h2>
<?php if (isset($error)) echo display_error($error); ?>
<form action="" method="post">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Login</button>
</form>

<?php include 'footer.php'; ?>