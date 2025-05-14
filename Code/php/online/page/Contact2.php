<?php
/**
 * Page de contact - ALLOPRO (version authentifiée)
 * 
 * Cette page permet aux utilisateurs connectés d'envoyer des messages
 * via un formulaire de contact avec choix de service et informations personnelles.
 */

// Configuration du débogage - Utile en développement
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session pour accéder aux informations de l'utilisateur
session_start();

// Inclusion du fichier de configuration de la base de données
require_once '../../../config.php'; // Chemin vers le fichier de configuration

// Initialisation de la variable d'erreur (utilisée si nécessaire pour afficher des messages)
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
    <title>Contactez-nous - Allopro</title>
    <!-- Feuilles de style externes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <!-- Feuille de style personnalisée -->
    <link rel="stylesheet" href="../../../css/index.css">
</head>
<body>
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

            <!-- Bouton de compte utilisateur avec nom (version desktop) -->
            <div class="hidden md:flex items-center">
                <a href="../compte/moncompte.php"
                   class="bg-yellow-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300">
                   <?= $username ?> <!-- Affiche le nom de l'utilisateur connecté -->
                </a>
            </div>

            <!-- Affichage mobile: nom d'utilisateur et bouton de menu -->
            <div class="md:hidden flex items-center space-x-3">
                <!-- Bouton compte utilisateur mobile avec nom -->
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

    <!-- Section principale avec formulaire de contact -->
    <section class="pt-32 pb-16 bg-gray-900">
        <div class="max-w-4xl mx-auto px-6">
            <!-- En-tête de la section contact -->
            <div class="text-center mb-16">
                <span class="text-yellow-500 text-lg font-medium tracking-wider mb-3 block">NOUS CONTACTER</span>
                <h2 class="text-4xl font-bold text-white mb-4">Parlons de votre projet</h2>
                <div class="w-20 h-1 bg-yellow-500 mx-auto opacity-50"></div>
            </div>

            <!-- Formulaire de contact avec effet glassmorphism -->
            <form action="../../index3.php" method="POST" class="bg-gray-800/50 p-8 rounded-lg shadow-lg space-y-6 backdrop-blur-sm">
                <!-- Sélection du service concerné -->
                <div>
                    <label for="service" class="block text-gray-300 font-medium mb-2">Service</label>
                    <select id="service" name="service" required 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-gray-100">
                        <option value="" disabled selected>Choisissez un service</option>
                        <option value="Plomberie">Plomberie</option>
                        <option value="Maçonnerie">Maçonnerie</option>
                        <option value="Peinture">Peinture</option>
                        <option value="Électricité">Électricité</option>
                        <option value="Carrelage">Carrelage</option>
                        <option value="Menuiserie">Menuiserie</option>
                        <option value="Serrurerie">Serrurerie</option>
                        <option value="Plâtrerie">Plâtrerie</option>
                        <option value="Climatisation">Climatisation</option>
                        <option value="Construction métallique">Construction métallique</option>
                        <option value="Rénovation générale">Rénovation générale</option>
                        <option value="Terrassement">Terrassement</option>
                        <option value="Couverture">Couverture</option>
                        <option value="Isolation">Isolation</option>
                        <option value="Autre">Autre ...</option>
                    </select>
                </div>
                
                <!-- Groupe de champs d'informations personnelles - grille responsive -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Champ Nom -->
                    <div>
                        <label for="name" class="block text-gray-300 font-medium mb-2">Nom complet</label>
                        <input type="text" id="name" name="name" required 
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-gray-100">
                    </div>
                    <!-- Champ Email -->
                    <div>
                        <label for="email" class="block text-gray-300 font-medium mb-2">E-mail</label>
                        <input type="email" id="email" name="email" required 
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-gray-100">
                    </div>
                    <!-- Champ Téléphone -->
                    <div>
                        <label for="phone" class="block text-gray-300 font-medium mb-2">Téléphone</label>
                        <input type="tel" id="phone" name="phone" required 
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-gray-100"
                               placeholder="+237 1 23 45 67 89">
                    </div>
                </div>
                
                <!-- Champ Sujet -->
                <div>
                    <label for="subject" class="block text-gray-300 font-medium mb-2">Sujet</label>
                    <input type="text" id="subject" name="subject" required 
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-gray-100">
                </div>
                
                <!-- Zone de texte pour le message -->
                <div>
                    <label for="message" class="block text-gray-300 font-medium mb-2">Message</label>
                    <textarea id="message" name="message" rows="5" required 
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-gray-100"></textarea>
                </div>
                
                <!-- Bouton d'envoi avec animation au survol -->
                <button type="submit" 
                        class="w-full bg-yellow-500 py-3 rounded-lg text-white font-bold hover:bg-yellow-600 transform hover:-translate-y-1 transition-all duration-300 shadow-lg">
                    Envoyer votre message
                </button>
            </form>
        </div>
    </section>

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
<!-- Script JavaScript pour la gestion du menu mobile -->
<script src="../../../js/navbarmobile.js"></script>
</html>