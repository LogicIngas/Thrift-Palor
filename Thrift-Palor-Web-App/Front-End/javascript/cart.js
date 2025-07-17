// cart.js - Enhanced with modern practices
document.addEventListener('DOMContentLoaded', () => {
  const CART_KEY = 'thriftpalor_cart';
  let cart = loadCart();

  // Initialize cart UI
  updateCartUI();
  setupEventListeners();

  function loadCart() {
    const savedCart = localStorage.getItem(CART_KEY);
    return savedCart ? JSON.parse(savedCart) : [];
  }

  function saveCart() {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    dispatchCartUpdated();
  }

  function dispatchCartUpdated() {
    window.dispatchEvent(new CustomEvent('cart-updated', {
      detail: { cart }
    }));
  }

  function updateCartUI() {
    const cartList = document.getElementById('cart-list');
    const cartTotal = document.getElementById('cart-total');
    const cartCountElements = document.querySelectorAll('.cart-count');

    if (cartList) {
      cartList.innerHTML = cart.length > 0
        ? cart.map(item => `
            <li class="cart-item">
              <div class="cart-item-info">
                <span class="cart-item-name">${item.name}</span>
                <span class="cart-item-price">R${item.price.toFixed(2)}</span>
              </div>
              <div class="cart-item-actions">
                <button class="btn-remove" data-id="${item.id}">Remove</button>
              </div>
            </li>
          `).join('')
        : '<li class="empty-cart">Your cart is empty</li>';
    }

    if (cartTotal) {
      const total = cart.reduce((sum, item) => sum + item.price, 0);
      cartTotal.textContent = `Total: R${total.toFixed(2)}`;
    }

    cartCountElements.forEach(el => {
      el.textContent = cart.length;
    });
  }

  function setupEventListeners() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
      button.addEventListener('click', addToCart);
    });

    // Remove item buttons
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('btn-remove')) {
        removeFromCart(e.target.dataset.id);
      }
    });

    // Cart updated event
    window.addEventListener('cart-updated', updateCartUI);
  }

  function addToCart(e) {
    const button = e.target;
    const product = {
      id: button.dataset.id || Date.now().toString(),
      name: button.dataset.name,
      price: parseFloat(button.dataset.price),
      image: button.dataset.image || ''
    };

    // Check if item already exists
    const existingItem = cart.find(item => item.id === product.id);
    if (existingItem) {
      showToast(`${product.name} is already in your cart`, 'info');
      return;
    }

    cart.push(product);
    saveCart();
    showToast(`${product.name} added to cart!`, 'success');
  }

  function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    saveCart();
    showToast('Item removed from cart', 'info');
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
});