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

// Fungsi untuk mendeteksi kemampuan browser dan perangkat
function detectBrowserCapabilities() {
    // Deteksi browser dan perangkat
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isOldSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent) && 
                      navigator.userAgent.indexOf('Version/') > -1 && 
                      parseInt(navigator.userAgent.match(/Version\/(\d+)/)[1], 10) < 14;
    const isOldAndroid = /Android/.test(navigator.userAgent) && 
                       parseFloat(navigator.userAgent.match(/Android (\d+(\.\d+)?)/)[1]) < 7;
    
    // Cek fitur-fitur yang dibutuhkan
    const supportsTransform = 'transform' in document.documentElement.style;
    const supportsWillChange = 'willChange' in document.documentElement.style;
    const supportsIntersectionObserver = 'IntersectionObserver' in window;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isLowEnd = navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4;
    const isTouchOnly = window.matchMedia('(hover: none)').matches;
    
    // Logika untuk memutuskan apakah perlu fallback
    const needsParallaxFallback = isIOS || isOldSafari || isOldAndroid || !supportsTransform || 
                               !supportsWillChange || prefersReducedMotion || isLowEnd || isTouchOnly;
    
    return {
        isMobile,
        needsParallaxFallback,
        supportsIntersectionObserver,
        prefersReducedMotion
    };
}

// Pre-load gambar untuk parallax yang lebih mulus
function preloadParallaxImages() {
    // Temukan semua element parallax dengan background-image
    const parallaxElements = document.querySelectorAll('.parallax-effect, .hero-image');
    
    let imagesLoaded = 0;
    let totalImages = 0;
    
    // Tambahkan loading spinner jika diperlukan
    parallaxElements.forEach(element => {
        const spinner = document.createElement('div');
        spinner.className = 'parallax-loading';
        spinner.innerHTML = '<div class="spinner"></div>';
        element.appendChild(spinner);
        
        // Ekstrak URL gambar dari computed style
        const style = getComputedStyle(element);
        const bgImage = style.backgroundImage;
        
        if (bgImage && bgImage !== 'none') {
            totalImages++;
            
            // Juga cek ::before element jika ada
            const beforePseudo = window.getComputedStyle(element, '::before');
            const beforeBgImage = beforePseudo.backgroundImage;
            
            if (beforeBgImage && beforeBgImage !== 'none') {
                totalImages++;
                
                // Pre-load gambar pseudo-element
                const imgUrl = beforeBgImage.replace(/url\(['"]?([^'"]*)['"]?\)/g, '$1');
                if (imgUrl) {
                    const img = new Image();
                    img.onload = () => {
                        imagesLoaded++;
                        updateLoadingStatus();
                    };
                    img.onerror = () => {
                        imagesLoaded++;
                        updateLoadingStatus();
                    };
                    img.src = imgUrl;
                }
            }
            
            // Pre-load gambar elemen utama
            const mainImgUrl = bgImage.replace(/url\(['"]?([^'"]*)['"]?\)/g, '$1');
            if (mainImgUrl) {
                const img = new Image();
                img.onload = () => {
                    imagesLoaded++;
                    updateLoadingStatus();
                };
                img.onerror = () => {
                    imagesLoaded++;
                    updateLoadingStatus();
                };
                img.src = mainImgUrl;
            }
        }
    });
    
    // Update status loading
    function updateLoadingStatus() {
        if (imagesLoaded >= totalImages) {
            document.querySelectorAll('.parallax-loading').forEach(loader => {
                loader.classList.add('loaded');
                setTimeout(() => {
                    loader.remove();
                }, 500);
            });
        }
    }
    
    // Fallback jika tidak ada gambar yang dimuat
    setTimeout(updateLoadingStatus, 2000);
}

// Tunggu DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // Deteksi kemampuan browser
    const capabilities = detectBrowserCapabilities();
    
    // Tambahkan class pada body untuk fallback CSS
    if (capabilities.needsParallaxFallback) {
        document.body.classList.add('no-parallax');
    }
    
    // Pre-load gambar parallax
    preloadParallaxImages();
    
    // Deteksi perangkat mobile atau low-end device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isLowEndDevice = navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    // Flag untuk mengaktifkan/menonaktifkan efek parallax
    const enableParallax = !(isMobile || isLowEndDevice || prefersReducedMotion);
    
    // Cek kemampuan browser untuk fitur yang kita gunakan
    const supportsParallax = 'transform' in document.documentElement.style && 
                         'will-change' in document.documentElement.style &&
                         !(/iPad|iPhone|iPod/.test(navigator.userAgent));
    
    // Debounce function untuk mengoptimalkan scrolling event
    function debounce(func, wait = 10, immediate = true) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
    
    // Hanya terapkan efek parallax jika perangkat mendukung
    if (enableParallax && supportsParallax) {
        // Throttled scroll handler untuk performa yang lebih baik
        const scrollHandler = debounce(function() {
            const scrollPosition = window.pageYOffset;
            
            // Efek parallax pada hero image
            const heroElements = document.querySelectorAll('.hero-image');
            heroElements.forEach(function(element) {
                // Menggerakkan background berdasarkan posisi scroll
                const speed = 0.5;
                
                // Periksa apakah element masih dalam viewport
                const rect = element.getBoundingClientRect();
                if (rect.bottom > 0 && rect.top < window.innerHeight) {
                    const yPos = -(scrollPosition * speed);
                    element.style.backgroundPositionY = yPos + 'px';
                    
                    // Menggerakkan judul dengan kecepatan yang berbeda
                    const title = element.querySelector('h1');
                    if (title) {
                        const titleSpeed = 0.2;
                        title.style.transform = `translateY(${scrollPosition * -titleSpeed}px)`;
                    }
                }
            });
            
            // Efek parallax pada artikel gambar
            const articleImages = document.querySelectorAll('.article-image img.parallax-element, .article-featured-image img.parallax-element');
            articleImages.forEach(function(img) {
                const parent = img.parentElement;
                const rect = parent.getBoundingClientRect();
                
                // Hanya terapkan efek jika elemen terlihat di viewport
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    const scrollPercentage = (rect.top / window.innerHeight);
                    const transformAmount = Math.min(Math.max(scrollPercentage * 30, -30), 30); // Membatasi pergerakan
                    img.style.transform = `translateY(${transformAmount}px)`;
                }
            });
            
            // Efek parallax pada elemen dengan kelas parallax-effect
            const parallaxElements = document.querySelectorAll('.parallax-effect');
            parallaxElements.forEach(function(element) {
                const rect = element.getBoundingClientRect();
                const parallaxSpeed = element.dataset.speed || 0.3;
                
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    // Hanya terapkan parallax jika elemen dalam viewport
                    const transformAmount = scrollPosition * parallaxSpeed;
                    element.style.transform = `translateY(${transformAmount}px)`;
                }
            });
        }, 10);
        
        // Tambahkan event listener dengan throttling
        window.addEventListener('scroll', scrollHandler);
        window.addEventListener('resize', scrollHandler);
        scrollHandler(); // Panggil sekali saat load
    } else {
        // Jika perangkat tidak mendukung parallax, hapus class untuk menghindari efek visual yang buruk
        document.querySelectorAll('.parallax-effect').forEach(element => {
            element.classList.remove('parallax-effect');
        });
        
        document.querySelectorAll('.parallax-element').forEach(element => {
            element.classList.remove('parallax-element');
        });
    }
    
    // Animasi saat elemen muncul di viewport dengan Intersection Observer
    // Ini bekerja lebih baik daripada scroll event untuk animasi reveal
    if ('IntersectionObserver' in window) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        const revealObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Tambahkan delay bertahap untuk animasi kaskade
                    setTimeout(() => {
                        entry.target.classList.add('animate-in');
                    }, entry.target.dataset.delay || 0);
                    
                    // Berhenti mengamati setelah elemen muncul
                    revealObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Setup delay bertahap untuk animasi revelal berurutan
        const revealElements = document.querySelectorAll('.reveal');
        revealElements.forEach((element, index) => {
            // Tambahkan delay yang berbeda berdasarkan posisi
            if (!element.dataset.delay) {
                element.dataset.delay = index * 100; // 100ms tambahan per elemen
            }
            revealObserver.observe(element);
        });
    } else {
        // Fallback untuk browser yang tidak mendukung Intersection Observer
        document.querySelectorAll('.reveal').forEach(element => {
            element.classList.add('animate-in');
        });
    }
}); 