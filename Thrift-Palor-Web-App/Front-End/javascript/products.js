// Category filter functionality
document.querySelectorAll('.category-filter').forEach(button => {
    button.addEventListener('click', function() {
        // Update active button
        document.querySelectorAll('.category-filter').forEach(btn => {
            btn.classList.remove('active');
        });
        this.classList.add('active');

        const category = this.dataset.category;

        // Show/hide categories
        document.querySelectorAll('.product-category').forEach(section => {
            if (category === 'all' || section.dataset.category === category) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    });
});

// Initialize all categories to show when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.product-category').forEach(section => {
        section.style.display = 'block';
    });
});