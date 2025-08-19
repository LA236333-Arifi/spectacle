<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>Gestion des PDF Spectacles</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/PubliciteSpectacle.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>

    <main>
        <h1>Gestion des PDF Spectacles</h1>

        <div class="tabs">
            <button class="tab-btn active" data-tab="tab1">La liste de tous les spectacles</button>
            <button class="tab-btn" data-tab="tab2">Choisir un spectacle</button>
        </div>

        <div class="tab-content">
            <!-- Onglet 1 -->
            <div id="tab1" class="tab-pane active">
                <p>En cliquant sur le bouton suivant, vous allez visionner le PDF de tous les spectacles en cours.</p>
                <div class="btn-center">
                    <a href="<?= BASE_URL ?>/spectacle/print/all" class="btn primary">Générer le PDF</a>
                </div>
            </div>

            <!-- Onglet 2 -->
            <div id="tab2" class="tab-pane">
                <div class="onglet2-header">
                    <p>Choisissez un spectacle parmi cette liste de spectacles en cours</p>
                </div>
                <!-- Conteneur de la liste -->
                <div id="spectacleList" class="spectacle-list"></div>

                <!-- Bouton d’action -->
                <div class="btn-center">
                    <button id="generateSelectedPdf" class="btn primary" disabled>Générer PDF</button>
                </div>

                <!-- Template caché -->
                <template id="spectacleItemTemplate">
                    <div class="spectacle-item">
                        <h3 class="titre"></h3>
                        <p class="accroche"></p>
                        <div class="infos-cle"></div>
                        <div class="groupe"></div>
                        <div class="auteur"></div>
                    </div>
                </template>

            </div>
        </div>
    </main>

    <?php require 'View/Layout/Footer.php'; ?>

    <script>
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/PubliciteSpectacle.js"></script>
</body>

</html>