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
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="job_listings.php">Job Listings</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_type'] == 'candidate'): ?>
                        <li><a href="candidate_profile.php">My Profile</a></li>
                        <li><a href="application_tracking.php">My Applications</a></li>
                    <?php elseif ($_SESSION['user_type'] == 'employer'): ?>
                        <li><a href="employer_profile.php">Company Profile</a></li>
                        <li><a href="post_job.php">Post a Job</a></li>
                    <?php elseif ($_SESSION['user_type'] == 'admin'): ?>
                        <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>