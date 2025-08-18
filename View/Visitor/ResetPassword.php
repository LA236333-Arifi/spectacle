<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>Mot de passe oublié</title>
    <!-- Nouveau CSS moderne -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/ResetPassword.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>

    <main>
        <div class="reset-container">
            <h1>Mot de passe oublié</h1>
            <p>Entrez votre email pour recevoir un lien de réinitialisation</p>

            <form action="<?= BASE_URL ?>/password/reset" method="POST" id="forgot-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">

                <input type="email" id="email" name="mail_utilisateur" required autocomplete="email"
                    inputmode="email" placeholder="vous@exemple.com"
                    aria-describedby="email-error">
                <p class="field-error" id="email-error" aria-live="polite"></p>

                <input type="email" id="emailConfirm" name="emailConfirm" required autocomplete="email"
                    inputmode="email" placeholder="Confirmez votre adresse"
                    aria-describedby="emailConfirm-error">
                <p class="field-error" id="emailConfirm-error" aria-live="polite"></p>

                <button type="submit" id="forgot-submit" disabled>Envoyer</button>
            </form>

            <a href="<?= BASE_URL ?>/login" class="back-link">← Retour à la connexion</a>
        </div>
    </main>

    <?php require 'View/Layout/Footer.php'; ?>

    <script>
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/ResetPassword.js"></script>
</body>

</html>