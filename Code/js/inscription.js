/**
 * Gestionnaire de création et validation de mot de passe
 * Fonctionnalités: génération aléatoire, vérification de force, affichage/masquage
 */
document.addEventListener('DOMContentLoaded', function() {
    // Sélection des éléments du DOM
    const generateBtn = document.getElementById('generate-password'); // Bouton de génération
    const passwordInput = document.getElementById('pwd'); // Champ de mot de passe
    const confirmPasswordInput = document.getElementById('confirm_password'); // Champ de confirmation
    const passwordStrength = document.getElementById('password-strength'); // Indicateur de force
    const passwordToggle = document.getElementById('pwd-toggle'); // Bouton afficher/masquer
    const confirmPasswordToggle = document.getElementById('confirm-pwd-toggle'); // Bouton afficher/masquer
    
    // Éléments pour les critères de validation
    const lengthCheck = document.getElementById('length-check'); // Critère de longueur
    const lowercaseCheck = document.getElementById('lowercase-check'); // Critère de minuscules
    const uppercaseCheck = document.getElementById('uppercase-check'); // Critère de majuscules
    const numberCheck = document.getElementById('number-check'); // Critère de chiffres
    const specialCheck = document.getElementById('special-check'); // Critère de caractères spéciaux
    
    /**
     * Génère un mot de passe fort selon les critères de sécurité
     * @returns {string} Mot de passe généré
     */
    function generatePassword() {
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const numbers = '0123456789';
        const special = '!@#$%^&*()_+[]{}|;:,.<>?';
        
        // Garantit au moins un caractère de chaque catégorie
        let password = '';
        password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
        password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
        password += numbers.charAt(Math.floor(Math.random() * numbers.length));
        password += special.charAt(Math.floor(Math.random() * special.length));
        
        // Complète jusqu'à 12 caractères avec un mélange aléatoire
        const allChars = lowercase + uppercase + numbers + special;
        for (let i = 4; i < 12; i++) {
            password += allChars.charAt(Math.floor(Math.random() * allChars.length));
        }
        
        // Mélange final pour éviter des motifs prévisibles
        password = shuffleString(password);
        
        return password;
    }
    
    /**
     * Mélange les caractères d'une chaîne (algorithme de Fisher-Yates)
     * @param {string} str - Chaîne à mélanger
     * @returns {string} Chaîne mélangée
     */
    function shuffleString(str) {
        let array = str.split('');
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]]; // Échange des positions
        }
        return array.join('');
    }
    
    /**
     * Met à jour l'interface visuelle des critères de validité
     * @param {string} password - Mot de passe à évaluer
     */
    function updatePasswordCriteria(password) {
        // Vérification de longueur (min 12 caractères)
        if (password.length >= 12) {
            lengthCheck.querySelector('i').className = 'fas fa-check-circle text-xs mr-2 text-green-500';
        } else {
            lengthCheck.querySelector('i').className = 'fas fa-circle text-xs mr-2 text-gray-500';
        }
        
        // Vérification des lettres minuscules
        if (/[a-z]/.test(password)) {
            lowercaseCheck.querySelector('i').className = 'fas fa-check-circle text-xs mr-2 text-green-500';
        } else {
            lowercaseCheck.querySelector('i').className = 'fas fa-circle text-xs mr-2 text-gray-500';
        }
        
        // Vérification des lettres majuscules
        if (/[A-Z]/.test(password)) {
            uppercaseCheck.querySelector('i').className = 'fas fa-check-circle text-xs mr-2 text-green-500';
        } else {
            uppercaseCheck.querySelector('i').className = 'fas fa-circle text-xs mr-2 text-gray-500';
        }
        
        // Vérification des chiffres
        if (/[0-9]/.test(password)) {
            numberCheck.querySelector('i').className = 'fas fa-check-circle text-xs mr-2 text-green-500';
        } else {
            numberCheck.querySelector('i').className = 'fas fa-circle text-xs mr-2 text-gray-500';
        }
        
        // Vérification des caractères spéciaux
        if (/[\W_]/.test(password)) {
            specialCheck.querySelector('i').className = 'fas fa-check-circle text-xs mr-2 text-green-500';
        } else {
            specialCheck.querySelector('i').className = 'fas fa-circle text-xs mr-2 text-gray-500';
        }
    }
    
    /**
     * Calcule et affiche la force du mot de passe
     * @param {string} password - Mot de passe à évaluer
     */
    function checkPasswordStrength(password) {
        let strength = 0;
        
        // Attribution de points pour chaque critère rempli
        if (password.length >= 12) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[\W_]/.test(password)) strength += 1;
        
        // Affichage selon le niveau de force (0-5)
        if (password === '') {
            // Champ vide
            passwordStrength.textContent = '';
            passwordStrength.className = 'password-strength';
        } else if (strength < 3) {
            // Force faible (rouge)
            passwordStrength.textContent = 'Mot de passe faible';
            passwordStrength.className = 'password-strength strength-weak';
        } else if (strength < 5) {
            // Force moyenne (orange)
            passwordStrength.textContent = 'Mot de passe moyen';
            passwordStrength.className = 'password-strength strength-medium';
        } else {
            // Force maximale (vert)
            passwordStrength.textContent = 'Mot de passe fort';
            passwordStrength.className = 'password-strength strength-strong';
        }
        
        // Met à jour les indicateurs visuels des critères
        updatePasswordCriteria(password);
    }
    
    // Gestionnaire d'événement pour le bouton de génération
    generateBtn.addEventListener('click', function() {
        const newPassword = generatePassword();
        
        // Remplit les deux champs avec le même mot de passe
        passwordInput.value = newPassword;
        confirmPasswordInput.value = newPassword;
        
        // Déclenche l'événement input pour actualiser tous les indicateurs
        passwordInput.dispatchEvent(new Event('input'));
        
        // Vérifie et affiche la force du mot de passe
        checkPasswordStrength(newPassword);
        
        // Feedback visuel temporaire de succès
        this.innerHTML = '<i class="fas fa-check mr-2"></i>Mot de passe généré';
        this.classList.add('bg-green-600');
        this.classList.remove('bg-gray-600');
        
        // Rétablissement du bouton après 2 secondes
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-key mr-2"></i>Générer un mot de passe';
            this.classList.remove('bg-green-600');
            this.classList.add('bg-gray-600');
        }, 2000);
    });
    
    // Évalue la force à chaque changement du mot de passe
    passwordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });
    
    /**
     * Bascule l'affichage du mot de passe (masqué/visible)
     * @param {HTMLElement} inputField - Champ de saisie
     * @param {HTMLElement} toggleIcon - Icône du bouton
     */
    function togglePasswordVisibility(inputField, toggleIcon) {
        if (inputField.type === 'password') {
            // Rendre visible
            inputField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            // Masquer
            inputField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
    
    // Applique les fonctions de basculement aux boutons
    passwordToggle.addEventListener('click', function() {
        togglePasswordVisibility(passwordInput, this);
    });
    
    confirmPasswordToggle.addEventListener('click', function() {
        togglePasswordVisibility(confirmPasswordInput, this);
    });
    
    // Vérifie la correspondance entre les deux champs de mot de passe
    confirmPasswordInput.addEventListener('input', function() {
        if (passwordInput.value !== this.value && this.value !== '') {
            // En cas de non-correspondance
            this.classList.add('border-red-500');
            this.classList.remove('border-gray-600');
        } else {
            // En cas de correspondance ou champ vide
            this.classList.remove('border-red-500');
            this.classList.add('border-gray-600');
        }
    });
});