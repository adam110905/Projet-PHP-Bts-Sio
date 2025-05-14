<?php
/**
 * Page d'accueil - ALLOPRO (version authentifiée)
 * 
 * Cette page représente l'accueil principal du site pour les utilisateurs connectés.
 * Elle affiche une présentation de l'entreprise, ses expertises, ses services 
 * et ses partenaires avec une interface visuelle moderne et interactive.
 */

// Configuration du débogage - Utile en développement
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session pour accéder aux informations utilisateur
session_start();

// Inclusion de la configuration de la base de données
require_once '../../../config.php';

// Initialisation de la variable d'erreur (si nécessaire)
$error = null;

// Récupération et sécurisation du nom d'utilisateur depuis la session
// Protection contre les attaques XSS avec htmlspecialchars
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Utilisateur inconnu";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALLOPRO - Construction et Rénovation</title>
    
    <!-- Feuilles de style externes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.7/swiper-bundle.min.css" rel="stylesheet">
    
    <!-- Scripts externes -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.7/swiper-bundle.min.js"></script>
    
    <!-- Feuille de style personnalisée -->
    <link rel="stylesheet" href="../../../css/index.css">
</head>
<body class="font-sans antialiased">
    <!-- Barre de navigation principale avec effet de dégradé -->
    <nav class="gradient-nav fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <!-- Logo du site -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="../page/index2.php"><span class="text-3xl font-bold text-yellow-500">ALLOPRO</span></a>
                </div>
                
                <!-- Menu de navigation principal (version desktop) -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="../page/index2.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Accueil</a>
                    <a href="../page/produit.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Nos Produits</a>
                    <a href="../page/panier.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors relative">
                        Votre Panier
                    </a>
                    <a href="../page/Contact2.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Contact</a>
                </div>
                
                <!-- Bouton de profil utilisateur (version desktop) -->
                <div class="hidden md:flex items-center">
                    <a href="../compte/moncompte.php"
                       class="bg-yellow-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300">
                       <?= $username ?> <!-- Affiche le nom de l'utilisateur connecté -->
                    </a>
                </div>
                
                <!-- Affichage mobile avec bouton profil et hamburger -->
                <div class="md:hidden flex items-center space-x-3">
                    <!-- Profil utilisateur en version mobile -->
                    <a href="../compte/moncompte.php"
                       class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300 text-sm">
                       <?= $username ?> <!-- Affiche le nom de l'utilisateur connecté -->
                    </a>
                   
                    <!-- Bouton hamburger pour le menu mobile -->
                    <button id="mobile-menu-button" class="text-gray-100 hover:text-yellow-500 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path class="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path class="close-icon hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Menu mobile (masqué par défaut) -->
        <div id="mobile-menu" class="hidden md:hidden absolute w-full bg-gray-900 shadow-lg border-t border-gray-800">
            <div class="px-4 py-4 space-y-3">
                <a href="../page/index2.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Accueil</a>
                <a href="../page/produit.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Nos Produits</a>
                <a href="../page/panier.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors relative">
                    Votre Panier
                </a>
                <a href="../page/Contact2.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Contact</a>
            </div>
        </div>
    </nav>
    
    <!-- Overlay semi-transparent pour le fond quand le menu mobile est ouvert -->
    <div id="menu-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>
    
    <!-- Section Hero principale avec fond et effet visuel -->
    <section class="hero-section flex items-center justify-center relative overflow-hidden min-h-screen" id="test">
        <!-- Overlay sombre pour améliorer la lisibilité du texte -->
        <div class="absolute inset-0 bg-black opacity-30"></div>
        
        <!-- Cercle décoratif avec effet de flou -->
        <div class="absolute right-0 top-1/2 transform translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-yellow-500 rounded-full opacity-10 blur-3xl"></div>
   
        <!-- Contenu textuel central avec appel à l'action -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <!-- Titre avec animation de survol -->
            <span class="text-yellow-500 text-lg font-medium tracking-wider mb-8 block transform hover:scale-105 transition-transform cursor-default">
                ALLOPRO
            </span>
           
            <!-- Titre principal avec accent de couleur -->
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-8 leading-tight">
                Une Nouvelle Vision <br/>
                <span class="text-yellow-500">de la Construction</span>
            </h1>
           
            <!-- Sous-titre descriptif -->
            <p class="text-xl text-gray-300 mb-12 max-w-2xl mx-auto">
                Des projets innovants pour un avenir durable
            </p>
   
            <!-- Bouton d'appel à l'action avec animation au survol -->
            <a href="#test1" class="inline-block bg-yellow-500 text-white px-12 py-4 rounded-lg font-medium transform hover:-translate-y-1 transition-all duration-300 shadow-lg hover:shadow-xl hover:bg-yellow-400">
                Continuer
            </a>
        </div>
    </section>
    
    <!-- Section principale de contenu avec fond sombre -->
    <div class="bg-gray-900" id="test1">
        <!-- Section d'expertise avec cartes descriptives -->
        <section class="expertise-section py-20 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- En-tête de section -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold mb-4">Notre Expertise</h2>
                    <p class="text-xl text-gray-300">Une équipe de professionnels à votre service</p>
                </div>
                
                <!-- Grille des expertises - 4 colonnes sur desktop, 1 sur mobile -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Expertise 1: Gros Œuvre -->
                    <div class="expertise-card p-6 rounded-lg">
                        <!-- Icône circulaire jaune -->
                        <div class="icon-container w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-4">Gros Œuvre</h3>
                        <p class="text-gray-300">Fondations solides et structures durables pour votre projet</p>
                    </div>
                    
                    <!-- Expertise 2: Second Œuvre -->
                    <div class="expertise-card p-6 rounded-lg">
                        <div class="icon-container w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h10a2 2 0 012 2v12a4 4 0 01-4 4H7z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-4">Second Œuvre</h3>
                        <p class="text-gray-300">Finitions et aménagements intérieurs de qualité</p>
                    </div>
                    
                    <!-- Expertise 3: Rénovation -->
                    <div class="expertise-card p-6 rounded-lg">
                        <div class="icon-container w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-4">Rénovation</h3>
                        <p class="text-gray-300">Transformation et modernisation de vos espaces</p>
                    </div>
                    
                    <!-- Expertise 4: Extension -->
                    <div class="expertise-card p-6 rounded-lg">
                        <div class="icon-container w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-4">Extension</h3>
                        <p class="text-gray-300">Agrandissement et optimisation de l'espace</p>
                    </div>
                </div>
                
                <!-- Bouton d'appel à l'action pour découvrir les services -->
                <div class="text-center mt-12">
                    <button class="bg-yellow-500 text-white px-8 py-3 rounded-lg font-medium hover:bg-yellow-600 transform hover:-translate-y-1 transition-all duration-300 shadow-lg">
                        <a href="#Carousel">Découvrir Nos Services</a>
                    </button>
                </div>
            </div>
        </section>
        
        <!-- Section des services avec carousel Swiper -->
        <section class="services-section">
            <div class="container mx-auto px-4 py-16">
                <!-- En-tête de section -->
                <div class="text-center mb-16" id="Carousel">
                    <h2 class="text-4xl font-bold text-white mb-4">Nos Services</h2>
                    <p class="text-xl text-gray-300">Des solutions professionnelles adaptées à vos besoins</p>
                </div>
                
                <!-- Carousel Swiper pour la présentation des services -->
                <div class="swiper">
                    <div class="swiper-wrapper">
                        <!-- Slide 1: Gros Œuvre -->
                        <div class="swiper-slide">
                            <div class="service-card">
                                <!-- Effet glassmorphism -->
                                <div class="glass-effect"></div>
                                <!-- Image d'arrière-plan -->
                                <img src="../../../../IMAGES/menuserie.jpg" alt="Gros Œuvre" class="service-image">
                                <!-- Contenu du service avec description -->
                                <div class="service-content">
                                    <div class="icon-circle">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-bold text-white mb-3">Gros Œuvre</h3>
                                    <p class="text-gray-300 mb-6 leading-relaxed">
                                        Expertise complète en travaux de fondation et structure. Notre équipe assure des constructions robustes et durables.
                                    </p>
                                    <!-- Bouton de redirection vers la page portfolio -->
                                    <button href="../page/Portfolio2.php" class="service-button bg-yellow-500 text-white px-8 py-3 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300 shadow-lg">
                                        <a href="../page/Portfolio2.php">En savoir plus</a>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Slide 2: Second Œuvre (même structure) -->
                        <div class="swiper-slide">
                            <div class="service-card">
                                <div class="glass-effect"></div>
                                <img src="../../../../IMAGES/peintre.jpg" alt="Second Œuvre" class="service-image">
                                <div class="service-content">
                                    <div class="icon-circle">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h10a2 2 0 012 2v12a4 4 0 01-4 4H7z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-bold text-white mb-3">Second Œuvre</h3>
                                    <p class="text-gray-300 mb-6 leading-relaxed">
                                        Finitions impeccables et aménagements intérieurs de qualité. Nos artisans qualifiés apportent le souci du détail.
                                    </p>
                                    <button href="../page/Portfolio2.php" class="service-button bg-yellow-500 text-white px-8 py-3 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300 shadow-lg">
                                        <a href="../page/Portfolio2.php">En savoir plus</a>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Slide 3: Plomberie (même structure) -->
                        <div class="swiper-slide">
                            <div class="service-card">
                                <div class="glass-effect"></div>
                                <img src="../../../../IMAGES/plomberie.webp" alt="Plomberie" class="service-image">
                                <div class="service-content">
                                    <div class="icon-circle">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-bold text-white mb-3">Plomberie</h3>
                                    <p class="text-gray-300 mb-6 leading-relaxed">
                                        Installation et réparation de systèmes de plomberie. Solutions modernes et économiques pour votre confort.
                                    </p>
                                    <button href="../page/Portfolio2.php" class="service-button bg-yellow-500 text-white px-8 py-3 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300 shadow-lg">
                                        <a href="../page/Portfolio2.php">En savoir plus</a>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Slide 4: Électricité (même structure) -->
                        <div class="swiper-slide">
                            <div class="service-card">
                                <div class="glass-effect"></div>
                                <img src="../../../../IMAGES/éléctriciens.webp" alt="Électricité" class="service-image">
                                <div class="service-content">
                                    <div class="icon-circle">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-bold text-white mb-3">Électricité</h3>
                                    <p class="text-gray-300 mb-6 leading-relaxed">
                                        Installations électriques aux normes et dépannage rapide. Expertise en électricité générale et domotique.
                                    </p>
                                    <button href="../page/Portfolio2.php" class="service-button bg-yellow-500 text-white px-8 py-3 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300 shadow-lg">
                                        <a href="../page/Portfolio2.php">En savoir plus</a>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation et pagination du carousel -->
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
        </section>
        
        <!-- Section des partenaires avec logos interactifs -->
        <section class="py-24">
            <div class="max-w-7xl mx-auto px-4">
                <!-- En-tête de section -->
                <div class="text-center mb-16">
                    <span class="text-yellow-500 text-lg font-medium tracking-wider mb-3 block">NOS PARTENAIRES</span>
                    <h2 class="text-3xl font-bold text-white mb-4">Ils nous font confiance</h2>
                    <div class="w-20 h-1 bg-yellow-500 mx-auto opacity-50"></div>
                </div>
       
                <!-- Grille de logos des partenaires avec effet au survol -->
                <div class="flex flex-wrap justify-center items-center gap-x-16 gap-y-12">
                    <!-- Chaque logo a le même effet d'opacité et d'échelle au survol -->
                    <div class="w-40 h-24 flex items-center justify-center">
                        <img src="../../../../ImagesPartenaires/alucam.jpg"
                            alt="Logo partenaire"
                            class="max-h-16 w-auto opacity-50 hover:opacity-100 transition-all duration-300 hover:transform hover:scale-110">
                    </div>
                    
                    <!-- Répétition similaire pour tous les autres logos... -->
                    <div class="w-40 h-24 flex items-center justify-center">
                        <img src="../../../../ImagesPartenaires/ArabContractors.jpg"
                            alt="Logo partenaire"
                            class="max-h-16 w-auto opacity-50 hover:opacity-100 transition-all duration-300 hover:transform hover:scale-110">
                    </div>
                    
                    <!-- (Les autres logos suivent le même modèle) -->
                </div>
            </div>
        </section>
    </div>
    
    <!-- Pied de page avec informations de contact et liens -->
    <footer class="footer py-12 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Grille responsive à 4 colonnes -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Colonne 1: À propos -->
                <div>
                    <h3 class="text-2xl font-bold mb-6">ALLOPRO</h3>
                    <p class="text-gray-400">Votre partenaire de confiance pour tous vos projets de construction et rénovation.</p>
                </div>
                
                <!-- Colonne 2: Services -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Services</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li><a href="#" class="hover:text-yellow-500 transition-colors">Construction</a></li>
                        <li><a href="#" class="hover:text-yellow-500 transition-colors">Rénovation</a></li>
                        <li><a href="#" class="hover:text-yellow-500 transition-colors">Expertise</a></li>
                    </ul>
                </div>
                
                <!-- Colonne 3: Coordonnées -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Contact</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li>123 Rue de la Construction</li>
                        <li>75000 Paris, France</li>
                        <li>+33 1 23 45 67 89</li>
                        <li>contact@allopro.fr</li>
                    </ul>
                </div>
                
                <!-- Colonne 4: Réseaux sociaux -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Suivez-nous</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-yellow-500 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Copyright et mentions légales -->
            <div class="border-t border-gray-700 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; 2025 ALLOPRO. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
<!-- Scripts pour les fonctionnalités interactives -->
<script src="../../../js/index2.js"></script> <!-- Initialisation du carousel et animations -->
<script src="../../../js/navbarmobile.js"></script> <!-- Gestion du menu mobile -->
</html>