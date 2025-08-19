<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>Vue d'ensemble annuelle</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/Main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/GestionSpectacle.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>
    <main>
        <section class="spectacle-actions">
        <h2>Gestion des spectacles</h2>
        <form id="spectacle-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">

            <label for="spectacle-select">Sélectionnez un spectacle :</label>
            <select id="spectacle-select" name="spectacle_id" required>
                <option value="">-- Chargement... --</option>
            </select>

            <div class="btn-group">
                <button type="button" id="btn-view" class="btn btn-info">Voir les infos</button>
                <button type="button" id="btn-cloturer" class="btn btn-danger">Clôturer</button>
            </div>
        </form>

        <p id="action-message" class="info-message">Clôturer le spectacle annulera toutes les prochaines séances</p>
    </section>
    </main>

    <?php require 'View/Layout/Footer.php'; ?>
    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/CloturerSpectacle.js"></script>
</body>

</html>