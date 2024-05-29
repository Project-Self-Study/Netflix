<?php
    session_start(); // Track the user's session

?>

<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css"/> <!-- Link your CSS file -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <title>Document</title>
</head>

<body>
    <div id="container">
        <div id="logo">
            <img id="logo-pic" src = "Hoop-logo.png" alt = "Hoop-logo">
            <?php
                if (isset($_SESSION['id'])) {
                    echo '<span><p class="welcome-meessage" >Welcome, ' . htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '<p><a href="login.php">Logout</a></p>';
                } else {
                    echo '<li><a href="login.php">Login</a></li>';
                    echo '<li><a href="register.php">Register</a></li>';
                }
            ?>
        </div>
    </div>
</body>

</html>