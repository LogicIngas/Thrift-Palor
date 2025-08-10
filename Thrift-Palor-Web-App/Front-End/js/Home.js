document.addEventListener('DOMContentLoaded', function () {
    // Hamburger menu functionality
    const mobileMenu = document.getElementById('mobile-menu');
    const navbar = document.getElementById('navbar');
    const closeMenu = document.createElement('i');
    closeMenu.className = 'fas fa-times';
    closeMenu.style.display = 'none';

    mobileMenu.addEventListener('click', function() {
        navbar.classList.toggle('active');
        if (navbar.classList.contains('active')) {
            mobileMenu.innerHTML = '';
            mobileMenu.appendChild(closeMenu);
            closeMenu.style.display = 'block';
        } else {
            mobileMenu.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });

    // Close menu when clicking on a link (for mobile)
    document.querySelectorAll('#navbar li a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                navbar.classList.remove('active');
                mobileMenu.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    });

    // Close menu when clicking outside (for mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            !navbar.contains(e.target) && 
            e.target !== mobileMenu && 
            !mobileMenu.contains(e.target)) {
            navbar.classList.remove('active');
            mobileMenu.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });

    // Keep the existing cart functionality
    const cart = [];
    let cartVisible = false;
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalElement = document.getElementById('cart-total');
    const closeCartBtn = document.getElementById('close-cart');
    const proceedToPaymentBtn = document.getElementById('proceed-to-payment');
    const cartIcon = document.getElementById('cart-icon');

    // Add click event to all cart buttons
    document.querySelectorAll('.pro .cart, .pro a .cart, .pro i.fa-shopping-cart').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const productElement = this.closest('.pro');
            addToCart(productElement);
        });
    });

    // Toggle cart visibility
    cartIcon.addEventListener('click', function (e) {
        e.preventDefault();
        toggleCart();
    });

    closeCartBtn.addEventListener('click', toggleCart);

    // Proceed to payment
    proceedToPaymentBtn.addEventListener('click', function () {
        if (cart.length > 0) {
            localStorage.setItem('cart', JSON.stringify(cart));
            localStorage.setItem('cartTotal', cartTotalElement.textContent);
            window.location.href = 'Payment.html';
        } else {
            alert('Your cart is empty. Please add items before proceeding to payment.');
        }
    });

    function addToCart(productElement) {
        const productImg = productElement.querySelector('img').src;
        const productBrand = productElement.querySelector('.des span').textContent;
        const productName = productElement.querySelector('.des h5').textContent;
        const priceText = productElement.querySelector('.des h4').textContent;
        const productPrice = parseFloat(priceText.replace(/[^0-9.]/g, ''));

        const existingItem = cart.find(item => item.name === productName);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                img: productImg,
                brand: productBrand,
                name: productName,
                price: productPrice,
                quantity: 1
            });
        }

        updateCart();

        if (!cartVisible) {
            toggleCart();
        }

        updateCartCount();
        showNotification(`${productName} added to cart`);
    }

    function updateCart() {
        cartItemsContainer.innerHTML = '';
        let total = 0;
        cart.forEach((item, index) => {
            total += item.price * item.quantity;

            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.innerHTML = `
                <img src="${item.img}" alt="${item.name}">
                <div class="cart-item-info">
                    <span>${item.brand}</span>
                    <h5>${item.name}</h5>
                    <div>R${item.price.toFixed(2)} x ${item.quantity}</div>
                </div>
                <div class="cart-item-price">R${(item.price * item.quantity).toFixed(2)}</div>
                <button class="remove-item" data-index="${index}"><i class="fas fa-trash"></i></button>
            `;

            cartItemsContainer.appendChild(itemElement);
        });

        cartTotalElement.textContent = total.toFixed(2);

        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function () {
                const index = parseInt(this.getAttribute('data-index'));
                const removedItem = cart[index].name;
                cart.splice(index, 1);
                updateCart();
                updateCartCount();
                showNotification(`${removedItem} removed from cart`);
                if (cart.length === 0 && cartVisible) {
                    toggleCart();
                }
            });
        });
    }

    function toggleCart() {
        cartVisible = !cartVisible;
        if (cartVisible) {
            cartSidebar.style.right = '0';
        } else {
            cartSidebar.style.right = '-400px';
        }
    }

    function updateCartCount() {
        let cartCount = document.getElementById('cart-count');
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

        if (totalItems > 0) {
            if (!cartCount) {
                cartCount = document.createElement('span');
                cartCount.id = 'cart-count';
                cartIcon.appendChild(cartCount);
            }
            cartCount.textContent = totalItems;
        } else if (cartCount) {
            cartCount.remove();
        }
    }

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
});