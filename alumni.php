<?php
require_once 'db.php';
$isLoggedIn = isset($_SESSION['user_id']);

// Handle posting a new Job
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_job']) && $isLoggedIn) {
    $jTitle = htmlspecialchars(trim($_POST['job_title']));
    $jCompany = htmlspecialchars(trim($_POST['company']));
    $jLoc = htmlspecialchars(trim($_POST['location']));
    $jDesc = htmlspecialchars(trim($_POST['description']));

    $stmt = $conn->prepare("INSERT INTO jobs (title, company, location, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $jTitle, $jCompany, $jLoc, $jDesc);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Career opportunity published to the board!');</script>";
}

// Handle posting a new Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event']) && $isLoggedIn) {
    $eTitle = htmlspecialchars(trim($_POST['event_title']));
    $eDate = htmlspecialchars(trim($_POST['event_date']));
    $eLoc = htmlspecialchars(trim($_POST['event_location']));
    $eDesc = htmlspecialchars(trim($_POST['event_description']));

    $stmt = $conn->prepare("INSERT INTO events (title, event_date, location, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $eTitle, $eDate, $eLoc, $eDesc);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Event published to the calendar!');</script>";
}

$events = $conn->query("SELECT * FROM events ORDER BY id DESC");
$jobs = $conn->query("SELECT * FROM jobs ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events &amp; Career Board - Official Alumni Network</title>
    <link rel="stylesheet" href="alustyles.css">
    <style>
        .top-nav { background: #163a5f; padding: 15px; text-align: center; }
        .top-nav a { color: #fff; margin: 0 15px; text-decoration: none; font-weight: bold; }
        .post-box { max-width: 520px; margin: 25px auto; padding: 20px; background: #f4f7fa; border-radius: 8px; border: 1px solid #ddd; text-align: left; }
        .post-box h3 { margin-bottom: 10px; color: #163a5f; }
        .post-box input, .post-box textarea { width: 96%; padding: 9px; margin: 6px 0; border-radius: 4px; border: 1px solid #ccc; }
        .post-box button { background: #163a5f; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 6px; }
        .post-box button:hover { background: #0f2944; }
    </style>
</head>
<body>
    <div class="top-nav">
        <a href="index.php">← Back to Home &amp; Directory</a>
        <a href="admin.php">Admin Portal</a>
    </div>

    <section id="alumni-info" class="section">
        <div class="alumni-content">
            <h2 class="welcome-heading">Alumni Events &amp; Career Board</h2>
            <p class="welcome-paragraph">Explore upcoming reunions, industry panels, and career opportunities curated exclusively for our graduate community.</p>
            <div class="image-container">
                <img src="images/alumni front.jpg" alt="Alumni networking gathering" class="alumni-image" />
            </div>
        </div>
    </section>

    <section id="upcoming-events" class="section">
        <h2>Upcoming Alumni Events &amp; Summits</h2>
        <?php if ($events && $events->num_rows > 0): ?>
            <?php while ($ev = $events->fetch_assoc()): ?>
                <div class="event">
                    <h3><?php echo htmlspecialchars($ev['title']); ?></h3>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($ev['event_date']); ?> | <strong>Location:</strong> <?php echo htmlspecialchars($ev['location']); ?></p>
                    <p><?php echo htmlspecialchars($ev['description']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No upcoming events scheduled at this time. Check back soon!</p>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
            <div class="post-box">
                <h3>+ Host or Announce an Alumni Event</h3>
                <form method="POST" action="">
                    <input type="text" name="event_title" placeholder="Event Title (e.g., Annual Tech Alumni Meetup)" required>
                    <input type="text" name="event_date" placeholder="Date (e.g., March 15, 2027)" required>
                    <input type="text" name="event_location" placeholder="Venue or Virtual Link" required>
                    <textarea name="event_description" rows="3" placeholder="Provide agenda highlights, speakers, and RSVP instructions..." required></textarea>
                    <button type="submit" name="add_event">Publish Event</button>
                </form>
            </div>
        <?php endif; ?>
    </section>

    <section id="job-opportunities" class="section">
        <h2>Exclusive Alumni Career Opportunities</h2>
        <ul class="job-listings">
            <?php if ($jobs && $jobs->num_rows > 0): ?>
                <?php while ($job = $jobs->fetch_assoc()): ?>
                    <li>
                        <h3><?php echo htmlspecialchars($job['title']); ?> — <?php echo htmlspecialchars($job['company']); ?></h3>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                        <p><?php echo htmlspecialchars($job['description']); ?></p>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li><p>No active job postings right now. Share an opening from your organization below!</p></li>
            <?php endif; ?>
        </ul>

        <?php if ($isLoggedIn): ?>
            <div class="post-box">
                <h3>+ Share a Career Opportunity</h3>
                <form method="POST" action="">
                    <input type="text" name="job_title" placeholder="Position Title (e.g., Full-Stack Software Engineer)" required>
                    <input type="text" name="company" placeholder="Organization Name" required>
                    <input type="text" name="location" placeholder="Location (City, Country or Remote)" required>
                    <textarea name="description" rows="3" placeholder="Outline key qualifications, role scope, and application contact..." required></textarea>
                    <button type="submit" name="add_job">Post Career Opportunity</button>
                </form>
            </div>
        <?php endif; ?>
    </section>

    <footer>
        <p>&copy; 2026 Official Alumni Network. All Rights Reserved.</p>
    </footer>
</body>
</html>