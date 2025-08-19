<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/ListeUsersValides.css">
</head> 

<body>
    <?php require_once 'View/Layout/Header.php'; ?>

    <main>
        <h2>Liste des utilisateurs</h2>
        <p>Il y a <span id="userCount">0</span> utilisateurs</p>

        <div class="actions-bar">
            <span class="badge" id="pendingBadge" style="display:none;"></span>
            <div id="alert" class="alert" style="display:none;"></div>
        </div>

        <table id="usersTable" cellpadding="5">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody id="usersTbody">
                <!-- Lignes générées en JS -->
            </tbody>
        </table>

        <!-- Popup de confirmation -->
        <div id="confirmPopup" class="popup" style="display:none;">
            <div class="popup-content">
                <p id="confirmText"></p>
                <div class="popup-actions">
                    <button id="confirmYes" class="btn primary">Appliquer</button>
                    <button id="confirmNo" class="btn">Annuler</button>
                </div>
            </div>
        </div>

        <div class="pagination">
            <button class="btn" id="prevBtn" disabled>Précédent</button>
            <span id="pageIndicator">Page 1 sur 1</span>
            <button class="btn" id="nextBtn" disabled>Suivant</button>
        </div>
        <div id="config"
            data-userstatuts='<?= $view["userStatutsJson"] ?>'
            data-csrf-token="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        
    </main>

    <?php require 'View/Layout/Footer.php'; ?>
    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/ListeUsersValides.js"></script>
</body>

</html>