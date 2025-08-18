<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>Vue d'ensemble annuelle</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/Calendrier.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>
    <main>
        <h1>Vue d'ensemble annuelle</h1>

        <div class="calendar-wrapper">
            <div id="year-selector">
                <button type="button" data-year="2025">2025</button>
                <button type="button" data-year="2026">2026</button>
                <span id="status"></span>
            </div>

            <div class="mois-container" id="mois-container"></div>
        </div>

        <template id="mois-template">
            <div class="mois">
                <h2 class="mois-nom">Mois</h2>
                <p class="mois-total"></p>
                <ul class="mois-types"></ul>
                <p class="mois-pourcentage"></p>
                <div class="progress">
                    <div class="progress-bar"></div>
                </div>
            </div>
        </template>
    </main>

    <?php require 'View/Layout/Footer.php'; ?>
    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/Calendrier.js"></script>
</body>

</html>