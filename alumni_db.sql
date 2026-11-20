CREATE DATABASE IF NOT EXISTS alumni_db;
USE alumni_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    college VARCHAR(150) DEFAULT 'Sathyabama University',
    job_title VARCHAR(100) DEFAULT 'Alumni Member',
    bio TEXT,
    profile_pic VARCHAR(255) DEFAULT 'images/john.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    event_date VARCHAR(50) NOT NULL,
    location VARCHAR(100) NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    company VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    description TEXT NOT NULL
);

INSERT INTO events (title, event_date, location, description) VALUES
('Annual Alumni Homecoming & Networking Summit 2026', 'December 15, 2026', 'Chennai, TN', 'Join fellow graduates, faculty, and industry leaders for an evening of keynote panels, career networking, and campus updates.'),
('Global Virtual Career Fair & Mentorship Mixer', 'January 20, 2027', 'Online / Virtual', 'Connect directly with alumni recruiters and hiring managers across technology, finance, and design sectors.');

INSERT INTO jobs (title, company, location, description) VALUES
('Full-Stack Software Engineer', 'TNPL Tech', 'Marthandam, TN', 'Seeking an experienced developer proficient in modern web architectures to build scalable enterprise applications.'),
('Digital Marketing Manager', 'Global Brands Co.', 'Singapore (Remote Eligible)', 'Lead multi-channel brand strategy, performance campaigns, and global digital growth initiatives.'),
('Business & Data Analyst', 'Insight Analytics', 'Bangalore, KA', 'Transform complex datasets into actionable business intelligence and executive reporting dashboards.');