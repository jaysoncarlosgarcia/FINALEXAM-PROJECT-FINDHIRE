<?php
session_start();
include_once 'core/dbConfig.php';

// Fetch all available job posts
$sql = "SELECT * FROM job_posts ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$jobPosts = $stmt->fetchAll();

// Query to get jobs the applicant has applied to
$applicantId = $_SESSION['user_id']; // Assuming the user is logged in and their ID is stored in session
$sql = "SELECT job_posts.job_id, job_posts.title AS job_title, job_posts.description AS description, job_posts.created_at AS applied_at, applications.resume_path, applications.description AS application_description
        FROM applications
        JOIN job_posts ON applications.job_id = job_posts.job_id
        WHERE applications.applicant_id = :applicant_id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['applicant_id' => $applicantId]);
$appliedJobs = $stmt->fetchAll();

// Handle the job application form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_job'])) {
    $jobId = $_POST['job_id']; 
    $applicantId = $_SESSION['user_id']; 
    $description = $_POST['description']; 

    // Handle file upload for resume
    $file = $_FILES['resume'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];
    $fileSize = $file['size'];

    // Check if the file is a valid PDF
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($fileError === 0 && $fileExt === 'pdf') {
        if ($fileSize <= 5000000) { // Max size 5MB
            $fileNewName = uniqid('', true) . '.' . $fileExt;
            $fileDestination = 'uploads/resume/' . $fileNewName;
            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                // Insert application into the database
                $sql = "INSERT INTO applications (job_id, applicant_id, description, resume_path) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$jobId, $applicantId, $description, $fileDestination])) {
                    $_SESSION['success_message'] = "Application submitted successfully!";
                    header("Location: applicant_dashboard.php");
                    exit;
                } else {
                    $_SESSION['error_message'] = "Failed to submit your application.";
                }
            } else {
                $_SESSION['error_message'] = "There was an error uploading your resume.";
            }
        } else {
            $_SESSION['error_message'] = "Resume file is too large. Maximum size is 5MB.";
        }
    } else {
        $_SESSION['error_message'] = "Invalid file type. Only PDF files are allowed.";
    }
}

// Fetch messages for the current user (applicant)
$userId = $_SESSION['user_id'];
$sql = "
    SELECT 
        m.message_id, 
        m.message_content, 
        m.sender_id, 
        CONCAT(sender.first_name, ' ', sender.last_name) AS first_name, 
        CONCAT(receiver.first_name, ' ', receiver.last_name) AS last_name, 
        m.created_at 
    FROM messages m
    JOIN users sender ON m.sender_id = sender.user_id
    JOIN users receiver ON m.receiver_id = receiver.user_id
    WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
    ORDER BY m.created_at ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$messages = $stmt->fetchAll();

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiverId = $_POST['receiver_id'];
    $message = trim($_POST['message']);

    $sql = "INSERT INTO messages (sender_id, receiver_id, message_content) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$userId, $receiverId, $message])) {
        $_SESSION['success_message'] = "Message sent successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to send the message.";
    }

    header("Location: applicant_dashboard.php");
    exit();
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
    $messageId = $_POST['delete_message_id'];

    $sql = "DELETE FROM messages WHERE message_id = ? AND sender_id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$messageId, $userId])) {
        $_SESSION['success_message'] = "Message deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to delete the message.";
    }

    header("Location: applicant_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <a href="core/handleForms.php?logoutUserBtn=1" class="logout-link">Logout</a>
    <?php if (isset($_SESSION['success_message'])): ?>
        <p class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <p class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
    <?php endif; ?>

    <h1>Hello <?php echo $_SESSION['username']; ?>!</h1>

    <!-- Job Application Form -->
    <div class="apply-form">
    <h2>Apply to a Job</h2>
    <form action="applicant_dashboard.php" method="POST" enctype="multipart/form-data">
        <!-- Job Selection -->
        <select name="job_id" id="job_id" required>
            <option value="" disabled selected>Select Job</option>
            <?php foreach ($jobPosts as $jobPost): ?>
                <option value="<?php echo $jobPost['job_id']; ?>"><?php echo htmlspecialchars($jobPost['title']); ?></option>
            <?php endforeach; ?>
        </select><br>

        <!-- Job Description (Why are you the best candidate?) -->
        <textarea name="description" id="description" placeholder="Why are you the best candidate?" required></textarea><br>

        <!-- Resume Upload -->
        <input type="file" name="resume" id="resume" accept="application/pdf" required><br>

        <!-- Submit Button -->
        <button type="submit" name="apply_job">Apply</button>
    </form>
    </div>

    <hr>

    <!-- Applied Jobs List -->
    <div class="applied-jobs-container">
    <h3>Jobs You've Applied To</h3>
    <?php if (count($appliedJobs) > 0): ?>
        <table class="applied-jobs-table">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Description</th>
                    <th>Resume</th>
                    <th>Applied On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appliedJobs as $job): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                        <td><?php echo htmlspecialchars($job['description']); ?></td>
                        <td>
                            <?php if ($job['resume_path']): ?>
                                <a href="<?php echo htmlspecialchars($job['resume_path']); ?>" download>Download Resume</a>
                            <?php else: ?>
                                No Resume Uploaded
                            <?php endif; ?>
                        </td>
                        <td><?php echo date($job['applied_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You haven't applied to any jobs yet.</p>
    <?php endif; ?>
    </div>

    <hr>

    <!-- Messages -->
    <div class="form-container">
    <h2>Messages</h2>

    <!-- Send Message Form -->
    <form action="applicant_dashboard.php" method="POST">
        <div class="form-group">
            <label for="receiver_id">Send To:</label>
            <select name="receiver_id" id="receiver_id" required>
                <?php
                $hrUsersSql = "SELECT user_id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE role = 'HR'";
                $hrUsersStmt = $pdo->query($hrUsersSql);
                $hrUsers = $hrUsersStmt->fetchAll();
                foreach ($hrUsers as $hrUser): ?>
                    <option value="<?php echo $hrUser['user_id']; ?>"><?php echo htmlspecialchars($hrUser['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Message Display Section -->
        <div class="message-container">
            <?php foreach ($messages as $message): ?>
                <div class="message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received'; ?>">
                    <p class="message-content"><?php echo htmlspecialchars($message['message_content']); ?></p>
                    <small class="message-meta">
                        From: <?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?> | 
                        At: <?php echo htmlspecialchars($message['created_at']); ?>
                    </small>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Reply Form -->
        <div class="reply-form">
            <textarea name="message" placeholder="Write your message..." required></textarea><br>
            <button type="submit" name="send_message">Send</button>
        </div>
    </form>
    </div>

</body>
</html>
