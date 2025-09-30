// Enhanced interactive effects
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    const mobileMenu = document.getElementById('mobile-menu');
    const navbar = document.getElementById('navbar');
    
    if (mobileMenu && navbar) {
        mobileMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            navbar.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navbar.contains(e.target) && !mobileMenu.contains(e.target)) {
                navbar.classList.remove('active');
            }
        });
    }
    
    // Add hover effects to products
    const products = document.querySelectorAll('.pro');
    products.forEach(product => {
        product.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        product.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add loading animation to images
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        if (img.complete) {
            img.style.opacity = '1';
            img.style.transform = 'scale(1)';
        } else {
            img.addEventListener('load', function() {
                this.style.opacity = '1';
                this.style.transform = 'scale(1)';
            });
            img.style.opacity = '0';
            img.style.transform = 'scale(0.9)';
            img.style.transition = 'all 0.5s ease';
        }
    });
    
    // Enhanced form interactions
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (this.value === '') {
                this.parentElement.classList.remove('focused');
            }
        });
    });
});

// Modal functions
function openReturnModal(orderId) {
    console.log('Opening return modal for order:', orderId);
    document.getElementById('returnOrderId').value = orderId;
    document.getElementById('returnModal').style.display = 'block';
}

function closeReturnModal() {
    console.log('Closing return modal');
    document.getElementById('returnModal').style.display = 'none';
    document.getElementById('returnOrderId').value = '';
    document.getElementById('reason').value = '';
    document.getElementById('additional_notes').value = '';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('returnModal');
    if (event.target == modal) {
        closeReturnModal();
    }
}