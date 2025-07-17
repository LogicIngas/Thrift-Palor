document.addEventListener('DOMContentLoaded', () => {
    const cart = [];
    const cartList = document.getElementById('cart-list');
    const cartTotal = document.getElementById('cart-total');

    // Helper to refresh cart UI
    function updateCartDisplay() {
        cartList.innerHTML = ''; // Clear existing items
        let total = 0;

        cart.forEach(item => {
            const li = document.createElement('li');
            li.textContent = `${item.name} — R${item.price.toFixed(2)}`;
            cartList.appendChild(li);
            total += item.price;
        });

        cartTotal.textContent = `Total: R${total.toFixed(2)}`;
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
