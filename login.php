<?php
    session_start();
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoop Login</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="../images/hoop.png" alt="Hoop Logo">
        </div>
        <div class="login-form-container">
            <form id="login-form">
                <h2>Sign-In</h2>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Sign-In</button>
                <p id="login-error"></p>
            </form>
            <div class="new-user">
                <p>New to Hoop?</p>
                <a href="register.php">Create your Hoop account</a>
            </div>
        </div>
    </div>
    <script src="login.js"></script>
</body>
</html>
