<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'HR') {
    header("Location: login.php");
    exit();
}

include_once 'dbConfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageId = $_POST['message_id'];
    $response = $_POST['response'];
    $stmt = $pdo->prepare("SELECT sender, recipient FROM messages WHERE message_id = ?");
    $stmt->execute([$messageId]);
    $message = $stmt->fetch();
    $stmtResponse = $pdo->prepare("INSERT INTO messages (sender, recipient, message, recipient_role) 
                                   VALUES ('HR', ?, ?, 'Applicant')");
    $stmtResponse->execute([$message['sender'], $response]);
    header("Location: hr_dashboard.php");
    exit();
}
