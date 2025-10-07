# 🏗️ AlloPro - Site E-commerce BTP

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

## 📋 Description

**AlloPro** est une plateforme e-commerce complète dédiée au secteur du BTP (Bâtiment et Travaux Publics). Ce projet permet aux professionnels et particuliers d'acheter des matériaux de construction, des équipements de sécurité et des outils en ligne.

Développé dans le cadre d'un projet BTS SIO (Services Informatiques aux Organisations), ce site propose une expérience utilisateur fluide avec un système de gestion des commandes complet et une interface d'administration robuste.

## ✨ Fonctionnalités

### 👤 Espace Client
- **Authentification sécurisée** : Inscription et connexion avec hashage des mots de passe (bcrypt)
- **Catalogue produits** : Navigation par catégories (Ciment, Béton, Sécurité, Outils)
- **Panier dynamique** : Ajout/suppression de produits avec gestion des quantités
- **Système de commande** : Validation et suivi des commandes
- **Compte personnel** : Historique des commandes et gestion du profil
- **Récupération de mot de passe** : Système de réinitialisation par token

### 🔐 Espace Administrateur
- **Gestion des utilisateurs** : Liste, modification du rôle et blocage/déblocage de comptes
- **Gestion des produits** : CRUD complet (création, lecture, mise à jour, suppression)
- **Gestion des commandes** : Visualisation et suivi des commandes clients
- **Tableau de bord** : Statistiques et vue d'ensemble de l'activité

### 🛡️ Sécurité
- Protection contre les injections SQL (requêtes préparées)
- Hashage sécurisé des mots de passe (bcrypt avec salt)
- Gestion des sessions PHP
- Validation côté serveur des données
- Protection CSRF (Cross-Site Request Forgery)

## 🛠️ Technologies Utilisées

### Backend
- **PHP 8.0+** : Langage serveur principal
- **MySQL 8.0** : Base de données relationnelle
- **PDO** : Interface d'accès aux bases de données

### Frontend
- **HTML5** : Structure des pages
- **CSS3** : Mise en forme et responsive design
- **JavaScript** : Interactions dynamiques côté client

### Outils
- **XAMPP/WAMP** : Serveur de développement local
- **HeidiSQL** : Gestion de la base de données
- **Git/GitHub** : Versioning et collaboration

## 📦 Installation

### Prérequis
- PHP 8.0 ou supérieur
- MySQL 8.0 ou supérieur
- Serveur Apache (XAMPP, WAMP, MAMP)
- Un navigateur web moderne

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone https://github.com/adam110905/Projet-PHP-Bts-Sio.git
cd Projet-PHP-Bts-Sio
```

2. **Configurer la base de données**
   - Démarrez votre serveur MySQL
   - Créez une base de données nommée `basesitebtp`
   - Importez le fichier SQL situé dans `projet php v2/BDD/base de donnée pour le site allopro.sql`

```sql
mysql -u root -p basesitebtp < "chemin/vers/base de donnée pour le site allopro.sql"
```

3. **Configurer la connexion à la base de données**
   - Ouvrez le fichier de configuration de connexion
   - Modifiez les paramètres de connexion selon votre environnement

```php
$host = 'localhost';
$dbname = 'basesitebtp';
$username = 'root';
$password = ''; // Votre mot de passe MySQL
```

4. **Démarrer l'application**
   - Placez le projet dans le dossier `htdocs` (XAMPP) ou `www` (WAMP)
   - Démarrez Apache et MySQL
   - Accédez à : `http://localhost/Projet-PHP-Bts-Sio/projet php v2/Code/php/offline/index.html`

## 🔑 Comptes de Test

### Compte Administrateur
- **Email** : admin@gmail.com
- **Mot de passe** : G7u$kP-m2NYvb!

### Compte Client
- **Email** : test@gmail.com
- **Mot de passe** : (Créez votre propre compte via l'inscription)

> ⚠️ **Note de sécurité** : Changez ces identifiants en production !

## 📂 Structure du Projet

```
Projet-PHP-Bts-Sio/
│
├── projet php v2/
│   ├── BDD/
│   │   └── base de donnée pour le site allopro.sql
│   │
│   └── Code/
│       ├── php/
│       │   ├── online/              # Pages dynamiques (PHP)
│       │   │   ├── admin/           # Interface admin
│       │   │   ├── connexion.php    # Authentification
│       │   │   ├── inscription.php  # Inscription
│       │   │   ├── produits.php     # Catalogue
│       │   │   ├── panier.php       # Gestion du panier
│       │   │   └── moncompte.php    # Espace client
│       │   │
│       │   └── offline/             # Pages statiques (HTML)
│       │       └── index.html       # Page d'accueil
│       │
│       ├── css/                     # Feuilles de style
│       ├── js/                      # Scripts JavaScript
│       └── images/                  # Assets visuels
│
└── README.md
```

## 🗄️ Base de Données

### Schéma relationnel

Le projet utilise 6 tables principales :

- **users** : Gestion des utilisateurs (clients et administrateurs)
- **products** : Catalogue des produits
- **cart** : Panier temporaire des utilisateurs
- **orders** : Commandes validées
- **order_items** : Détails des articles commandés
- **password_resets** : Tokens de réinitialisation de mot de passe

### Relations
```
users (1,n) ←→ (0,n) cart
users (1,n) ←→ (0,n) orders
orders (1,n) ←→ (1,n) order_items
products (1,n) ←→ (0,n) cart
products (1,n) ←→ (0,n) order_items
users (1,1) ←→ (0,n) password_resets
```

## 🎯 Fonctionnalités Techniques

### Architecture MVC (adaptée)
- **Modèle** : Interaction avec la base de données via PDO
- **Vue** : Templates HTML/CSS avec injection PHP
- **Contrôleur** : Scripts PHP gérant la logique métier

### Gestion des Sessions
```php
session_start();
$_SESSION['user_id'] = $userId;
$_SESSION['role'] = $userRole;
```

### Sécurité des Mots de Passe
```php
// Hashage à l'inscription
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Vérification à la connexion
password_verify($inputPassword, $storedHash);
```

## 📸 Aperçu du Projet

> 💡 **Suggestion** : Ajoutez des captures d'écran ici
```markdown
### Page d'accueil
![Accueil](screenshots/home.png)

### Catalogue produits
![Catalogue](screenshots/products.png)

### Interface administrateur
![Admin](screenshots/admin.png)
```

## 🚀 Améliorations Futures

- [ ] Système de paiement en ligne (Stripe, PayPal)
- [ ] Notifications par email (confirmation de commande)
- [ ] Système de notation et avis produits
- [ ] API REST pour application mobile
- [ ] Gestion des stocks en temps réel
- [ ] Multi-devises et multi-langues
- [ ] Mode sombre
- [ ] Chat en direct avec le support

## 🤝 Contributeurs

Ce projet a été développé en binôme par :

| Développeur | Rôle | GitHub |
|-------------|------|--------|
| **Adam BOUALI** | Full Stack Developer | [@adam110905](https://github.com/adam110905) |
| **Yanis PERRIN** | Full Stack Developer | - |

### Répartition des tâches

**Travail collaboratif :**
- Développement complet de la partie offline (HTML/CSS)
- Conception de la base de données et schéma relationnel
- Développement du système d'authentification
- Mise en place de la structure `moncompte.php`

**Contributions individuelles :**
- Implémentation des fonctionnalités spécifiques
- Tests et débogage
- Documentation technique

## 📄 Licence

Ce projet a été développé dans un cadre pédagogique (BTS SIO).

## 📞 Contact

Pour toute question ou suggestion :
- GitHub : [@adam110905](https://github.com/adam110905)

---

<div align="center">
  <p>Développé avec ❤️ par des étudiants BTS SIO</p>
  <p>© 2025 AlloPro - Tous droits réservés</p>
</div>
