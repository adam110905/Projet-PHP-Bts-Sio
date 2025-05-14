/**
 * Classe Carousel - Implémente un carrousel d'images interactif avec autoplay et contrôles
 */
class Carousel {
    constructor() {
        // Configuration des slides avec leurs données
        this.slides = [
            {
                image: '../../../ImagesRealisations/Chantier1.jpg',
                title: 'Construction moderne',
                description: 'Un savoir-faire d\'excellence'
            },
            {
                image: '../../../ImagesRealisations/Chantier2.jpg',
                title: 'Rénovation',
                description: 'Des projets sur mesure'
            },
            {
                image: '../../../ImagesRealisations/Chantier4.jpg',
                title: 'Aménagement',
                description: 'Des espaces repensés'
            },
            {
                image: '../../../ImagesRealisations/Chantier5.jpg',
                title: 'Rénovation intérieure',
                description: 'Excellence et précision'
            },
            {
                image: '../../../ImagesRealisations/Chantier6.jpg',
                title: 'Construction neuve',
                description: 'Innovation et qualité'
            }
        ];

        // Variables d'état pour le tracking du carrousel
        this.currentIndex = 0; // Index actuel
        this.isTransitioning = false; // Verrouille pendant les transitions
        this.autoplayInterval = null; // Référence au timer d'autoplay
        this.autoplayDelay = 5000; // Durée entre chaque slide (ms)
        this.touchStartX = null; // Position initiale du toucher
        this.transitionDuration = 500; // Durée d'animation (ms)

        // Récupération des éléments DOM
        this.track = document.getElementById('carouselTrack'); // Conteneur des slides
        this.indicatorsContainer = document.getElementById('indicators'); // Points de navigation
        this.prevBtn = document.getElementById('prevBtn'); // Bouton précédent
        this.nextBtn = document.getElementById('nextBtn'); // Bouton suivant

        // Lancement de l'initialisation
        this.init();
    }

    /**
     * Initialise tous les composants du carrousel
     */
    init() {
        this.createSlides(); // Génère les slides dans le DOM
        this.createIndicators(); // Génère les indicateurs de navigation
        this.setupEventListeners(); // Configure les interactions
        this.startAutoplay(); // Démarre le défilement automatique
        this.updateCarousel(); // Met à jour l'affichage initial
    }

    /**
     * Crée les éléments HTML des slides à partir des données
     */
    createSlides() {
        this.slides.forEach((slide, index) => {
            const slideElement = document.createElement('div');
            slideElement.className = 'carousel-slide';
            slideElement.innerHTML = `
                <img 
                    src="${slide.image}" 
                    alt="${slide.title}"
                    class="carousel-image"
                    loading="${index === 0 ? 'eager' : 'lazy'}" // Charge immédiatement la 1ère image
                >
                <div class="carousel-overlay">
                    <h2 class="text-2xl font-bold mb-2">${slide.title}</h2>
                    <p class="text-gray-200">${slide.description}</p>
                </div>
            `;
            this.track.appendChild(slideElement);
        });
    }

    /**
     * Crée les indicateurs de navigation (points)
     */
    createIndicators() {
        this.slides.forEach((_, index) => {
            const indicator = document.createElement('button');
            indicator.className = 'slide-indicator';
            indicator.setAttribute('aria-label', `Diapositive ${index + 1}`);
            indicator.addEventListener('click', () => this.goToSlide(index));
            this.indicatorsContainer.appendChild(indicator);
        });
    }

    /**
     * Configure tous les écouteurs d'événements pour les interactions
     */
    setupEventListeners() {
        // Boutons de navigation
        this.prevBtn.addEventListener('click', () => this.prevSlide());
        this.nextBtn.addEventListener('click', () => this.nextSlide());

        // Événements tactiles pour mobile/tablette
        this.track.addEventListener('touchstart', (e) => {
            this.touchStartX = e.touches[0].clientX; // Enregistre position initiale
            this.pauseAutoplay(); // Arrête le défilement auto pendant l'interaction
        }, { passive: true });

        this.track.addEventListener('touchmove', (e) => {
            if (this.touchStartX === null) return;
            
            // Calcule le déplacement du doigt
            const touchCurrentX = e.touches[0].clientX;
            const diff = this.touchStartX - touchCurrentX;
            
            // Déplace les slides en temps réel avec le doigt
            const offset = -this.currentIndex * 100 - (diff / this.track.offsetWidth * 100);
            this.track.style.transform = `translateX(${offset}%)`;
        }, { passive: true });

        this.track.addEventListener('touchend', (e) => {
            if (this.touchStartX === null) return;
            
            // Détecte la fin du swipe
            const touchEndX = e.changedTouches[0].clientX;
            const diff = this.touchStartX - touchEndX;
            
            // Change de slide si le swipe est assez long
            if (Math.abs(diff) > 50) {
                if (diff > 0) this.nextSlide(); // Swipe gauche -> slide suivant
                else this.prevSlide(); // Swipe droit -> slide précédent
            } else {
                // Retour à la position initiale si swipe trop court
                this.updateCarousel();
            }

            this.touchStartX = null;
            this.startAutoplay(); // Relance le défilement auto
        }, { passive: true });

        // Pause sur survol de la souris
        this.track.addEventListener('mouseenter', () => this.pauseAutoplay());
        this.track.addEventListener('mouseleave', () => this.startAutoplay());

        // Pause quand l'onglet/page n'est pas visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) this.pauseAutoplay();
            else this.startAutoplay();
        });

        // Pause quand la fenêtre perd le focus
        window.addEventListener('blur', () => this.pauseAutoplay());
        window.addEventListener('focus', () => this.startAutoplay());
        
        // Navigation au clavier avec les flèches
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.prevSlide();
            if (e.key === 'ArrowRight') this.nextSlide();
        });
    }

    /**
     * Met à jour l'état visuel du carrousel
     */
    updateCarousel() {
        // Déplace le track à la position actuelle
        this.track.style.transform = `translateX(-${this.currentIndex * 100}%)`;

        // Met à jour les indicateurs (points)
        const indicators = this.indicatorsContainer.children;
        Array.from(indicators).forEach((indicator, index) => {
            indicator.classList.toggle('active', index === this.currentIndex);
        });
    }

    /**
     * Navigue vers un slide spécifique
     * @param {number} index - L'index du slide cible
     */
    goToSlide(index) {
        if (this.isTransitioning || index === this.currentIndex) return;
        
        this.isTransitioning = true; // Verrouille pendant la transition
        this.currentIndex = index;
        this.updateCarousel();
        
        // Déverrouille après la fin de l'animation
        setTimeout(() => {
            this.isTransitioning = false;
        }, this.transitionDuration);
    }

    /**
     * Passe au slide suivant avec boucle à la fin
     */
    nextSlide() {
        if (this.isTransitioning) return;
        const nextIndex = (this.currentIndex + 1) % this.slides.length; // Boucle à la fin
        this.goToSlide(nextIndex);
    }

    /**
     * Passe au slide précédent avec boucle au début
     */
    prevSlide() {
        if (this.isTransitioning) return;
        const prevIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length; // Boucle au début
        this.goToSlide(prevIndex);
    }

    /**
     * Démarre le défilement automatique
     */
    startAutoplay() {
        this.pauseAutoplay(); // Nettoie l'intervalle existant
        this.autoplayInterval = setInterval(() => this.nextSlide(), this.autoplayDelay);
    }

    /**
     * Met en pause le défilement automatique
     */
    pauseAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }
}

// Initialise le carrousel quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    new Carousel();
});