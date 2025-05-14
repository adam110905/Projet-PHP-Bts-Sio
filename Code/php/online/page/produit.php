<?php
// Activation de l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarre une session PHP pour stocker des informations sur l'utilisateur
session_start();

// Inclusion du fichier de configuration pour la connexion à la base de données
require_once '../../../config.php';

// Récupérer les catégories uniques
$stmt_cat = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL");
$categories = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);

// Gestion du filtre par catégorie
$category_filter = $_GET['category'] ?? '';
$search_query = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'name_asc';

// Construction de la requête SQL avec filtre
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
}

// Ajout du tri
switch ($sort_by) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY name DESC";
        break;
    case 'name_asc':
    default:
        $sql .= " ORDER BY name ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération du nom d'utilisateur
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Utilisateur";

// Récupérer le nombre d'articles dans le panier
$item_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $item_count += isset($item['quantity']) ? (int)$item['quantity'] : 0;
    }
}

// Messages de notification (pour simuler le même comportement que la page panier)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Effacer les messages après les avoir récupérés
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALLOPRO - Nos Produits</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../../css/index.css">
    <link rel="stylesheet" href="../../../css/produit.css">
</head>
<body class="font-sans antialiased min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="gradient-nav fixed w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex-shrink-0 flex items-center">
                <a href="../page/index2.php"><span class="text-3xl font-bold text-yellow-500">ALLOPRO</span></a>
            </div>

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
                
                <!-- Bouton hamburger -->
                <button id="mobile-menu-button" class="text-gray-100 hover:text-yellow-500 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path class="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path class="close-icon hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menu Mobile -->
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

    <!-- Notifications -->
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

    <!-- Contenu principal -->
    <main class="flex-grow pt-32 pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold mb-2 relative inline-block">
                    Nos <span class="text-yellow-500">Produits</span>
                </h1>
                <p class="text-gray-400 mt-4 mb-2">Découvrez notre sélection d'équipements et matériaux professionnels pour tous vos projets.</p>
            </div>

            <!-- Panneau de recherche et filtres -->
            <div class="glass-panel p-6 mb-10 shadow-2xl">
                <div class="flex items-center mb-4">
                    <i class="fas fa-filter text-yellow-500 text-xl mr-3"></i>
                    <h2 class="text-xl font-bold">Filtrer les résultats</h2>
                </div>
                
                <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-search text-yellow-500 mr-2"></i>Rechercher un produit
                        </label>
                        <input type="text" id="search" name="search" 
                              value="<?= htmlspecialchars($search_query) ?>"
                              placeholder="Nom ou description..." 
                              class="w-full px-4 py-3 rounded-lg form-input bg-gray-700 border border-gray-600 text-white">
                    </div>
                    
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-tag text-yellow-500 mr-2"></i>Catégorie
                        </label>
                        <select id="category" name="category" class="w-full px-4 py-3 rounded-lg form-input bg-gray-700 border border-gray-600 text-white">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-sort text-yellow-500 mr-2"></i>Trier par
                        </label>
                        <div class="flex gap-4">
                            <select id="sort" name="sort" class="flex-1 px-4 py-3 rounded-lg form-input bg-gray-700 border border-gray-600 text-white">
                                <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Nom (A-Z)</option>
                                <option value="name_desc" <?= $sort_by === 'name_desc' ? 'selected' : '' ?>>Nom (Z-A)</option>
                                <option value="price_asc" <?= $sort_by === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                                <option value="price_desc" <?= $sort_by === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                            </select>
                            
                            <button type="submit" class="button-primary text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center">
                                <i class="fas fa-filter mr-2"></i>Appliquer
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Liste des produits -->
            <?php if (empty($produits)): ?>
                <!-- Message si aucun produit trouvé -->
                <div class="bg-gray-800 rounded-lg p-12 text-center max-w-lg mx-auto shadow-2xl border border-gray-700">
                    <div class="mb-6">
                        <i class="fas fa-search text-6xl text-yellow-500 mb-4"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-3">Aucun produit trouvé</h2>
                    <p class="text-gray-400 mb-8">Essayez avec d'autres critères de recherche.</p>
                    <a href="produit.php" class="inline-block bg-yellow-500 text-white px-8 py-3 rounded-lg font-medium hover:bg-yellow-600 transition-all duration-300 shadow-lg transform hover:-translate-y-1">
                        <i class="fas fa-th-large mr-2"></i>Voir tous les produits
                    </a>
                </div>
            <?php else: ?>
                <div class="bg-gray-800 rounded-xl overflow-hidden shadow-2xl border border-gray-700">
                    <!-- En-tête des produits -->
                    <div class="section-header px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-tools text-yellow-500 text-2xl mr-3"></i>
                                <h2 class="text-xl font-bold">Catalogue des produits</h2>
                            </div>
                            <span class="px-3 py-1 bg-yellow-500 text-black font-bold rounded-full">
                                <?= count($produits) ?> produit<?= count($produits) > 1 ? 's' : '' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Grille de produits -->
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($produits as $produit): ?>
                            <div class="product-card rounded-lg overflow-hidden fade-in">
                                <div class="relative overflow-hidden h-64">
                                    <img src="../../../../ImagesMagasin/<?= htmlspecialchars($produit['image']) ?>" 
                                         alt="<?= htmlspecialchars($produit['name']) ?>" 
                                         class="w-full h-full object-cover product-image">
                                    
                                    <?php if (isset($produit['category'])): ?>
                                        <div class="product-badge">
                                            <?= htmlspecialchars($produit['category']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-5">
                                    <h3 class="text-lg font-bold mb-2"><?= htmlspecialchars($produit['name']) ?></h3>
                                    <p class="text-gray-400 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($produit['description']) ?></p>
                                    
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-lg font-bold text-yellow-400">
                                            <?= number_format($produit['price'], 2, ',', ' ') ?> €
                                        </span>
                                    </div>
                                    
                                    <?php if (isset($_SESSION['id'])): ?>
                                        <form method="POST" action="Gestion_panier.php" class="flex items-center space-x-2">
                                            <input type="hidden" name="product_id" value="<?= $produit['id'] ?>">
                                            
                                            <div class="quantity-control">
                                                <button type="button" class="quantity-btn minus-btn" onclick="decrementQuantity(this)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" name="quantity" value="1" min="1" max="99" 
                                                       class="quantity-input" id="quantity-input-<?= $produit['id'] ?>">
                                                <button type="button" class="quantity-btn plus-btn" onclick="incrementQuantity(this)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            
                                            <button type="submit" class="button-primary flex-1 text-white px-4 py-2 rounded-lg font-medium flex items-center justify-center">
                                                <i class="fas fa-cart-plus mr-2"></i> Ajouter
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="../connexion/connexion.php" class="block w-full bg-gray-700 text-gray-300 px-4 py-2 rounded-lg font-medium hover:bg-gray-600 transition-all duration-300 text-center">
                                            <i class="fas fa-user mr-2"></i> Connectez-vous pour acheter
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer py-12 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div>
                    <h3 class="text-2xl font-bold mb-6">ALLOPRO</h3>
                    <p class="text-gray-400">Votre partenaire de confiance pour tous vos projets de construction et rénovation.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-6">Services</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li><a href="#" class="hover:text-yellow-500 transition-colors">Construction</a></li>
                        <li><a href="#" class="hover:text-yellow-500 transition-colors">Rénovation</a></li>
                        <li><a href="#" class="hover:text-yellow-500 transition-colors">Expertise</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-6">Contact</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li>123 Rue de la Construction</li>
                        <li>75000 Paris, France</li>
                        <li>+33 1 23 45 67 89</li>
                        <li>contact@allopro.fr</li>
                    </ul>
                </div>
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
            <div class="border-t border-gray-700 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; 2025 ALLOPRO. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
    <!-- Script -->
    <style>
        /* Animation d'entrée pour les cartes */
        .product-card {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        
        /* Hover effect sur les cards */
        .bg-gray-700:hover {
            background-color: rgba(75, 85, 99, 0.8);
        }
        
        /* Animation de badge panier */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .cart-badge {
            animation: pulse 2s infinite;
        }
    </style>
</body>
<script src="../../../js/produit.js"></script>
</html>