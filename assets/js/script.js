function copyCurrentURL() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const button = document.querySelector('.copy-link');
        const originalIcon = button.innerHTML;
        
        // Ganti icon dengan check
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.style.background = '#28a745';
        
        // Kembalikan ke icon semula setelah 2 detik
        setTimeout(() => {
            button.innerHTML = originalIcon;
            button.style.background = '#9b4dff';
        }, 2000);
    });
} 

// Carousel untuk artikel pilihan
document.addEventListener('DOMContentLoaded', function() {
    const scrollContainer = document.querySelector('.pilihan-scrollable');
    const prevBtn = document.querySelector('.pilihan-prev');
    const nextBtn = document.querySelector('.pilihan-next');
    
    if (scrollContainer && prevBtn && nextBtn) {
        // Hapus duplikasi yang mungkin sudah ada di DOM
        const cleanupExistingClones = () => {
            const existingClones = scrollContainer.querySelectorAll('.pilihan-clone, [data-clone="true"]');
            existingClones.forEach(clone => {
                clone.remove();
            });
        };
        
        // Bersihkan terlebih dahulu
        cleanupExistingClones();
        
        // Setup infinite scroll
        const setupInfiniteScroll = () => {
            // Ambil semua item asli (bukan clone)
            const items = Array.from(scrollContainer.querySelectorAll('.pilihan-item:not(.pilihan-clone):not([data-clone="true"])'));
            
            if (items.length > 0) {
                // Kumpulkan semua slug yang sudah ada
                const existingSlugs = new Set();
                items.forEach(item => {
                    const slug = item.getAttribute('data-slug');
                    if (slug) existingSlugs.add(slug);
                });
                
                // Clone beberapa item pertama dan tambahkan ke akhir untuk efek infinite
                const cloneCount = Math.min(5, items.length);
                for (let i = 0; i < cloneCount; i++) {
                    // Tambahkan atribut data-clone pada elemen clone untuk membedakannya
                    const clone = items[i].cloneNode(true);
                    clone.classList.add('pilihan-clone');
                    clone.setAttribute('data-clone', 'true');
                    scrollContainer.appendChild(clone);
                }
            }
        };
        
        // Panggil fungsi setup
        setupInfiniteScroll();
        
        // Dapatkan total lebar konten yang dapat di-scroll
        const getMaxScrollLeft = () => scrollContainer.scrollWidth - scrollContainer.clientWidth;
        
        // Auto scroll variables
        let autoScrollInterval;
        const scrollSpeed = 1; // Pixel per tick (lebih lambat untuk efek lebih mulus)
        const scrollInterval = 20; // Milliseconds between ticks (lebih cepat untuk efek lebih mulus)
        let autoScrollActive = true;
        let isScrollingBack = false; // Flag untuk menentukan saat sedang scroll kembali ke awal
        
        // Fungsi untuk auto scroll
        function startAutoScroll() {
            if (autoScrollInterval) clearInterval(autoScrollInterval);
            
            autoScrollInterval = setInterval(() => {
                if (!autoScrollActive) return;
                
                // Cek apakah sudah mencapai ujung kanan
                const maxScrollLeft = getMaxScrollLeft();
                
                if (scrollContainer.scrollLeft >= maxScrollLeft - 5) {
                    // Reset scroll ke awal dengan animasi mundur
                    isScrollingBack = true;
                    scrollContainer.scrollLeft = 0;
                    isScrollingBack = false;
                } else {
                    // Scroll normal ke kanan
                    scrollContainer.scrollLeft += scrollSpeed;
                }
            }, scrollInterval);
        }
        
        // Mulai auto scroll saat halaman dimuat
        startAutoScroll();
        
        // Hentikan auto scroll saat hover
        scrollContainer.addEventListener('mouseenter', () => {
            autoScrollActive = false;
        });
        
        // Lanjutkan auto scroll saat mouse keluar
        scrollContainer.addEventListener('mouseleave', () => {
            autoScrollActive = true;
        });
        
        // Tombol scroll ke kanan
        nextBtn.addEventListener('click', function() {
            // Hentikan auto scroll sementara
            autoScrollActive = false;
            
            // Scroll manual
            const itemWidth = scrollContainer.querySelector('.pilihan-item').offsetWidth;
            const scrollDistance = itemWidth * 3 + 45; // Scroll 3 item (3 item width + 3 gaps)
            
            // Cek apakah kita sudah di akhir
            const maxScrollLeft = getMaxScrollLeft();
            
            if (scrollContainer.scrollLeft + scrollDistance > maxScrollLeft) {
                // Jika sudah mencapai akhir, kembali ke awal
                scrollContainer.scrollTo({
                    left: 0,
                    behavior: 'smooth'
                });
            } else {
                // Jika belum, scroll seperti biasa
                scrollContainer.scrollBy({
                    left: scrollDistance,
                    behavior: 'smooth'
                });
            }
            
            // Set timer untuk melanjutkan auto scroll
            setTimeout(() => {
                autoScrollActive = true;
            }, 2000); // Tunggu 2 detik setelah klik
        });
        
        // Tombol scroll ke kiri
        prevBtn.addEventListener('click', function() {
            // Hentikan auto scroll sementara
            autoScrollActive = false;
            
            // Scroll manual
            const itemWidth = scrollContainer.querySelector('.pilihan-item').offsetWidth;
            const scrollDistance = itemWidth * 3 + 45; // Scroll 3 item ke kiri
            
            // Cek apakah di awal
            if (scrollContainer.scrollLeft < scrollDistance) {
                // Jika sudah mencapai awal, pergi ke akhir
                scrollContainer.scrollTo({
                    left: getMaxScrollLeft(),
                    behavior: 'smooth'
                });
            } else {
                // Jika belum, scroll seperti biasa
                scrollContainer.scrollBy({
                    left: -scrollDistance,
                    behavior: 'smooth'
                });
            }
            
            // Set timer untuk melanjutkan auto scroll
            setTimeout(() => {
                autoScrollActive = true;
            }, 2000); // Tunggu 2 detik setelah klik
        });
        
        // Touch events handling untuk mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        scrollContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            autoScrollActive = false;
        }, {passive: true});
        
        scrollContainer.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            
            // Deteksi arah swipe
            const swipeDistance = touchStartX - touchEndX;
            if (Math.abs(swipeDistance) > 50) {
                if (swipeDistance > 0) {
                    // Swipe kiri (next)
                    nextBtn.click();
                } else {
                    // Swipe kanan (prev)
                    prevBtn.click();
                }
            } else {
                // Lanjutkan auto scroll setelah sentuhan selesai
                setTimeout(() => {
                    autoScrollActive = true;
                }, 2000);
            }
        }, {passive: true});
        
        // Tangani scroll event
        scrollContainer.addEventListener('scroll', function() {
            if (isScrollingBack) return;
            
            // Cek apakah di awal atau di akhir untuk button visibility
            checkButtons();
        });
        
        // Sembunyikan tombol navigasi jika tidak diperlukan
        function checkButtons() {
            // Tombol prev hanya disembunyikan di awal
            if (scrollContainer.scrollLeft <= 10) {
                prevBtn.style.opacity = '0.3';
            } else {
                prevBtn.style.opacity = '0.7';
            }
            
            // Tombol next tetap terlihat untuk infinite scroll
            if (scrollContainer.scrollLeft >= getMaxScrollLeft() - 10) {
                nextBtn.style.opacity = '0.3';
            } else {
                nextBtn.style.opacity = '0.7';
            }
        }
        
        // Periksa tombol saat halaman dimuat
        checkButtons();
        
        // Periksa tombol saat window diresize
        window.addEventListener('resize', checkButtons);
    }
}); 

