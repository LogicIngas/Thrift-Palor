document.addEventListener("DOMContentLoaded", () => {
    const signupForm = document.getElementById("signupForm");

    if (signupForm) {
        signupForm.addEventListener("submit", function (e) {
            e.preventDefault();

            // Get form values
            const username = document.getElementById('username')?.value.trim();
            const firstName = document.getElementById('firstName')?.value.trim();
            const lastName = document.getElementById('lastName')?.value.trim();
            const email = document.getElementById("email")?.value.trim();
            const password = document.getElementById("password")?.value.trim();

            // Basic validation
            if (!firstName || !lastName || !email || !password || !username) {
                alert("⚠️ Please fill in all required fields.");
                return;
            }

            // Display confirmation
            alert(`Welcome, ${firstName} ${lastName}! Your account has been created.`);

            // Clear form fields
            signupForm.reset();

            // Redirect to login.html after 2 seconds
            setTimeout(() => {
                window.location.href = "login.html";
            }, 2000);
        });
    }
});
