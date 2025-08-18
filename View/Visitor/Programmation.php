<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Programmation</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/Programmation.css">
</head>

<body>
    <?php require 'View/Layout/Header.php'; ?>

    <main>
        <h1>Programmation</h1>

        <div class="container">
            <form id="filters">
                <label>Date min: <input type="date" name="dateMin"></label>
                <label>Date max: <input type="date" name="dateMax"></label>
                <label>Prix min: <input type="number" name="prixMin" step="0.01"></label>
                <label>Prix max: <input type="number" name="prixMax" step="0.01"></label>
                <label>Durée min: <input type="number" name="dureeMin"></label>
                <label>Durée max: <input type="number" name="dureeMax"></label>
                <label>Type spectacle:
                    <select name="typeSpectacle">
                        <option value="0">Tous les types</option>
                        <?php ViewUtils::displayOptions($view['types']) ?>
                    </select>
                </label>
                <label>Statut séance:
                    <select name="statutSeance">
                        <?php ViewUtils::displayOptions($view['statuts']) ?>
                    </select>
                </label>
                <button type="submit">Filtrer</button>
            </form>
            <div class="table-wrapper">
                <table id="seances">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Durée</th>
                            <th>Prix</th>
                            <th>Groupe</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="pagination">
                <div id="pagination"></div>
            </div>
        </div>
    </main>

    <?php require 'View/Layout/Footer.php'; ?>
    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/javascript/Programmation.js"></script>
</body>

</html>