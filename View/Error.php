<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($error->title ?? 'Erreur') ?> - Salle de Spectacle</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/Error.css">
</head>

<body>
    <?php require_once 'View/Layout/Header.php'; ?>
    <main>
        <h1><?= htmlspecialchars($error->title ?? 'Erreur') ?></h1>
        <p><?= htmlspecialchars($error->message ?? 'Une erreur est survenue. Veuillez réessayer plus tard.') ?></p>
        <a href="<?= BASE_URL ?>/">Retour à l'accueil</a>
    </main>

    <?php require 'View/Layout/Footer.php'; ?>
</body>

</html>