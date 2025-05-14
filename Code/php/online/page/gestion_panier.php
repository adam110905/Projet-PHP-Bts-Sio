<?php
/**
 * Traitement du panier d'achat - ALLOPRO
 * 
 * Ce script gère toutes les opérations liées au panier :
 * - Ajout de produits au panier
 * - Mise à jour des quantités
 * - Suppression d'articles
 * - Validation de commande avec création dans la base de données
 * 
 * Chaque action est sécurisée avec validation des données et gestion des erreurs.
 */

// Démarrage de la session pour accéder aux données utilisateur
session_start();

// Inclusion de la configuration de la base de données
require_once '../../../config.php';

// Vérification de l'authentification - Protection contre les accès non autorisés
if (!isset($_SESSION['id'])) {
    // Message d'erreur stocké en session pour affichage après redirection
    $_SESSION['error_message'] = "Veuillez vous connecter pour gérer votre panier.";
    header('Location: login.php');
    exit;
}

// Récupération et validation des données principales
$user_id = $_SESSION['id'];
$product_id = intval($_POST['product_id'] ?? 0);  // Conversion en entier (0 si non défini)
$cart_id = intval($_POST['cart_id'] ?? 0);        // Identifiant d'un article déjà dans le panier
$quantity = intval($_POST['quantity'] ?? 1);      // Quantité par défaut = 1

// Validation de la quantité minimale
if (isset($_POST['quantity']) && $quantity < 1) {
    $_SESSION['error_message'] = "La quantité doit être au moins de 1.";
    header('Location: Panier.php');
    exit;
}

/**
 * SECTION 1: TRAITEMENT DE VALIDATION DE COMMANDE (CHECKOUT)
 * Cette section s'exécute lors de la finalisation d'une commande
 */
if (isset($_POST['checkout'])) {
    try {
        // Utilisation d'une transaction pour garantir l'intégrité des données
        // (Toutes les opérations réussissent ou aucune n'est appliquée)
        $pdo->beginTransaction();
        
        // Étape 1: Récupération de tous les articles du panier avec leurs prix
        $stmt = $pdo->prepare("SELECT p.id, p.price, c.quantity 
                              FROM cart c
                              JOIN products p ON c.product_id = p.id
                              WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Vérification que le panier n'est pas vide
        if (empty($cart_items)) {
            $_SESSION['error_message'] = "Votre panier est vide.";
            header('Location: Panier.php');
            exit();
        }
        
        // Étape 2: Calcul du montant total de la commande
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Application des frais de livraison (gratuits pour les commandes > 100€)
        $shipping = ($total < 100) ? 5.99 : 0;
        $final_total = $total + $shipping;
        
        // Étape 3: Création de l'entrée dans la table des commandes
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, order_date, status) 
                              VALUES (?, ?, NOW(), 'confirmée')");
        $stmt->execute([$user_id, $final_total]);
        $order_id = $pdo->lastInsertId();  // Récupération de l'ID généré
        
        // Étape 4: Enregistrement des détails de chaque article commandé
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                              VALUES (?, ?, ?, ?)");
                              
        foreach ($cart_items as $item) {
            $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        }
        
        // Étape 5: Vidage du panier après validation de la commande
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Finalisation de la transaction - Applique toutes les modifications
        $pdo->commit();
        
        // Message de succès et redirection
        $_SESSION['success_message'] = "Commande validée avec succès. Merci pour votre achat !";
        header('Location: Panier.php');
        exit;
    } catch (PDOException $e) {
        // Annulation de toutes les modifications en cas d'erreur
        $pdo->rollBack();
        
        // Journalisation de l'erreur et message utilisateur
        $_SESSION['error_message'] = "Une erreur est survenue lors de la validation de votre commande: " . $e->getMessage();
        error_log("Erreur lors de la validation de commande: " . $e->getMessage());
        header('Location: Panier.php');
        exit;
    }
}

/**
 * SECTION 2: AJOUT OU MISE À JOUR D'UN PRODUIT
 * Cette section s'exécute lorsqu'un produit est ajouté au panier depuis la page produit
 */
if ($product_id > 0) {
    // Vérification que le produit existe bien dans la base de données
    $stmt_product = $pdo->prepare("SELECT id, name FROM products WHERE id = ?");
    $stmt_product->execute([$product_id]);
    $product = $stmt_product->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $_SESSION['error_message'] = "Ce produit n'existe pas.";
        header('Location: Produit.php');
        exit;
    }
    
    // Vérification si le produit est déjà dans le panier
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ligne) {
        // Cas 1: Le produit est déjà dans le panier - Mise à jour de la quantité
        // (avec limite maximale de 10 unités par produit)
        $new_quantity = min($ligne['quantity'] + $quantity, 10);
        $stmt_update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt_update->execute([$new_quantity, $ligne['id']]);
        $_SESSION['success_message'] = "La quantité de " . htmlspecialchars($product['name']) . " a été mise à jour dans votre panier.";
    } else {
        // Cas 2: Nouveau produit à ajouter au panier
        $stmt_insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt_insert->execute([$user_id, $product_id, $quantity]);
        $_SESSION['success_message'] = htmlspecialchars($product['name']) . " a été ajouté à votre panier.";
    }
} 
/**
 * SECTION 3: MODIFICATION OU SUPPRESSION D'UN ARTICLE DU PANIER
 * Cette section s'exécute lorsqu'un article est modifié ou supprimé depuis la page panier
 */
elseif ($cart_id > 0) {
    // Vérification que l'article existe bien dans le panier de l'utilisateur
    $stmt = $pdo->prepare("SELECT id, product_id, quantity FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ligne) {
        if (isset($_POST['delete_item'])) {
            // Cas 1: Suppression de l'article du panier
            $stmt_delete = $pdo->prepare("DELETE FROM cart WHERE id = ?");
            if ($stmt_delete->execute([$cart_id])) {
                $_SESSION['success_message'] = "L'article a été supprimé de votre panier.";
            } else {
                $_SESSION['error_message'] = "Erreur lors de la suppression de l'article.";
            }
        } elseif ($quantity > 0) {
            // Cas 2: Mise à jour de la quantité d'un article
            // (avec limite maximale de 10 unités par produit)
            $new_quantity = min($quantity, 10);
            $stmt_update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt_update->execute([$new_quantity, $cart_id]);
            $_SESSION['success_message'] = "La quantité a été mise à jour.";
        } else {
            // Cas d'erreur: Quantité invalide
            $_SESSION['error_message'] = "Quantité invalide.";
        }
    } else {
        // Cas d'erreur: Article non trouvé dans le panier
        $_SESSION['error_message'] = "L'article demandé n'existe pas dans votre panier.";
    }
} else {
    // Cas d'erreur: Aucun identifiant valide fourni
    $_SESSION['error_message'] = "Produit ou panier invalide.";
}

/**
 * SECTION 4: REDIRECTION INTELLIGENTE
 * Redirection vers la page d'origine ou vers le panier par défaut
 */
$referer = $_SERVER['HTTP_REFERER'] ?? null;
if ($referer && strpos($referer, 'Produit.php') !== false) {
    // Retour à la page produit si l'action vient de là
    header('Location: ' . $referer);
} else {
    // Redirection vers le panier par défaut
    header('Location: ./panier.php');
}
exit;
?>