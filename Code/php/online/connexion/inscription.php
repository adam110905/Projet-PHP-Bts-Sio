<?php
/**
 * Page d'inscription - ALLOPRO
 * 
 * Ce script gère la création de nouveaux comptes utilisateur avec:
 * - Validation complète des champs du formulaire
 * - Vérification de la complexité du mot de passe (normes CNIL/RGPD)
 * - Contrôle de l'unicité de l'email et du nom d'utilisateur
 * - Hashage sécurisé des mots de passe avant stockage
 * - Limitation des tentatives de connexion
 * - Système de timeout après échecs répétés
 */

// Activation du débogage en développement (à désactiver en production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclusion de la configuration de connexion à la base de données
require_once('../../../config.php');

// Démarrage de la session pour stocker les informations utilisateur
session_start();

// Initialisation des variables pour les messages de feedback
$error = null;   // Stocke les messages d'erreur
$success = null; // Stocke les messages de succès

// Configuration des limites de tentatives de connexion
define('MAX_LOGIN_ATTEMPTS', 5);    // Nombre maximum de tentatives autorisées
define('LOCKOUT_TIME', 15 * 60);    // Durée de verrouillage en secondes (15 minutes)

// Vérification si l'utilisateur est actuellement en période de timeout
if (isset($_SESSION['lockout_time']) && $_SESSION['lockout_time'] > time()) {
    // Calcul du temps restant avant expiration du timeout
    $remaining_time = $_SESSION['lockout_time'] - time();
    $minutes = floor($remaining_time / 60);
    $seconds = $remaining_time % 60;
    
    $error = "Trop de tentatives échouées. Veuillez réessayer dans {$minutes}min {$seconds}s.";
}

// Traitement du formulaire d'inscription lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si l'utilisateur est en timeout, on ne traite pas la soumission
    if (isset($_SESSION['lockout_time']) && $_SESSION['lockout_time'] > time()) {
        // Ne rien faire, le message d'erreur est déjà défini ci-dessus
    } else {
        // Récupération des données du formulaire avec sécurité (null si non défini)
        $username = $_POST['username'] ?? null;
        $email = $_POST['email'] ?? null;
        $password = $_POST['pwd'] ?? null;
        $confirm_password = $_POST['confirm_password'] ?? null;

        // Étape 1: Validation de base des champs requis
        if (!$username || !$email || !$password || !$confirm_password) {
            $error = "Veuillez remplir tous les champs.";
        } 
        // Étape 2: Vérification de la correspondance des mots de passe
        elseif ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas.";
        } 
        // Étape 3: Validation de la complexité du mot de passe
        elseif (!isValidPassword($password)) {
            $error = "Le mot de passe doit contenir au moins 12 caractères, avec des minuscules, majuscules, chiffres, et caractères spéciaux.";
        } 
        else {
            try {
                // Étape 4: Vérification que l'email et le nom d'utilisateur sont uniques
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                $count = $stmt->fetchColumn(); // Récupère le nombre d'occurrences trouvées

                // Si un compte existe déjà avec ces identifiants
                if ($count > 0) {
                    $error = "Nom d'utilisateur ou email déjà utilisé.";
                } else {
                    // Étape 5: Hashage sécurisé du mot de passe avant stockage
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                    // Étape 6: Insertion du nouvel utilisateur dans la base de données
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, pwd, login_attempts, last_attempt_time) VALUES (?, ?, ?, 0, NULL)");
                    if ($stmt->execute([$username, $email, $hashed_password])) {
                        // Récupération de l'ID généré pour le nouvel utilisateur
                        $user_id = $pdo->lastInsertId();

                        // Étape 7: Création de la session utilisateur après inscription réussie
                        $_SESSION['id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = 'users'; // Attribution du rôle par défaut
                        
                        // Réinitialisation des compteurs de tentatives de connexion
                        $_SESSION['login_attempts'] = 0;
                        unset($_SESSION['lockout_time']);

                        // Redirection vers la page d'accueil
                        header("Location: ../page/index2.php");
                        exit();
                    } else {
                        // Échec de l'insertion en base de données
                        $error = "Erreur lors de l'inscription.";
                    }
                }
            } catch (PDOException $e) {
                // Gestion des erreurs de base de données
                $error = "Erreur de connexion à la base de données : " . $e->getMessage();
            }
        }
    }
}

/**
 * Fonction de validation de la complexité du mot de passe selon CNIL/RGPD
 * 
 * @param string $password Le mot de passe à valider
 * @return bool True si le mot de passe respecte tous les critères, sinon False
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
    <title>Inscription - ALLOPRO</title>
    <!-- Feuilles de style externes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Feuille de style personnalisée pour l'inscription -->
    <link rel="stylesheet" href="../../../css/inscription.css">
</head>
<body class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- En-tête avec logo et titre -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-yellow-500 mb-2">ALLOPRO</h1>
            <h2 class="text-2xl font-semibold text-white">Créez votre compte</h2>
            <p class="text-gray-400 mt-2">Rejoignez notre communauté de professionnels du BTP</p>
        </div>
        
        <!-- Carte principale avec effet de dégradé -->
        <div class="gradient-card rounded-lg overflow-hidden shadow-2xl border border-gray-700 p-8">
            
            <!-- Affichage des messages d'erreur -->
            <?php if ($error): ?>
            <div class="notification notification-error mb-6">
                <div class="icon-container">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                </div>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Affichage des messages de succès -->
            <?php if ($success): ?>
            <div class="notification notification-success mb-6">
                <div class="icon-container">
                    <i class="fas fa-check-circle text-green-500"></i>
                </div>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Formulaire d'inscription -->
            <form action="./inscription.php" method="post" class="space-y-6">
                <!-- Champ Email avec icône -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-envelope text-yellow-500 mr-2"></i>Adresse email
                    </label>
                    <input type="email" id="email" name="email" required 
                           class="form-input" 
                           placeholder="exemple@email.com">
                </div>
                
                <!-- Champ Nom d'utilisateur avec icône -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-user text-yellow-500 mr-2"></i>Nom d'utilisateur
                    </label>
                    <input type="text" id="username" name="username" required 
                           class="form-input" 
                           placeholder="Votre nom d'utilisateur">
                </div>
                
                <!-- Champ Mot de passe avec icône et indicateur de force -->
                <div>
                    <label for="pwd" class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-lock text-yellow-500 mr-2"></i>Mot de passe
                    </label>
                    <div class="password-container">
                        <input type="password" id="pwd" name="pwd" required 
                               class="form-input pr-10" 
                               placeholder="Votre mot de passe">
                        <!-- Icône pour afficher/masquer le mot de passe -->
                        <i class="password-toggle fas fa-eye" id="pwd-toggle"></i>
                    </div>
                    <!-- Indicateur de force du mot de passe (rempli par JavaScript) -->
                    <div class="password-strength" id="password-strength"></div>
                </div>
                
                <!-- Champ Confirmation du mot de passe avec icône -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-check-circle text-yellow-500 mr-2"></i>Confirmer le mot de passe
                    </label>
                    <div class="password-container">
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               class="form-input pr-10" 
                               placeholder="Confirmez votre mot de passe">
                        <!-- Icône pour afficher/masquer le mot de passe -->
                        <i class="password-toggle fas fa-eye" id="confirm-pwd-toggle"></i>
                    </div>
                </div>
                
                <!-- Aide visuelle sur les critères de sécurité du mot de passe -->
                <div class="bg-gray-800 bg-opacity-50 rounded-lg p-4 text-sm text-gray-300 border border-gray-700">
                    <p class="font-medium mb-2"><i class="fas fa-shield-alt text-yellow-500 mr-2"></i>Votre mot de passe doit :</p>
                    <ul class="space-y-1 pl-6">
                        <!-- Liste des critères avec indicateurs d'état (mis à jour par JavaScript) -->
                        <li id="length-check" class="flex items-center">
                            <i class="fas fa-circle text-xs mr-2 text-gray-500"></i>
                            Contenir au moins 12 caractères
                        </li>
                        <li id="lowercase-check" class="flex items-center">
                            <i class="fas fa-circle text-xs mr-2 text-gray-500"></i>
                            Inclure au moins une lettre minuscule
                        </li>
                        <li id="uppercase-check" class="flex items-center">
                            <i class="fas fa-circle text-xs mr-2 text-gray-500"></i>
                            Inclure au moins une lettre majuscule
                        </li>
                        <li id="number-check" class="flex items-center">
                            <i class="fas fa-circle text-xs mr-2 text-gray-500"></i>
                            Inclure au moins un chiffre
                        </li>
                        <li id="special-check" class="flex items-center">
                            <i class="fas fa-circle text-xs mr-2 text-gray-500"></i>
                            Inclure au moins un caractère spécial
                        </li>
                    </ul>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex gap-4 pt-2">
                    <!-- Bouton d'inscription -->
                    <button type="submit" class="button-primary flex-1 flex items-center justify-center">
                        <i class="fas fa-user-plus mr-2"></i>S'inscrire
                    </button>
                    <!-- Bouton de génération automatique de mot de passe -->
                    <button type="button" id="generate-password" class="button-secondary flex-1 flex items-center justify-center">
                        <i class="fas fa-key mr-2"></i>Générer un mot de passe
                    </button>
                </div>
            </form>
            
            <!-- Lien vers la page de connexion pour les utilisateurs déjà inscrits -->
            <div class="mt-8 text-center">
                <p class="text-gray-400">
                    Déjà inscrit ? 
                    <a href="./connexion.php" class="text-yellow-500 hover:text-yellow-400 font-medium transition-colors">
                        Se connecter
                    </a>
                </p>
            </div>
        </div>
        
        <!-- Pied de page avec copyright -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>© 2025 ALLOPRO. Tous droits réservés.</p>
        </div>
    </div>

<!-- Script JavaScript pour la validation interactive du mot de passe et autres fonctionnalités -->
<script src="../../../js/inscription.js"></script>
</body>
</html>