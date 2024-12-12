<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'HR') {
    header("Location: login.php");
    exit();
}

include_once 'dbConfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = $_POST['application_id'];
    $action = $_POST['action'];

    $status = ($action === 'accept') ? 'accepted' : 'rejected';

    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE application_id = ?");
    $stmt->execute([$status, $applicationId]);
    $stmtMessage = $pdo->prepare("INSERT INTO messages (sender, recipient, message, recipient_role) 
                                  VALUES ('HR', (SELECT applicant_email FROM applications WHERE application_id = ?), ?, 'Applicant')");
    $message = ($status === 'accepted') ? "Congratulations! Your application has been accepted." : "Unfortunately, your application has been rejected.";
    $stmtMessage->execute([$applicationId, $message]);

    header("Location: hr_dashboard.php");
    exit();
}
?>
