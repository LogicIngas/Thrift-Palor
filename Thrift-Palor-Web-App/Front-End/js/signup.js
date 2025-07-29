document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const userData = {
        firstName: document.getElementById('firstName').value.trim(),
        lastName: document.getElementById('lastName').value.trim(),
        username: document.getElementById('username').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone: document.getElementById('phone').value.replace(/\D/g, ''),
        password: document.getElementById('password').value
    };
    
    if (userData.password !== document.getElementById('confirmPassword').value) {
        alert('Passwords do not match!');
        return;
    }
    
    if (userData.password.length < 8) {
        alert('Password must be at least 8 characters long');
        return;
    }
    
    try {
        const response = await fetch('/signup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Signup failed');
        }
        
        alert(data.message);
        window.location.href = '/Login.html';
        
    } catch (error) {
        console.error('Signup error:', error);
        alert(error.message || 'An error occurred during signup');
    }
});