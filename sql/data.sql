-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    username VARCHAR(255),
    role VARCHAR(255) DEFAULT 'Applicant',
	  first_name VARCHAR(255),
	  last_name VARCHAR(255),
	  password TEXT,
	  date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Job Posts table
CREATE TABLE job_posts (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Applications table
CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    applicant_id INT NOT NULL,
    description TEXT,
    resume_path VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Messages table
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- HR INFO
INSERT INTO users (email, username, role, first_name, last_name, password) VALUES
('hrdepartment@gmail.com', 'admin', 'HR', 'HR', 'Admin', '$2y$10$hy0G3NU1feoMCnpAdTEn4.uo213Es8gntHRg2lrnReCTh/g6bp9sC');
