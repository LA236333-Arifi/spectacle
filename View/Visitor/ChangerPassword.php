<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe - Salle de Spectacle</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/ChangerPassword.css">
</head>
<body>
    <?php require_once 'View/Layout/Header.php'; ?>
    
    <main class="main-content">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Nouveau mot de passe</h1>
                <p>Saisissez votre nouveau mot de passe</p>
            </div>

            <form class="auth-form" id="change-password-form" method="POST" action="<?= BASE_URL ?>/password/change">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf']) ?>">
                <!-- Le token sera ajouté dynamiquement par JavaScript depuis l'URL -->
                
                <div class="password-requirements">
                    <h4>Exigences du mot de passe</h4>
                    <ul class="requirements-list">
                        <li>Au moins 8 caractères</li>
                        <li>Une majuscule</li>
                        <li>Une minuscule</li>
                        <li>Un chiffre</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <span class="error-message" id="new-password-error"></span>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span class="error-message" id="confirm-password-error"></span>
                </div>

                <button type="submit" class="btn-primary" id="submit-btn">
                    Changer le mot de passe
                </button>

                <div class="auth-message" id="auth-message"></div>
            </form>
        </div>
    </main>

    <?php require 'View/Layout/Footer.php'; ?>
    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/javascript/ChangerPassword.js"></script>
</body>
</html>