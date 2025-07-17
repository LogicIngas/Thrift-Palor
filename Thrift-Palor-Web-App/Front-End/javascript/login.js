document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");

    loginForm.addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent form submission

        const username = document.getElementById("username").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();

        // Get selected role
        const selectedRole = document.querySelector('input[name="role"]:checked');

        // Validation
        if (!username || !email || !password) {
            alert("⚠️ Please fill in all fields.");
            return;
        }

        if (!selectedRole) {
            alert("⚠️ Please select a user role before logging in.");
            return;
        }

        const role = selectedRole.value;

        // Optional: Save user info to localStorage
        localStorage.setItem("thriftpalor_user", JSON.stringify({ username, email, role }));

        // Redirect to homepage
        alert(`✅ Login successful as ${role.toUpperCase()}!`);
        window.location.href = "index.html";
    });
});
