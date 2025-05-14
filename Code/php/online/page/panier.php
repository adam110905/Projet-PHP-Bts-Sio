<?php
session_start();
require_once '../../../config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// === SECTION AJAX ===
// Gestion des requêtes AJAX pour les détails d'une commande
if (isset($_GET['ajax']) && isset($_GET['order_id'])) {
    $userId = $_SESSION['id'];
    $orderId = intval($_GET['order_id']);
    $orderDetails = getOrderDetails($pdo, $orderId, $userId);
    
    // Retourne les détails au format JSON
    header('Content-Type: application/json');
    if ($orderDetails) {
        echo json_encode([
            'success' => true,
            'order' => $orderDetails['order'],
            'items' => $orderDetails['items']
        ]);
    } else {
        echo json_encode(['error' => 'Commande non trouvée']);
    }
    exit();
}

// === GESTION DU PANIER ===
// Récupérer les articles du panier de l'utilisateur
$userId = $_SESSION['id'];
$stmt = $pdo->prepare("SELECT p.id, p.name, p.price, p.image, c.quantity, c.id as cart_id 
                       FROM cart c
                       JOIN products p ON c.product_id = p.id
                       WHERE c.user_id = ?");
$stmt->execute([$userId]);
$panier = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la requête pour vider le panier
if (isset($_POST['empty_cart'])) {
    $empty_stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $empty_stmt->execute([$userId]);
    
    $_SESSION['success_message'] = "Votre panier a été vidé";
    header("Location: Panier.php");
    exit();
}

// === FONCTIONS UTILITAIRES ===
/**
 * Récupère l'historique des commandes d'un utilisateur
 * @param PDO $pdo - Connexion à la base de données
 * @param int $userId - ID de l'utilisateur
 * @return array - Liste des commandes
 */
function getOrderHistory($pdo, $userId) {
    try {
        // Requête pour obtenir les commandes avec le nombre d'articles
        $stmt = $pdo->prepare("
            SELECT o.id, o.order_date, o.total_amount, o.status,
                COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id, o.order_date, o.total_amount, o.status
            ORDER BY o.order_date DESC
        ");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formater les données pour l'affichage
        foreach ($orders as &$order) {
            $order['order_date'] = date('d/m/Y à H:i', strtotime($order['order_date']));
            $order['formatted_total'] = number_format($order['total_amount'], 2, ',', ' ') . ' €';
        }
        
        return $orders;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des commandes: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les détails d'une commande spécifique
 * @param PDO $pdo - Connexion à la base de données
 * @param int $orderId - ID de la commande
 * @param int $userId - ID de l'utilisateur
 * @return array|null - Détails de la commande ou null si non trouvée
 */
function getOrderDetails($pdo, $orderId, $userId) {
    try {
        // Vérification de sécurité: la commande appartient-elle à l'utilisateur?
        $stmt = $pdo->prepare("
            SELECT * FROM orders WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return null;
        }
        
        // Récupérer les articles de la commande
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name, p.image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatter les données pour l'affichage
        $order['order_date'] = date('d/m/Y à H:i', strtotime($order['order_date']));
        $order['formatted_total'] = number_format($order['total_amount'], 2, ',', ' ') . ' €';
        
        foreach ($items as &$item) {
            $item['formatted_price'] = number_format($item['price'], 2, ',', ' ') . ' €';
            $item['formatted_subtotal'] = number_format($item['price'] * $item['quantity'], 2, ',', ' ') . ' €';
        }
        
        return [
            'order' => $order,
            'items' => $items
        ];
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des détails de la commande: " . $e->getMessage());
        return null;
    }
}

// === PRÉPARATION DES DONNÉES POUR L'AFFICHAGE ===
// Récupérer l'historique des commandes
$userOrders = getOrderHistory($pdo, $userId);

// Récupérer les détails d'une commande spécifique si demandé
$orderDetail = null;
if (isset($_GET['order_id']) && !isset($_GET['ajax'])) {
    $orderDetail = getOrderDetails($pdo, $_GET['order_id'], $userId);
}

// Gestion des messages de notification
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Nettoyage des messages après récupération
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Récupération du nom d'utilisateur pour l'affichage
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Utilisateur inconnu";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier - ALLOPRO</title>
    <!-- Chargement des feuilles de style -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../css/index.css">
    <link rel="stylesheet" href="../../../css/panier.css">
</head>
<body class="font-sans antialiased bg-gray-900 text-white min-h-screen flex flex-col">
    <!-- Barre de navigation -->
    <nav class="gradient-nav fixed w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <!-- Logo du site -->
            <div class="flex-shrink-0 flex items-center">
                <a href="../page/index2.php"><span class="text-3xl font-bold text-yellow-500">ALLOPRO</span></a>
            </div>

            <!-- Menu de navigation (desktop) -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="../page/index2.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Accueil</a>
                <a href="../page/produit.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Nos Produits</a>
                <a href="../page/panier.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors relative">
                    Votre Panier
                </a>
                <a href="../page/Contact2.php" class="nav-link text-gray-100 hover:text-yellow-500 px-3 py-6 font-medium transition-colors">Contact</a>
            </div>

            <!-- Bouton de connexion (Desktop) -->
            <div class="hidden md:flex items-center">
                <a href="../compte/moncompte.php"
                   class="bg-yellow-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300">
                   <?= $username ?>
                </a>
            </div>

            <!-- Section mobile avec username et hamburger -->
            <div class="md:hidden flex items-center space-x-3">
                <!-- Bouton username en mobile -->
                <a href="../compte/moncompte.php" 
                   class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300 text-sm">
                   <?= $username ?>
                </a>
                
                <!-- Bouton hamburger pour menu mobile -->
                <button id="mobile-menu-button" class="text-gray-100 hover:text-yellow-500 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path class="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path class="close-icon hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menu Mobile (initialement caché) -->
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
<div id="menu-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

    <!-- Notifications (messages de succès et d'erreur) -->
    <?php if ($success_message): ?>
    <div class="notification fixed top-24 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center" id="success-notification">
        <div class="bg-white bg-opacity-20 rounded-full p-2 mr-3">
            <i class="fas fa-check"></i>
        </div>
        <span><?= htmlspecialchars($success_message) ?></span>
        <button class="ml-3 text-white hover:text-gray-200" onclick="this.parentElement.remove();">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="notification fixed top-24 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center" id="error-notification">
        <div class="bg-white bg-opacity-20 rounded-full p-2 mr-3">
            <i class="fas fa-exclamation"></i>
        </div>
        <span><?= htmlspecialchars($error_message) ?></span>
        <button class="ml-3 text-white hover:text-gray-200" onclick="this.parentElement.remove();">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- Contenu principal de la page -->
    <main class="flex-grow pt-32 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Titre et introduction -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold mb-2 relative inline-block">
                    Votre <span class="text-yellow-500">Panier</span>
                </h1>
                <p class="text-gray-400 mt-4 mb-2">Voici les articles que vous avez ajoutés à votre panier.</p>
                
                <!-- Bouton pour accéder à l'historique des commandes -->
                <button id="orderHistoryBtn" class="flex items-center justify-center mx-auto mt-3 px-5 py-2.5 bg-gradient-to-r from-gray-700 to-gray-800 hover:from-yellow-500 hover:to-yellow-600 text-white rounded-lg shadow-md border border-gray-600 hover:border-yellow-400 transition-all duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-history mr-2 text-yellow-500"></i> 
                    <span class="font-medium">Voir mon historique de commandes</span>
                </button>
            </div>

            <?php if (empty($panier)): ?>
            <!-- Affichage quand le panier est vide -->
            <div class="bg-gray-800 rounded-lg p-12 text-center max-w-lg mx-auto shadow-2xl border border-gray-700">
                <div class="mb-6">
                    <i class="fas fa-shopping-basket text-6xl text-yellow-500 mb-4"></i>
                </div>
                <h2 class="text-2xl font-bold mb-3">Votre panier est vide</h2>
                <p class="text-gray-400 mb-8">Ajoutez des produits pour commencer vos achats.</p>
                <a href="../page/produit.php" class="inline-block bg-yellow-500 text-white px-8 py-3 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300 shadow-lg transform hover:-translate-y-1">
                    <i class="fas fa-shopping-cart mr-2"></i>Découvrir nos produits
                </a>
            </div>

            <?php else: ?>
            <!-- Affichage du panier avec produits -->
            <div class="bg-gray-800 rounded-xl overflow-hidden shadow-2xl border border-gray-700">
                <!-- En-tête du panier -->
                <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 border-b border-gray-700">
                    <div class="flex items-center">
                        <i class="fas fa-shopping-basket text-yellow-500 text-2xl mr-3"></i>
                        <h2 class="text-xl font-bold">Résumé de votre commande</h2>
                        <span class="ml-auto px-3 py-1 bg-yellow-500 text-black font-bold rounded-full">
                            <?= array_sum(array_column($panier, 'quantity')) ?> article<?= array_sum(array_column($panier, 'quantity')) > 1 ? 's' : '' ?>
                        </span>
                    </div>
                </div>

                <!-- Tableau des produits (version desktop) -->
                <div class="hidden md:block">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-900 text-gray-400">
                                <th class="py-4 px-6 text-left font-medium">Produit</th>
                                <th class="py-4 px-6 text-right font-medium">Prix unitaire</th>
                                <th class="py-4 px-6 text-center font-medium">Quantité</th>
                                <th class="py-4 px-6 text-right font-medium">Sous-total</th>
                                <th class="py-4 px-6 text-center font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = 0;
                            foreach ($panier as $article): 
                                $subtotal = $article['price'] * $article['quantity'];
                                $total += $subtotal;
                            ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-750 transition-colors">
                                <!-- Colonne produit avec image et nom -->
                                <td class="py-5 px-6">
                                    <div class="flex items-center">
                                        <div class="w-20 h-20 rounded-lg overflow-hidden mr-4 bg-gray-700 flex-shrink-0 border border-gray-600">
                                            <img src="../../../../ImagesMagasin/<?= htmlspecialchars($article['image']) ?>" 
                                                 alt="<?= htmlspecialchars($article['name']) ?>" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-lg"><?= htmlspecialchars($article['name']) ?></h3>
                                            <p class="text-gray-400 text-sm mt-1">Réf: PRD-<?= str_pad($article['id'], 5, '0', STR_PAD_LEFT) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <!-- Prix unitaire -->
                                <td class="py-5 px-6 text-right font-medium">
                                    <span class="text-yellow-400"><?= number_format($article['price'], 2, ',', ' ') ?> €</span>
                                </td>
                                <!-- Sélecteur de quantité -->
                                <td class="py-5 px-6">
                                    <form method="POST" action="../page/gestion_panier.php" class="flex justify-center">
                                        <input type="hidden" name="cart_id" value="<?= $article['cart_id'] ?>">
                                        <div class="flex items-center bg-gray-700 rounded-lg border border-gray-600 p-1">
                                            <button type="button" class="quantity-decrease w-8 h-8 flex items-center justify-center bg-gray-800 rounded-l-lg hover:bg-gray-900 transition-colors">
                                                <i class="fas fa-minus text-xs"></i>
                                            </button>
                                            <input type="number" name="quantity" value="<?= $article['quantity'] ?>" 
                                                   min="1" max="10" 
                                                   class="w-12 h-8 bg-gray-700 border-0 text-center focus:outline-none focus:ring-2 focus:ring-yellow-500 text-white" 
                                                   onchange="this.form.submit()">
                                            <button type="button" class="quantity-increase w-8 h-8 flex items-center justify-center bg-gray-800 rounded-r-lg hover:bg-gray-900 transition-colors">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                        </div>
                                    </form>
                                </td>
                                <!-- Sous-total par article -->
                                <td class="py-5 px-6 text-right font-bold">
                                    <span class="text-white"><?= number_format($subtotal, 2, ',', ' ') ?> €</span>
                                </td>
                                <!-- Actions (bouton supprimer) -->
                                <td class="py-5 px-6 text-center">
                                    <form method="POST" action="../page/gestion_panier.php" class="inline-block">
                                        <input type="hidden" name="cart_id" value="<?= $article['cart_id'] ?>">
                                        <input type="hidden" name="delete_item" value="1">
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition-colors" title="Supprimer"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Version mobile du panier -->
                <div class="md:hidden">
                    <?php foreach ($panier as $article):
                        $subtotal = $article['price'] * $article['quantity'];
                    ?>
                    <div class="p-4 border-b border-gray-700">
                        <!-- Information produit (image et nom) -->
                        <div class="flex items-center mb-4">
                            <div class="w-20 h-20 rounded-lg overflow-hidden mr-3 bg-gray-700 flex-shrink-0 border border-gray-600">
                                <img src="../../../../ImagesMagasin/<?= htmlspecialchars($article['image']) ?>" 
                                     alt="<?= htmlspecialchars($article['name']) ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-lg mb-1"><?= htmlspecialchars($article['name']) ?></h3>
                                <p class="text-yellow-400 font-medium"><?= number_format($article['price'], 2, ',', ' ') ?> €</p>
                            </div>
                        </div>
                        
                        <!-- Contrôles de quantité et actions (mobile) -->
                        <div class="bg-gray-700 rounded-lg p-3 mb-4">
                            <div class="flex justify-between items-center">
                                <form method="POST" action="../page/gestion_panier.php" class="flex">
                                    <input type="hidden" name="cart_id" value="<?= $article['cart_id'] ?>">
                                    <div class="flex items-center bg-gray-800 rounded-lg p-1 border border-gray-600">
                                        <button type="button" class="quantity-decrease w-8 h-8 flex items-center justify-center bg-gray-900 rounded-l-lg hover:bg-black transition-colors">
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <input type="number" name="quantity" value="<?= $article['quantity'] ?>" 
                                               min="1" max="10" 
                                               class="w-12 h-8 bg-gray-800 border-0 text-center focus:outline-none focus:ring-2 focus:ring-yellow-500" 
                                               onchange="this.form.submit()">
                                        <button type="button" class="quantity-increase w-8 h-8 flex items-center justify-center bg-gray-900 rounded-r-lg hover:bg-black transition-colors">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Sous-total et bouton supprimer (mobile) -->
                                <div class="flex items-center">
                                    <span class="mr-4 font-bold text-white"><?= number_format($subtotal, 2, ',', ' ') ?> €</span>
                                    <form method="POST" action="../page/gestion_panier.php">
                                        <input type="hidden" name="cart_id" value="<?= $article['cart_id'] ?>">
                                        <input type="hidden" name="delete_item" value="1">
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition-colors" title="Supprimer"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Section de récapitulatif et actions -->
                <div class="p-6 bg-gray-800">
                    <div class="md:flex md:justify-between md:items-start">
                        <!-- Boutons d'actions (vider panier, continuer achats) -->
                        <div class="mb-6 md:mb-0">
                            <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir vider votre panier ?');">
                                <input type="hidden" name="empty_cart" value="1">
                                <button type="submit" class="flex items-center justify-center bg-red-500 hover:bg-red-600 text-white px-5 py-3 rounded-lg transition-colors shadow-lg">
                                    <i class="fas fa-trash mr-2"></i> Vider le panier
                                </button>
                            </form>
                            <a href="../page/produit.php" class="flex items-center justify-center mt-4 bg-gray-700 hover:bg-gray-600 text-white px-5 py-3 rounded-lg transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i> Continuer mes achats
                            </a>
                        </div>
                        
                        <!-- Récapitulatif de la commande -->
                        <div class="w-full md:w-80 bg-gradient-to-b from-gray-700 to-gray-800 rounded-lg p-6 shadow-xl border border-gray-600">
                            <h3 class="text-lg font-bold mb-4 pb-3 border-b border-gray-600 flex items-center">
                                <i class="fas fa-receipt text-yellow-500 mr-2"></i>Récapitulatif
                            </h3>
                            
                            <!-- Sous-total -->
                            <div class="flex justify-between mb-3">
                                <span>Sous-total</span>
                                <span><?= number_format($total, 2, ',', ' ') ?> €</span>
                            </div>
                            
                            <!-- Frais de livraison -->
                            <div class="flex justify-between mb-4 pb-4 border-b border-gray-600">
                                <span>Frais de livraison</span>
                                <span>
                                    <?php 
                                    // Calcul des frais de livraison (gratuits si commande > 100€)
                                    $shipping = ($total < 100) ? 5.99 : 0;
                                    if ($shipping > 0) {
                                        echo number_format($shipping, 2, ',', ' ') . ' €';
                                    } else {
                                        echo '<span class="text-green-400">Gratuit</span>';
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <!-- Total TTC -->
                            <div class="flex justify-between mb-6">
                                <span class="text-lg font-bold">Total TTC</span>
                                <span class="text-lg font-bold text-yellow-500">
                                    <?php
                                    $final_total = $total + $shipping;
                                    echo number_format($final_total, 2, ',', ' ') . ' €';
                                    ?>
                                </span>
                            </div>

                            <!-- Message informatif sur la livraison gratuite -->
                            <?php if ($shipping > 0): ?>
                            <div class="p-2 bg-gray-900 rounded mb-6 text-center text-sm text-gray-300">
                                <p>Plus que <strong class="text-yellow-400"><?= number_format(100 - $shipping - $total, 2, ',', ' ') ?>€</strong> d'achat pour bénéficier de la <strong class="text-green-400">livraison gratuite</strong></p>
                            </div>
                            <?php endif; ?>

                            <!-- Bouton de validation de commande -->
                            <form action="../page/gestion_panier.php" method="POST">
                                <input type="hidden" name="checkout" value="1">
                                <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-3 rounded-lg font-medium transition-colors flex items-center justify-center shadow-lg transform hover:-translate-y-1 duration-300">
                                    <i class="fas fa-credit-card mr-2"></i> Valider ma commande
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Popup pour l'historique des commandes -->
<div id="orderHistoryModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Overlay semi-transparent -->
    <div class="absolute inset-0 bg-black bg-opacity-70 backdrop-blur-sm"></div>
    <!-- Contenu du modal -->
    <div class="bg-gray-800 rounded-xl shadow-2xl border border-gray-700 w-full max-w-4xl max-h-[80vh] overflow-hidden z-10 relative">
        <!-- En-tête du popup -->
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 border-b border-gray-700 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center">
                <i class="fas fa-history text-yellow-500 text-2xl mr-3"></i>
                <h2 class="text-xl font-bold">Historique de vos commandes</h2>
            </div>
            <button id="closeOrderHistory" class="text-gray-400 hover:text-white focus:outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
            
            <!-- Zone de contenu avec défilement -->
            <div class="p-6 overflow-y-auto max-h-[calc(80vh-64px)]" id="orderHistoryContent">
                <?php if (empty($userOrders)): ?>
                <!-- Message quand aucune commande n'existe -->
                <div class="bg-gray-700 rounded-lg p-8 text-center">
                    <i class="fas fa-shopping-basket text-4xl text-yellow-500 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Aucune commande trouvée</h3>
                    <p class="text-gray-400">Vous n'avez pas encore effectué de commande.</p>
                </div>
                <?php else: ?>
                <!-- Liste des commandes -->
                <div class="mb-6" id="orderListSection">
                    <h3 class="text-lg font-semibold mb-4">Vos commandes récentes</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <?php foreach ($userOrders as $order): 
                            // Déterminer la couleur du statut selon l'état de la commande
                            $statusColor = 'bg-gray-500';
                            switch (strtolower($order['status'])):
                                case 'en attente':
                                    $statusColor = 'bg-yellow-500';
                                    break;
                                case 'expédiée':
                                    $statusColor = 'bg-blue-500';
                                    break;
                                case 'livrée':
                                    $statusColor = 'bg-green-500';
                                    break;
                                case 'annulée':
                                    $statusColor = 'bg-red-500';
                                    break;
                                case 'confirmée':
                                    $statusColor = 'bg-purple-500';
                                    break;
                            endswitch;
                        ?>
                        <!-- Carte pour chaque commande -->
                        <div class="bg-gray-700 rounded-lg p-4 hover:bg-gray-650 transition-colors border border-gray-600 shadow-md">
                            <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
                                <div>
                                    <span class="text-gray-400 text-sm">Commande #<?= $order['id'] ?></span>
                                    <h4 class="font-semibold"><?= $order['order_date'] ?></h4>
                                </div>
                                <div class="mt-2 md:mt-0 flex items-center">
                                    <span class="<?= $statusColor ?> text-white text-xs px-2 py-1 rounded-full mr-3">
                                        <?= $order['status'] ?>
                                    </span>
                                    <span class="font-bold text-yellow-400"><?= $order['formatted_total'] ?></span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-400 mb-4">
                                <?= $order['item_count'] ?> article<?= $order['item_count'] > 1 ? 's' : '' ?>
                            </div>
                            <!-- Bouton pour voir les détails (déclenche l'AJAX) -->
                            <button 
                                class="view-order-details w-full bg-gray-600 hover:bg-gray-500 text-white py-2 rounded-lg transition-colors text-sm flex items-center justify-center"
                                data-order-id="<?= $order['id'] ?>">
                                <i class="fas fa-eye mr-2"></i> Voir les détails
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Section pour afficher les détails d'une commande spécifique (initialement cachée) -->
                <div id="orderDetailsSection" class="hidden">
                    <div class="mb-4">
                        <button id="backToOrderList" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center justify-center w-auto inline-flex">
                            <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
                        </button>
                    </div>
                    
                    <!-- Conteneur pour les détails chargés via AJAX -->
                    <div id="orderDetailsContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pied de page -->
    <footer class="footer py-12 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Logo et description -->
                <div>
                    <h3 class="text-2xl font-bold mb-6">ALLOPRO</h3>
                    <p class="text-gray-400">Votre partenaire de confiance pour tous vos projets de construction et rénovation.</p>
                </div>
                <!-- Navigation services -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Services</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li><a href="#" class="hover:text-yellow-500 transition-colors">Construction</a></li>
                        <li><a href="#" class="hover:text-yellow-500 transition-colors">Rénovation</a></li>
                        <li><a href="#" class="hover:text-yellow-500 transition-colors">Expertise</a></li>
                    </ul>
                </div>
                <!-- Coordonnées -->
                <div>
                    <h4 class="text-lg font-semibold mb-6">Contact</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li>123 Rue de la Construction</li>
                        <li>75000 Paris, France</li>
                        <li>+33 1 23 45 67 89</li>
                        <li>contact@allopro.fr</li>
                    </ul>
                </div>
                <!-- Réseaux sociaux -->
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
    
    <!-- Chargement du script JavaScript -->
    <script src="../../../js/panier.js"></script>
</body>
</html>