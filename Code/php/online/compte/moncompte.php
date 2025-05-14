<?php
/**
 * Page Mon Compte - ALLOPRO
 * 
 * Cette page affiche les informations de l'utilisateur connecté et
 * propose des options de gestion de compte adaptées à son rôle
 */

// Démarrage de la session pour accéder aux données utilisateur
session_start();

// Vérification de l'authentification - Redirection si non connecté
if (!isset($_SESSION['id'])) {
    // Sécurité: Protection contre l'accès non autorisé
    header('Location: ./connexion.php');
    exit(); // Arrête l'exécution du script après la redirection
}

// Récupération et sécurisation des informations utilisateur
// Utilisation de htmlspecialchars pour éviter les failles XSS
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Utilisateur inconnu";
$role = isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : "Rôle non défini";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - Allopro</title>
    <!-- Feuilles de style externes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../Images/iconVV.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Feuille de style personnalisée -->
    <link rel="stylesheet" href="../../../css/index.css">
</head>
<body class="bg-gray-900">
    <!-- Barre de navigation principale -->
    <nav class="gradient-nav fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <!-- Logo du site -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="../page/index2.php"><span class="text-3xl font-bold text-yellow-500">ALLOPRO</span></a>
                </div>
    
                <!-- Liens de navigation (version desktop) -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="../page/index2.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Accueil</a>
                    <a href="../page/produit.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Nos Produits</a>
                    <a href="../page/panier.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Votre Panier</a>
                    <a href="../page/contact2.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Contact</a>
                </div>
    
                <!-- Affichage du nom d'utilisateur (version desktop) -->
                <div class="hidden md:flex items-center">
                    <a href="#" 
                       class="bg-yellow-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300">
                       <?= $username ?>
                    </a>
                </div>
    
                <!-- Bouton du menu mobile -->
                <div class="md:hidden flex items-center">
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
        <div id="mobile-menu" class="mobile-menu hidden md:hidden absolute w-full menu-backdrop">
            <div class="px-4 py-4 space-y-3">
                <a href="../page/index2.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Accueil</a>
                <a href="../page/produit.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Nos Produits</a>
                <a href="../page/panier.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Votre Panier</a>
                <a href="../page/Contact2.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Contenu principal de la page (avec marge pour la navbar fixe) -->
    <div class="pt-32 pb-20">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Carte profil utilisateur -->
            <div class="bg-gray-800 rounded-lg shadow-xl p-8">
                <!-- En-tête avec titre et séparateur -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-white mb-4">Mon Compte</h1>
                    <div class="w-16 h-1 bg-yellow-500 mx-auto opacity-50"></div>
                </div>
                
                <!-- Affichage du profil avec avatar (première lettre du nom) -->
                <div class="text-center mb-6">
                    <!-- Avatar circulaire avec l'initiale de l'utilisateur -->
                    <div class="w-24 h-24 rounded-full bg-yellow-500 flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-bold text-white"><?= substr($username, 0, 1) ?></span>
                    </div>
                    <p class="text-xl text-white">Bienvenue, <strong class="text-yellow-500"><?= $username ?></strong> !</p>
                    <p class="text-gray-400 mt-2">Votre rôle : <span class="font-medium text-yellow-500"><?= $role ?></span></p>
                </div>
                
                <!-- Options du compte (différentes selon le rôle) -->
                <div class="flex flex-col space-y-4 items-center mt-8">
                    <?php if ($role === 'admin'): ?>
                        <!-- Bouton spécial pour les administrateurs -->
                        <a href="./admin.php" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 w-full max-w-xs text-center">
                            Mode Administrateur
                        </a>
                    <?php endif; ?>
                    
                    <!-- Bouton de changement de mot de passe (pour tous les utilisateurs) -->
                    <a href="./modifmdp.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 w-full max-w-xs text-center">
                        Changer le mot de passe
                    </a>
                    
                    <!-- Bouton de déconnexion -->
                    <a href="../../offline/index.html" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 w-full max-w-xs text-center">
                        Se déconnecter
                    </a>
                </div>
            </div>
        </div>
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
    <!-- Référence au script JavaScript pour la gestion du menu mobile -->
    <script src="../../../js/navbar.js"></script>
</body>
</html>