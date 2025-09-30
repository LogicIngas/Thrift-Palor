document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('save-button');
    const updateButton = document.getElementById('update-button');
    const deleteButton = document.getElementById('delete-button');
    const statusMessage = document.getElementById('status-message');
    const addressForm = document.getElementById('address-form');

    // Check if user is logged in (you would typically get this from session)
    const userId = 1; // This should come from your authentication system

    // Load addresses on page load
    loadAddresses();

    saveButton.addEventListener('click', function() {
        saveAddress();
    });

    updateButton.addEventListener('click', function() {
        updateAddress();
    });

    deleteButton.addEventListener('click', function() {
        deleteAddress();
    });

    function loadAddresses() {
        fetch(`api.php?action=read&user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.addresses.length > 0) {
                    const address = data.addresses[0]; // Get first address for demo
                    populateForm(address);
                    showMessage('Address loaded successfully', 'success');
                } else {
                    showMessage('No address found. Please add your address.', 'info');
                }
            })
            .catch(error => {
                showMessage('Error loading address: ' + error.message, 'error');
            });
    }

    function populateForm(address) {
        document.getElementById('street_address').value = address.street_address || '';
        document.getElementById('city').value = address.city || '';
        document.getElementById('province').value = address.province || '';
        document.getElementById('postal_code').value = address.postal_code || '';
        document.getElementById('address_type').value = address.address_type || 'Home';
        document.getElementById('is_default').checked = address.is_default || false;
    }

    function saveAddress() {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('street_address', document.getElementById('street_address').value);
        formData.append('city', document.getElementById('city').value);
        formData.append('province', document.getElementById('province').value);
        formData.append('postal_code', document.getElementById('postal_code').value);
        formData.append('address_type', document.getElementById('address_type').value);
        formData.append('is_default', document.getElementById('is_default').checked ? 1 : 0);

        fetch('api.php?action=create', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                loadAddresses();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Error saving address: ' + error.message, 'error');
        });
    }

    function updateAddress() {
        // For simplicity, we're assuming there's only one address
        // In a real application, you'd need to know the address ID
        const formData = new FormData();
        formData.append('address_id', 1); // This should be dynamic
        formData.append('street_address', document.getElementById('street_address').value);
        formData.append('city', document.getElementById('city').value);
        formData.append('province', document.getElementById('province').value);
        formData.append('postal_code', document.getElementById('postal_code').value);
        formData.append('address_type', document.getElementById('address_type').value);
        formData.append('is_default', document.getElementById('is_default').checked ? 1 : 0);

        fetch('api.php?action=update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Error updating address: ' + error.message, 'error');
        });
    }

    function deleteAddress() {
        if (!confirm('Are you sure you want to delete this address?')) {
            return;
        }

        const formData = new FormData();
        formData.append('address_id', 1); // This should be dynamic

        fetch('api.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                addressForm.reset();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Error deleting address: ' + error.message, 'error');
        });
    }

    function showMessage(message, type) {
        statusMessage.textContent = message;
        statusMessage.className = type;
        
        setTimeout(() => {
            statusMessage.textContent = '';
            statusMessage.className = '';
        }, 5000);
    }
});