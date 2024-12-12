<?php  

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'HR') {
        header("Location: ../hr_dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == 'Applicant') {
        header("Location: ../applicant_dashboard.php");
        exit();
    }
}

require_once 'core/models.php'; 
require_once 'core/handleForms.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FindHire - HR</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <?php  
        if (isset($_SESSION['message']) && isset($_SESSION['status'])): ?>
            <div class="alert <?php echo $_SESSION['status'] == "200" ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
            </div>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['status']);
        endif; ?>

        <div class="login-header">
            <h1>Login Now!</h1>
        </div>

        <form action="core/handleForms.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" name="loginHRBtn" class="submit-btn">Login</button>
        </form>

        <p class="register-link">Don't have an account? You may register <a href="register.php">here</a></p>
    </div>
</body>
</html>
