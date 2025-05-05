// Fungsi untuk menyalin URL pada meta share
function copyCurrentURL() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const button = document.querySelector('.copy-link');
        const originalIcon = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.style.background = '#28a745';
        
        setTimeout(() => {
            button.innerHTML = originalIcon;
            button.style.background = '#9b4dff';
        }, 2000);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const scrollContainer = document.querySelector('.pilihan-scrollable');
    const prevBtn = document.querySelector('.pilihan-prev');
    const nextBtn = document.querySelector('.pilihan-next');

    if (!scrollContainer || !prevBtn || !nextBtn) return;

    // Setup infinite scroll
    const setupInfiniteScroll = () => {
        const items = Array.from(scrollContainer.querySelectorAll('.pilihan-item:not(.pilihan-clone)'));
        items.slice(0, Math.min(5, items.length)).forEach(item => {
            const clone = item.cloneNode(true);
            clone.classList.add('pilihan-clone');
            scrollContainer.appendChild(clone);
        });
    };

    // Hapus clone yang ada dan setup ulang
    scrollContainer.querySelectorAll('.pilihan-clone').forEach(clone => clone.remove());
    setupInfiniteScroll();

    // Variabel auto scroll
    let autoScrollActive = true;
    const scrollSpeed = 1;
    const scrollInterval = 20;

    // Auto scroll
    const startAutoScroll = () => {
        const interval = setInterval(() => {
            if (!autoScrollActive) return;
            const maxScrollLeft = scrollContainer.scrollWidth - scrollContainer.clientWidth;
            scrollContainer.scrollLeft = scrollContainer.scrollLeft >= maxScrollLeft - 5
                ? 0
                : scrollContainer.scrollLeft + scrollSpeed;
        }, scrollInterval);
        return interval;
    };

    let autoScrollInterval = startAutoScroll();

    // Toggle auto scroll on hover
    scrollContainer.addEventListener('mouseenter', () => autoScrollActive = false);
    scrollContainer.addEventListener('mouseleave', () => autoScrollActive = true);

    // Manual scroll
    const scrollByItems = (direction) => {
        autoScrollActive = false;
        clearInterval(autoScrollInterval);

        const itemWidth = scrollContainer.querySelector('.pilihan-item').offsetWidth;
        const scrollDistance = itemWidth * 3 + 45;
        const maxScrollLeft = scrollContainer.scrollWidth - scrollContainer.clientWidth;

        let targetScroll;
        if (direction === 'next') {
            targetScroll = scrollContainer.scrollLeft + scrollDistance > maxScrollLeft
                ? 0
                : scrollContainer.scrollLeft + scrollDistance;
        } else {
            targetScroll = scrollContainer.scrollLeft < scrollDistance
                ? maxScrollLeft
                : scrollContainer.scrollLeft - scrollDistance;
        }

        scrollContainer.scrollTo({ left: targetScroll, behavior: 'smooth' });
        setTimeout(() => {
            autoScrollActive = true;
            autoScrollInterval = startAutoScroll();
        }, 2000);
    };

    // Tombol navigasi
    nextBtn.addEventListener('click', () => scrollByItems('next'));
    prevBtn.addEventListener('click', () => scrollByItems('prev'));

    // Touch support
    let touchStartX = 0;
    scrollContainer.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        autoScrollActive = false;
    }, { passive: true });

    scrollContainer.addEventListener('touchend', (e) => {
        const swipeDistance = touchStartX - e.changedTouches[0].screenX;
        if (Math.abs(swipeDistance) > 50) {
            scrollByItems(swipeDistance > 0 ? 'next' : 'prev');
        } else {
            setTimeout(() => autoScrollActive = true, 2000);
        }
    }, { passive: true });

    // Update tombol navigasi
    const updateButtons = () => {
        prevBtn.style.opacity = scrollContainer.scrollLeft <= 10 ? '0.3' : '0.7';
        nextBtn.style.opacity = scrollContainer.scrollLeft >= (scrollContainer.scrollWidth - scrollContainer.clientWidth - 10) ? '0.3' : '0.7';
    };

    scrollContainer.addEventListener('scroll', updateButtons);
    window.addEventListener('resize', updateButtons);
    updateButtons();
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

// Tambahkan listener untuk dark mode toggle
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggles = document.querySelectorAll('.theme-switch input[type="checkbox"]');
    
    darkModeToggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            // Tunggu sebentar untuk memastikan class dark-mode sudah diterapkan
            setTimeout(function() {
                // Trigger scroll event untuk memperbarui efek parallax
                window.dispatchEvent(new Event('scroll'));
            }, 50);
        });
    });
}); 