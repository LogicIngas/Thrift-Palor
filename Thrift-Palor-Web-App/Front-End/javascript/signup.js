document.addEventListener('DOMContentLoaded', function () {
    const signupForm = document.getElementById('signupForm');

    const passwordInput = document.getElementById('password');
    const toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.textContent = 'Show';
    toggleBtn.style.marginLeft = '8px';
    passwordInput.parentNode.appendChild(toggleBtn);

    toggleBtn.addEventListener('click', function () {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleBtn.textContent = 'Hide';
        } else {
            passwordInput.type = 'password';
            toggleBtn.textContent = 'Show';
        }
    });

    const phoneInput = document.getElementById('phone');
    const phoneFeedback = document.createElement('small');
    phoneFeedback.style.color = 'red';
    phoneInput.parentNode.appendChild(phoneFeedback);

    phoneInput.addEventListener('input', function () {
        const pattern = /^(\+27|0)[6-8][0-9]{8}$/;
        if (phoneInput.value && !pattern.test(phoneInput.value)) {
            phoneFeedback.textContent = 'Invalid SA phone number format.';
        } else {
            phoneFeedback.textContent = '';
        }
    });

    const messageDiv = document.createElement('div');
    signupForm.parentNode.insertBefore(messageDiv, signupForm.nextSibling);

    function getRegisteredUsers() {
        const users = localStorage.getItem("thriftpalor_users");
        return users ? JSON.parse(users) : [];
    }

    function saveUser(user) {
        const users = getRegisteredUsers();
        users.push(user);
        localStorage.setItem("thriftpalor_users", JSON.stringify(users));
    }

    signupForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const userData = {
            username: document.getElementById('username').value.trim(),
            firstName: document.getElementById('firstName').value.trim(),
            lastName: document.getElementById('lastName').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            email: document.getElementById('email').value.trim(),
            password: document.getElementById('password').value
        };

        const phonePattern = /^(\+27|0)[6-8][0-9]{8}$/;
        if (!phonePattern.test(userData.phone)) {
            phoneFeedback.textContent = 'Invalid SA phone number format.';
            phoneInput.focus();
            return;
        } else {
            phoneFeedback.textContent = '';
        }

        const users = getRegisteredUsers();
        const usernameExists = users.some(u => u.username === userData.username);
        const emailExists = users.some(u => u.email === userData.email);

        if (usernameExists) {
            messageDiv.innerHTML = '<span style="color:red;">Username already taken.</span>';
            return;
        }
        if (emailExists) {
            messageDiv.innerHTML = '<span style="color:red;">Email already registered.</span>';
            return;
        }

        saveUser(userData);

        messageDiv.innerHTML = '<span style="color:green;">Sign up successful! Redirecting...</span>';
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 2000);
    });
});