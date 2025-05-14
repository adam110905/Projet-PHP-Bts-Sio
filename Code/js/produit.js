/**
 * Script fusionné pour produit.php
 * Combine la gestion de la navbar mobile et les fonctionnalités spécifiques à la page produit
 */
document.addEventListener('DOMContentLoaded', function() {
    // ====== PARTIE 1: GESTION DE LA NAVBAR MOBILE ======
    // Récupération des éléments d'interface pour le menu mobile
    const mobileMenuButton = document.getElementById('mobile-menu-button'); // Bouton hamburger
    const mobileMenu = document.getElementById('mobile-menu'); // Conteneur du menu
    const menuIcon = document.querySelector('.menu-icon'); // Icône hamburger
    const closeIcon = document.querySelector('.close-icon'); // Icône de fermeture (X)
    const menuOverlay = document.getElementById('menu-overlay'); // Fond semi-transparent

    /**
     * Bascule l'affichage du menu mobile et gère les états associés
     * - Alterne entre les icônes hamburger et croix
     * - Affiche/masque l'overlay semi-transparent
     * - Bloque/débloque le défilement de la page
     */
    function toggleMobileMenu() {
        if (mobileMenu) {
            mobileMenu.classList.toggle('hidden'); // Affiche/masque le menu
            
            // Alterne entre les icônes du menu
            if (menuIcon && closeIcon) {
                menuIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            }
            
            // Gère l'affichage de l'overlay
            if (menuOverlay) {
                menuOverlay.classList.toggle('hidden');
            }
            
            // Bloque le défilement du fond quand le menu est ouvert
            if (mobileMenu.classList.contains('hidden')) {
                document.body.style.overflow = ''; // Réactive le défilement
            } else {
                document.body.style.overflow = 'hidden'; // Bloque le défilement
            }
        }
    }
    
    // Active le menu mobile au clic sur le bouton hamburger
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', toggleMobileMenu);
    }
    
    // Ferme le menu au clic sur l'overlay (fond semi-transparent)
    if (menuOverlay) {
        menuOverlay.addEventListener('click', toggleMobileMenu);
    }
    
    // Ferme le menu au clic sur un lien dans le menu mobile
    const mobileMenuLinks = document.querySelectorAll('#mobile-menu a');
    mobileMenuLinks.forEach(link => {
        link.addEventListener('click', toggleMobileMenu);
    });
    
    // Effet de changement de style de la navbar lors du défilement
    const nav = document.querySelector('.gradient-nav');
    if (nav) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled'); // Style opaque au défilement
            } else {
                nav.classList.remove('scrolled'); // Style transparent en haut
            }
        });
    }
    
    // Ferme automatiquement le menu mobile en mode desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768 && mobileMenu && !mobileMenu.classList.contains('hidden')) {
            toggleMobileMenu(); // Ferme le menu si on passe en vue desktop
        }
    });

    // ====== PARTIE 2: GESTION DES BOUTONS DE QUANTITÉ ======
    /**
     * Configure les contrôles de quantité pour les produits
     * Permet d'augmenter/diminuer la quantité avec des boutons +/-
     */
    function setupQuantityControls() {
        const decreaseButtons = document.querySelectorAll('.minus-btn'); // Boutons -
        const increaseButtons = document.querySelectorAll('.plus-btn'); // Boutons +
        
        // Configuration des boutons de diminution
        decreaseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input[type="number"]');
                if (input && parseInt(input.value) > 1) {
                    input.value = parseInt(input.value) - 1; // Diminue quantité (min: 1)
                }
            });
        });
        
        // Configuration des boutons d'augmentation
        increaseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input[type="number"]');
                if (input && parseInt(input.value) < 99) {
                    input.value = parseInt(input.value) + 1; // Augmente quantité (max: 99)
                }
            });
        });
    }
    
    // Initialisation des contrôles de quantité au chargement
    setupQuantityControls();

    // ====== PARTIE 3: NOTIFICATIONS AUTO-MASQUANTES ======
    // Fait disparaître automatiquement les notifications après 5 secondes
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            // Animation de fondu et de translation vers le haut
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            notification.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            // Suppression de la notification du DOM après l'animation
            setTimeout(() => {
                notification.remove();
            }, 500); // Durée de l'animation (ms)
        }, 5000); // Délai avant disparition (ms)
    });

    // ====== PARTIE 4: ANIMATIONS DES CARTES DE PRODUITS ======
    // Animation séquentielle d'apparition des cartes de produits
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '1'; // Rend la carte visible
            card.style.transform = 'translateY(0)'; // Déplace la carte à sa position finale
        }, 100 + (index * 50)); // Délai progressif pour effet en cascade
    });

    // ====== PARTIE 5: FILTRES ET RECHERCHE ======
    // Soumission automatique du formulaire de filtrage au changement de sélection
    const filterForm = document.querySelector('form[method="GET"]');
    const categorySelect = document.getElementById('category'); // Sélecteur de catégorie
    const sortSelect = document.getElementById('sort'); // Sélecteur de tri
    
    // Soumet le formulaire quand l'utilisateur change de catégorie
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            if (filterForm) {
                filterForm.submit(); // Actualise la page avec le nouveau filtre
            }
        });
    }
    
    // Soumet le formulaire quand l'utilisateur change l'ordre de tri
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            if (filterForm) {
                filterForm.submit(); // Actualise la page avec le nouvel ordre
            }
        });
    }
});