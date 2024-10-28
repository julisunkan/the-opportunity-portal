<?php
require_once 'db_connect.php';
require_once 'error_handler.php';

session_start();

// Fetch recent job listings
try {
    $stmt = $conn->prepare("SELECT j.id, j.title, j.location, e.company_name 
                            FROM jobs j 
                            JOIN employers e ON j.employer_id = e.id 
                            ORDER BY j.created_at DESC LIMIT 10");
    $stmt->execute();
    $job_listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    log_message("Error fetching job listings: " . $e->getMessage(), 'ERROR');
    $job_listings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Opportunity Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">The Opportunity Portal</div>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#jobs">Jobs</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="home" class="hero">
            <h1>Find Your Dream Job</h1>
            <p>Connect with top employers and discover exciting career opportunities.</p>
            <div class="cta-buttons">
                <button id="showCandidateModal">I'm a Job Seeker</button>
                <button id="showEmployerModal">I'm an Employer</button>
            </div>
        </section>

        <section id="jobs" class="job-listings">
            <h2>Recent Job Listings</h2>
            <div class="job-list">
                <?php foreach ($job_listings as $job): ?>
                    <div class="job-card">
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p class="company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                        <p class="location"><?php echo htmlspecialchars($job['location']); ?></p>
                        <a href="job_details.php?id=<?php echo $job['id']; ?>" class="view-job">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 The Opportunity Portal. All rights reserved.</p>
    </footer>

    <!-- Modal for Candidate Login/Register -->
    <div id="candidateModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Candidate Portal</h2>
            <div class="tab">
                <button class="tablinks active" onclick="openTab(event, 'candidateLogin')">Login</button>
                <button class="tablinks" onclick="openTab(event, 'candidateRegister')">Register</button>
            </div>
            <div id="candidateLogin" class="tabcontent" style="display:block;">
                <form id="candidateLoginForm" action="login.php" method="post">
                    <input type="hidden" name="user_type" value="candidate">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
            </div>
            <div id="candidateRegister" class="tabcontent">
                <form id="candidateRegisterForm" action="register.php" method="post">
                    <input type="hidden" name="user_type" value="candidate">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <button type="submit">Register</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Employer Login/Register -->
    <div id="employerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Employer Portal</h2>
            <div class="tab">
                <button class="tablinks active" onclick="openTab(event, 'employerLogin')">Login</button>
                <button class="tablinks" onclick="openTab(event, 'employerRegister')">Register</button>
            </div>
            <div id="employerLogin" class="tabcontent" style="display:block;">
                <form id="employerLoginForm" action="login.php" method="post">
                    <input type="hidden" name="user_type" value="employer">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
            </div>
            <div id="employerRegister" class="tabcontent">
                <form id="employerRegisterForm" action="register.php" method="post">
                    <input type="hidden" name="user_type" value="employer">
                    <input type="text" name="company_name" placeholder="Company Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <button type="submit">Register</button>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>