/**
 * Script pour le formulaire de connexion/inscription
 * Ajoute des animations et des interactions aux éléments du formulaire
 */
document.addEventListener('DOMContentLoaded', function() {
    // Animation de fondu/apparition pour les formulaires au chargement de la page
    // Le délai de 100ms permet au DOM d'être complètement chargé avant l'animation
    setTimeout(function() {
        document.querySelectorAll('.form-transition').forEach(form => {
            form.classList.add('active'); // Déclenche la transition CSS (opacity: 0 -> 1)
        });
    }, 100);
   
    // Fonction pour basculer l'affichage du mot de passe (visible/masqué)
    const passwordToggle = document.getElementById('pwd-toggle'); // Icône pour basculer
    const passwordInput = document.getElementById('pwd'); // Champ mot de passe
   
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                // Rendre le mot de passe visible
                passwordInput.type = 'text';
                // Changer l'icône (œil ouvert -> œil barré)
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                // Masquer le mot de passe
                passwordInput.type = 'password';
                // Changer l'icône (œil barré -> œil ouvert)
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    }

    // Animation de l'icône du bouton de connexion/inscription au survol
    const submitButton = document.querySelector('button[type="submit"]');
   
    if (submitButton) {
        // Ajoute l'animation de rebond à l'icône au survol
        submitButton.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i'); // Icône à l'intérieur du bouton
            if (icon) {
                icon.classList.add('fa-bounce'); // Ajoute l'animation FontAwesome
            }
        });
       
        // Arrête l'animation quand la souris quitte le bouton
        submitButton.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-bounce');
            }
        });
    }
});