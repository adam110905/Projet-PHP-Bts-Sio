/**
 * Script principal de la page d'accueil
 * Gère les animations, le carousel et les effets interactifs
 */

// Effet de transparence/opacité de la navigation lors du défilement
window.addEventListener('scroll', () => {
    const nav = document.querySelector('nav');
    if (window.scrollY > 50) {
        nav.classList.add('scrolled'); // Applique style opaque quand on défile
    } else {
        nav.classList.remove('scrolled'); // Restaure la transparence en haut de page
    }
});

// Configuration et initialisation du carousel Swiper avec effet coverflow
const swiper = new Swiper('.swiper', {
    effect: 'coverflow', // Effet 3D avec perspective
    grabCursor: true, // Curseur "main" au survol
    centeredSlides: true, // Centre le slide actif
    slidesPerView: 'auto', // Adapte le nombre de slides visibles
    loop: true, // Boucle infinie
    speed: 1000, // Vitesse de transition (ms)
    autoplay: {
        delay: 3000, // Temps entre les transitions (ms)
        disableOnInteraction: false, // Continue l'autoplay après interaction
        pauseOnMouseEnter: true // Pause au survol
    },
    coverflowEffect: {
        rotate: 5, // Rotation des slides
        stretch: 0, // Étirement
        depth: 100, // Profondeur 3D
        modifier: 2.5, // Intensité de l'effet
        slideShadows: true // Ombres sous les slides
    },
    pagination: {
        el: '.swiper-pagination', // Sélecteur des bullets
        clickable: true, // Permet navigation par clic
        dynamicBullets: true // Adapte taille des bullets selon proximité
    },
    navigation: {
        nextEl: '.swiper-button-next', // Bouton suivant
        prevEl: '.swiper-button-prev' // Bouton précédent
    },
    breakpoints: {
        // Paramètres responsives selon largeur d'écran
        640: {
            slidesPerView: 1.2, // Petit mobile
        },
        768: {
            slidesPerView: 2, // Tablette
        },
        1024: {
            slidesPerView: 2.5, // Petit écran
        },
        1280: {
            slidesPerView: 3, // Grand écran
        }
    }
});

// Contrôle précis de l'autoplay au survol individuel des slides
const slides = document.querySelectorAll('.swiper-slide');
slides.forEach(slide => {
    slide.addEventListener('mouseenter', () => {
        swiper.autoplay.stop(); // Arrête défilement au survol
    });
    slide.addEventListener('mouseleave', () => {
        swiper.autoplay.start(); // Reprend défilement quand souris quitte
    });
});

// Configuration de l'Intersection Observer pour animations au scroll
const observerOptions = {
    root: null, // Utilise le viewport comme zone d'observation
    rootMargin: '0px', // Pas de marge supplémentaire
    threshold: 0.1 // Déclenche dès que 10% de l'élément est visible
};

// Création de l'observer qui anime les éléments quand ils deviennent visibles
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // Applique l'animation de fondu et translation vers le haut
            entry.target.classList.add('animate-fade-in');
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Prépare et observe les cartes d'expertise pour animation au scroll
document.querySelectorAll('.expertise-card').forEach(card => {
    // État initial invisible et décalé vers le bas
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
    observer.observe(card); // Commence à observer la carte
});

// Ajoute un effet d'élévation au survol des boutons jaunes
document.querySelectorAll('button, a').forEach(element => {
    if (element.classList.contains('bg-yellow-500')) {
        element.addEventListener('mouseenter', () => {
            element.style.transform = 'translateY(-2px)'; // Élévation au survol
        });
        element.addEventListener('mouseleave', () => {
            element.style.transform = 'translateY(0)'; // Retour position normale
        });
    }
});