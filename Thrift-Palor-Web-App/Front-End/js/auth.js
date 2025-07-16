document.addEventListener("DOMContentLoaded", () => {
    const signupForm = document.getElementById("signupForm");

    signupForm.addEventListener("submit", function (e) {
        e.preventDefault(); // prevent actual form submission

        // You can collect data here if needed
        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;

        // Simulate successful registration
        alert("Account created successfully!");

        // Clear form fields
        signupForm.reset();

        // Redirect to login page after 2 seconds
        setTimeout(() => {
            window.location.href = "login.html";
        }, 2000);
    });
});
