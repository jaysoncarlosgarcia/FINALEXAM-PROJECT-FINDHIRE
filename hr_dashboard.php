<?php
session_start();
include_once 'core/dbConfig.php';

// Handle the job post form submission (creating a new job post)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['job_title'], $_POST['job_description'])) {
        $jobTitle = $_POST['job_title']; // Get the job title
        $jobDescription = $_POST['job_description']; // Get the job description

        if (isset($_POST['job_id']) && !empty($_POST['job_id'])) {
            // Update the existing job post
            $jobId = $_POST['job_id'];
            $sql = "UPDATE job_posts SET title = ?, description = ? WHERE job_id = ?";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$jobTitle, $jobDescription, $jobId])) {
                $_SESSION['success_message'] = 'Job post updated successfully.';
            } else {
                $_SESSION['error_message'] = 'Failed to update job post.';
            }
        } else {
            // Insert a new job post
            $sql = "INSERT INTO job_posts (title, description, created_at) VALUES (?, ?, NOW())";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$jobTitle, $jobDescription])) {
                $_SESSION['success_message'] = 'Job post created successfully.';
            } else {
                $_SESSION['error_message'] = 'Failed to create job post.';
            }
        }
        header("Location: hr_dashboard.php"); // Redirect to avoid form resubmission
        exit();
    }
}

// Edit Job Post Form
if (isset($_GET['edit_job_id'])) {
    $editJobId = $_GET['edit_job_id'];
    $sql = "SELECT * FROM job_posts WHERE job_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$editJobId]);
    $jobPost = $stmt->fetch();
}

// Handle job post Deletion
if (isset($_GET['delete_job_id'])) {
    $deleteJobId = $_GET['delete_job_id'];

    // Prepare SQL statement to delete the job post
    $sql = "DELETE FROM job_posts WHERE job_id = ?";
    $stmt = $pdo->prepare($sql);

    // Execute the statement
    if ($stmt->execute([$deleteJobId])) {
        
    } else {
        
    }
}

// Handle application accept or reject
if (isset($_GET['accept_application_id'])) {
    $applicationId = $_GET['accept_application_id'];
    $sql = "UPDATE applications SET status = 'Accepted' WHERE application_id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$applicationId])) {
        $_SESSION['success_message'] = 'Application accepted successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to accept the application';
    }
    header("Location: hr_dashboard.php");
    exit();
}

if (isset($_GET['reject_application_id'])) {
    $applicationId = $_GET['reject_application_id'];
    $sql = "UPDATE applications SET status = 'Rejected' WHERE application_id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$applicationId])) {
        $_SESSION['success_message'] = 'Application rejected successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to reject the application';
    }
    header("Location: hr_dashboard.php");
    exit();
}

// Fetch all job posts to display on the dashboard
$sql = "SELECT * FROM job_posts ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$jobPosts = $stmt->fetchAll();

// Fetch all applications for each job post
$applicationsSql = "SELECT a.application_id, a.job_id, a.applicant_id, a.status, a.created_at, a.description, a.resume_path, u.first_name, u.last_name 
                    FROM applications a 
                    JOIN users u ON a.applicant_id = u.user_id
                    ORDER BY a.created_at DESC";
$applicationsStmt = $pdo->query($applicationsSql);
$applications = $applicationsStmt->fetchAll();

// Fetch messages related to each application
$messagesSql = "SELECT m.message_id, m.sender_id, m.receiver_id, m.message_content, m.created_at, u.first_name, u.last_name
                FROM messages m 
                JOIN users u ON m.sender_id = u.user_id
                ORDER BY m.created_at ASC";
$messagesStmt = $pdo->query($messagesSql);
$messages = $messagesStmt->fetchAll();

// Handle reply message to applicants
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $receiverId = $_POST['receiver_id'];
    $messageContent = $_POST['message_content']; 
    $senderId = $_SESSION['user_id']; 

    // Insert the reply message into the database
    $sql = "INSERT INTO messages (sender_id, receiver_id, message_content, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);

    // Execute the query with the provided values
    if ($stmt->execute([$senderId, $receiverId, $messageContent])) {
        $_SESSION['success_message'] = 'Message sent successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to send the message.';
    }
    header("Location: hr_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard</title>
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
    
    <!-- Job Posting Form -->
    <div class="job-posting-form">
        <h2>Post a New Job</h2>
        <form action="hr_dashboard.php" method="POST">
            <label for="job_title">Job Title:</label>
            <input type="text" name="job_title" id="job_title" required><br>

            <label for="job_description">Job Description:</label>
            <textarea name="job_description" id="job_description" required></textarea><br>

            <button type="submit" name="post_job">Post Job</button>
        </form>
    </div>

    <hr>

    <!-- Edit Job Post Form -->
    <?php if (isset($_GET['edit_job_id'])): ?>
    <?php 
    $editJobId = $_GET['edit_job_id'];
    $sql = "SELECT * FROM job_posts WHERE job_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$editJobId]);
    $jobPost = $stmt->fetch();
    ?>
    <div class="container">
        <div class="job-container">
            <h2 class="section-title">Edit Job Post</h2>
            <div class="job-card">
                <form action="hr_dashboard.php" method="POST">
                    <input type="hidden" name="job_id" value="<?php echo $jobPost['job_id']; ?>">

                    <div class="form-group">
                        <label for="job_title" class="form-label">Job Title</label>
                        <input type="text" name="job_title" id="job_title" 
                               value="<?php echo htmlspecialchars($jobPost['title']); ?>" 
                               class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="job_description" class="form-label">Job Description</label>
                        <textarea name="job_description" id="job_description" 
                                  class="form-textarea" required><?php echo htmlspecialchars($jobPost['description']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="edit_job" class="btn save-btn">Update Job Post</button>
                        <a href="hr_dashboard.php" class="btn cancel-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <hr>

    <!-- List of Job Posts -->
    <div class="jobs-container">
    <h2 class="section-title">Existing Job Posts</h2>
    <div class="job-list">
        <?php foreach ($jobPosts as $jobPost): ?>
            <div class="job-item">
                <div class="job-details">
                    <h3 class="job-title"><?php echo htmlspecialchars($jobPost['title']); ?></h3>
                    <p class="job-description"><?php echo htmlspecialchars($jobPost['description']); ?></p>
                    <p class="job-created">Created At: <?php echo htmlspecialchars($jobPost['created_at']); ?></p>
                </div>
                <div class="job-actions">
                    <a href="hr_dashboard.php?edit_job_id=<?php echo $jobPost['job_id']; ?>" class="btn edit-btn">Edit</a>
                    <a href="hr_dashboard.php?delete_job_id=<?php echo $jobPost['job_id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this job post?');">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    </div>

    <hr>

    <!-- View Applications -->
    <div class="applications-card">
    <div class="applications-card-header">
        <h2>Applications</h2>
    </div>
    <div class="applications-card-content">
        <table class="applications-styled-table">
            <thead>
                <tr>
                    <th>Application ID</th>
                    <th>Job Title</th>
                    <th>Applicant</th>
                    <th>Description</th>
                    <th>Uploaded Resume</th>
                    <th>Status</th>
                    <th>Applied On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($application['application_id']); ?></td>
                        <td>
                            <?php 
                                // Fetch the job title based on job_id
                                $jobSql = "SELECT title FROM job_posts WHERE job_id = ?";
                                $jobStmt = $pdo->prepare($jobSql);
                                $jobStmt->execute([$application['job_id']]);
                                $job = $jobStmt->fetch();
                                echo htmlspecialchars($job['title']);
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($application['first_name']) . " " . htmlspecialchars($application['last_name']); ?></td>
                        <td>
                            <?php
                                echo !empty($application['description']) ? htmlspecialchars($application['description']) : 'No description provided';
                            ?>
                        </td>
                        <td>
                            <?php
                                if (!empty($application['resume_path'])) {
                                    echo "<a href='download_resume.php?file=" . urlencode($application['resume_path']) . "' target='_blank'>Download Resume</a>";
                                } else {
                                    echo 'No resume uploaded';
                                }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($application['status']); ?></td>
                        <td><?php echo htmlspecialchars($application['created_at']); ?></td>
                        <td>
                            <a class="applications-action-btn applications-accept" href="hr_dashboard.php?accept_application_id=<?php echo $application['application_id']; ?>">Accept</a>
                            <br></br>
                            <a class="applications-action-btn applications-reject" href="hr_dashboard.php?reject_application_id=<?php echo $application['application_id']; ?>">Reject</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>

    <hr>

    <!-- Messages -->
    <div class="messages-card">
    <div class="messages-card-header">
        <h2>Messages</h2>
    </div>
    <div class="messages-card-content">
        <div class="messages-container">
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
        <div class="reply-form">
            <form action="hr_dashboard.php" method="POST">
                <input type="hidden" name="receiver_id" value="<?php echo htmlspecialchars($message['sender_id']); ?>">
                <textarea name="message_content" placeholder="Write your message..." required></textarea>
                <button type="submit" name="reply_message">Send Reply</button>
            </form>
        </div>
    </div>
    </div>

</body>
</html>
