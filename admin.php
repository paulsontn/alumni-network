<?php
require_once 'db.php';

// Admin Password Gate
$adminPassword = "admin123"; // Change this to your preferred password

if (isset($_POST['admin_login'])) {
    if ($_POST['admin_pass'] === $adminPassword) {
        $_SESSION['is_admin'] = true;
    } else {
        $loginError = "Incorrect Admin Password!";
    }
}

if (isset($_GET['admin_logout'])) {
    unset($_SESSION['is_admin']);
    header("Location: admin.php");
    exit();
}

// Show Admin Login screen if not authenticated
if (empty($_SESSION['is_admin'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Alumni Network</title>
    <link rel="stylesheet" href="logincss.css">
</head>
<body>
    <div class="login-container">
        <h2>Admin Panel Login</h2>
        <?php if (!empty($loginError)): ?>
            <p style="color: red; margin-bottom: 10px;"><?php echo $loginError; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="input-group">
                <label for="admin_pass">Admin Password</label>
                <input type="password" id="admin_pass" name="admin_pass" placeholder="Enter admin password" required>
            </div>
            <button type="submit" name="admin_login" class="login-btn">Access Dashboard</button>
        </form>
        <div class="signup-section">
            <p><a href="index.php">← Back to Main Website</a></p>
        </div>
    </div>
</body>
</html>
<?php
exit();
endif;

// Handle deleting a user
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Handle deleting a message
if (isset($_GET['delete_msg'])) {
    $id = intval($_GET['delete_msg']);
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Handle deleting an event
if (isset($_GET['delete_event'])) {
    $id = intval($_GET['delete_event']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Handle deleting a job
if (isset($_GET['delete_job'])) {
    $id = intval($_GET['delete_job']);
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

$users = $conn->query("SELECT id, name, email, mobile, username, college, job_title, created_at FROM users ORDER BY id DESC");
$messages = $conn->query("SELECT id, sender_name, sender_email, message, sent_at FROM messages ORDER BY id DESC");
$events = $conn->query("SELECT id, title, event_date, location FROM events ORDER BY id DESC");
$jobs = $conn->query("SELECT id, title, company, location FROM jobs ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Alumni Network</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-container {
            max-width: 1050px;
            margin: 30px auto;
            padding: 25px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 35px;
        }
        th, td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #163a5f;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .del-btn {
            color: #d9534f;
            text-decoration: none;
            font-weight: bold;
        }
        .del-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>Alumni Network — Admin Dashboard</h1>
        <nav>
            <a href="index.php">← Back to Main Website</a>
            <a href="alumni.php">Events & Jobs</a>
            <a href="admin.php?admin_logout=1" style="color: #ff6b6b;">Lock Admin</a>
        </nav>
    </header>

    <div class="admin-container">
        <h2>Registered Alumni Members (<?php echo $users ? $users->num_rows : 0; ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>College</th>
                    <th>Job Title</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users && $users->num_rows > 0): ?>
                    <?php while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['mobile']); ?></td>
                            <td><?php echo htmlspecialchars($u['college']); ?></td>
                            <td><?php echo htmlspecialchars($u['job_title']); ?></td>
                            <td><a class="del-btn" href="admin.php?delete_user=<?php echo $u['id']; ?>" onclick="return confirm('Delete this user?');">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No registered users yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Contact Us Messages (<?php echo $messages ? $messages->num_rows : 0; ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sender Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($messages && $messages->num_rows > 0): ?>
                    <?php while ($m = $messages->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $m['id']; ?></td>
                            <td><?php echo htmlspecialchars($m['sender_name']); ?></td>
                            <td><?php echo htmlspecialchars($m['sender_email']); ?></td>
                            <td><?php echo htmlspecialchars($m['message']); ?></td>
                            <td><?php echo $m['sent_at']; ?></td>
                            <td><a class="del-btn" href="admin.php?delete_msg=<?php echo $m['id']; ?>" onclick="return confirm('Delete this message?');">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No messages received yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Published Events (<?php echo $events ? $events->num_rows : 0; ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event Title</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($events && $events->num_rows > 0): ?>
                    <?php while ($ev = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $ev['id']; ?></td>
                            <td><?php echo htmlspecialchars($ev['title']); ?></td>
                            <td><?php echo htmlspecialchars($ev['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($ev['location']); ?></td>
                            <td><a class="del-btn" href="admin.php?delete_event=<?php echo $ev['id']; ?>" onclick="return confirm('Delete this event?');">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No events posted yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Job Opportunities (<?php echo $jobs ? $jobs->num_rows : 0; ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Role</th>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($jobs && $jobs->num_rows > 0): ?>
                    <?php while ($j = $jobs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $j['id']; ?></td>
                            <td><?php echo htmlspecialchars($j['title']); ?></td>
                            <td><?php echo htmlspecialchars($j['company']); ?></td>
                            <td><?php echo htmlspecialchars($j['location']); ?></td>
                            <td><a class="del-btn" href="admin.php?delete_job=<?php echo $j['id']; ?>" onclick="return confirm('Delete this job?');">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No job listings posted yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>