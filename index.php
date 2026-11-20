<?php
require_once 'db.php';

// Require authentication: redirect unauthenticated visitors to Sign In
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$isLoggedIn = true;
$userId = $_SESSION['user_id'];
$currentUser = $_SESSION['name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Alumni Member';

// 1. Fetch logged-in user's current profile data
$userData = [
    'name' => $currentUser,
    'college' => 'Sathyabama Institute of Science and Technology',
    'job_title' => 'Alumni Member',
    'bio' => '',
    'profile_pic' => 'images/john.jpg'
];

if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT name, college, job_title, bio, profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $userData['name'] = $row['name'];
        $userData['college'] = $row['college'] ?: 'Sathyabama Institute of Science and Technology';
        $userData['job_title'] = $row['job_title'] ?: 'Alumni Member';
        $userData['bio'] = $row['bio'] ?? '';
        $userData['profile_pic'] = !empty($row['profile_pic']) ? $row['profile_pic'] : 'images/john.jpg';
    }
    $stmt->close();
}

// 2. Handle Profile Update + Photo Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile']) && $isLoggedIn) {
    $newName = htmlspecialchars(trim($_POST['profileName']));
    $newCollege = htmlspecialchars(trim($_POST['collegeName']));
    $newJob = htmlspecialchars(trim($_POST['jobTitle']));
    $newBio = htmlspecialchars(trim($_POST['bio']));
    $picPath = $userData['profile_pic'];

    if (!empty($_FILES['profilePic']['name']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $target = "uploads/user_" . $userId . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $target)) {
                $picPath = $target;
            }
        }
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?, college = ?, job_title = ?, bio = ?, profile_pic = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $newName, $newCollege, $newJob, $newBio, $picPath, $userId);

    if ($stmt->execute()) {
        $_SESSION['name'] = $newName;
        $currentUser = $newName;
        $userData['name'] = $newName;
        $userData['college'] = $newCollege;
        $userData['job_title'] = $newJob;
        $userData['bio'] = $newBio;
        $userData['profile_pic'] = $picPath;
        echo "<script>alert('Your executive profile has been updated successfully.');</script>";
    } else {
        echo "<script>alert('Unable to update profile. Please try again.');</script>";
    }
    $stmt->close();
}

// 3. Handle Contact Us submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_contact'])) {
    $cName = htmlspecialchars(trim($_POST['contactName']));
    $cEmail = htmlspecialchars(trim($_POST['contactEmail']));
    $cMsg = htmlspecialchars(trim($_POST['contactMessage']));

    $stmt = $conn->prepare("INSERT INTO messages (sender_name, sender_email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $cName, $cEmail, $cMsg);

    if ($stmt->execute()) {
        echo "<script>alert('Thank you for connecting. The Office of Alumni Relations has logged your inquiry.');</script>";
    } else {
        echo "<script>alert('Unable to submit your inquiry. Please try again.');</script>";
    }
    $stmt->close();
}

// 4. Fetch registered users for the directory
$dbAlumni = [];
$dirResult = $conn->query("SELECT name, email, college, job_title, bio, profile_pic FROM users ORDER BY id DESC");
if ($dirResult) {
    while ($row = $dirResult->fetch_assoc()) {
        $dbAlumni[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Alumni Network | University Graduate Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="explore.css">
    <style>
        /* Refined Executive Navigation & Typography */
        body {
            background-color: #f6f8fb;
            color: #1e293b;
        }
        header {
            background: linear-gradient(135deg, #102a43 0%, #1c4973 100%);
            padding: 18px 24px;
            box-shadow: 0 4px 14px rgba(16, 42, 67, 0.15);
        }
        header h1 {
            margin: 0 0 12px;
            font-size: 1.85rem;
            letter-spacing: 0.4px;
        }
        header nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        header nav a {
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.94rem;
            font-weight: 600;
            transition: background 0.2s ease, color 0.2s ease;
        }
        header nav a:hover, header nav a.nav-active {
            background: rgba(255, 255, 255, 0.16);
        }
        .nav-profile-pill {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.28);
        }
        .nav-logout-btn {
            background: rgba(239, 68, 68, 0.16);
            color: #fca5a5 !important;
            border: 1px solid rgba(239, 68, 68, 0.35);
        }
        .nav-logout-btn:hover {
            background: #dc2626 !important;
            color: #ffffff !important;
        }

        .section { display: none; padding: 30px 20px; text-align: center; }
        .active { display: block; }

        /* Executive Home Hero & Metrics */
        .hero-card {
            max-width: 880px;
            margin: 10px auto 30px;
            background: #ffffff;
            border-radius: 14px;
            padding: 38px 32px;
            box-shadow: 0 8px 28px rgba(16, 42, 67, 0.08);
            border: 1px solid #e2e8f0;
        }
        .hero-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 12px;
        }
        .hero-card h2 {
            font-size: 1.9rem;
            color: #102a43;
            margin: 0 0 8px;
        }
        .hero-card h3 {
            color: #334e68;
            font-weight: 500;
            font-size: 1.15rem;
            margin: 0 0 18px;
        }
        .hero-card p {
            max-width: 680px;
            margin: 0 auto 24px;
            color: #486581;
            line-height: 1.65;
        }
        .pillar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin: 24px 0 30px;
            text-align: left;
        }
        .pillar-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #1c4973;
            border-radius: 10px;
            padding: 16px;
        }
        .pillar-item h4 {
            margin: 0 0 6px;
            color: #102a43;
            font-size: 1rem;
        }
        .pillar-item p {
            margin: 0;
            font-size: 0.88rem;
            color: #627d98;
            line-height: 1.45;
        }

        /* Directory Cards & Search */
        .directory-header {
            max-width: 720px;
            margin: 0 auto 20px;
        }
        .search-bar-input {
            padding: 12px 16px;
            width: 100%;
            max-width: 420px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 0.96rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .search-bar-input:focus {
            outline: none;
            border-color: #1c4973;
            box-shadow: 0 0 0 3px rgba(28, 73, 115, 0.14);
        }
        #alumniList {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 22px;
            margin-top: 26px;
        }
        .alumni-card {
            cursor: pointer;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }
        .alumni-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(16, 42, 67, 0.12);
        }
        .alumni-card img {
            object-fit: cover;
        }
        .verified-tag {
            display: inline-block;
            margin-top: 6px;
            font-size: 0.72rem;
            font-weight: 700;
            color: #047857;
            background: #d1fae5;
            padding: 2px 8px;
            border-radius: 12px;
        }

        /* Executive Profile Card */
        .profile-card-box {
            max-width: 480px;
            margin: 15px auto 30px;
            background: #ffffff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(16, 42, 67, 0.08);
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        .profile-card-box label {
            display: block;
            margin-top: 12px;
            margin-bottom: 5px;
            color: #102a43;
            font-size: 0.92rem;
        }
        .profile-card-box input[type="text"],
        .profile-card-box textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.95rem;
        }
        .profile-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 14px;
            border: 3px solid #1c4973;
            box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(16, 42, 67, 0.65);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-box {
            background: #fff;
            padding: 28px;
            border-radius: 14px;
            max-width: 440px;
            width: 90%;
            text-align: left;
            position: relative;
            box-shadow: 0 16px 36px rgba(0,0,0,0.28);
        }
        .modal-close {
            position: absolute;
            top: 14px; right: 18px;
            font-size: 24px;
            cursor: pointer;
            font-weight: bold;
            color: #64748b;
        }
        .modal-box img {
            width: 100px; height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 14px;
            border: 3px solid #1c4973;
        }

        /* Upgraded Alumni Relations Hub Styles */
        .relations-wrapper {
            max-width: 960px;
            margin: 15px auto 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            text-align: left;
        }
        .relations-info-card, .relations-form-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 8px 24px rgba(16, 42, 67, 0.08);
            border: 1px solid #e2e8f0;
        }
        .relations-info-card h3, .relations-form-card h3 {
            color: #102a43;
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 1.25rem;
        }
        .contact-channel {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
            padding: 11px 14px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #1c4973;
        }
        .contact-channel span.icon {
            font-size: 1.25rem;
            line-height: 1.2;
        }
        .chapter-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        .chapter-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.84rem;
            font-weight: 600;
        }
        .relations-form-card label {
            display: block;
            margin-top: 12px;
            margin-bottom: 4px;
            color: #102a43;
            font-size: 0.92rem;
        }
        .relations-form-card input,
        .relations-form-card select,
        .relations-form-card textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.95rem;
        }
        .relations-form-card input:focus,
        .relations-form-card select:focus,
        .relations-form-card textarea:focus {
            outline: none;
            border-color: #1c4973;
            box-shadow: 0 0 0 3px rgba(28, 73, 115, 0.12);
        }
        .submit-inquiry-btn {
            width: 100%;
            margin-top: 18px;
            padding: 12px;
            background: #1c4973;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .submit-inquiry-btn:hover {
            background: #102a43;
        }
    </style>
</head>
<body>

    <header>
        <h1>Official Alumni Network</h1>
        <nav>
            <a href="#" id="nav-home" class="nav-active" onclick="showSection('home')">Home</a>
            <a href="#" id="nav-alumni" onclick="showSection('alumni')">Alumni Directory</a>
            <a href="alumni.php">Events &amp; Career Board</a>
            <a href="#" id="nav-contact" onclick="showSection('contact')">Contact Relations</a>
            <a href="admin.php">Admin Portal</a>
            <?php if ($isLoggedIn): ?>
                <a href="#" id="nav-profile" class="nav-profile-pill" onclick="showSection('profile')">My Profile (<?php echo htmlspecialchars($currentUser); ?>)</a>
                <a href="logout.php" class="nav-logout-btn">Logout</a>
            <?php else: ?>
                <a href="signup.php">Register</a>
                <a href="login.php">Sign In</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <!-- HOME SECTION -->
        <section id="home" class="section active">
            <div class="hero-card">
                <span class="hero-badge">University Graduate Community</span>
                <h2>Welcome<?php echo $isLoggedIn ? ', ' . htmlspecialchars($currentUser) : ' to the Official Alumni Network'; ?>!</h2>
                <h3>Building Lifelong Connections Across Generations of Graduates</h3>
                <p>Stay connected with fellow graduates, expand your executive network, and give back to our academic community. Whether you are seeking mentorship, industry collaborations, or updates on campus milestones, your alumni portal keeps you engaged worldwide.</p>

                <div class="pillar-grid">
                    <div class="pillar-item">
                        <h4>🌐 Global Directory</h4>
                        <p>Search and reconnect with classmates, researchers, and industry leaders across continents.</p>
                    </div>
                    <div class="pillar-item">
                        <h4>💼 Career &amp; Referrals</h4>
                        <p>Access curated job openings and hiring referrals shared directly by fellow alumni.</p>
                    </div>
                    <div class="pillar-item">
                        <h4>🎓 Reunions &amp; Summits</h4>
                        <p>Participate in annual homecoming summits, regional chapter meetups, and webinars.</p>
                    </div>
                </div>

                <button class="explore-btn" onclick="showSection('alumni')">Explore Alumni Directory</button>
            </div>
        </section>

        <!-- ALUMNI DIRECTORY SECTION -->
        <section id="alumni" class="section">
            <div class="directory-header">
                <h2 style="color: #102a43; margin-bottom: 6px;">Global Alumni Directory</h2>
                <p style="color: #486581; margin-bottom: 18px;">Connect with verified graduates by searching by name, degree program, role, or organization.</p>
                <input type="text" id="search" class="search-bar-input" placeholder="Search by name, role, degree, or organization..." onkeyup="searchAlumni()">
            </div>

            <div id="alumniList">
                <!-- Newly Registered Members from Database -->
                <?php foreach ($dbAlumni as $member): 
                    $memberPic = !empty($member['profile_pic']) ? $member['profile_pic'] : 'images/john.jpg';
                    $safeBio = str_replace(["\r", "\n"], ' ', addslashes(htmlspecialchars($member['bio'] ?? '')));
                ?>
                    <div class="alumni-card" onclick="openModal(
                        '<?php echo addslashes(htmlspecialchars($member['name'])); ?>',
                        '<?php echo addslashes(htmlspecialchars($member['job_title'] ?: 'Alumni Member')); ?>',
                        '<?php echo addslashes(htmlspecialchars($member['college'] ?: 'Sathyabama Institute of Science and Technology')); ?>',
                        'Verified Member',
                        '<?php echo addslashes(htmlspecialchars($member['email'])); ?>',
                        '<?php echo addslashes(htmlspecialchars($memberPic)); ?>',
                        '<?php echo $safeBio; ?>'
                    )">
                        <img src="<?php echo htmlspecialchars($memberPic); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                        <div class="alumni-info">
                            <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                            <p><?php echo htmlspecialchars($member['job_title'] ?: 'Alumni Member'); ?></p>
                            <span class="verified-tag">✓ Verified Alumni</span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Featured Alumni Profiles -->
                <div class="alumni-card" onclick="openModal('Raj', 'Software Engineer', 'B.E. Computer Science & Engineering', 'Class of 2021', 'raj@example.com', 'images/sanju.jpg', 'Specializing in scalable backend systems and cloud infrastructure.')">
                    <img src="images/sanju.jpg" alt="Raj">
                    <div class="alumni-info"><h3>Raj</h3><p>Software Engineer</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Gautham', 'Data Scientist', 'B.E. Data Science', 'Class of 2022', 'gautham@example.com', 'images/cook.jpg', 'Focused on predictive analytics, machine learning pipelines, and NLP.')">
                    <img src="images/cook.jpg" alt="Gautham">
                    <div class="alumni-info"><h3>Gautham</h3><p>Data Scientist</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Joe', 'Marketing Specialist', 'MBA Marketing & Strategy', 'Class of 2019', 'joe@example.com', 'images/boy4.jpg', 'Leading B2B brand growth and digital acquisition campaigns.')">
                    <img src="images/boy4.jpg" alt="Joe">
                    <div class="alumni-info"><h3>Joe</h3><p>Marketing Specialist</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Davis', 'Professor of Computer Science', 'Ph.D. Computer Science', 'Class of 2015', 'davis@example.com', 'images/hardik.jpg', 'Academic researcher and mentor in distributed computing and AI ethics.')">
                    <img src="images/hardik.jpg" alt="Davis">
                    <div class="alumni-info"><h3>Davis</h3><p>Professor</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Beni', 'Senior UX/UI Designer', 'B.Des. Visual Communication', 'Class of 2021', 'beni@example.com', 'images/rabada.jpg', 'Designing accessible, human-centered enterprise and mobile products.')">
                    <img src="images/rabada.jpg" alt="Beni">
                    <div class="alumni-info"><h3>Beni</h3><p>UX/UI Designer</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Alex', 'Full Stack Developer', 'B.E. Computer Science & Engineering', 'Class of 2020', 'alex@example.com', 'images/smith.jpg', 'Building end-to-end web platforms with React, Node.js, and relational databases.')">
                    <img src="images/smith.jpg" alt="Alex">
                    <div class="alumni-info"><h3>Alex</h3><p>Full Stack Developer</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Sam', 'Business & Data Analyst', 'B.Sc. Applied Statistics', 'Class of 2022', 'sam@example.com', 'images/boy2.jpg', 'Turning operational metrics into executive decision-making dashboards.')">
                    <img src="images/boy2.jpg" alt="Sam">
                    <div class="alumni-info"><h3>Sam</h3><p>Data Analyst</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Jeni', 'Lead Database Administrator', 'B.E. Information Technology', 'Class of 2018', 'jeni@example.com', 'images/girl1.jpg', 'Managing high-availability MySQL and PostgreSQL clusters.')">
                    <img src="images/girl1.jpg" alt="Jeni">
                    <div class="alumni-info"><h3>Jeni</h3><p>Database Administrator</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('James', 'Frontend Web Developer', 'Bachelor of Computer Applications', 'Class of 2023', 'james@example.com', 'images/boy1.jpg', 'Passionate about modern web standards, performance optimization, and UI engineering.')">
                    <img src="images/boy1.jpg" alt="James">
                    <div class="alumni-info"><h3>James</h3><p>Web Developer</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Madhu', 'Game Systems Developer', 'B.E. Computer Science & Engineering', 'Class of 2021', 'madhu@example.com', 'images/girl3.jpg', 'Developing interactive 3D gameplay mechanics and real-time physics engines.')">
                    <img src="images/girl3.jpg" alt="Madhu">
                    <div class="alumni-info"><h3>Madhu</h3><p>Game Developer</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Durai', 'Data Scientist at Amazon', 'B.E. Data Science', 'Class of 2020', 'durai21@example.com', 'images/booy3.jpg', 'Working on large-scale recommendation systems and customer demand forecasting.')">
                    <img src="images/booy3.jpg" alt="Durai">
                    <div class="alumni-info"><h3>Durai</h3><p>Data Scientist at Amazon</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Daniel', 'Software Developer at TCS', 'B.E. Computer Science & Engineering', 'Class of 2016', 'daniel22@example.com', 'images/stokes.jpg', 'Delivering enterprise cloud migration and core banking software solutions.')">
                    <img src="images/stokes.jpg" alt="Daniel">
                    <div class="alumni-info"><h3>Daniel</h3><p>Software Developer at TCS</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Riya', 'Senior Brand & Graphic Designer', 'Bachelor of Graphic Design', 'Class of 2011', 'riya02@example.com', 'images/girl2.jpg', 'Crafting visual identities and brand systems for global startups.')">
                    <img src="images/girl2.jpg" alt="Riya">
                    <div class="alumni-info"><h3>Riya</h3><p>Graphic Designer</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Merin', 'Dental Surgeon at Apollo Speciality Hospital', 'Bachelor of Dental Surgery', 'Class of 2010', 'merin10@example.com', 'images/girl4.jpg', 'Dedicated to restorative dentistry and community healthcare outreach.')">
                    <img src="images/girl4.jpg" alt="Merin">
                    <div class="alumni-info"><h3>Merin</h3><p>Dental Surgeon</p></div>
                </div>
                <div class="alumni-card" onclick="openModal('Matthew', 'Senior Electrical Engineer at Larsen & Toubro', 'B.E. Electrical & Electronics', 'Class of 2014', 'matthew31@example.com', 'images/boy5.jpg', 'Overseeing industrial power distribution and infrastructure projects.')">
                    <img src="images/boy5.jpg" alt="Matthew">
                    <div class="alumni-info"><h3>Matthew</h3><p>Electrical Engineer at L&amp;T</p></div>
                </div>
            </div>
        </section>

        <!-- MY PROFILE SECTION -->
        <section id="profile" class="section">
            <h2 style="color: #102a43; margin-bottom: 6px;">Executive Member Profile</h2>
            <p style="color: #486581; margin-bottom: 20px;">Keep your professional credentials and biography updated for the global alumni directory.</p>
            <?php if ($isLoggedIn): ?>
                <div class="profile-card-box">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <img src="<?php echo htmlspecialchars($userData['profile_pic']); ?>" alt="Profile Picture" class="profile-preview">

                        <label for="profilePic"><strong>Profile Photograph</strong></label>
                        <input type="file" id="profilePic" name="profilePic" accept="image/*" style="margin-bottom: 6px;">

                        <label for="profileName"><strong>Full Name</strong></label>
                        <input type="text" id="profileName" name="profileName" value="<?php echo htmlspecialchars($userData['name']); ?>" required>

                        <label for="collegeName"><strong>Institution / Department</strong></label>
                        <input type="text" id="collegeName" name="collegeName" value="<?php echo htmlspecialchars($userData['college']); ?>" required>

                        <label for="jobTitle"><strong>Current Role &amp; Organization</strong></label>
                        <input type="text" id="jobTitle" name="jobTitle" value="<?php echo htmlspecialchars($userData['job_title']); ?>" required>

                        <label for="bio"><strong>Executive Biography</strong></label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Summarize your current role, industry expertise, graduation cohort, and areas open for mentorship or collaboration."><?php echo htmlspecialchars($userData['bio']); ?></textarea>

                        <button type="submit" name="update_profile" class="submit-inquiry-btn">Save Profile Changes</button>
                    </form>
                </div>
            <?php else: ?>
                <p>Please <a href="login.php">sign in</a> to view and manage your alumni profile.</p>
            <?php endif; ?>
        </section>

        <!-- CONTACT SECTION -->
        <section id="contact" class="section">
            <h2 style="color: #102a43; margin-bottom: 8px;">Alumni Relations &amp; Engagement Hub</h2>
            <p style="max-width: 660px; margin: 0 auto 22px; color: #486581;">Whether you wish to organize a regional chapter meetup, mentor graduating cohorts, or partner on campus recruitment, our Alumni Relations team is ready to assist.</p>

            <div class="relations-wrapper">
                <!-- Left Column: Office Info & Regional Chapters -->
                <div class="relations-info-card">
                    <h3>Office of Alumni Relations</h3>
                    <p style="color: #486581; font-size: 0.94rem; margin-bottom: 18px;">Dedicated to fostering lifelong connections, career mobility, and institutional excellence across our global graduate community.</p>

                    <div class="contact-channel">
                        <span class="icon">📍</span>
                        <div>
                            <strong>Campus Secretariat</strong><br>
                            <small style="color: #486581;">Alumni House, Block A, Main University Campus</small>
                        </div>
                    </div>

                    <div class="contact-channel">
                        <span class="icon">✉️</span>
                        <div>
                            <strong>Official Correspondence</strong><br>
                            <small style="color: #486581;">alumni.relations@university.edu</small>
                        </div>
                    </div>

                    <div class="contact-channel">
                        <span class="icon">🤝</span>
                        <div>
                            <strong>Engagement Initiatives</strong><br>
                            <small style="color: #486581;">Executive Talks • Student Mentorship • Campus Hiring</small>
                        </div>
                    </div>

                    <h4 style="color: #102a43; margin: 20px 0 8px; font-size: 0.94rem;">Active Regional Chapters</h4>
                    <div class="chapter-badges">
                        <span class="chapter-badge">Chennai</span>
                        <span class="chapter-badge">Bangalore</span>
                        <span class="chapter-badge">Kanyakumari</span>
                        <span class="chapter-badge">Singapore</span>
                        <span class="chapter-badge">Middle East</span>
                    </div>
                </div>

                <!-- Right Column: Structured Inquiry Card -->
                <div class="relations-form-card">
                    <h3>Submit an Official Inquiry</h3>
                    <form method="POST" action="">
                        <label for="contactName"><strong>Full Name</strong></label>
                        <input type="text" id="contactName" name="contactName" value="<?php echo $isLoggedIn ? htmlspecialchars($currentUser) : ''; ?>" placeholder="Enter your full name" required>

                        <label for="contactEmail"><strong>Email Address</strong></label>
                        <input type="email" id="contactEmail" name="contactEmail" placeholder="Enter your email address" required>

                        <label for="inquiryTopic"><strong>Department / Topic</strong></label>
                        <select id="inquiryTopic" onchange="prependTopic(this.value)">
                            <option value="">General Alumni Inquiry</option>
                            <option value="[Chapter & Events] ">Regional Chapter &amp; Reunion Planning</option>
                            <option value="[Mentorship & Speaking] ">Student Mentorship / Guest Lecture</option>
                            <option value="[Campus Recruitment] ">Corporate Hiring &amp; Job Referral</option>
                            <option value="[Transcripts & Records] ">Alumni ID &amp; Academic Records</option>
                        </select>

                        <label for="contactMessage"><strong>Message Details</strong></label>
                        <textarea id="contactMessage" name="contactMessage" rows="4" placeholder="Provide details regarding your inquiry, event proposal, or recruitment partnership..." required></textarea>

                        <button type="submit" name="send_contact" class="submit-inquiry-btn">Submit to Alumni Relations</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- ALUMNI DETAILS POPUP MODAL -->
    <div id="alumniModal" class="modal-overlay" onclick="closeModal(event)">
        <div class="modal-box">
            <span class="modal-close" onclick="document.getElementById('alumniModal').style.display='none'">&times;</span>
            <img id="modalImg" src="" alt="Alumnus">
            <h2 id="modalName" style="text-align:center; color: #102a43; margin-bottom: 14px;"></h2>
            <p><strong>Current Role:</strong> <span id="modalRole"></span></p>
            <p><strong>Program / Institution:</strong> <span id="modalDegree"></span></p>
            <p><strong>Cohort / Status:</strong> <span id="modalYear"></span></p>
            <p><strong>Direct Email:</strong> <a id="modalEmail" href=""></a></p>
            <p id="modalBioRow" style="display:none; margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;"><strong>Executive Summary:</strong> <span id="modalBio"></span></p>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Official Alumni Network. All Rights Reserved.</p>
    </footer>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');

            document.querySelectorAll('header nav a').forEach(link => link.classList.remove('nav-active'));
            const activeNav = document.getElementById('nav-' + sectionId);
            if (activeNav) {
                activeNav.classList.add('nav-active');
            }
        }

        function searchAlumni() {
            const query = document.getElementById('search').value.toLowerCase();
            document.querySelectorAll('.alumni-card').forEach(card => {
                const text = card.innerText.toLowerCase();
                card.style.display = text.includes(query) ? 'block' : 'none';
            });
        }

        function openModal(name, role, degree, year, email, imgSrc, bio) {
            document.getElementById('modalName').innerText = name;
            document.getElementById('modalRole').innerText = role;
            document.getElementById('modalDegree').innerText = degree;
            document.getElementById('modalYear').innerText = year;
            const emailLink = document.getElementById('modalEmail');
            emailLink.innerText = email;
            emailLink.href = 'mailto:' + email;
            document.getElementById('modalImg').src = imgSrc;

            const bioRow = document.getElementById('modalBioRow');
            if (bio && bio.trim() !== '') {
                document.getElementById('modalBio').innerText = bio;
                bioRow.style.display = 'block';
            } else {
                bioRow.style.display = 'none';
            }

            document.getElementById('alumniModal').style.display = 'flex';
        }

        function closeModal(event) {
            if (event.target.id === 'alumniModal') {
                document.getElementById('alumniModal').style.display = 'none';
            }
        }

        function prependTopic(prefix) {
            const msgBox = document.getElementById('contactMessage');
            msgBox.value = msgBox.value.replace(/^\[.*?\]\s*/, '');
            if (prefix) {
                msgBox.value = prefix + msgBox.value;
            }
        }
    </script>
</body>
</html>