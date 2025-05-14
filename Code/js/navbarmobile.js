/**
 * Script commun pour la gestion de la navigation et des interactions UI
 * Ce script modulaire gère les éléments partagés sur plusieurs pages :
 * - Menu mobile responsive avec overlay
 * - Effet de transparence/opacité de la navbar au défilement
 * - Contrôles de quantité pour les produits (panier et page produit)
 */

document.addEventListener('DOMContentLoaded', function() {
    // Récupération des éléments d'interface pour le menu mobile
    const mobileMenuButton = document.getElementById('mobile-menu-button'); // Bouton hamburger
    const mobileMenu = document.getElementById('mobile-menu'); // Conteneur du menu
    const menuIcon = document.querySelector('.menu-icon'); // Icône de menu (hamburger)
    const closeIcon = document.querySelector('.close-icon'); // Icône de fermeture (X)
    const menuOverlay = document.getElementById('menu-overlay'); // Fond semi-transparent

    /**
     * Bascule l'affichage du menu mobile et gère tous les états associés
     * - Affiche/masque le menu
     * - Alterne les icônes hamburger/croix
     * - Contrôle l'overlay de fond
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
            
            // Gère l'overlay de fond semi-transparent
            if (menuOverlay) {
                menuOverlay.classList.toggle('hidden');
            }
            
            // Bloque le défilement de la page quand le menu est ouvert
            if (mobileMenu.classList.contains('hidden')) {
                document.body.style.overflow = ''; // Réactive le défilement
            } else {
                document.body.style.overflow = 'hidden'; // Bloque le défilement
            }
        }
    }

    // Ouvre/ferme le menu au clic sur le bouton hamburger
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', toggleMobileMenu);
    }

    // Ferme le menu au clic sur l'overlay
    if (menuOverlay) {
        menuOverlay.addEventListener('click', toggleMobileMenu);
    }

    // Ferme le menu au clic sur un lien
    const mobileMenuLinks = document.querySelectorAll('#mobile-menu a');
    mobileMenuLinks.forEach(link => {
        link.addEventListener('click', toggleMobileMenu);
    });

    // Initialise les contrôles de quantité pour les produits
    setupQuantityControls();

    // Configure l'effet de transition de la navbar au défilement
    setupScrollEffect();

    // Gestion responsive du menu : fermeture automatique en mode desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768 && mobileMenu && !mobileMenu.classList.contains('hidden')) {
            toggleMobileMenu(); // Ferme le menu si on passe en mode desktop
        }
    });
});

/**
 * Configure les contrôles de quantité (boutons + et -)
 * Fonctionne avec différentes classes pour compatibilité entre pages
 * Supporte la mise à jour automatique via l'événement 'change'
 */
function setupQuantityControls() {
    // Sélecteurs combinés pour fonctionner sur toutes les pages
    const decreaseButtons = document.querySelectorAll('.quantity-decrease, .minus-btn');
    const increaseButtons = document.querySelectorAll('.quantity-increase, .plus-btn');

    // Configuration des boutons de diminution
    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('input[type="number"]');
            if (input && input.value > 1) {
                input.value = parseInt(input.value) - 1; // Diminue la quantité (min: 1)
                
                // Déclenche l'événement change pour les mises à jour automatiques
                if (input.hasAttribute('onchange')) {
                    input.dispatchEvent(new Event('change'));
                }
            }
        });
    });

    // Configuration des boutons d'augmentation
    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('input[type="number"]');
            const maxValue = input.hasAttribute('max') ? parseInt(input.getAttribute('max')) : 10;
            
            if (input && input.value < maxValue) {
                input.value = parseInt(input.value) + 1; // Augmente la quantité (max configurable)
                
                // Déclenche l'événement change pour les mises à jour automatiques
                if (input.hasAttribute('onchange')) {
                    input.dispatchEvent(new Event('change'));
                }
            }
        });
    });
}

/**
 * Configure l'effet de transparence/opacité de la navbar au défilement
 * La navbar est transparente en haut de page et devient opaque en défilant
 */
function setupScrollEffect() {
    const nav = document.querySelector('.gradient-nav');
    
    if (nav) {
        // Vérifie l'état initial au chargement de la page
        if (window.scrollY > 50) {
            nav.classList.add('scrolled'); // Applique le style opaque si déjà scrollé
        }
        
        // Ajoute l'écouteur pour le défilement
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled'); // Style opaque quand on défile
            } else {
                nav.classList.remove('scrolled'); // Style transparent en haut de page
            }
        });
    }
}

/**
 * Fonctions globales pour les contrôles de quantité
 * Ces fonctions sont conçues pour être appelées directement depuis des attributs HTML
 * Exemple : <button onclick="decrementQuantity(this)">-</button>
 */

/**
 * Diminue la quantité d'un produit
 * @param {HTMLElement} button - Le bouton de diminution cliqué
 */
function decrementQuantity(button) {
    const input = button.parentNode.querySelector('input[type="number"]');
    if (input && input.value > 1) {
        input.value = parseInt(input.value) - 1; // Empêche d'aller sous 1
    }
}

/**
 * Augmente la quantité d'un produit
 * @param {HTMLElement} button - Le bouton d'augmentation cliqué
 */
function incrementQuantity(button) {
    const input = button.parentNode.querySelector('input[type="number"]');
    const maxValue = input.hasAttribute('max') ? parseInt(input.getAttribute('max')) : 99;
    
    if (input && input.value < maxValue) {
        input.value = parseInt(input.value) + 1; // Respecte la valeur maximale
    }
}