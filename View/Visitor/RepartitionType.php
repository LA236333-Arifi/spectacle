<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Répartition des types de spectacles</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/RepartitionType.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>
    <div class="stats-container">
        <div class="year-controls">
            <h3>Sélectionner une année</h3>
            <button class="year-btn" data-year="2025">2025</button>
            <button class="year-btn" data-year="2026">2026</button>
        </div>

        <div class="stats-content">
            <h2>Statistiques des séances</h2>
            <div class="stats-summary">
                Toutes les séances de l'année : <span id="total-year">0</span>
            </div>
            
            <div class="table-container">
                <table id="table-seances">
                    <thead>
                        <tr>
                            <th>Type de spectacle</th>
                            <th>Répartition en % sur l'année</th>
                            <th>Séances planifiées (à venir)</th>
                            <th>Séances déjà passées</th>
                            <th>Séances totales de l'année</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Lignes générées en JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php require 'View/Layout/Footer.php'; ?>
    <script>
        // On définit la base URL depuis le PHP pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/RepartitionType.js"></script>
</body>

</html>