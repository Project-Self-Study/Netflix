document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('login-form');

    loginForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const loginData = {
            type: 'Login',
            email: email,
            password: password
        };

        fetch('http://localhost/221Prac5/php/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(loginData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'index.php';
            } else {
                document.getElementById('login-error').textContent = 'Login failed. Please try again.';
            }
        })
        .catch(error => {
            console.error('Error logging in:', error);
            document.getElementById('login-error').textContent = 'Login failed. Please try again.';
        });
    });
});
