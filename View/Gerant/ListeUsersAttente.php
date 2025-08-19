<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des utilisateurs en attente</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/ListeUsersAttente.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>
    <main>
        <h2>Validation des accès</h2>
        <p>Il y a <span id="userCount">0</span> utilisateurs à valider</p>

        <table id="usersTable" cellpadding="5">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="usersTbody"></tbody>
        </table>

        <div class="pagination">
            <button class="btn" id="prevBtn" disabled>Précédent</button>
            <span id="pageIndicator">Page 1 sur 1</span>
            <button class="btn" id="nextBtn" disabled>Suivant</button>
        </div>

        <div id="alert" class="alert" style="display:none;"></div>

        <!-- Popup -->
        <div id="confirmPopup" class="popup" style="display:none;">
            <div class="popup-content">
                <p id="confirmText"></p>
                <div class="popup-actions">
                    <button id="confirmYes" class="btn primary">Confirmer</button>
                    <button id="confirmNo" class="btn">Annuler</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Config côté PHP -->
    <div id="config"
        data-csrf-token="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <?php require_once 'View/Layout/Footer.php'; ?>
    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/ListeUsersAttente.js"></script>

</body>

</html>