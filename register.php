<?php
include 'header.php';
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $user_type);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        if ($user_type == 'candidate') {
            $stmt = $conn->prepare("INSERT INTO candidates (user_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $_POST['first_name'], $_POST['last_name']);
        } else {
            $stmt = $conn->prepare("INSERT INTO employers (user_id, company_name) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $_POST['company_name']);
        }
        $stmt->execute();
        echo "<p class='success'>Registration successful. You can now <a href='login.php'>login</a>.</p>";
    } else {
        echo "<p class='error'>Error: " . $stmt->error . "</p>";
    }
}
?>

<h2>Register</h2>
<form action="" method="post">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <label for="user_type">I am a:</label>
    <select id="user_type" name="user_type" required>
        <option value="candidate">Job Seeker</option>
        <option value="employer">Employer</option>
    </select>

    <div id="candidate_fields" style="display:none;">
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name">

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name">
    </div>

    <div id="employer_fields" style="display:none;">
        <label for="company_name">Company Name:</label>
        <input type="text" id="company_name" name="company_name">
    </div>

    <button type="submit">Register</button>
</form>

<script>
document.getElementById('user_type').addEventListener('change', function() {
    var candidateFields = document.getElementById('candidate_fields');
    var employerFields = document.getElementById('employer_fields');
    if (this.value === 'candidate') {
        candidateFields.style.display = 'block';
        employerFields.style.display = 'none';
    } else {
        candidateFields.style.display = 'none';
        employerFields.style.display = 'block';
    }
});
</script>

<?php include 'footer.php'; ?>