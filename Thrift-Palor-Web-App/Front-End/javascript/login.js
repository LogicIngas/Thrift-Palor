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

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");

    loginForm.addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent actual form submission

        const username = document.getElementById("username").value;
        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;

        const role = document.querySelector('input[name="role"]:checked')?.value;

        if (!role) {
            alert("⚠️ Please select a role before logging in.");
            return;
        }

        // Displaying the collected info (for now)
        console.log("Username:", username);
        console.log("Email:", email);
        console.log("Password:", password);
        console.log("Role:", role);

        // Simulate success message
        alert(`✅ Welcome ${username}!\nRole: ${role.toUpperCase()}`);

        // Optionally redirect or clear form
        loginForm.reset();
    });
});
