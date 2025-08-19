<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mon profil - Salle de Spectacle</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/ModifierProfil.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>

    <main class="profile-edit">
        <!-- Formulaire au centre, léger offset à gauche -->
        <div class="form-container">
            <form id="profileForm" novalidate>
                <h1>Modifier mon profil</h1>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                
                <!-- Nom -->
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <div class="input-wrapper">
                        <input type="text" id="nom" name="nom_utilisateur" value="<?= htmlspecialchars($view['nom'])?>">
                        <button type="button" class="reset-btn" data-target="nom">✕</button>
                    </div>
                </div>

                <!-- Prénom -->
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <div class="input-wrapper">
                        <input type="text" id="prenom" name="prenom_utilisateur" value="<?= htmlspecialchars($view['prenom'])?>">
                        <button type="button" class="reset-btn" data-target="prenom">✕</button>
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="mail_utilisateur" value="<?= htmlspecialchars($view['email'])?>">
                        <button type="button" class="reset-btn" data-target="email">✕</button>
                    </div>
                </div>

                <!-- Confirmation email -->
                <div class="form-group hidden" id="emailConfirmGroup">
                    <label for="email_confirm">Confirmer Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email_confirm" name="email_confirm">
                    </div>
                </div>

                <!-- Nouveau mot de passe -->
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="mdp_utilisateur">
                        <button type="button" class="reset-btn" data-target="password">✕</button>
                    </div>
                </div>

                <!-- Confirmation mot de passe -->
                <div class="form-group hidden" id="passwordConfirmGroup">
                    <label for="password_confirm">Confirmer mot de passe</label>
                    <div class="input-wrapper">
                        <input type="password" id="password_confirm" name="password_confirm">
                    </div>
                </div>

                <!-- Bouton Enregistrer -->
                <div class="btn-center">
                    <button type="submit" id="saveBtn" class="btn primary" disabled>Enregistrer</button>
                </div>
            </form>
        </div>

        <!-- Bloc aide à droite -->
        <aside class="help-block">
            <h2>Rappel mot de passe</h2>
            <ul>
                <li>Minimum 8 caractères</li>
                <li>Au moins un chiffre</li>
                <li>Au moins une majuscule</li>
                <li>Au moins une minuscule</li>
            </ul>
        </aside>
    </main>

    <?php require_once 'View/Layout/Footer.php'; ?>
    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/javascript/ModifierProfil.js"></script>
</body>

</html>