<?php
/**
 * Page de changement de mot de passe - ALLOPRO
 * 
 * Ce script permet à un utilisateur connecté de modifier son mot de passe
 * en respectant les critères de sécurité CNIL/RGPD:
 * - Vérification du mot de passe actuel
 * - Validation de la complexité du nouveau mot de passe
 * - Hashage sécurisé avant stockage en base de données
 */

// Configuration du débogage - Utile en développement, à désactiver en production
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclusion du fichier de configuration (contient la connexion à la base de données)
require_once('../../../config.php');

// Démarrage de la session pour accéder aux données utilisateur
session_start();

// Vérification de l'authentification - Redirection si non connecté
if (!isset($_SESSION['id'])) {
    header("Location: ../connexion/connexion.php");
    exit();
}

// Récupération et sécurisation des informations utilisateur de la session
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Utilisateur inconnu";
$role = isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : "Rôle non défini";

// Initialisation des variables de notification
$error = null;
$success = null;

// Traitement du formulaire soumis en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $current_password = $_POST['current_password'] ?? null;
    $new_password = $_POST['new_password'] ?? null;
    $confirm_password = $_POST['confirm_password'] ?? null;

    // Étape 1: Validation des champs requis
    if (!$current_password || !$new_password || !$confirm_password) {
        $error = "Veuillez remplir tous les champs.";
    } 
    // Étape 2: Vérification de la correspondance des nouveaux mots de passe
    elseif ($new_password !== $confirm_password) {
        $error = "Les nouveaux mots de passe ne correspondent pas.";
    } 
    // Étape 3: Validation de la complexité du nouveau mot de passe
    elseif (!isValidPassword($new_password)) {
        $error = "Le mot de passe doit contenir au moins 12 caractères, avec des minuscules, majuscules, chiffres et caractères spéciaux.";
    } 
    else {
        try {
            // Étape 4: Récupération du mot de passe actuel depuis la base de données
            $stmt = $pdo->prepare("SELECT pwd FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['id']]);
            $user = $stmt->fetch();

            // Étape 5: Vérification du mot de passe actuel avec password_verify (compare avec le hash)
            if ($user && password_verify($current_password, $user['pwd'])) {
                // Étape 6: Vérification que le nouveau mot de passe diffère de l'ancien
                if ($new_password === $current_password) {
                    $error = "Le nouveau mot de passe ne peut pas être identique à l'ancien.";
                } else {
                    // Étape 7: Hashage du nouveau mot de passe avant stockage
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                    // Étape 8: Mise à jour du mot de passe dans la base de données
                    $stmt = $pdo->prepare("UPDATE users SET pwd = ? WHERE id = ?");
                    if ($stmt->execute([$hashed_password, $_SESSION['id']])) {
                        // Succès: affichage d'un message de confirmation
                        $success = "Mot de passe modifié avec succès.";
                    } else {
                        // Erreur lors de la mise à jour en base de données
                        $error = "Erreur lors de la mise à jour du mot de passe.";
                    }
                }
            } else {
                // Mot de passe actuel incorrect
                $error = "Le mot de passe actuel est incorrect.";
            }
        } catch (PDOException $e) {
            // Erreur de connexion à la base de données
            $error = "Erreur de connexion à la base de données : " . $e->getMessage();
        }
    }
}

/**
 * Fonction de validation de la complexité du mot de passe selon CNIL/RGPD
 * 
 * @param string $password Le mot de passe à valider
 * @return bool True si le mot de passe respecte les critères, sinon False
 */
function isValidPassword($password) {
    return (strlen($password) >= 12 &&                  // Au moins 12 caractères
            preg_match('/[a-z]/', $password) &&         // Au moins une lettre minuscule
            preg_match('/[A-Z]/', $password) &&         // Au moins une lettre majuscule
            preg_match('/[0-9]/', $password) &&         // Au moins un chiffre
            preg_match('/[\W_]/', $password));          // Au moins un caractère spécial
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe - Allopro</title>
    <!-- Feuilles de style externes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../Images/iconVV.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Feuilles de style personnalisées -->
    <link rel="stylesheet" href="../../../css/index.css">
    <link rel="stylesheet" href="../../../css/navbar.css">
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
    
                <!-- Bouton Mon Compte (affiche le nom d'utilisateur) -->
                <div class="hidden md:flex items-center">
                    <a href="./moncompte.php" 
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
                <a href="../page/panier.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Votre Produit</a>
                <a href="../page/contact2.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Contact</a>
                <a href="./moncompte.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Mon Compte</a>
            </div>
        </div>
    </nav>

    <!-- Conteneur principal du formulaire avec marge pour la navbar fixe -->
    <div class="pt-32 pb-20">
        <div class="max-w-lg mx-auto px-4">
            <!-- Carte formulaire avec fond sombre et ombre -->
            <div class="bg-gray-800 rounded-lg shadow-xl p-8">
                <!-- En-tête avec titre et séparateur -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-white mb-4">Modifier votre mot de passe</h1>
                    <div class="w-16 h-1 bg-yellow-500 mx-auto opacity-50"></div>
                </div>

                <!-- Affichage des notifications (succès ou erreur) -->
                <?php if ($success): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                        <p><?= htmlspecialchars($success); ?></p>
                    </div>
                <?php elseif ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        <p><?= htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Formulaire de changement de mot de passe -->
                <form method="post" class="space-y-6">
                    <!-- Champ pour le mot de passe actuel -->
                    <div>
                        <label for="current_password" class="block text-gray-300 mb-2">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors" 
                               required>
                    </div>

                    <!-- Champ pour le nouveau mot de passe avec aide sur les critères -->
                    <div>
                        <label for="new_password" class="block text-gray-300 mb-2">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors" 
                               required>
                        <p class="text-gray-400 text-sm mt-2">Le mot de passe doit contenir au moins 12 caractères, des lettres minuscules, majuscules, des chiffres et des caractères spéciaux.</p>
                    </div>

                    <!-- Champ pour confirmer le nouveau mot de passe -->
                    <div>
                        <label for="confirm_password" class="block text-gray-300 mb-2">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-yellow-500 transition-colors" 
                               required>
                    </div>

                    <!-- Bouton de soumission du formulaire -->
                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                            Valider le changement
                        </button>
                    </div>
                </form>

                <!-- Lien de retour vers la page du compte -->
                <div class="mt-6 text-center">
                    <a href="./moncompte.php" class="text-gray-400 hover:text-yellow-500 transition-colors">Retour à mon compte</a>
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