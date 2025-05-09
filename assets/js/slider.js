document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;
    
    // Fungsi untuk menampilkan slide
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        slides[index].classList.add('active');
    }
    
    // Fungsi untuk pindah ke slide berikutnya
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    // Tampilkan slide pertama
    showSlide(0);
    
    // Atur interval pergantian slide setiap 3 detik
    setInterval(nextSlide, 3000);
}); 