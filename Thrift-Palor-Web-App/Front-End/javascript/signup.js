document.addEventListener('DOMContentLoaded', function () {
    const signupForm = document.getElementById('signupForm');
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const phoneInput = document.getElementById('phone');
    const phoneFeedback = document.getElementById('phoneFeedback');
    const usernameFeedback = document.getElementById('usernameFeedback');
    const emailFeedback = document.getElementById('emailFeedback');
    const messageDiv = document.getElementById('messageDiv');

    // Toggle password visibility
    togglePasswordBtn.addEventListener('click', function () {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            togglePasswordBtn.textContent = 'Hide';
        } else {
            passwordInput.type = 'password';
            togglePasswordBtn.textContent = 'Show';
        }
    });

    // Phone number validation
    phoneInput.addEventListener('input', function () {
        const pattern = /^(\+27|0)[6-8][0-9]{8}$/;
        if (phoneInput.value && !pattern.test(phoneInput.value)) {
            phoneFeedback.textContent = 'Invalid SA phone number format.';
            phoneFeedback.style.color = 'red';
        } else {
            phoneFeedback.textContent = '';
        }
    });

    // Username availability check (on blur)
    document.getElementById('username').addEventListener('blur', async function() {
        const username = this.value.trim();
        if (username.length < 3) return;
        
        try {
            const response = await fetch('http://localhost:8080/ThriftPalorWebApp/checkUsername?username=' + username);
            const result = await response.json();
            
            if (result.exists) {
                usernameFeedback.textContent = 'Username already taken';
                usernameFeedback.style.color = 'red';
            } else {
                usernameFeedback.textContent = 'Username available';
                usernameFeedback.style.color = 'green';
            }
        } catch (error) {
            console.error('Error checking username:', error);
        }
    });

    // Email availability check (on blur)
    document.getElementById('email').addEventListener('blur', async function() {
        const email = this.value.trim();
        if (!email.includes('@')) return;
        
        try {
            const response = await fetch('http://localhost:8080/ThriftPalorWebApp/checkEmail?email=' + email);
            const result = await response.json();
            
            if (result.exists) {
                emailFeedback.textContent = 'Email already registered';
                emailFeedback.style.color = 'red';
            } else {
                emailFeedback.textContent = 'Email available';
                emailFeedback.style.color = 'green';
            }
        } catch (error) {
            console.error('Error checking email:', error);
        }
    });

    // Form submission
    signupForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Clear previous messages
        messageDiv.innerHTML = '';
        
        const userData = {
            username: document.getElementById('username').value.trim(),
            firstName: document.getElementById('firstName').value.trim(),
            lastName: document.getElementById('lastName').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            email: document.getElementById('email').value.trim(),
            password: document.getElementById('password').value,
            role: "Buyer" // Default role
        };

        // Validate phone number
        const phonePattern = /^(\+27|0)[6-8][0-9]{8}$/;
        if (!phonePattern.test(userData.phone)) {
            phoneFeedback.textContent = 'Invalid SA phone number format.';
            phoneFeedback.style.color = 'red';
            phoneInput.focus();
            return;
        }

        try {
            const response = await fetch('http://localhost:8080/ThriftPalorWebApp/signup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            });

            const result = await response.json();
            
            if (response.ok) {
                messageDiv.innerHTML = '<span style="color:green;">Sign up successful! Redirecting...</span>';
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                messageDiv.innerHTML = `<span style="color:red;">${result.message || 'Sign up failed'}</span>`;
            }
        } catch (error) {
            console.error('Error:', error);
            messageDiv.innerHTML = '<span style="color:red;">Network error. Please try again.</span>';
        }
    });
});