<?php
/**
 * Page de connexion - ALLOPRO
 * 
 * Ce script gère l'authentification des utilisateurs avec:
 * - Validation des identifiants de connexion
 * - Limitation des tentatives de connexion (5 max)
 * - Système de timeout après échecs répétés (30 secondes)
 * - Option de réinitialisation du timeout
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
define('MAX_LOGIN_ATTEMPTS', 5);     // Nombre maximum de tentatives autorisées
define('LOCKOUT_TIME', 30);          // Durée de verrouillage en secondes (30 secondes)

// Vérification automatique de l'expiration du verrouillage au début de chaque chargement
if (isset($_SESSION['lockout_time']) && time() >= $_SESSION['lockout_time']) {
    // Le temps de verrouillage est expiré, on réinitialise
    unset($_SESSION['lockout_time']);
    unset($_SESSION['login_attempts']);
    
    // On réinitialise aussi dans la base de données si un identifiant est enregistré
    if (isset($_SESSION['last_login_attempt']) && !empty($_SESSION['last_login_attempt'])) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, last_attempt_time = NULL WHERE email = ? OR username = ?");
            $stmt->execute([$_SESSION['last_login_attempt'], $_SESSION['last_login_attempt']]);
        } catch (PDOException $e) {
            // Gérer l'erreur silencieusement
        }
    }
    
    $success = "Le verrouillage a expiré. Vous pouvez réessayer.";
}

// Traitement de la réinitialisation du timeout
if (isset($_POST['reset_timeout'])) {
    // Réinitialisation des variables de session
    unset($_SESSION['login_attempts']);
    unset($_SESSION['lockout_time']);
    
    // Si vous avez accès à la base de données, réinitialisez aussi les tentatives
    if (isset($_SESSION['last_login_attempt']) && !empty($_SESSION['last_login_attempt'])) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, last_attempt_time = NULL WHERE email = ? OR username = ?");
            $stmt->execute([$_SESSION['last_login_attempt'], $_SESSION['last_login_attempt']]);
        } catch (PDOException $e) {
            // Gérer l'erreur silencieusement
        }
    }
    
    $success = "Le verrouillage a été réinitialisé avec succès.";
}

// Stockons le dernier login tenté pour pouvoir réinitialiser plus tard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $_SESSION['last_login_attempt'] = $_POST['login'];
}

// Traitement du formulaire de connexion lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['reset_timeout'])) {
    // Vérification si l'IP/utilisateur est actuellement bloqué
    if (isset($_SESSION['lockout_time']) && $_SESSION['lockout_time'] > time()) {
        // Calcul du temps restant avant expiration du timeout
        $remaining_time = $_SESSION['lockout_time'] - time();
        $minutes = floor($remaining_time / 60);
        $seconds = $remaining_time % 60;
        
        $error = "Trop de tentatives échouées. Veuillez réessayer dans {$minutes}min {$seconds}s.";
    } else {
        // Récupération des données du formulaire avec sécurité
        $login = $_POST['login'] ?? null; // Peut être email ou username
        $password = $_POST['password'] ?? null;

        // Validation de base des champs requis
        if (!$login || !$password) {
            $error = "Veuillez remplir tous les champs.";
        } else {
            try {
                // Recherche de l'utilisateur par email ou nom d'utilisateur
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
                $stmt->execute([$login, $login]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Vérification si le temps de verrouillage en base de données est expiré
                    if ($user['login_attempts'] >= MAX_LOGIN_ATTEMPTS && $user['last_attempt_time'] !== null) {
                        $last_attempt_timestamp = strtotime($user['last_attempt_time']);
                        $lockout_expiry = $last_attempt_timestamp + LOCKOUT_TIME;
                        
                        if (time() >= $lockout_expiry) {
                            // Le verrouillage a expiré, on réinitialise
                            $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, last_attempt_time = NULL WHERE id = ?");
                            $stmt->execute([$user['id']]);
                            // Continuer avec la vérification du mot de passe
                        } elseif (time() < $lockout_expiry) {
                            // Toujours verrouillé
                            $remaining_time = $lockout_expiry - time();
                            $minutes = floor($remaining_time / 60);
                            $seconds = $remaining_time % 60;
                            
                            $_SESSION['lockout_time'] = $lockout_expiry;
                            $error = "Trop de tentatives échouées. Veuillez réessayer dans {$minutes}min {$seconds}s.";
                        }
                    }

                    // Si l'utilisateur n'est pas bloqué, on vérifie le mot de passe
                    if (!isset($error)) {
                        if (password_verify($password, $user['pwd'])) {
                            // Mot de passe correct - Connexion réussie
                            
                            // Réinitialisation des compteurs de tentatives
                            $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, last_attempt_time = NULL WHERE id = ?");
                            $stmt->execute([$user['id']]);
                            
                            // Création de la session utilisateur
                            $_SESSION['id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['role'] = $user['role'] ?? 'users';
                            
                            // Suppression des variables de blocage
                            unset($_SESSION['login_attempts']);
                            unset($_SESSION['lockout_time']);
                            
                            // Redirection vers la page d'accueil
                            header("Location: ../page/index2.php");
                            exit();
                        } else {
                            // Mot de passe incorrect - Augmentation du compteur de tentatives
                            $new_attempts = ($user['login_attempts'] ?? 0) + 1;
                            
                            // Mise à jour en base de données
                            $stmt = $pdo->prepare("UPDATE users SET login_attempts = ?, last_attempt_time = NOW() WHERE id = ?");
                            $stmt->execute([$new_attempts, $user['id']]);
                            
                            // Si le nombre max de tentatives est atteint, on bloque pour LOCKOUT_TIME secondes
                            if ($new_attempts >= MAX_LOGIN_ATTEMPTS) {
                                // Verrouillage pour LOCKOUT_TIME secondes
                                $_SESSION['lockout_time'] = time() + LOCKOUT_TIME;
                                $minutes = floor(LOCKOUT_TIME / 60);
                                $seconds = LOCKOUT_TIME % 60;
                                
                                $error = "Trop de tentatives échouées. Veuillez réessayer dans {$minutes}min {$seconds}s.";
                            } else {
                                $remaining_attempts = MAX_LOGIN_ATTEMPTS - $new_attempts;
                                $error = "Identifiants incorrects. Il vous reste {$remaining_attempts} tentative(s).";
                            }
                        }
                    }
                } else {
                    // Utilisateur non trouvé
                    // On incrémente quand même le compteur de tentatives au niveau de la session
                    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                    
                    if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
                        // Verrouillage pour LOCKOUT_TIME secondes
                        $_SESSION['lockout_time'] = time() + LOCKOUT_TIME;
                        $minutes = floor(LOCKOUT_TIME / 60);
                        $seconds = LOCKOUT_TIME % 60;
                        
                        $error = "Trop de tentatives échouées. Veuillez réessayer dans {$minutes}min {$seconds}s.";
                    } else {
                        $remaining_attempts = MAX_LOGIN_ATTEMPTS - $_SESSION['login_attempts'];
                        $error = "Identifiants incorrects. Il vous reste {$remaining_attempts} tentative(s).";
                    }
                }
            } catch (PDOException $e) {
                // Gestion des erreurs de base de données
                $error = "Erreur de connexion à la base de données : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ALLOPRO</title>
    <!-- Feuilles de style externes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Feuille de style personnalisée pour la connexion -->
    <link rel="stylesheet" href="../../../css/connexion.css">
</head>
<body class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- En-tête avec logo et titre -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-yellow-500 mb-2">ALLOPRO</h1>
            <h2 class="text-2xl font-semibold text-white">Connexion</h2>
            <p class="text-gray-400 mt-2">Accédez à votre espace personnel</p>
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
            
            <!-- Formulaire de connexion -->
            <form action="./connexion.php" method="post" class="space-y-6">
                <!-- Champ Identifiant avec icône -->
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-user text-yellow-500 mr-2"></i>Email ou nom d'utilisateur
                    </label>
                    <input type="text" id="login" name="login" required 
                           class="form-input" 
                           placeholder="Votre email ou nom d'utilisateur">
                </div>
                
                <!-- Champ Mot de passe avec icône -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-lock text-yellow-500 mr-2"></i>Mot de passe
                    </label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required 
                               class="form-input pr-10" 
                               placeholder="Votre mot de passe">
                        <!-- Icône pour afficher/masquer le mot de passe -->
                        <i class="password-toggle fas fa-eye" id="pwd-toggle"></i>
                    </div>
                </div>
                
                <!-- Option "Se souvenir de moi" et lien "Mot de passe oublié" -->
                <div class="flex items-center justify-between">
                </div>
                
                <!-- Bouton de connexion -->
                <div>
                    <button type="submit" class="button-primary w-full flex items-center justify-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
                    </button>
                </div>
            </form>
            
            <!-- Formulaire de réinitialisation du timeout (séparé) -->
            <?php if (isset($_SESSION['lockout_time']) && $_SESSION['lockout_time'] > time()): ?>
            <div class="mt-4">
                <form action="./connexion.php" method="post">
                    <input type="hidden" name="reset_timeout" value="1">
                    <button type="submit" class="button-secondary w-full flex items-center justify-center">
                        <i class="fas fa-unlock-alt mr-2"></i>Réinitialiser le verrouillage ( à enlever lors du déploiement )
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Bouton de retour (en dehors du formulaire) -->
            <div class="mt-4">
                <a href="../../offline/index.html" class="button-return">
                    <i class="fas fa-arrow-left"></i>Retour
                </a>
            </div>
            
            <!-- Lien vers la page d'inscription pour les nouveaux utilisateurs -->
            <div class="mt-8 text-center">
                <p class="text-gray-400">
                    Pas encore de compte ? 
                    <a href="./inscription.php" class="text-yellow-500 hover:text-yellow-400 font-medium transition-colors">
                        S'inscrire
                    </a>
                </p>
            </div>
        </div>
        
        <!-- Pied de page avec copyright -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>© 2025 ALLOPRO. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>