document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('register-form');

    registerForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const name = document.getElementById('name').value;
        const surname = document.getElementById('surname').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const registerData = {
            type: 'Register',
            name: name,
            surname: surname,
            email: email,
            password: password
        };

        fetch('http://localhost/COS221/PA5/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(registerData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = 'login.php';
            } else {
                document.getElementById('register-error').textContent = 'Registration failed: ' + data.message;
            }
        })
        .catch(error => {
            console.error('Error registering:', error);
            document.getElementById('register-error').textContent = 'Registration failed. Please try again.';
        });
    });
});
