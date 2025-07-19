document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");

    // Load registered users from localStorage (simulate database)
    function getRegisteredUsers() {
        const users = localStorage.getItem("thriftpalor_users");
        return users ? JSON.parse(users) : [];
    }

    // Save a new user to localStorage (for signup page to use)
    function saveUser(user) {
        const users = getRegisteredUsers();
        users.push(user);
        localStorage.setItem("thriftpalor_users", JSON.stringify(users));
    }

    loginForm.addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent form submission

        const username = document.getElementById("username").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();

        // Get selected role
        const selectedRole = document.querySelector('input[name="role"]:checked');

        // Validation
        if (!username || !email || !password) {
            alert("Please fill in all fields.");
            return;
        }

        if (!selectedRole) {
            alert("Please select a user role before logging in.");
            return;
        }

        const role = selectedRole.value;

        // Check credentials against registered users
        const users = getRegisteredUsers();
        const foundUser = users.find(
            user =>
                user.username === username &&
                user.email === email &&
                user.password === password
        );

        if (!foundUser) {
            alert("Invalid credentials or user does not exist. Please sign up first.");
            return;
        }

        // Save user info to localStorage (for session)
        localStorage.setItem("thriftpalor_user", JSON.stringify({ username, email }));

        // Redirect to homepage
        alert("Login successful!");
        window.location.href = "products.html";
    });

    // OPTIONAL: Expose saveUser for signup.js to use
    window.saveThriftPalorUser = saveUser;
});