/**
 * Script fusionné pour panier.php
 * Combine la gestion de la navbar mobile et les fonctionnalités spécifiques à la page panier
 */
document.addEventListener('DOMContentLoaded', function() {
    // ====== PARTIE 1: GESTION DE LA NAVBAR MOBILE ======
    // Récupération des éléments d'interface pour le menu mobile
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.querySelector('.menu-icon');
    const closeIcon = document.querySelector('.close-icon');
    const menuOverlay = document.getElementById('menu-overlay');

    /**
     * Bascule l'affichage du menu mobile et gère l'état des icônes et de l'overlay
     * Bloque également le défilement de la page quand le menu est ouvert
     */
    function toggleMobileMenu() {
        if (mobileMenu) {
            mobileMenu.classList.toggle('hidden');
            
            // Alterne les icônes du menu (hamburger/croix)
            if (menuIcon && closeIcon) {
                menuIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            }
            
            // Affiche/masque l'overlay semi-transparent
            if (menuOverlay) {
                menuOverlay.classList.toggle('hidden');
            }
            
            // Empêche le défilement du fond quand menu ouvert
            if (mobileMenu.classList.contains('hidden')) {
                document.body.style.overflow = ''; // Réactive défilement
            } else {
                document.body.style.overflow = 'hidden'; // Bloque défilement
            }
        }
    }
    
    // Active le basculement du menu sur clic du bouton hamburger
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', toggleMobileMenu);
    }
    
    // Ferme le menu quand on clique sur l'overlay
    if (menuOverlay) {
        menuOverlay.addEventListener('click', toggleMobileMenu);
    }
    
    // Ferme le menu quand on clique sur un lien du menu
    const mobileMenuLinks = document.querySelectorAll('#mobile-menu a');
    mobileMenuLinks.forEach(link => {
        link.addEventListener('click', toggleMobileMenu);
    });
    
    // Change l'apparence de la navbar au défilement
    const nav = document.querySelector('.gradient-nav');
    if (nav) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled'); // Applique style opaque
            } else {
                nav.classList.remove('scrolled'); // Revient au style transparent
            }
        });
    }
    
    // Ferme automatiquement le menu mobile lors du redimensionnement vers desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768 && mobileMenu && !mobileMenu.classList.contains('hidden')) {
            toggleMobileMenu();
        }
    });

    // ====== PARTIE 2: GESTION DES BOUTONS DE QUANTITÉ ======
    // Récupère les boutons de contrôle de quantité pour les articles du panier
    const decreaseButtons = document.querySelectorAll('.quantity-decrease');
    const increaseButtons = document.querySelectorAll('.quantity-increase');

    // Configure les boutons de diminution de quantité
    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('input[type="number"]');
            if (input && parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1; // Diminue la quantité
                
                // Soumet le formulaire si un événement onchange est défini
                if (input.hasAttribute('onchange')) {
                    input.dispatchEvent(new Event('change'));
                }
            }
        });
    });

    // Configure les boutons d'augmentation de quantité
    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('input[type="number"]');
            if (input && parseInt(input.value) < 10) {
                input.value = parseInt(input.value) + 1; // Augmente la quantité
                
                // Soumet le formulaire si un événement onchange est défini
                if (input.hasAttribute('onchange')) {
                    input.dispatchEvent(new Event('change'));
                }
            }
        });
    });

    // ====== PARTIE 3: NOTIFICATIONS AUTO-MASQUANTES ======
    // Fait disparaître les notifications après 5 secondes
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            // Animation de fondu et déplacement vers le haut
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            notification.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            // Supprime l'élément du DOM après la fin de l'animation
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 5000); // Délai avant disparition
    });

    // ====== PARTIE 4: ANIMATIONS DU TABLEAU ======
    // Effet de surbrillance des lignes du tableau au survol
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.classList.add('active-row'); // Ajoute classe de surbrillance
        });
        row.addEventListener('mouseleave', function() {
            this.classList.remove('active-row'); // Retire classe de surbrillance
        });
    });

    // ====== PARTIE 5: GESTION DU POPUP HISTORIQUE DES COMMANDES ======
    // Récupération des éléments pour la modale d'historique des commandes
    const orderHistoryBtn = document.getElementById('orderHistoryBtn');
    const orderHistoryModal = document.getElementById('orderHistoryModal');
    const closeOrderHistory = document.getElementById('closeOrderHistory');
    const orderHistoryContent = document.getElementById('orderHistoryContent');
    const orderDetailsSection = document.getElementById('orderDetailsSection');
    const orderListSection = document.getElementById('orderListSection');
    const backToOrderList = document.getElementById('backToOrderList');

    // Ouvre la modale d'historique des commandes
    if (orderHistoryBtn) {
        orderHistoryBtn.addEventListener('click', function() {
            orderHistoryModal.classList.remove('hidden'); // Affiche la modale
            document.body.style.overflow = 'hidden'; // Bloque le défilement de la page
        });
    }

    // Ferme la modale d'historique des commandes
    if (closeOrderHistory) {
        closeOrderHistory.addEventListener('click', function() {
            orderHistoryModal.classList.add('hidden'); // Masque la modale
            document.body.style.overflow = ''; // Réactive le défilement
        });
    }

    // Ferme la modale en cliquant à l'extérieur du contenu
    if (orderHistoryModal) {
        orderHistoryModal.addEventListener('click', function(e) {
            if (e.target === orderHistoryModal) {
                orderHistoryModal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    }

    // Configure les boutons pour voir les détails d'une commande spécifique
    document.querySelectorAll('.view-order-details').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            
            // Navigation entre les vues de liste et détail
            if (orderListSection) orderListSection.classList.add('hidden');
            if (orderDetailsSection) orderDetailsSection.classList.remove('hidden');
            
            // Affiche un indicateur de chargement
            if (orderDetailsSection) {
                orderDetailsSection.querySelector('#orderDetailsContent').innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin text-2xl text-yellow-500 mb-3"></i>
                        <p>Chargement des détails...</p>
                    </div>
                `;
                
                // Effectue une requête AJAX pour récupérer les détails
                fetch(`panier.php?order_id=${orderId}&ajax=1`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Affiche un message d'erreur si échec
                    if (data.error) {
                        orderDetailsSection.querySelector('#orderDetailsContent').innerHTML = `
                            <div class="bg-red-500 bg-opacity-20 border border-red-500 rounded-lg p-4 text-center">
                                <i class="fas fa-exclamation-circle text-red-500 text-xl mb-2"></i>
                                <p>${data.error}</p>
                            </div>`;
                        return;
                    }
                    
                    // Extraction des données de la commande
                    const order = data.order;
                    const items = data.items;
                    
                    // Détermine la classe de couleur selon le statut de la commande
                    let statusColorClass = 'bg-yellow-500'; // Statut par défaut
                    switch (order.status.toLowerCase()) {
                        case 'expédiée': statusColorClass = 'bg-blue-500'; break;
                        case 'livrée': statusColorClass = 'bg-green-500'; break;
                        case 'annulée': statusColorClass = 'bg-red-500'; break;
                        case 'confirmée': statusColorClass = 'bg-purple-500'; break;
                    }
                    
                    // Construction du HTML pour l'en-tête de la commande
                    let detailsHtml = `
                        <div class="bg-gray-700 rounded-lg border border-gray-600 mb-6">
                            <div class="p-4 border-b border-gray-600">
                                <div class="flex flex-col md:flex-row justify-between">
                                    <div>
                                        <h3 class="text-lg font-bold">Commande #${order.id}</h3>
                                        <p class="text-gray-400">${order.order_date}</p>
                                    </div>
                                    <div class="mt-2 md:mt-0">
                                        <span class="${statusColorClass} text-white text-sm px-3 py-1 rounded-full">
                                            ${order.status}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-4">
                                <h4 class="font-semibold mb-3">Articles commandés</h4>
                                <div class="space-y-4">
                    `;
                    
                    // Ajoute chaque article de la commande au HTML
                    items.forEach(item => {
                        detailsHtml += `
                            <div class="bg-gray-800 rounded-lg p-3 border border-gray-700">
                                <div class="flex flex-col md:flex-row">
                                    <div class="w-20 h-20 bg-gray-700 rounded-lg overflow-hidden flex-shrink-0 border border-gray-600 mr-4 mb-3 md:mb-0">
                                        <img src="../../../../ImagesMagasin/${item.image}" alt="${item.name}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-grow">
                                        <h5 class="font-medium">${item.name}</h5>
                                        <div class="flex flex-col md:flex-row md:justify-between mt-2">
                                            <div class="text-gray-400">
                                                <span>Prix: ${item.formatted_price}</span>
                                                <span class="mx-2">×</span>
                                                <span>${item.quantity}</span>
                                            </div>
                                            <div class="font-bold text-yellow-400 mt-2 md:mt-0">
                                                ${item.formatted_subtotal}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    // Ajoute le total de la commande
                    detailsHtml += `
                                </div>
                            </div>
                            
                            <div class="p-4 bg-gray-800 border-t border-gray-600">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">Total</span>
                                    <span class="font-bold text-yellow-500 text-lg">${order.formatted_total}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Insère le contenu généré dans la section des détails
                    orderDetailsSection.querySelector('#orderDetailsContent').innerHTML = detailsHtml;
                })
                .catch(error => {
                    // Affiche un message d'erreur en cas d'échec de la requête
                    orderDetailsSection.querySelector('#orderDetailsContent').innerHTML = `
                        <div class="bg-red-500 bg-opacity-20 border border-red-500 rounded-lg p-4 text-center">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl mb-2"></i>
                            <p>Erreur lors du chargement: ${error}</p>
                        </div>
                    `;
                });
            }
        });
    });

    // Retour à la liste des commandes depuis la vue détaillée
    if (backToOrderList) {
        backToOrderList.addEventListener('click', function() {
            if (orderDetailsSection) orderDetailsSection.classList.add('hidden');
            if (orderListSection) orderListSection.classList.remove('hidden');
        });
    }

    // ====== PARTIE 6: CONFIRMATION POUR VIDER LE PANIER ======
    // Demande confirmation avant de vider le panier
    const emptyCartForm = document.querySelector('form[name="empty_cart"]');
    if (emptyCartForm) {
        emptyCartForm.addEventListener('submit', function(e) {
            const confirmed = confirm('Êtes-vous sûr de vouloir vider votre panier ?');
            if (!confirmed) {
                e.preventDefault(); // Annule la soumission si non confirmé
            }
        });
    }
});

/**
 * Fonctions globales pour les contrôles de quantité 
 * (utilisables depuis les attributs onclick dans le HTML)
 */

/**
 * Diminue la quantité d'un produit (appelée depuis attribut onclick)
 * @param {HTMLElement} button - Le bouton de diminution cliqué
 */
function decrementQuantity(button) {
    const input = button.parentNode.querySelector('input[type="number"]');
    if (input && parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

/**
 * Augmente la quantité d'un produit (appelée depuis attribut onclick)
 * @param {HTMLElement} button - Le bouton d'augmentation cliqué
 */
function incrementQuantity(button) {
    const input = button.parentNode.querySelector('input[type="number"]');
    const maxValue = input.hasAttribute('max') ? parseInt(input.getAttribute('max')) : 99;
    
    if (input && parseInt(input.value) < maxValue) {
        input.value = parseInt(input.value) + 1;
    }
}