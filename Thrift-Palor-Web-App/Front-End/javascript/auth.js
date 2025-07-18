// project-2/Thrift-Palor-Web-App/front-end/javascript/auth.js
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handleLogin();
        });
    }

    if (signupForm) {
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handleSignup();
        });
    }

    async function handleLogin() {
        const form = document.getElementById('loginForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (response.redirected) {
                window.location.href = response.url;
            } else {
                const result = await response.json();
                showToast(result.error || 'Login failed', 'error');
            }
        } catch (error) {
            showToast('Network error occurred', 'error');
        }
    }

    async function handleSignup() {
        const form = document.getElementById('signupForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (data.password !== data.confirmPassword) {
            showToast('Passwords do not match', 'error');
            return;
        }

        try {
            const response = await fetch('/auth/signup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (response.redirected) {
                window.location.href = response.url;
            } else {
                const result = await response.json();
                showToast(result.error || 'Registration failed', 'error');
            }
        } catch (error) {
            showToast('Network error occurred', 'error');
        }
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }, 10);
    }
});