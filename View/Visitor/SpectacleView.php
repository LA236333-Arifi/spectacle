<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/SpectacleView.css">
    <title>Spectacle - Salle de Spectacle</title>
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>

    <main id="spectacle-root"
        class="spectacle-container"
        data-id="<?= (int)$view['spectacle_id'] ?>">

        <header>
            <h1 class="spectacle-title"></h1>
            <p class="spectacle-hook"></p>
        </header>

        <div class="spectacle-meta">
            <div><strong>Prix :</strong> <span id="spectacle-price"></span></div>
            <div><strong>Durée :</strong> <span id="spectacle-duration"></span></div>
            <div><strong>Type :</strong> <span id="spectacle-type"></span></div>
        </div>

        <section>
            <h2>Programmations</h2>
            <ul id="spectacle-seances"></ul>
        </section>

        <section>
            <h2>Crédits</h2>
            <div id="spectacle-credits"></div>
        </section>

        <section>
            <h2>Groupe</h2>
            <div class="spectacle-meta">
                <div><strong>Nom :</strong> <span id="spectacle-group-name"></span></div>
                <div><strong>Formé le :</strong> <span id="spectacle-group-date"></span></div>
            </div>
            <ul id="spectacle-members"></ul>
        </section>
    </main>

    <?php require 'View/Layout/Footer.php'; ?>
    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/javascript/SpectacleView.js"></script>
</body>

</html>