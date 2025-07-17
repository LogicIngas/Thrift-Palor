// auth.js - Enhanced with modern practices
document.addEventListener("DOMContentLoaded", () => {
  const signupForm = document.getElementById("signupForm");
  const loginForm = document.getElementById("loginForm");

  // Enhanced Signup Form Handling
  if (signupForm) {
    signupForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      // Get form values with better validation
      const formData = {
        username: getValue('username'),
        firstName: getValue('firstName'),
        lastName: getValue('lastName'),
        phone: getValue('phone'),
        email: getValue("email"),
        password: getValue("password")
      };

      // Advanced validation
      if (!validateForm(formData)) return;

      try {
        // Show loading state
        toggleLoading(true);
        
        // Simulate API call
        await fakeAPICall('/api/signup', formData);
        
        // Show success message
        showToast(`Welcome, ${formData.firstName}! Account created successfully.`, 'success');
        
        // Redirect after delay
        setTimeout(() => {
          window.location.href = "login.html";
        }, 1500);
      } catch (error) {
        showToast(error.message, 'error');
      } finally {
        toggleLoading(false);
      }
    });
  }

  // Enhanced Login Form Handling
  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const formData = {
        username: getValue('username'),
        email: getValue('email'),
        password: getValue('password'),
        role: document.querySelector('input[name="role"]:checked')?.value
      };

      if (!formData.role) {
        showToast("Please select a user role", 'error');
        return;
      }

      try {
        toggleLoading(true);
        await fakeAPICall('/api/login', formData);
        
        // Save user data
        localStorage.setItem('thriftpalor_user', JSON.stringify({
          username: formData.username,
          email: formData.email,
          role: formData.role
        }));
        
        showToast(`Welcome back! Logged in as ${formData.role}`, 'success');
        setTimeout(() => {
          window.location.href = "home.html";
        }, 1000);
      } catch (error) {
        showToast(error.message, 'error');
      } finally {
        toggleLoading(false);
      }
    });
  }

  // Helper functions
  function getValue(id) {
    const element = document.getElementById(id);
    return element ? element.value.trim() : '';
  }

  function validateForm(data) {
    const { firstName, lastName, email, password, phone } = data;
    const errors = [];
    
    if (!firstName) errors.push('First name is required');
    if (!lastName) errors.push('Last name is required');
    if (!email || !isValidEmail(email)) errors.push('Valid email is required');
    if (!password || password.length < 8) errors.push('Password must be at least 8 characters');
    if (phone && !isValidPhone(phone)) errors.push('Valid SA phone number required');
    
    if (errors.length) {
      showToast(errors.join(', '), 'error');
      return false;
    }
    return true;
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function isValidPhone(phone) {
    return /^(\+27|0)[6-8][0-9]{8}$/.test(phone);
  }

  function toggleLoading(loading) {
    const buttons = document.querySelectorAll('button[type="submit"]');
    buttons.forEach(btn => {
      btn.disabled = loading;
      btn.innerHTML = loading 
        ? '<span class="spinner"></span> Processing...'
        : btn.dataset.originalText;
    });
  }

  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  function fakeAPICall(url, data) {
    return new Promise((resolve, reject) => {
      setTimeout(() => {
        if (Math.random() > 0.1) { // 90% success rate for demo
          resolve({ success: true, data });
        } else {
          reject(new Error('Server error. Please try again.'));
        }
      }, 1000);
    });
  }
});