<?php
include 'header.php';
include 'db_connect.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'employer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM employers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employer = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = $_POST['company_name'];
    $company_description = $_POST['company_description'];

    $stmt = $conn->prepare("UPDATE employers SET company_name = ?, company_description = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $company_name, $company_description, $user_id);
    $stmt->execute();

    $success = "Profile updated successfully";
}
?>

<h2>Employer Profile</h2>
<?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
<form action="" method="post">
    <label for="company_name">Company Name:</label>
    <input type="text" id="company_name" name="company_name" value="<?php echo $employer['company_name']; ?>" required>

    <label for="company_description">Company Description:</label>
    <textarea id="company_description" name="company_description" rows="5"><?php echo $employer['company_description']; ?></textarea>

    <button type="submit">Update Profile</button>
</form>

<?php include 'footer.php'; ?>