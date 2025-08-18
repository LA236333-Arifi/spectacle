<!-- Header CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/styles/header.css">

<header class="main-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?= BASE_URL ?>/">
                    <h1>Salle de Spectacle</h1>
                </a>
            </div>

            <nav class="main-nav">
                <ul class="nav-list">
                    <li><a href="<?= BASE_URL ?>/programmation" class="nav-link">Programmation</a></li>
                    <li><a href="<?= BASE_URL ?>/calendrier" class="nav-link">Calendrier</a></li>

                    <?php if (UserConnectionUtils::isAdminConnected()): ?>
                        <li class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle">Gestion</a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= BASE_URL ?>/spectacle/add/index">Ajouter un spectacle</a></li>
                                <li><a href="<?= BASE_URL ?>/spectacle/cloturer/index">Cloturer un spectacle</a></li>
                                <li><a href="<?= BASE_URL ?>/seance">Gestion des séances</a></li>
                                <li><a href="<?= BASE_URL ?>/groupe">Gestion des groupes et auteurs</a></li>
                                <li><a href="<?= BASE_URL ?>/user/list/access">Valider les accès</a></li>
                                <li><a href="<?= BASE_URL ?>/user/list/users">Listes des utilisateurs</a></li>
                                <li><a href="<?= BASE_URL ?>/spectacle/print">Télécharger PDF</a></li>
                            </ul>
                        </li>
                        <li><a href="<?= BASE_URL ?>/spectacle/stats" class="nav-link">Statistiques</a></li>
                    <?php elseif (UserConnectionUtils::isSecretaireConnected()): ?>
                        <li><a href="<?= BASE_URL ?>/spectacle/print" class="nav-link">Télécharger PDF</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="header-actions">
                <div class="header-search-container">
                    <a href="<?= BASE_URL ?>/spectacle/search" class="header-search-btn-link">🔍 Rechercher</a>
                </div>  

                <div class="auth-section">
                    <?php if (UserConnectionUtils::isUserConnected()): ?>
                        <div class="dropdown user-dropdown">
                            <a href="#" class="nav-link dropdown-toggle">
                                <span class="emoji">👤</span>
                                <span><?= htmlspecialchars($_SESSION['user']['prenom'] ?? 'Utilisateur') ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= BASE_URL ?>/profile">Mon profil</a></li>
                                <li><a href="<?= BASE_URL ?>/logout">Déconnexion</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login" class="btn btn-primary">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>