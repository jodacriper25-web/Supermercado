// hero.js
// Inicia el carrusel y asegura que respete data-bs-interval por slide
document.addEventListener('DOMContentLoaded', function(){
    const hero = document.getElementById('heroCarousel');
    if(!hero) return;

    // Bootstrap 5 respeta data-bs-interval en cada .carousel-item, pero
    // por compatibilidad reinicializamos el intervalo según el slide activo.
    const carousel = new bootstrap.Carousel(hero, { ride: 'carousel', interval: false });

    let timeoutId = null;
    function scheduleNext() {
        clearTimeout(timeoutId);
        const active = hero.querySelector('.carousel-item.active');
        const interval = active ? parseInt(active.getAttribute('data-bs-interval') || 10000, 10) : 10000;
        timeoutId = setTimeout(()=> carousel.next(), interval);
    }

    hero.addEventListener('slid.bs.carousel', scheduleNext);
    // start
    scheduleNext();
});