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
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('signupForm');

    form.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        // 💡 You can add any validation or "mini action" here
        const username = document.getElementById('username').value;
        const firstName = document.getElementById('firstName').value;
        const lastName = document.getElementById('lastName').value;

        // 🧠 Example: Display a welcome alert
        alert(`Welcome, ${firstName} ${lastName}! Your account has been created.`);

        // 🛒 Redirect to products page
        window.location.href = 'products.html';
    });
});
