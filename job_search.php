<?php
require_once 'header.php';
require_once 'db_connect.php';
require_once 'error_handler.php';

session_start();

try {
    $search_term = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    $sql = "SELECT j.*, e.company_name FROM jobs j 
            JOIN employers e ON j.employer_id = e.id 
            WHERE j.title LIKE ? OR j.description LIKE ? OR e.company_name LIKE ?
            ORDER BY j.created_at DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $search_param = "%$search_term%";
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $per_page, $offset);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    // Get total number of results for pagination
    $total_sql = "SELECT COUNT(*) as count FROM jobs j 
                  JOIN employers e ON j.employer_id = e.id 
                  WHERE j.title LIKE ? OR j.description LIKE ? OR e.company_name LIKE ?";
    $total_stmt = $conn->prepare($total_sql);
    if (!$total_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $total_stmt->bind_param("sss", $search_param, $search_param, $search_param);
    if (!$total_stmt->execute()) {
        throw new Exception("Execute failed: " . $total_stmt->error);
    }

    $total_result = $total_stmt->get_result();
    $total_jobs = $total_result->fetch_assoc()['count'];
    $total_pages = ceil($total_jobs / $per_page);

    log_message("Job search performed. Search term: '$search_term', Results: $total_jobs");
} catch (Exception $e) {
    log_message("Error in job search: " . $e->getMessage(), 'ERROR');
    echo display_error("An error occurred while searching for jobs. Please try again later.");
    include 'footer.php';
    exit();
}
?>

<h2>Job Search</h2>
<form action="" method="get">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search jobs...">
    <button type="submit">Search</button>
</form>

<h3>Search Results</h3>
<?php if ($result->num_rows > 0): ?>
    <?php while ($job = $result->fetch_assoc()): ?>
        <div class="job-listing">
            <h4><?php echo htmlspecialchars($job['title']); ?></h4>
            <p>Company: <?php echo htmlspecialchars($job['company_name']); ?></p>
            <p>Location: <?php echo htmlspecialchars($job['location']); ?></p>
            <p>Salary: <?php echo htmlspecialchars($job['salary']); ?></p>
            <p><?php echo substr(htmlspecialchars($job['description']), 0, 200) . '...'; ?></p>
            <a href="job_details.php?id=<?php echo $job['id']; ?>">View Details</a>
        </div>
    <?php endwhile; ?>

    <!-- Pagination -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?search=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>" <?php echo ($page == $i) ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
<?php else: ?>
    <p>No jobs found matching your search criteria.</p>
<?php endif; ?>

<?php include 'footer.php'; ?>