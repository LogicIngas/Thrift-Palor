let cart = [];

function updateCart() {
    const cartList = document.getElementById('cart-list');
    const cartTotal = document.getElementById('cart-total');
    
    // Clear current cart display
    cartList.innerHTML = '';
    
    // Calculate total
    let total = 0;
    
    // Add each item to cart display
    cart.forEach(item => {
        const li = document.createElement('li');
        li.innerHTML = `
            <span>${item.name}</span>
            <span>R${item.price}</span>
        `;
        cartList.appendChild(li);
        total += item.price;
    });
    
    // Update total
    cartTotal.textContent = `Total: R${total}`;
}

document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', function() {
        const name = this.dataset.name;
        const price = parseFloat(this.dataset.price);
        const image = this.dataset.image;
        
        // Add to cart
        cart.push({ name, price, image });
        
        // Update cart display
        updateCart();
        
        // Provide feedback
        this.innerHTML = '<i class="fas fa-check"></i> Added!';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
        }, 1000);
    });
});

// Checkout button
document.querySelector('.checkout-btn')?.addEventListener('click', function() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
    } else {
        alert(`Proceeding to checkout with ${cart.length} items. Total: R${cart.reduce((sum, item) => sum + item.price, 0)}`);
        // In a real app, you would redirect to checkout page
    }
});