<?php  
require_once 'core/models.php'; 
require_once 'core/handleForms.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FindHire</title>
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
            <h1>Welcome to FindHire</h1>
			<p>Your platform for seamless job application and management.</p>
            <p>Proceed to Login:</p>
        </div>

        <div class="login-options">
            <form action="login_applicant.php" method="GET">
                <button type="submit" class="login-button applicant">Login as Applicant</button>
            </form>
            <form action="login_hr.php" method="GET">
                <button type="submit" class="login-button hr">Login as HR</button>
            </form>
        </div>
    </div>
</body>
</html>
