<?php
require_once 'db.php';

// Redirect already logged-in users directly to the portal
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$errorMsg = "";
$infoMsg = isset($_GET['logged_out']) ? "You have been securely logged out." : "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Save user info in PHP Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: index.php");
            exit();
        } else {
            $errorMsg = "Invalid password. Please try again.";
        }
    } else {
        $errorMsg = "Username not found. Please check or sign up.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Login</title>
    <link rel="stylesheet" href="logincss.css">
</head>
<body>
    <div class="login-container">
        <h2>Welcome to Alumni Management Network</h2>

        <?php if (!empty($infoMsg)): ?>
            <p style="color: #047857; font-weight: bold;"><?php echo htmlspecialchars($infoMsg); ?></p>
        <?php endif; ?>

        <?php if (!empty($errorMsg)): ?>
            <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($errorMsg); ?></p>
        <?php endif; ?>

        <!-- Fixed: action="" submits to this PHP file instead of skipping to HTML -->
        <form method="POST" action="">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="signup-section">
            <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
        </div>
    </div>
</body>
</html>