
// * MEMBUAT SCROLL PADA COVER NAVIGASI ---
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
}); 


// * MEMBUAT TOMBOL NAVIGASI ----
const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');

menuToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    
    // Animasi untuk hamburger menu
    const spans = menuToggle.querySelectorAll('span');
    spans[0].classList.toggle('rotate-45');
    spans[1].classList.toggle('opacity-0');
    spans[2].classList.toggle('rotate-negative-45');
});

// ? Tambahkan CSS untuk animasi hamburger
const style = document.createElement('style');
style.textContent = `
    .rotate-45 {
        transform: rotate(45deg) translate(6px, 6px);
    }
    
    .opacity-0 {
        opacity: 0;
    }
    
    .rotate-negative-45 {
        transform: rotate(-45deg) translate(6px, -6px);
    }
`;
document.head.appendChild(style); 