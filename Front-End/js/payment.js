function validateForm() {
    console.log('Validating payment form...');
    
    const cardName = document.getElementById("cardName").value;
    const cardNumber = document.getElementById("cardNumber").value;
    const expiryDate = document.getElementById("expiryDate").value;
    const cvv = document.getElementById("cvv").value;
    
    // Address fields
    const streetAddress = document.getElementById("street_address").value;
    const city = document.getElementById("city").value;
    const province = document.getElementById("province").value;
    const postalCode = document.getElementById("postal_code").value;

    const cardNameError = document.getElementById("cardNameError");
    const cardNumberError = document.getElementById("cardNumberError");
    const expiryDateError = document.getElementById("expiryDateError");
    const cvvError = document.getElementById("cvvError");

    // Reset error messages
    resetError(cardNameError);
    resetError(cardNumberError);
    resetError(expiryDateError);
    resetError(cvvError);

    let isValid = true;

    // Validate address fields
    if (!streetAddress.trim()) {
        alert("Street address is required");
        isValid = false;
    }

    if (!city.trim()) {
        alert("City is required");
        isValid = false;
    }

    if (!province.trim()) {
        alert("Province is required");
        isValid = false;
    }

    if (!postalCode.trim()) {
        alert("Postal code is required");
        isValid = false;
    }

    // Validate card name
    if (!cardName.trim()) {
        displayError(cardNameError, "Name on Card is required");
        isValid = false;
    }

    // Validate card number (16 digits)
    const cleanCardNumber = cardNumber.replace(/\s/g, '');
    if (!/^\d{16}$/.test(cleanCardNumber)) {
        displayError(cardNumberError, "Valid 16-digit Card Number is required");
        isValid = false;
    }

    // Validate expiry date (MM/YY)
    if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
        displayError(expiryDateError, "Valid Expiry Date (MM/YY) is required");
        isValid = false;
    } else {
        // Check if card is expired
        const [month, year] = expiryDate.split('/');
        const now = new Date();
        const currentYear = now.getFullYear() % 100; // Get last two digits
        const currentMonth = now.getMonth() + 1; // Months are 0-indexed
        
        if (parseInt(year) < currentYear || 
            (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
            displayError(expiryDateError, "Card has expired");
            isValid = false;
        }
    }

    // Validate CVV (3 digits)
    if (!/^\d{3}$/.test(cvv)) {
        displayError(cvvError, "Valid 3-digit CVV is required");
        isValid = false;
    }

    if (isValid) {
        console.log('Form validation passed');
        
        // Double-check cart data before submitting
        const cartData = localStorage.getItem('thrift_parlor_cart_checkout');
        if (!cartData) {
            alert('Cart data is missing. Please add items to your cart again.');
            return false;
        }
        
        try {
            const cart = JSON.parse(cartData);
            if (cart.length === 0) {
                alert('Your cart is empty. Please add items before proceeding to payment.');
                return false;
            }
        } catch (e) {
            alert('Error processing your cart. Please try again.');
            return false;
        }
        
        alert("Thank you for shopping with Thrift Parlor!");
        return true;
    }

    console.log('Form validation failed');
    return false;

    function displayError(element, message) {
        element.textContent = message;
        element.style.color = "red";
    }
    
    function resetError(element) {
        element.textContent = "";
        element.style.color = "";
    }
}

// Format card number (add spaces every 4 digits)
document.getElementById("cardNumber").addEventListener("input", function (e) {
    let input = e.target.value.replace(/\D/g, "");
    input = input.substring(0, 16);
    
    // Add spaces every 4 digits
    let formattedInput = "";
    for (let i = 0; i < input.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formattedInput += " ";
        }
        formattedInput += input[i];
    }
    
    e.target.value = formattedInput;
});

// Format expiry date (MM/YY)
document.getElementById("expiryDate").addEventListener("input", function (e) {
    let input = e.target.value.replace(/\D/g, "");
    input = input.substring(0, 4);
    
    if (input.length > 2) {
        e.target.value = input.substring(0, 2) + "/" + input.substring(2);
    } else {
        e.target.value = input;
    }
});

// Limit CVV to 3 digits
document.getElementById("cvv").addEventListener("input", function (e) {
    e.target.value = e.target.value.replace(/\D/g, "").substring(0, 3);
});

// Auto-format postal code (South African format)
document.getElementById("postal_code").addEventListener("input", function (e) {
    let input = e.target.value.replace(/\s/g, "").toUpperCase();
    if (input.length > 4) {
        input = input.substring(0, 4) + " " + input.substring(4);
    }
    e.target.value = input.substring(0, 9);
});

// Debug helper to check localStorage
function debugLocalStorage() {
    console.log('=== LOCAL STORAGE DEBUG ===');
    console.log('thrift_parlor_cart:', localStorage.getItem('thrift_parlor_cart'));
    console.log('thrift_parlor_cart_checkout:', localStorage.getItem('thrift_parlor_cart_checkout'));
    console.log('thrift_parlor_cart_total:', localStorage.getItem('thrift_parlor_cart_total'));
}

// Run debug on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Payment.js loaded');
    debugLocalStorage();
});