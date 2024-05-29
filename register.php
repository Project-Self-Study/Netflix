<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoop Register</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="register-container">
        <div class="register-logo">
            <img src="../images/hoop.png" alt="Hoop Logo">
        </div>
        <div class="register-form-container">
            <form id="register-form">
                <h2>Create Account</h2>
                <label for="name">First Name:</label>
                <input type="text" id="name" name="name" required>
                <label for="surname">Last Name:</label>
                <input type="text" id="surname" name="surname" required>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Create Account</button>
                <p id="register-error"></p>
            </form>
            <div class="existing-user">
                <p>Already have an account?</p>
                <a href="login.php">Sign-In</a>
            </div>
        </div>
    </div>
    <script src="register.js"></script>
</body>
</html>
