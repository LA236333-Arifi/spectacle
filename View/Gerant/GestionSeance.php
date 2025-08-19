<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>Gestion des séances</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/GestionSeance.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>

    <main class="seances-main">
        <h1>Gestion des séances</h1>

        <section class="tabs">
            <div class="tabs-header" role="tablist">
                <button class="tab-btn active" data-tab="tab-add" role="tab" aria-selected="true">Ajouter une séance</button>
                <button class="tab-btn" data-tab="tab-manage" role="tab" aria-selected="false">Déplacer ou annuler une séance</button>
            </div>

            <div class="tabs-body">
                <!-- Onglet Ajouter -->
                <div id="tab-add" class="tab-panel active" role="tabpanel">
                    <form id="form-add-seance" class="card">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">

                        <div class="field">
                            <label for="spectacle-select-add">Spectacle</label>
                            <select id="spectacle-select-add" name="spectacle" required>
                                <option value="">-- Chargement... --</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="date-seance-add">Date de la séance</label>
                            <input id="date-seance-add" name="date_seance" type="date" required>
                        </div>

                        <div class="actions">
                            <button type="button" id="btn-add-seance" class="btn btn-primary">Ajouter séance</button>
                        </div>

                        <p id="msg-add" class="message" aria-live="polite"></p>
                    </form>
                </div>

                <!-- Onglet Gérer -->
                <div id="tab-manage" class="tab-panel" role="tabpanel" aria-hidden="true">
                    <form id="form-manage-seance" class="card">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">

                        <div class="field">
                            <label for="spectacle-select-manage">Spectacle</label>
                            <select id="spectacle-select-manage" name="spectacle" required>
                                <option value="">-- Chargement... --</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="seance-date-select">Séance programmée</label>
                            <select id="seance-date-select" name="date_seance_existante" disabled required>
                                <option value="">Sélectionnez d’abord un spectacle</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="date-seance-new">Nouvelle date (pour déplacement)</label>
                            <input id="date-seance-new" name="nouvelle_date_seance" type="date">
                        </div>

                        <div class="actions">
                            <button type="button" id="btn-move-seance" class="btn btn-info">Déplacer la séance</button>
                            <button type="button" id="btn-cancel-seance" class="btn btn-danger">Annuler la séance</button>
                        </div>

                        <p class="help">
                            Clôturer ou annuler une séance est définitif. Pour “Déplacer”, choisissez d’abord une séance programmée, puis la nouvelle date.
                        </p>

                        <p id="msg-manage" class="message" aria-live="polite"></p>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php require 'View/Layout/Footer.php'; ?>
    <script>
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/GestionSeance.js"></script>

</body>

</html>