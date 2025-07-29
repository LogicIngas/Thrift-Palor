document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Get form values
    const userData = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        username: document.getElementById('username').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        password: document.getElementById('password').value,
        confirmPassword: document.getElementById('confirmPassword').value
    };
    
    // Validation
    if (userData.password !== userData.confirmPassword) {
        alert('Passwords do not match!');
        return;
    }
    
    if (userData.password.length < 8) {
        alert('Password must be at least 8 characters long');
        return;
    }
    
    // Remove confirmPassword before sending to server
    delete userData.confirmPassword;
    
    try {
        // Send data to server
        const response = await fetch('/signup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Signup failed');
        }
        
        const data = await response.json();
        alert('Account created successfully!');
        window.location.href = '/Login.html';
        
    } catch (error) {
        console.error('Signup error:', error);
        alert(error.message || 'An error occurred during signup');
    }
});

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    let phone = e.target.value.replace(/\D/g, '');
    if (phone.length > 3 && phone.length <= 6) {
        phone = phone.replace(/(\d{3})(\d{1,3})/, '$1-$2');
    } else if (phone.length > 6) {
        phone = phone.replace(/(\d{3})(\d{3})(\d{1,4})/, '$1-$2-$3');
    }
    e.target.value = phone;
});