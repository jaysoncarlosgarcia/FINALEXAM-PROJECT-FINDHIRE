<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="register-page-container">
    <div class="register-page">
        <h1>Register here!</h1>
        <?php  
        if (isset($_SESSION['message']) && isset($_SESSION['status'])) {
            if ($_SESSION['status'] == "200") {
                echo "<h1 style='color: green;'>{$_SESSION['message']}</h1>";
            } else {
                echo "<h1 style='color: red;'>{$_SESSION['message']}</h1>";    
            }
        }
        unset($_SESSION['message']);
        unset($_SESSION['status']);
        ?>
        <form action="core/handleForms.php" method="POST">
            <div class="form-group">
                <label for="email">Email: </label>
                <input type="text" name="email">
            </div>
            <div class="form-group">
                <label for="username">Username: </label>
                <input type="text" name="username">
            </div>
            <div class="form-group">
                <label for="firstname">First Name: </label>
                <input type="text" name="first_name">
            </div>
            <div class="form-group">
                <label for="lastname">Last Name: </label>
                <input type="text" name="last_name">
            </div>
            <div class="form-group">
                <label for="password">Password: </label>
                <input type="password" name="password">
            </div>
            <div class="form-group">
                <label for="password">Confirm Password: </label>
                <input type="password" name="confirm_password">
            </div>
            <input type="submit" name="insertNewUserBtn" value="Register">
        </form>
    </div>
</body>
</html>
