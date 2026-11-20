<?php
require_once 'db.php';

// Redirect already logged-in users directly to the portal
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "Username already exists. Please choose another.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insertStmt = $conn->prepare("INSERT INTO users (name, email, mobile, username, password) VALUES (?, ?, ?, ?, ?)");
        $insertStmt->bind_param("sssss", $name, $email, $mobile, $username, $hashed_password);

        if ($insertStmt->execute()) {
            // Automatically log in the newly registered user and enter the portal
            $_SESSION['user_id'] = $insertStmt->insert_id;
            $_SESSION['name'] = $name;
            $_SESSION['user_name'] = $name;
            header("Location: index.php");
            exit();
        } else {
            $message = "Error creating account. Please try again.";
        }
        $insertStmt->close();
    }
    $stmt->close();
}
$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <title>Sign Up - Alumni Network</title>
    <link rel="stylesheet" href="signupstyles.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel&family=Great+Vibes&display=swap" rel="stylesheet">
</head>
<body>

<div id="signUp">
    <div id="letsGo" style="font-size:10vh;font-family: 'Great Vibes', cursive;">Let's Connect!!</div>

    <?php if (!empty($message)): ?>
        <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="fname" class="field"><b>Name</b><span style="color:red;">*</span></label>
        <input class="inp" type="text" name="name" id="fname" placeholder="Enter your Name" size="35" required>

        <label for="mail" class="field"><b>Email</b><span style="color:red;">*</span></label>
        <input class="inp" type="email" name="email" id="mail" placeholder="Enter your Email id" size="35" required>

        <label for="num" class="field"><b>Mobile No.</b><span style="color:red;">*</span></label>
        <input class="inp" type="tel" name="mobile" id="num" placeholder="Enter your Mobile number" size="35" required>

        <label for="userN" class="field"><b>Username</b><span style="color:red;">*</span></label>
        <input class="inp" type="text" name="username" id="userN" placeholder="Enter your Username" size="35" required>

        <label for="pass" class="field"><b>Password</b><span style="color:red;">*</span></label>
        <input class="inp" type="password" name="password" id="pass" placeholder="Enter your Password" size="35" required>

        <button type="submit" class="btn">Sign Up</button>

        <p>Already have an account? <a href="login.php" class="login"><u>Click here to login!</u></a></p>
    </form>
</div>

</body>
</html>