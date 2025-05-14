<?php
/**
 * Page d'administration ALLOPRO
 * 
 * Ce script gère le tableau de bord administrateur avec:
 * - Gestion complète des utilisateurs (ajout, blocage, suppression)
 * - Tableau de bord avec statistiques
 * - Interface responsive pour desktop et mobile
 * - Sécurisation selon les standards CNIL/RGPD
 */

// Configuration de l'environnement de développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrage de la session pour la gestion de l'authentification
session_start();

// Vérification des permissions - restriction aux administrateurs uniquement
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    // Redirection vers la page de connexion si accès non autorisé
    header('Location: ../connexion/connexion.php');
    exit();
}

// Inclusion de la configuration de base de données
require_once('../../../config.php');

// Récupération des informations de l'utilisateur connecté
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Admin";

// Initialisation des messages de notification
$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error_message = '';

/**
 * Fonction de validation du mot de passe selon les normes CNIL/RGPD
 * Vérifie la complexité minimale requise pour un mot de passe sécurisé
 * 
 * @param string $password Le mot de passe à valider
 * @return string Message d'erreur ou chaîne vide si valide
 */
function validatePassword($password) {
    // Vérification de la longueur minimale (12 caractères)
    if (strlen($password) < 12) {
        return "Le mot de passe doit contenir au moins 12 caractères";
    }
    
    // Vérification de la présence d'au moins une majuscule
    if (!preg_match('/[A-Z]/', $password)) {
        return "Le mot de passe doit contenir au moins une lettre majuscule";
    }
    
    // Vérification de la présence d'au moins une minuscule
    if (!preg_match('/[a-z]/', $password)) {
        return "Le mot de passe doit contenir au moins une lettre minuscule";
    }
    
    // Vérification de la présence d'au moins un chiffre
    if (!preg_match('/[0-9]/', $password)) {
        return "Le mot de passe doit contenir au moins un chiffre";
    }
    
    // Vérification de la présence d'au moins un caractère spécial
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return "Le mot de passe doit contenir au moins un caractère spécial";
    }
    
    return ""; // Retourne une chaîne vide si le mot de passe est valide
}

// GESTIONNAIRE DE REQUÊTES POST - Traitement des actions administratives
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Traitement de l'ajout d'un nouvel utilisateur
    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        handleAddUser($pdo);
    }
    
    // Traitement du blocage/déblocage d'utilisateur
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
        handleToggleStatus($pdo);
    }
    
    // Traitement de la suppression d'utilisateur
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        handleDeleteUser($pdo);
    }
}

/**
 * Gère l'ajout d'un nouvel utilisateur
 * 
 * @param PDO $pdo Instance de connexion à la base de données
 */
function handleAddUser($pdo) {
    global $error_message, $success_message;
    
    // Récupération et nettoyage des données du formulaire
    $newEmail = trim($_POST['email']);
    $newUsername = trim($_POST['username']);
    $newPassword = trim($_POST['password']);
    $newRole = trim($_POST['role']);
    
    // Validation de base (champs obligatoires)
    if (empty($newEmail) || empty($newUsername) || empty($newPassword)) {
        $error_message = "Tous les champs sont obligatoires";
        return;
    }
    
    // Validation de la complexité du mot de passe (CNIL/RGPD)
    $password_error = validatePassword($newPassword);
    
    if (!empty($password_error)) {
        $error_message = $password_error;
        return;
    }
    
    // Vérification de l'unicité de l'email
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$newEmail]);
    if ($stmt->fetchColumn() > 0) {
        $error_message = "Cet email est déjà utilisé";
        return;
    }
    
    // Hachage sécurisé du mot de passe avant stockage
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Insertion du nouvel utilisateur en base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, pwd, username, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$newEmail, $hashedPassword, $newUsername, $newRole]);
        
        $success_message = "Utilisateur ajouté avec succès";
        
        // Redirection pour éviter les soumissions multiples (pattern PRG)
        header("Location: admin.php?success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Erreur lors de l'ajout de l'utilisateur: " . $e->getMessage();
    }
}

/**
 * Gère le changement de statut d'un utilisateur (blocage/déblocage)
 * 
 * @param PDO $pdo Instance de connexion à la base de données
 */
function handleToggleStatus($pdo) {
    global $success_message;
    
    $userId = $_POST['user_id'];
    $newStatus = $_POST['status']; // 'active' ou 'blocked'
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $userId]);
        
        $action = $newStatus === 'blocked' ? 'bloqué' : 'débloqué';
        $success_message = "Utilisateur $action avec succès";
        
        header("Location: admin.php?success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la mise à jour du statut: " . $e->getMessage();
    }
}

/**
 * Gère la suppression d'un utilisateur
 * 
 * @param PDO $pdo Instance de connexion à la base de données
 */
function handleDeleteUser($pdo) {
    global $success_message;
    
    $userId = $_POST['user_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        $success_message = "Utilisateur supprimé avec succès";
        
        header("Location: admin.php?success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// RÉCUPÉRATION DES DONNÉES POUR AFFICHAGE
// Récupération de la liste complète des utilisateurs avec formatage des dates
try {
    $stmt = $pdo->query("SELECT id, email, username, role, DATE_FORMAT(date, '%d/%m/%Y %H:%i') as formatted_date, 
                     IFNULL(status, 'active') as status 
                     FROM users ORDER BY date DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des utilisateurs: " . $e->getMessage();
    $users = []; // Initialisation d'un tableau vide en cas d'erreur
}

// Récupération des statistiques pour le tableau de bord
try {
    // Nombre total d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Nombre d'administrateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $total_admins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Nombre de professionnels
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'professionnel'");
    $total_pros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Nombre de clients
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'client'");
    $total_clients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Utilisateurs bloqués
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'blocked'");
    $blocked_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
    // Initialisation des variables statistiques à zéro en cas d'erreur
    $total_users = $total_admins = $total_pros = $total_clients = $blocked_users = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panneau d'Administration - ALLOPRO</title>
    <!-- Feuilles de style externes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Feuilles de style personnalisées -->
    <link rel="stylesheet" href="../../../css/index.css">
    <link rel="stylesheet" href="../../../css/admin.css">
</head>
<body class="bg-gray-900 text-white flex flex-col min-h-screen">
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
                    <a href="../page/Contact2.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Contact</a>
                </div>
    
                <!-- Bouton Mon Compte (version desktop) -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="../compte/moncompte.php" 
                       class="bg-yellow-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300">
                        Mon Compte
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
        <div id="mobile-menu" class="hidden md:hidden absolute w-full bg-gray-900 shadow-lg border-t border-gray-800">
            <div class="px-4 py-4 space-y-3">
                <a href="../page/index2.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Accueil</a>
                <a href="../page/produit.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Nos Produits</a>
                <a href="../page/panier.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Votre Panier</a>
                <a href="../page/Contact2.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Contact</a>
                <a href="../compte/moncompte.php" class="block text-gray-100 hover:text-yellow-500 font-medium px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">Mon Compte</a>
            </div>
        </div>
    </nav>

    <!-- Bouton de toggle pour la sidebar sur mobile (fixé en bas à droite) -->
    <div class="fixed bottom-6 right-6 md:hidden z-50">
        <button id="sidebar-toggle" class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-full p-3 shadow-lg transition-all">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Layout principal d'administration (conteneur flex) -->
    <div class="pt-20 flex flex-1">
        <!-- Sidebar de navigation administrative -->
        <aside id="sidebar" class="admin-sidebar w-64 bg-gray-800 shadow-lg text-gray-300">
            <!-- Profil de l'administrateur connecté -->
            <div class="p-4 border-b border-gray-700">
                <div class="flex items-center space-x-3">
                    <!-- Avatar avec initiale -->
                    <div class="w-10 h-10 rounded-full bg-yellow-500 flex items-center justify-center">
                        <span class="text-xl font-bold text-white"><?= substr($username, 0, 1) ?></span>
                    </div>
                    <div>
                        <p class="font-semibold text-white"><?= $username ?></p>
                        <p class="text-xs text-gray-400">Administrateur</p>
                    </div>
                </div>
            </div>
            
            <!-- Menu de navigation administrative -->
            <nav class="mt-4">
                <div class="px-4 py-2 text-xs text-gray-400 uppercase tracking-wider">
                    Administration
                </div>
                <!-- Liens vers les sections avec icônes -->
                <a href="#dashboard" class="flex items-center px-4 py-3 text-gray-200 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="#users" class="flex items-center px-4 py-3 text-gray-200 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-users w-5 mr-3"></i>
                    <span>Gestion des utilisateurs</span>
                </a>
                <a href="#add-user" class="flex items-center px-4 py-3 text-gray-200 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-user-plus w-5 mr-3"></i>
                    <span>Ajouter un utilisateur</span>
                </a>
                
                <!-- Lien de déconnexion -->
                <div class="mt-8 px-4 py-4 border-t border-gray-700">
                    <a href="../../offline/index.html" class="flex items-center text-red-400 hover:text-red-300">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Contenu principal de l'administration -->
        <main class="admin-content flex-1 overflow-y-auto pb-10">
            <!-- Notifications d'état (succès/erreur) -->
            <?php if (!empty($success_message)): ?>
            <div id="success-alert" class="mb-4 bg-green-500 text-white px-4 py-3 rounded relative">
                <span class="block sm:inline"><?= $success_message ?></span>
                <button onclick="this.parentElement.remove()" class="absolute top-0 right-0 px-4 py-3">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div id="error-alert" class="mb-4 bg-red-500 text-white px-4 py-3 rounded relative">
                <span class="block sm:inline"><?= $error_message ?></span>
                <button onclick="this.parentElement.remove()" class="absolute top-0 right-0 px-4 py-3">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>

            <!-- Section 1: Tableau de bord avec statistiques -->
            <section id="dashboard" class="mb-10">
                <h2 class="text-2xl font-bold mb-6 text-white flex items-center">
                    <i class="fas fa-tachometer-alt mr-3 text-yellow-500"></i>
                    Tableau de bord
                </h2>
                
                <!-- Cartes statistiques en grille responsive -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    <!-- Carte 1: Total utilisateurs -->
                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-gray-400 text-sm">Total utilisateurs</p>
                                <p class="text-3xl font-bold text-white"><?= $total_users ?></p>
                            </div>
                            <div class="h-12 w-12 bg-blue-500 bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-blue-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Carte 2: Administrateurs -->
                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-gray-400 text-sm">Administrateurs</p>
                                <p class="text-3xl font-bold text-white"><?= $total_admins ?></p>
                            </div>
                            <div class="h-12 w-12 bg-purple-500 bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-shield text-purple-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Carte 3: Professionnels -->
                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-gray-400 text-sm">Professionnels</p>
                                <p class="text-3xl font-bold text-white"><?= $total_pros ?></p>
                            </div>
                            <div class="h-12 w-12 bg-green-500 bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-hard-hat text-green-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Carte 4: Clients -->
                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-gray-400 text-sm">Clients</p>
                                <p class="text-3xl font-bold text-white"><?= $total_clients ?></p>
                            </div>
                            <div class="h-12 w-12 bg-yellow-500 bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-yellow-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Carte 5: Utilisateurs bloqués -->
                    <div class="card-gradient rounded-lg shadow-lg p-6">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-gray-400 text-sm">Utilisateurs bloqués</p>
                                <p class="text-3xl font-bold text-white"><?= $blocked_users ?></p>
                            </div>
                            <div class="h-12 w-12 bg-red-500 bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-lock text-red-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 2: Gestion des utilisateurs (tableau) -->
            <section id="users" class="mb-10">
                <h2 class="text-2xl font-bold mb-6 text-white flex items-center">
                    <i class="fas fa-users mr-3 text-yellow-500"></i>
                    Gestion des utilisateurs
                </h2>
                
                <!-- Tableau des utilisateurs avec bordures et style cohérent -->
                <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-gray-800">
                            <!-- En-tête du tableau -->
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 border-b border-gray-700 bg-gray-800 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 border-b border-gray-700 bg-gray-800 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nom d'utilisateur</th>
                                    <th class="px-6 py-3 border-b border-gray-700 bg-gray-800 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 border-b border-gray-700 bg-gray-800 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Rôle</th>
                                    <th class="px-6 py-3 border-b border-gray-700 bg-gray-800 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date d'inscription</th>
                                    <th class="px-6 py-3 border-b border-gray-700 bg-gray-800 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 border-b border-gray-700 bg-gray-800 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <!-- Corps du tableau avec données dynamiques -->
                            <tbody class="divide-y divide-gray-700">
                                <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?= $user['id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?= htmlspecialchars($user['username']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        // Badge coloré selon le rôle
                                        $roleBadgeClass = 'bg-gray-500';
                                        switch ($user['role']) {
                                            case 'admin':
                                                $roleBadgeClass = 'bg-purple-500';
                                                break;
                                            case 'professionnel':
                                                $roleBadgeClass = 'bg-green-500';
                                                break;
                                            case 'client':
                                                $roleBadgeClass = 'bg-blue-500';
                                                break;
                                        }
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $roleBadgeClass ?> text-white">
                                            <?= htmlspecialchars($user['role']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?= $user['formatted_date'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <!-- Badge de statut (vert/rouge) -->
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $user['status'] === 'active' ? 'bg-green-500' : 'bg-red-500' ?> text-white">
                                            <?= $user['status'] === 'active' ? 'Actif' : 'Bloqué' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($user['role'] !== 'admin' || $_SESSION['id'] != $user['id']): ?>
                                            <!-- Boutons d'actions (bloquer/débloquer et supprimer) -->
                                            <div class="flex space-x-2">
                                                <!-- Formulaire pour basculer le statut -->
                                                <form method="post" class="inline-block">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="status" value="<?= $user['status'] === 'active' ? 'blocked' : 'active' ?>">
                                                    <button type="submit" class="text-yellow-500 hover:text-yellow-400" title="<?= $user['status'] === 'active' ? 'Bloquer' : 'Débloquer' ?>">
                                                        <i class="fas <?= $user['status'] === 'active' ? 'fa-user-lock' : 'fa-user-check' ?>"></i>
                                                    </button>
                                                </form>
                                                
                                                <!-- Formulaire pour supprimer l'utilisateur (avec confirmation) -->
                                                <form method="post" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="text-red-500 hover:text-red-400" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <!-- Message si l'utilisateur ne peut pas être modifié (admin actuel) -->
                                            <span class="text-gray-500">Non disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <!-- Message si aucun utilisateur n'est trouvé -->
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-400">Aucun utilisateur trouvé</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            
            <!-- Section 3: Formulaire d'ajout d'utilisateur -->
            <section id="add-user" class="mb-10">
                <h2 class="text-2xl font-bold mb-6 text-white flex items-center">
                    <i class="fas fa-user-plus mr-3 text-yellow-500"></i>
                    Ajouter un utilisateur
                </h2>
                
                <!-- Conteneur du formulaire avec fond et ombre -->
                <div class="bg-gray-800 rounded-lg shadow-lg p-6">
                    <!-- Encart informatif sur les règles de sécurité CNIL/RGPD -->
                    <div class="mb-4 bg-blue-600 bg-opacity-20 text-blue-300 p-4 rounded-lg border border-blue-600">
                        <h3 class="font-semibold mb-2 flex items-center">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Règles de sécurité du mot de passe (CNIL/RGPD)
                        </h3>
                        <ul class="ml-6 text-sm list-disc">
                            <li>Minimum 12 caractères</li>
                            <li>Au moins une lettre majuscule</li>
                            <li>Au moins une lettre minuscule</li>
                            <li>Au moins un chiffre</li>
                            <li>Au moins un caractère spécial</li>
                        </ul>
                    </div>
                    
                    <!-- Formulaire d'ajout d'utilisateur -->
                    <form method="post" class="space-y-4">
                        <input type="hidden" name="action" value="add_user">
                        
                        <!-- Champs nom et email en ligne sur desktop, empilés sur mobile -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="username" class="block text-gray-300 mb-2">Nom d'utilisateur</label>
                                <input type="text" id="username" name="username" required
                                    class="w-full px-4 py-2 rounded-lg border border-gray-700 bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-gray-300 mb-2">Email</label>
                                <input type="email" id="email" name="email" required
                                    class="w-full px-4 py-2 rounded-lg border border-gray-700 bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                        </div>
                        
                        <!-- Champ mot de passe -->
                        <div>
                            <label for="password" class="block text-gray-300 mb-2">Mot de passe</label>
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-700 bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                        
                        <!-- Sélecteur de rôle -->
                        <div>
                            <label for="role" class="block text-gray-300 mb-2">Rôle</label>
                            <select id="role" name="role" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-700 bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <option value="client">Client</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        
                        <!-- Bouton de soumission -->
                        <div class="mt-6">
                            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors flex items-center">
                                <i class="fas fa-user-plus mr-2"></i>
                                Ajouter l'utilisateur
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <!-- Pied de page global -->
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
            
            <!-- Copyright -->
            <div class="border-t border-gray-700 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; 2025 ALLOPRO. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts JavaScript pour les interactions UI -->
    <script>
        // Gestion du menu mobile principal
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.querySelector('.menu-icon');
        const closeIcon = document.querySelector('.close-icon');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });
        
        // Toggle de la sidebar administrative sur mobile
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-open');
        });
        
        // Auto-disparition des notifications après 5 secondes
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');
        
        if (successAlert) {
            setTimeout(() => {
                successAlert.remove();
            }, 5000);
        }
        
        if (errorAlert) {
            setTimeout(() => {
                errorAlert.remove();
            }, 5000);
        }
        
        // Défilement doux vers les sections lors du clic sur les liens de navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>