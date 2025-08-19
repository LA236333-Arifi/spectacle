<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion groupes et auteurs</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/GestionGroupe.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>

    <main>
        <section>
            <h2>Créer un Groupe</h2>
            <p>Remplis le nom du groupe et ajoute un ou plusieurs performeurs. Chaque performeur doit avoir un nom, un prénom et un rôle.</p>

            <form id="createGroupForm">
                <!-- Nom du groupe -->
                <label for="nomGroupe">Nom du groupe</label>
                <input type="text" id="nomGroupe" name="nom_groupe" placeholder="Nom du groupe" required>

                <!-- Bloc performeurs -->
                <div id="performeursFields">
                    <h3>Performeurs</h3>
                    <button type="button" id="addPerformeurBtn">➕ Ajouter un performeur</button>
                </div>

                <!-- Sécurité -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">

                <!-- Action -->
                <button type="submit" id="submitBtn">Créer le groupe</button>
            </form>

            <!-- Retour utilisateur -->
            <div id="createGroupResult"></div>
        </section>
        <section>
            <h2>Créer un Auteur</h2>
            <p>Renseigne le nom et le prénom de l’auteur.</p>
            <form id="createAuteurForm">
                <label for="nomAuteur">Nom</label>
                <input type="text" id="nomAuteur" name="nom" placeholder="Nom" required>

                <label for="prenomAuteur">Prénom</label>
                <input type="text" id="prenomAuteur" name="prenom" placeholder="Prénom" required>

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">

                <button type="submit">Créer l’auteur</button>
            </form>
            <div id="createAuteurResult"></div>
        </section>

        <section>
            <h2>Créer un Metteur en Scène</h2>
            <p>Renseigne le nom et le prénom du metteur en scène.</p>
            <form id="createMetteurForm">
                <label for="nomMetteur">Nom</label>
                <input type="text" id="nomMetteur" name="nom" placeholder="Nom" required>

                <label for="prenomMetteur">Prénom</label>
                <input type="text" id="prenomMetteur" name="prenom" placeholder="Prénom" required>

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($view['token_csrf'], ENT_QUOTES, 'UTF-8') ?>">

                <button type="submit">Créer le metteur en scène</button>
            </form>
            <div id="createMetteurResult"></div>
        </section>

    </main>

    <?php require 'View/Layout/Footer.php'; ?>

    <script>
        // Variables globales pour le JS
        const BASE_URL = <?= json_encode(BASE_URL) ?>;
        window.ROLES = <?= json_encode($view['roles'], JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="<?= BASE_URL ?>/Javascript/GestionGroupe.js"></script>
</body>

</html>