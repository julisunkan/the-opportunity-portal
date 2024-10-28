<?php
include 'header.php';
include 'db_connect.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'candidate') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT a.*, j.title, e.company_name FROM applications a 
                        JOIN jobs j ON a.job_id = j.id 
                        JOIN employers e ON j.employer_id = e.id 
                        WHERE a.candidate_id = (SELECT id FROM candidates WHERE user_id = ?)
                        ORDER BY a.applied_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>My Applications</h2>
<table>
    <tr>
        <th>Job Title</th>
        <th>Company</th>
        <th>Applied Date</th>
        <th>Status</th>
    </tr>
    <?php while ($application = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $application['title']; ?></td>
            <td><?php echo $application['company_name']; ?></td>
            <td><?php echo date('Y-m-d', strtotime($application['applied_at'])); ?></td>
            <td><?php echo ucfirst($application['status']); ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<?php include 'footer.php'; ?>