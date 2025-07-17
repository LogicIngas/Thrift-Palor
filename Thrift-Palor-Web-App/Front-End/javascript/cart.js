document.addEventListener('DOMContentLoaded', () => {
    let cart = [];

    const cartList = document.getElementById('cart-list');
    const cartTotal = document.getElementById('cart-total');

    // Load cart from localStorage (if it exists)
    const savedCart = localStorage.getItem('thriftpalor_cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartDisplay();
    }

    // Helper to refresh cart UI
    function updateCartDisplay() {
        cartList.innerHTML = '';
        let total = 0;

        cart.forEach(item => {
            const li = document.createElement('li');
            li.textContent = `${item.name} — R${item.price.toFixed(2)}`;
            cartList.appendChild(li);
            total += item.price;
        });

        cartTotal.textContent = `Total: R${total.toFixed(2)}`;

        // Save cart to localStorage
        localStorage.setItem('thriftpalor_cart', JSON.stringify(cart));
    }

    // Add click listener to all "Add to Cart" buttons
    const buttons = document.querySelectorAll('.add-to-cart-btn');
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const item = {
                name: button.getAttribute('data-name'),
                price: parseFloat(button.getAttribute('data-price'))
            };

            cart.push(item);
            alert(`${item.name} has been added to your cart!`);
            updateCartDisplay();
        });
    });
});
