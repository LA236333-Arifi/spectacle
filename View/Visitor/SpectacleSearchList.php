<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rechercher Spectacle</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/Styles/SpectacleSearchList.css">
</head>
<body>
  <?php require_once 'View/Layout/Header.php'; ?>

  <main class="container">
    <header class="search-header">
      <h1>Rechercher un spectacle</h1>
      <form id="searchForm" class="search-form" role="search" aria-label="Recherche de spectacles">
        <input
          id="searchInput"
          name="query"
          type="search"
          placeholder="Recherchez par titre, groupe, performeur..."
          autocomplete="off"
          aria-label="Requête de recherche"
          required
        />
        <button class="btn primary" type="submit">Rechercher</button>
      </form>
      <div class="meta-row">
        <p id="resultCount" class="muted">Saisissez un mot-clé pour commencer</p>
        <div id="alert" class="alert" role="alert" style="display:none;"></div>
      </div>
      <div id="statusMsg" class="sr-only" aria-live="polite"></div>
    </header>

    <section id="resultsSection" class="results-section">
      <div id="loader" class="loader" style="display:none;">
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
      </div>

      <div id="emptyState" class="empty-state" style="display:none;">
        <p>Aucun spectacle trouvé pour cette recherche.</p>
        <ul>
          <li><strong>Astuce:</strong> Essayez un mot-clé plus court.</li>
          <li><strong>Exemples:</strong> “comédie”, “Shakespeare”, “impro”.</li>
        </ul>
      </div>

      <div id="resultsList" class="results-list" aria-live="polite"></div>
    </section>

    <!-- Template de carte spectacle -->
    <template id="itemTemplate">
      <article class="spectacle-item" tabindex="0">
        <div class="item-body">
          <h3 class="titre"></h3>
          <p class="accroche"></p>

          <div class="infos-cle"></div>

          <div class="meta">
            <div class="groupe"></div>
            <div class="auteur"></div>
          </div>
        </div>
        <footer class="item-actions">
          <a class="btn link" target="_self" rel="noopener" href="#">Voir le détail</a>
        </footer>
      </article>
    </template>
  </main>

  <?php require 'View/Layout/Footer.php'; ?>

  <script>
    // On définit la base URL depuis le PHP pour le JS
    const BASE_URL = <?= json_encode(BASE_URL) ?>;
  </script>
  <script src="<?= BASE_URL ?>/Javascript/SpectacleSearchList.js"></script>
</body>
</html>
