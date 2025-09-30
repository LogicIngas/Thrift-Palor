// document.addEventListener('DOMContentLoaded', function() {
//     // Radio button functionality
//     const radioOptions = document.querySelectorAll('.radio-option');
//     const radioInputs = document.querySelectorAll('input[name="user_type"]');
    
//     // Handle radio input changes
//     radioInputs.forEach(radio => {
//         radio.addEventListener('change', function() {
//             // Remove selected class from all options
//             radioOptions.forEach(option => {
//                 option.classList.remove('selected');
//             });
//             // Add selected class to the current option
//             this.closest('.radio-option').classList.add('selected');
//         });
//     });
    
//     // Add click handler to labels for better UX
//     radioOptions.forEach(option => {
//         option.addEventListener('click', function() {
//             const radio = this.querySelector('input[type="radio"]');
//             radio.checked = true;
//             // Trigger the change event
//             radio.dispatchEvent(new Event('change'));
//         });
//     });

//     // Form validation (client-side validation for better UX)
//     document.getElementById('signupForm').addEventListener('submit', function (e) {
//         // Get form values for validation
//         const firstName = document.getElementById('firstName').value.trim();
//         const lastName = document.getElementById('lastName').value.trim();
//         const username = document.getElementById('username').value.trim();
//         const email = document.getElementById('email').value.trim();
//         const phone = document.getElementById('phone').value.trim();
//         const password = document.getElementById('password').value;
//         const confirmPassword = document.getElementById('confirmPassword').value;
//         const userType = document.querySelector('input[name="user_type"]:checked');
//         const termsChecked = document.getElementById('terms').checked;

//         // Clear previous error messages
//         clearMessages();

//         // Validation checks
//         if (!firstName || !lastName || !username || !email || !phone || !password || !confirmPassword) {
//             showError('Please fill in all required fields');
//             e.preventDefault();
//             return;
//         }

//         if (!userType) {
//             showError('Please select an account type');
//             e.preventDefault();
//             return;
//         }

//         if (password !== confirmPassword) {
//             showError('Passwords do not match!');
//             e.preventDefault();
//             return;
//         }

//         if (password.length < 8) {
//             showError('Password must be at least 8 characters');
//             e.preventDefault();
//             return;
//         }

//         if (!termsChecked) {
//             showError('You must accept the terms and conditions');
//             e.preventDefault();
//             return;
//         }

//         // If all validations pass, show loading state
//         const submitBtn = document.querySelector('.login_button');
//         submitBtn.disabled = true;
//         submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
//         // Form will submit normally to auth.php
//     });
// });

// // Helper functions
// function showError(message) {
//     clearMessages();
    
//     const errorElement = document.createElement('div');
//     errorElement.id = 'error-message';
//     errorElement.style.color = '#ff3333';
//     errorElement.style.margin = '10px 0';
//     errorElement.style.padding = '10px';
//     errorElement.style.borderRadius = '4px';
//     errorElement.style.backgroundColor = 'rgba(255, 51, 51, 0.1)';
//     errorElement.style.border = '1px solid #ff3333';
//     errorElement.style.textAlign = 'center';
//     errorElement.textContent = message;

//     const formTitle = document.querySelector('.login_title');
//     formTitle.insertAdjacentElement('afterend', errorElement);
// }

// function clearMessages() {
//     const oldError = document.getElementById('error-message');
//     if (oldError) oldError.remove();
    
//     const oldSuccess = document.getElementById('success-message');
//     if (oldSuccess) oldSuccess.remove();
// }