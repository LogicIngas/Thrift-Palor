document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const credentials = {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
    };

    try {
        const response = await fetch('http://localhost:8080/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(credentials)
        });

        const result = await response.text();
        alert(result);
        
        if (response.ok) {
            localStorage.setItem('thriftpalor_user', credentials.email);
            window.location.href = 'index.html';
        }
    } catch (error) {
        console.error('Login error:', error);
    }
});