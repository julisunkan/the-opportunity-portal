<?php
include 'header.php';
include 'db_connect.php';
include 'error_handler.php';

session_start();
?>

<h2>Job Listings</h2>

<div id="job-search">
    <input type="text" id="job-search-input" placeholder="Search for jobs...">
    <div id="search-results"></div>
</div>

<div id="job-listings">
    <?php
    try {
        $stmt = $conn->prepare("SELECT j.*, e.company_name FROM jobs j 
                                JOIN employers e ON j.employer_id = e.id 
                                ORDER BY j.created_at DESC LIMIT 10");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($job = $result->fetch_assoc()):
    ?>
        <div class="job-listing">
            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
            <p>Company: <?php echo htmlspecialchars($job['company_name']); ?></p>
            <p>Location: <?php echo htmlspecialchars($job['location']); ?></p>
            <p>Salary: <?php echo htmlspecialchars($job['salary']); ?></p>
            <p><?php echo substr(htmlspecialchars($job['description']), 0, 200) . '...'; ?></p>
            <a href="job_details.php?id=<?php echo $job['id']; ?>">View Details</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'candidate'): ?>
                <button class="apply-button" data-job-id="<?php echo $job['id']; ?>">Apply</button>
            <?php endif; ?>
        </div>
    <?php
        endwhile;
    } catch (Exception $e) {
        log_message("Error fetching job listings: " . $e->getMessage(), 'ERROR');
        echo display_error("An error occurred while fetching job listings. Please try again later.");
    }
    ?>
</div>

<?php include 'footer.php'; ?>