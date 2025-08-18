<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un spectacle</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/GestionSpectacle.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>

    <main>
        <section>
            <h2>Ajouter un spectacle</h2>
            <form id="createSpectacleForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">

                <div>
                    <label for="type_spectacle_id">Type de spectacle :</label>
                    <select id="type_spectacle_id" name="type_spectacle_id" required>
                        <option value="" disabled selected>Choisir un type</option>
                        <?php ViewUtils::displayOptions($view['types']); ?>
                    </select>
                </div>

                <div>
                    <label for="nom_spectacle">Nom du spectacle :</label>
                    <input type="text" id="nom_spectacle" name="nom_spectacle" required>
                </div>

                <div>
                    <label for="texte_accroche_spectacle">Texte d'accroche :</label>
                    <textarea id="texte_accroche_spectacle" name="texte_accroche_spectacle" required></textarea>
                </div>

                <div>
                    <label for="prix_spectacle">Prix (€) :</label>
                    <input type="number" id="prix_spectacle" name="prix_spectacle" step="0.01" min="0" required>
                </div>

                <div>
                    <label for="duree_minutes_spectacle">Durée (minutes) :</label>
                    <input type="number" id="duree_minutes_spectacle" name="duree_minutes_spectacle" min="1" required>
                </div>

                <div>
                    <label for="groupe_id">Groupe :</label>
                    <select id="groupe_id" name="groupe_id" required></select>
                </div>

                <div id="auteurContainer">
                    <label for="auteur_id">Auteur :</label>
                    <select id="auteur_id" name="auteur_id"></select>
                </div>

                <div id="metteurContainer">
                    <label for="metteur_id">Metteur en scène :</label>
                    <select id="metteur_id" name="metteur_id"></select>
                </div>

                <button type="submit">Créer le spectacle</button>
            </form>
            <div id="createSpectacleResult"></div>
        </section>
    </main>

    <?php require 'View/Layout/Footer.php'; ?>
    <script>
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/AjouterSpectacle.js"></script>
</body>

</html>