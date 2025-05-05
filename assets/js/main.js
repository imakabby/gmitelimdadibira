/**
 * Main JavaScript file untuk LangkahKata.com
 * Menangani fungsionalitas iklan, navigasi, dan interaksi pengguna
 */

(function() {
    'use strict';
    
    // Inisialisasi saat DOM telah dimuat
    document.addEventListener('DOMContentLoaded', function() {
        initAdsHandling();
        initScrollFunctionality();
        initShareButtons();
    });
    
    // Fungsi untuk menangani iklan AdSense
    function initAdsHandling() {
        // Cek apakah AdSense tersedia
        if (typeof adsbygoogle !== 'undefined') {
            // Uji coba muat iklan yang tersedia
            try {
                Array.from(document.querySelectorAll('.adsbygoogle')).forEach(function(ad) {
                    if (ad.getAttribute('data-ad-loaded') !== 'true') {
                        (adsbygoogle = window.adsbygoogle || []).push({});
                        ad.setAttribute('data-ad-loaded', 'true');
                    }
                });
            } catch (e) {
                console.log('AdSense error: ' + e.message);
            }
        }
        
        // Deteksi apakah iklan terblokir
        window.setTimeout(function() {
            const adsContainers = document.querySelectorAll('.adsbygoogle');
            adsContainers.forEach(function(container) {
                const computedStyle = window.getComputedStyle(container);
                const isHidden = computedStyle.display === 'none' || computedStyle.visibility === 'hidden' 
                              || computedStyle.opacity === '0' || container.offsetHeight < 10;
                
                if (isHidden) {
                    console.log('Ad may be blocked or not loaded properly');
                    container.classList.add('ad-blocked');
                    
                    // Tambahkan placeholder jika diperlukan
                    if (container.parentNode && container.parentNode.classList.contains('adsbygoogle-container')) {
                        container.parentNode.classList.add('ad-blocked-container');
                    }
                }
            });
        }, 3000); // Berikan waktu untuk iklan dimuat
    }
    
    // Fungsi untuk mengoptimalkan scrolling dan UX
    function initScrollFunctionality() {
        // Sticky header handling
        const header = document.querySelector('header');
        if (header) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('sticky-active');
                    // Tambahkan z-index yang lebih tinggi untuk memastikan header tetap di atas
                    header.style.zIndex = '1500';
                } else {
                    header.classList.remove('sticky-active');
                    // Kembalikan ke z-index default
                    header.style.zIndex = '';
                }
            });
        }
        
        // Smooth scroll untuk anchor links
        const anchorLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');
        anchorLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80, // Offset untuk header
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Lazy load untuk gambar (fallback jika loading="lazy" tidak didukung)
        if ('IntersectionObserver' in window) {
            const lazyImages = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(function(image) {
                imageObserver.observe(image);
            });
        }
    }
    
    // Perbaikan untuk masalah CSP dan Content-Security-Policy
    window.addEventListener('error', function(e) {
        // Deteksi error yang berkaitan dengan CSP
        if (e.message && (e.message.includes('Content Security Policy') ||
                          e.message.includes('ERR_BLOCKED_BY_CLIENT') ||
                          e.message.includes('net::ERR_BLOCKED_BY_CLIENT'))) {
            console.log('CSP or Ad blocker error detected:', e.message);
            
            // Coba perbaiki konten yang terpengaruh
            setTimeout(function() {
                const blockedContainers = document.querySelectorAll('.adsbygoogle-container');
                blockedContainers.forEach(function(container) {
                    container.style.height = 'auto';
                    container.style.minHeight = '100px';
                });
            }, 1000);
        }
    }, true);
    
    // Aktifkan dukungan untuk mode kutip vs mode standar
    const doctype = document.doctype;
    if (!doctype) {
        console.warn('Mode Kutip terdeteksi - tata letak halaman mungkin terpengaruh.');
        
        // Perbaikan untuk Mode Kutip
        document.documentElement.classList.add('quirks-mode');
        document.body.classList.add('quirks-mode-body');
    }
    
    // Perbaikan untuk perangkat mobile
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        document.documentElement.classList.add('mobile-device');
    }
})(); 