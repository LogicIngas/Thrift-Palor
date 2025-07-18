document.addEventListener('DOMContentLoaded', () => {
  // API endpoints — adjust URLs to your backend
  const API_GET_CART = '/api/cart/items';
  const API_ADD_ITEM = '/api/cart/add';
  const API_REMOVE_ITEM = '/api/cart/remove';

  // Load and render cart on page load
  fetchCart();

  setupEventListeners();

  function fetchCart() {
    fetch(API_GET_CART)
      .then(res => {
        if (!res.ok) throw new Error('Failed to fetch cart');
        return res.json();
      })
      .then(data => {
        updateCartUI(data);
      })
      .catch(err => {
        showToast('Error loading cart', 'error');
        updateCartUI([]); // Show empty cart on error
      });
  }

  function updateCartUI(cart) {
    const cartList = document.getElementById('cart-list');
    const cartTotal = document.getElementById('cart-total');
    const cartCountElements = document.querySelectorAll('.cart-count');

    if (cartList) {
      if (cart.length === 0) {
        cartList.innerHTML = '<li class="empty-cart">Your cart is empty</li>';
      } else {
        cartList.innerHTML = cart.map(item => `
          <li class="cart-item">
            <div class="cart-item-info">
              <span class="cart-item-name">${item.name}</span>
              <span class="cart-item-price">R${item.price.toFixed(2)}</span>
            </div>
            <div class="cart-item-actions">
              <button class="btn-remove" data-id="${item.id}">Remove</button>
            </div>
          </li>
        `).join('');
      }
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

    // Remove item buttons - event delegation
    document.addEventListener('click', e => {
      if (e.target.classList.contains('btn-remove')) {
        removeFromCart(e.target.dataset.id);
      }
    });
  }

  function addToCart(e) {
    const button = e.target;
    const product = {
      id: button.dataset.id,
      name: button.dataset.name,
      price: parseFloat(button.dataset.price),
      image: button.dataset.image || ''
    };

    fetch(API_ADD_ITEM, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(product)
    })
      .then(res => {
        if (!res.ok) throw new Error('Failed to add to cart');
        return res.json();
      })
      .then(data => {
        fetchCart(); // Refresh cart UI
        showToast(`${product.name} added to cart!`, 'success');
      })
      .catch(() => {
        showToast('Failed to add item to cart', 'error');
      });
  }

  function removeFromCart(id) {
    fetch(API_REMOVE_ITEM, {
      method: 'POST', // or DELETE depending on your API design
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    })
      .then(res => {
        if (!res.ok) throw new Error('Failed to remove from cart');
        return res.json();
      })
      .then(data => {
        fetchCart(); // Refresh cart UI
        showToast('Item removed from cart', 'info');
      })
      .catch(() => {
        showToast('Failed to remove item from cart', 'error');
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
});
