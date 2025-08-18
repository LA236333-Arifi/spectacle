<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/Auth.css">
    <title>Se connecter</title>
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>

    <main class="main-content">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Connexion</h1>
                    <p class="access-notice">Accès réservé aux professionnels</p>
                </div>

                <form class="auth-form" id="login-form" method="POST" action="<?= BASE_URL ?>/login">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-group">
                        <label for="email">Email professionnel</label>
                        <input type="email" id="email" name="mail_utilisateur" required>
                        <span class="error-message" id="email-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="mdp_utilisateur" required>
                        <span class="error-message" id="password-error"></span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Se connecter</button>
                        <a href="<?= BASE_URL ?>/password/reset" class="btn-secondary">Mot de passe oublié</a>
                    </div>

                    <div class="auth-message" id="auth-message"></div>
                </form>

                <div class="auth-footer">
                    <p>Pas encore d'accès ?</p>
                    <button type="button" class="btn-link" onclick="window.location.href='<?= BASE_URL ?>/register'">
                        Demander un accès professionnel
                    </button>
                </div>
            </div>
        </div>
    </main>
    <?php require_once 'View/Layout/Footer.php'; ?>
    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/Login.js"></script>
</body>
</html>