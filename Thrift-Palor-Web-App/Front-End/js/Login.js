document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm'); // Get form by ID
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault(); // Prevent default form submission

            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();

            // Basic client-side validation
            if (!email || !password) {
                alert('Please enter both email and password.');
                return;
            }

            try {
                // Send login data to the backend API
                const response = await fetch('/api/login', { // This is your backend login endpoint
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json(); // Parse the JSON response from the backend

                if (response.ok) { // Check if the HTTP status code is in the 200s
                    // Login successful!
                    alert(data.message || 'Login successful!');
                    // Store user token/session info (e.g., in localStorage or a cookie)
                    if (data.token) {
                        localStorage.setItem('authToken', data.token);
                    }
                    // Redirect to a protected page (e.g., user dashboard)
                    window.location.href = '/dashboard.html'; // Or whatever your dashboard URL is
                } else {
                    // Login failed
                    alert(data.message || 'Login failed. Please check your credentials.');
                    console.error('Login error:', data.error || response.statusText);
                }
            } catch (error) {
                console.error('Network or server error:', error);
                alert('An error occurred. Please try again later.');
            }
        });
    }
});