// Fichier: SpectacleSearchList.js

(function () {
  const els = {
    form: document.getElementById('searchForm'),
    input: document.getElementById('searchInput'),
    resultCount: document.getElementById('resultCount'),
    alert: document.getElementById('alert'),
    statusMsg: document.getElementById('statusMsg'),
    loader: document.getElementById('loader'),
    empty: document.getElementById('emptyState'),
    list: document.getElementById('resultsList'),
    itemTemplate: document.getElementById('itemTemplate')
  };

  const state = {
    query: '',
    results: [],
    totalResults: 0,
    loading: false
  };

  function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  function getParam(name) {
    const params = new URLSearchParams(window.location.search);
    return params.get(name) || '';
  }

  function setStatus(text) {
    els.statusMsg.textContent = text;
  }

  function showAlert(message, type = 'error') {
    els.alert.textContent = message;
    els.alert.className = 'alert ' + (type === 'success' ? 'success' : 'error');
    els.alert.style.display = '';
  }
  function clearAlert() {
    els.alert.style.display = 'none';
    els.alert.textContent = '';
  }

  function setLoading(isLoading) {
    state.loading = isLoading;
    els.loader.style.display = isLoading ? '' : 'none';
  }

  function priceOrDash(v) {
    const n = Number(v);
    return Number.isFinite(n) ? n.toFixed(2) + ' €' : '—';
  }

  function createSpectacleElement(s) {
    const clone = els.itemTemplate.content.cloneNode(true);
    const root = clone.querySelector('.spectacle-item');
    root.dataset.id = s.spectacle_id;

    // Titre & accroche
    clone.querySelector('.titre').textContent = s.nom_spectacle ?? '';
    clone.querySelector('.accroche').textContent = s.texte_accroche_spectacle || '';

    // Infos clés
    const infos = clone.querySelector('.infos-cle');
    infos.innerHTML = `
      <span>💰 <strong>Prix:</strong> ${escapeHtml(priceOrDash(s.prix_spectacle))}</span>
      <span>⏱ <strong>Durée:</strong> ${escapeHtml(String(s.duree_minutes_spectacle ?? '—'))} min</span>
      <span>📅 <strong>Séances:</strong> ${escapeHtml(String(s.nb_seances ?? '—'))}</span>
    `;

    // Groupe + performeurs
    const groupeEl = clone.querySelector('.groupe');
    let groupeHTML = `🎭 <strong>Groupe:</strong> ${escapeHtml(s.nom_groupe ?? '—')}`;
    if (Array.isArray(s.performeurs) && s.performeurs.length) {
      groupeHTML += '<ul>' + s.performeurs.map(p =>
        `<li>${escapeHtml(p.prenom_performeur ?? '')} ${escapeHtml(p.nom_performeur ?? '')} — <em>${escapeHtml(p.nom_role_performeur ?? '')}</em></li>`
      ).join('') + '</ul>';
    }
    groupeEl.innerHTML = groupeHTML;

    // Auteur & metteur
    const auteurEl = clone.querySelector('.auteur');
    if (s.auteur_metteur) {
      const a = s.auteur_metteur;
      auteurEl.innerHTML = `
        <strong>Auteur:</strong> ${escapeHtml([a.prenom_auteur, a.nom_auteur].filter(Boolean).join(' '))} —
        <strong>Metteur en scène:</strong> ${escapeHtml([a.prenom_metteur_scene, a.nom_metteur_scene].filter(Boolean).join(' '))}
      `;
    } else {
      auteurEl.textContent = '';
    }

    // Actions
    const link = clone.querySelector('.item-actions .btn.link');
    link.href = `${BASE_URL}/spectacle/view?id=${encodeURIComponent(s.spectacle_id)}`;
    link.title = 'Voir le détail du spectacle';

    // Sélection visuelle au clic
    root.addEventListener('click', (e) => {
      if (e.target.closest('a')) return;
      document.querySelectorAll('.spectacle-item.selected').forEach(el => el.classList.remove('selected'));
      root.classList.add('selected');
    });

    return clone;
  }

  function render() {
    // Compteur
    if (!state.query) {
      els.resultCount.textContent = 'Saisissez un mot-clé pour commencer';
    } else if (state.loading) {
      els.resultCount.textContent = `Recherche de « ${state.query} »...`;
    } else {
      els.resultCount.textContent = `${state.totalResults} résultat${state.totalResults > 1 ? 's' : ''} pour « ${state.query} »`;
    }

    // Liste / états
    els.list.innerHTML = '';
    els.empty.style.display = state.loading || !state.query ? 'none' : (state.results.length ? 'none' : '');
    if (!state.loading && state.results.length) {
      const frag = document.createDocumentFragment();
      state.results.forEach(s => frag.appendChild(createSpectacleElement(s)));
      els.list.appendChild(frag);
    }
  }

  // Version sans async/await
  function fetchResults(query) {
    clearAlert();
    if (!query) {
      state.query = '';
      state.results = [];
      state.totalResults = 0;
      render();
      return;
    }

    state.query = query;
    setLoading(true);
    render();
    setStatus('Chargement des spectacles…');

    const url = `${BASE_URL}/spectacle/search/data?query=${encodeURIComponent(query)}`;

    return fetch(url, { headers: { 'Accept': 'application/json' } })
      .then(function (res) {
        return res.json().catch(function () { return null; }).then(function (data) {
          if (!res.ok) {
            throw new Error((data && data.message) || 'Erreur lors de la recherche.');
          }
          return data;
        });
      })
      .then(function (data) {
        if (!data || data.status !== 'success') {
          throw new Error('Réponse invalide');
        }
        state.results = Array.isArray(data.results) ? data.results : [];
        state.totalResults = Number(data.totalResults != null ? data.totalResults : state.results.length);
        setStatus(`${state.totalResults} résultat(s) chargés`);
      })
      .catch(function () {
        showAlert('Une erreur est survenue lors du chargement des spectacles. Veuillez réessayer.', 'error');
        state.results = [];
        state.totalResults = 0;
        setStatus('Erreur de chargement');
      })
      .finally(function () {
        setLoading(false);
        render();
      });
  }

  // Gestion du formulaire
  els.form.addEventListener('submit', (e) => {
    e.preventDefault();
    const q = els.input.value.trim();
    const params = new URLSearchParams(window.location.search);
    if (q) params.set('query', q); else params.delete('query');
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.replaceState({}, '', newUrl);
    fetchResults(q);
  });

  // Initialisation avec le paramètre d’URL
  document.addEventListener('DOMContentLoaded', () => {
    const initialQuery = getParam('query');
    if (initialQuery) els.input.value = initialQuery;
    fetchResults(initialQuery);
  });
})();
