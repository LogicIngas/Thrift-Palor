document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById('menu-toggle');
    const navLinks = document.getElementById('nav-links');
    if (menuToggle && navLinks) {
        menuToggle.onclick = function () {
            menuToggle.classList.toggle('open');
            navLinks.classList.toggle('open');
        };
        // Optional: close menu when a link is clicked (for mobile UX)
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                menuToggle.classList.remove('open');
                navLinks.classList.remove('open');
            });
        });
    }
});