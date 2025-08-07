document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const userData = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        username: document.getElementById('username').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        phone: document.getElementById('phone').value.replace(/\D/g, '')
    };

    if (userData.password !== document.getElementById('confirmPassword').value) {
        showError('Passwords do not match!');
        return;
    }
    
    if (userData.password.length < 8) {
        showError('Password must be at least 8 characters long');
        return;
    }

    try {
        const response = await fetch('http://localhost:8080/api/signup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Registration failed');
        }

        const data = await response.json();
        showSuccess(data.message);
        setTimeout(() => window.location.href = 'Login.html', 1500);
        
    } catch (error) {
        showError(error.message);
        console.error('Signup error:', error);
    }
});

function showError(message) {
    const errorElement = document.getElementById('error-message') || createMessageElement('error-message');
    errorElement.textContent = message;
    errorElement.style.display = 'block';
}

function showSuccess(message) {
    const successElement = document.getElementById('success-message') || createMessageElement('success-message');
    successElement.textContent = message;
    successElement.style.display = 'block';
}

function createMessageElement(id) {
    const element = document.createElement('div');
    element.id = id;
    element.style.padding = '10px';
    element.style.margin = '10px 0';
    element.style.borderRadius = '4px';
    if (id === 'error-message') {
        element.style.backgroundColor = '#ffebee';
        element.style.color = '#c62828';
    } else {
        element.style.backgroundColor = '#e8f5e9';
        element.style.color = '#2e7d32';
    }
    document.querySelector('.login_form').prepend(element);
    return element;
}