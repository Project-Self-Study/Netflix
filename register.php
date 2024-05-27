<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <?php include("header.php") ?>
    <div class="register-container">
        <div class="gif-container">
            <img src="https://th.bing.com/th/id/R.97d7e8497b1b12737d2f5fad0097afe8?rik=G1oaaSMBrnm7bQ&riu=http%3a%2f%2fwww.cartoonbrew.com%2fwp-content%2fuploads%2f2014%2f03%2fmickeymouse-secondseason-a-580x333.jpg&ehk=XrOhsycnc8tYvTPaIpzaBGEtLUP9RAr8%2bzqgYm8akD8%3d&risl=&pid=ImgRaw&r=0" alt="Mickey Mouse GIF">
            <p class="gif-text">Join us! It's almost as good as Netflix!</p>
        </div>
        <?php
        require_once 'Database.php';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                
                $username = htmlspecialchars($_POST['username']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $location = htmlspecialchars($_POST['location']);
                $age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
                $payment_method = $_POST['payment_method'] ?? 'None';
                $amount = $_POST['amount'] ?? 0.00;
                $subscribed = 0;
                $creation_date = date("Y-m-d");

                $db = new Database();
                $connection = $db->getConnection(); // Correctly access the connection

                $stmt = $connection->prepare("INSERT INTO Users (username, password, email, location, age, payment_method, amount, subscribed, creation_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    throw new Exception("Unable to prepare statement: " . $connection->error);
                }

                // Bind and execute, handle errors
                $stmt->bind_param('ssssissds', $username, $password, $email, $location, $age, $payment_method, $amount, $subscribed, $creation_date);

                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                echo "New record created successfully";
                $stmt->close();
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            } finally {
                if (isset($db)) {
                    $db->close();
                }
            }
        }
        ?>

        <form method="post" action="" class="register-form">
            <div class="flex-input">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="flex-input">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="flex-input">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="flex-input">
                <label for="location">Location:</label>
                <input type="text" name="location" id="location" required>
            </div>
            <div class="flex-input">
                <label for="age">Age:</label>
                <input type="number" name="age" id="age" required>
            </div>
            <div class="flex-input">
                <label for="payment_method">Payment Method:</label>
                <input type="text" name="payment_method" id="payment_method">
            </div>
            <div class="flex-input">
                <label for="amount">Amount Paid:</label>
                <input type="number" step="0.01" name="amount" id="amount">
            </div>
            <div class="flex-input">
                <button type="submit">Register</button>
            </div>
        </form>
    </div>

    <script>
        function submitForm() {
            const formData = {
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                location: document.getElementById('location').value,
                age: parseInt(document.getElementById('age').value, 10),
                payment_method: document.getElementById('payment_method').value,
                amount: parseFloat(document.getElementById('amount').value),
                subscribed: document.getElementById('subscribed').checked ? 1 : 0
            };

            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => console.log(data))
            .catch(error => console.error('Error:', error));
        }
    </script>

    <footer>
        <?php include("footer.php") ?>
    </footer>
</body>
</html>