<?php
include 'header.php';
include 'db_connect.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'candidate') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM candidates WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    $stmt = $conn->prepare("UPDATE candidates SET first_name = ?, last_name = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $first_name, $last_name, $user_id);
    $stmt->execute();

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $target_dir =   "uploads/";
        $target_file = $target_dir . basename($_FILES["resume"]["name"]);
        if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE candidates SET resume_url = ? WHERE user_id = ?");
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
        }
    }

    $success = "Profile updated successfully";
}
?>

<h2>Candidate Profile</h2>
<?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
<form action="" method="post" enctype="multipart/form-data">
    <label for="first_name">First Name:</label>
    <input type="text" id="first_name" name="first_name" value="<?php echo $candidate['first_name']; ?>" required>

    <label for="last_name">Last Name:</label>
    <input type="text" id="last_name" name="last_name" value="<?php echo $candidate['last_name']; ?>" required>

    <label for="resume">Resume:</label>
    <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">

    <?php if ($candidate['resume_url']): ?>
        <p>Current resume: <a href="<?php echo $candidate['resume_url']; ?>" target="_blank">View</a></p>
    <?php endif; ?>

    <button type="submit">Update Profile</button>
</form>

<?php include 'footer.php'; ?>