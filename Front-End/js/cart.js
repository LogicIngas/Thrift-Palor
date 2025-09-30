class CartSystem {
    constructor() {
        this.cart = this.loadCart();
        this.cartVisible = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateCartCount();
        this.updateCartDisplay();
    }

    loadCart() {
        try {
            const savedCart = localStorage.getItem('thrift_parlor_cart');
            return savedCart ? JSON.parse(savedCart) : [];
        } catch (e) {
            console.error('Error loading cart:', e);
            return [];
        }
    }

    saveCart() {
        try {
            localStorage.setItem('thrift_parlor_cart', JSON.stringify(this.cart));
        } catch (e) {
            console.error('Error saving cart:', e);
        }
    }

    bindEvents() {
        // Add to cart buttons
        document.addEventListener('click', (e) => {
            const cartBtn = e.target.closest('.cart') || 
                           e.target.closest('.fa-shopping-cart') ||
                           (e.target.classList.contains('cart') ? e.target : null);
            
            if (cartBtn) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Cart button clicked');
                const productElement = cartBtn.closest('.pro');
                if (productElement) {
                    this.addToCart(productElement);
                } else {
                    console.error('Could not find product element');
                }
            }
        });

        // Cart icon toggle
        const cartIcon = document.getElementById('cart-icon');
        if (cartIcon) {
            cartIcon.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleCart();
            });
        }

        // Close cart button
        const closeCartBtn = document.getElementById('close-cart');
        if (closeCartBtn) {
            closeCartBtn.addEventListener('click', () => this.toggleCart());
        }

        // Proceed to payment
        const proceedBtn = document.getElementById('proceed-to-payment');
        if (proceedBtn) {
            proceedBtn.addEventListener('click', () => this.proceedToPayment());
        }
    }

    addToCart(productElement) {
        console.log('Adding product to cart...');

        try {
            const productImg = productElement.querySelector('img').src;
            const productBrand = productElement.querySelector('.des span').textContent;
            const productName = productElement.querySelector('.des h5').textContent;
            const priceText = productElement.querySelector('.des h4').textContent;
            const productPrice = parseFloat(priceText.replace(/[^0-9.]/g, ''));
            const productId = this.generateProductId(productElement);

            console.log('Product details:', {
                productImg, productBrand, productName, priceText, productPrice, productId
            });

            const existingItemIndex = this.cart.findIndex(item => item.id === productId);
            
            if (existingItemIndex > -1) {
                this.cart[existingItemIndex].quantity += 1;
            } else {
                this.cart.push({
                    id: productId,
                    img: productImg,
                    brand: productBrand,
                    name: productName,
                    price: productPrice,
                    quantity: 1
                });
            }

            console.log('Updated cart:', this.cart);
            
            this.saveCart();
            this.updateCartDisplay();
            this.updateCartCount();
            this.showNotification(`${productName} added to cart`);
            
            if (!this.isCartVisible()) {
                this.toggleCart();
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('Error adding product to cart');
        }
    }

    generateProductId(productElement) {
        const brand = productElement.querySelector('.des span').textContent;
        const name = productElement.querySelector('.des h5').textContent;
        return `${brand}-${name}`.replace(/\s+/g, '-').toLowerCase();
    }

    updateCartDisplay() {
        const cartItemsContainer = document.getElementById('cart-items');
        const cartTotalElement = document.getElementById('cart-total');
        
        if (!cartItemsContainer || !cartTotalElement) {
            console.error('Cart elements not found');
            return;
        }

        cartItemsContainer.innerHTML = '';
        let total = 0;

        if (this.cart.length === 0) {
            cartItemsContainer.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
            cartTotalElement.textContent = '0.00';
            return;
        }

        this.cart.forEach((item, index) => {
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

        // Add event listeners to remove buttons
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.getAttribute('data-index'));
                const removedItem = this.cart[index].name;
                this.cart.splice(index, 1);
                this.saveCart();
                this.updateCartDisplay();
                this.updateCartCount();
                this.showNotification(`${removedItem} removed from cart`);
                
                if (this.cart.length === 0 && this.cartVisible) {
                    this.toggleCart();
                }
            });
        });
    }

    updateCartCount() {
        const cartIcon = document.getElementById('cart-icon');
        if (!cartIcon) return;

        let cartCount = document.getElementById('cart-count');
        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);

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

    toggleCart() {
        const cartSidebar = document.getElementById('cart-sidebar');
        if (!cartSidebar) {
            console.error('Cart sidebar not found');
            return;
        }

        this.cartVisible = !this.cartVisible;
        cartSidebar.style.right = this.cartVisible ? '0' : '-400px';
    }

    isCartVisible() {
        return this.cartVisible;
    }

    proceedToPayment() {
        console.log('Proceeding to payment...');
        console.log('Cart items:', this.cart);
        
        if (this.cart && this.cart.length > 0) {
            try {
                const cartData = JSON.stringify(this.cart);
                const totalAmount = this.calculateTotal();
                
                console.log('Saving to localStorage:', {
                    cartData: cartData,
                    totalAmount: totalAmount
                });
                
                localStorage.setItem('thrift_parlor_cart_checkout', cartData);
                localStorage.setItem('thrift_parlor_cart_total', totalAmount);
                
                // Verify the data was saved
                const savedCart = localStorage.getItem('thrift_parlor_cart_checkout');
                const savedTotal = localStorage.getItem('thrift_parlor_cart_total');
                
                console.log('Data saved successfully:', {
                    savedCart: savedCart,
                    savedTotal: savedTotal
                });
                
                window.location.href = 'Payment.html';
            } catch (error) {
                console.error('Error proceeding to payment:', error);
                alert('Error processing your cart. Please try again.');
            }
        } else {
            console.error('Cart is empty or undefined');
            alert('Your cart is empty. Please add items before proceeding to payment.');
        }
    }

    calculateTotal() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0).toFixed(2);
    }

    showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #088178;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 10000;
        `;
        
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '1';
        }, 10);

        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
}

// Initialize cart system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing cart system...');
    window.cartSystem = new CartSystem();
    
    // Debug: Check localStorage
    console.log('Current cart in localStorage:', localStorage.getItem('thrift_parlor_cart'));
    console.log('Checkout cart in localStorage:', localStorage.getItem('thrift_parlor_cart_checkout'));
    console.log('Cart total in localStorage:', localStorage.getItem('thrift_parlor_cart_total'));
});