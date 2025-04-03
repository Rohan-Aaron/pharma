<?php
session_start(); // Start session at the very top
include 'assets/header.php';
require 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Initialize error message variable
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = trim($_POST['username']); // Trim whitespace
    $password = trim($_POST['password']); // Trim whitespace

    // Validate inputs
    if (empty($username) || empty($password)) {
        $error_message = "Username and password are required.";
    } else {
        // Fetch admin details from the database
        $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                // Verify password
                if (password_verify($password, $admin['password'])) {
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Set session variables
                    $_SESSION['user_id'] = $admin['user_id'];
                    $_SESSION['username'] = $admin['username'];
                    $_SESSION['role'] = $admin['role'];

                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error_message = "Invalid username or password.";
                }
            } else {
                $error_message = "Invalid username or password.";
            }
            $stmt->close();
        } else {
            $error_message = "Database error. Please try again later.";
        }
    }
}
?>

    <!--Wrapper-->
<div class="login-container">
    <h2>Login</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="submit">Login</button>
    </form>

    <?php if (!empty($error_message)) { ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php } ?>

    <a href="register.php" class="register-link">New user? Register here</a>
</div>
<!--Wrapper-->

<?php
include('assets/footer.php');
?>