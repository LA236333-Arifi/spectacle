<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Salle de Spectacle</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/styles/Register.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>
    <main class="main-content">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Demande d'accès professionnel</h1>
                    <p class="access-notice">Réservé aux employés de la salle</p>
                </div>

                <form class="auth-form" id="register-form" method="POST" action="<?= BASE_URL ?>/register">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom_utilisateur">Nom *</label>
                            <input type="text" id="nom" name="nom_utilisateur" required>
                            <span class="error-message" id="nom-error"></span>
                        </div>

                        <div class="form-group">
                            <label for="prenom_utilisateur">Prénom *</label>
                            <input type="text" id="prenom" name="prenom_utilisateur" required>
                            <span class="error-message" id="prenom-error"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mail_utilisateur">Email professionnel *</label>
                        <input type="email" id="email" name="mail_utilisateur" required>
                        <span class="error-message" id="email-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="mail_utilisateur_confirm">Confirmer email professionnel *</label>
                        <input type="email" id="email-confirm" name="mail_utilisateur_confirm" required>
                        <span class="error-message" id="email-confirm-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="mdp_utilisateur">Mot de passe *</label>
                        <input type="password" id="password" name="mdp_utilisateur" required>
                        <span class="error-message" id="password-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="mdp_utilisateur_confirm">Confirmer le mot de passe *</label>
                        <input type="password" id="password-confirm" name="mdp_utilisateur_confirm" required>
                        <span class="error-message" id="password-confirm-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="role_utilisateur">Rôle demandé *</label>
                        <select id="role" name="role_utilisateur" required>
                            <option value="0">Sélectionner un rôle</option>
                            <?php ViewUtils::displayOptions($view['roles']) ?>
                        </select>
                        <span class="error-message" id="role-error"></span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Demander l'accès</button>
                        <button type="button" class="btn-secondary" onclick="window.location.href='<?= BASE_URL ?>/login'">
                            Retour à la connexion
                        </button>
                    </div>

                    <div class="auth-message" id="auth-message"></div>
                </form>

                <div class="auth-footer">
                    <p><strong>Note :</strong> Votre demande sera examinée par un administrateur.</p>
                    <p>Vous recevrez un email de confirmation une fois votre accès approuvé.</p>
                </div>
            </div>
        </div>
    </main>
    <?php require_once 'View/Layout/Footer.php'; ?>
    <script src="<?= BASE_URL ?>/Javascript/Register.js"></script>
</body>
</html>