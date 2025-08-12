// document.getElementById('signupForm').addEventListener('submit', async function (e) {
//     e.preventDefault();

//     // Get form values
//     const userData = {
//         first_name: document.getElementById('firstName').value.trim(),
//         last_name: document.getElementById('lastName').value.trim(),
//         username: document.getElementById('username').value.trim(),
//         email: document.getElementById('email').value.trim(),
//         password: document.getElementById('password').value,
//         phone: document.getElementById('phone').value.replace(/\D/g, '') // Remove non-numeric chars
//     };
// z
//     // Validation checks
//     if (userData.password !== document.getElementById('confirmPassword').value) {
//         showError('Passwords do not match!');
//         return;
//     }

//     if (userData.password.length < 8) {
//         showError('Password must be at least 8 characters');
//         return;
//     }

//     if (!document.getElementById('terms').checked) {
//         showError('You must accept the terms and conditions');
//         return;
//     }

//     try {
//         // Show loading state
//         const submitBtn = document.querySelector('.login_button');
//         submitBtn.disabled = true;
//         submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

//         // Send data to backend
//         const response = await fetch('http://localhost/auth.php', {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//             },
//             body: JSON.stringify(userData)
//         });

//         const data = await response.json();

//         if (!data.success) {
//             throw new Error(data.message || 'Registration failed');
//         }

//         // Success handling
//         showSuccess(data.message);
//         setTimeout(() => {
//             window.location.href = 'Login.html';
//         }, 1500);

//     } catch (error) {
//         showError(error.message);
//         console.error('Signup error:', error);
        
//         // Reset button state
//         const submitBtn = document.querySelector('.login_button');
//         submitBtn.disabled = false;
//         submitBtn.textContent = 'Sign Up';
//     }
// });

// // Helper functions
// function showError(message) {
//     // Remove any existing messages
//     const oldError = document.getElementById('error-message');
//     if (oldError) oldError.remove();
    
//     const oldSuccess = document.getElementById('success-message');
//     if (oldSuccess) oldSuccess.remove();

//     // Create and show error message
//     const errorElement = document.createElement('div');
//     errorElement.id = 'error-message';
//     errorElement.style.color = '#ff3333';
//     errorElement.style.margin = '10px 0';
//     errorElement.style.padding = '10px';
//     errorElement.style.borderRadius = '4px';
//     errorElement.style.backgroundColor = '#ffeeee';
//     errorElement.textContent = message;

//     // Insert after the form title
//     const formTitle = document.querySelector('.login_title');
//     formTitle.insertAdjacentElement('afterend', errorElement);
// }

// function showSuccess(message) {
//     // Remove any existing messages
//     const oldError = document.getElementById('error-message');
//     if (oldError) oldError.remove();
    
//     const oldSuccess = document.getElementById('success-message');
//     if (oldSuccess) oldSuccess.remove();

//     // Create and show success message
//     const successElement = document.createElement('div');
//     successElement.id = 'success-message';
//     successElement.style.color = '#009900';
//     successElement.style.margin = '10px 0';
//     successElement.style.padding = '10px';
//     successElement.style.borderRadius = '4px';
//     successElement.style.backgroundColor = '#eeffee';
//     successElement.textContent = message;

//     // Insert after the form title
//     const formTitle = document.querySelector('.login_title');
//     formTitle.insertAdjacentElement('afterend', successElement);
// }