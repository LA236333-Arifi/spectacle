document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('spectacle-root');
    if (!root) return;

    var spectacleId = root.dataset.id;
    if (!spectacleId) return;

    var endpoint = BASE_URL + '/spectacle/data/view?id=' + encodeURIComponent(spectacleId);

    // Sélecteurs
    var elTitle = root.querySelector('.spectacle-title');
    var elHook = root.querySelector('.spectacle-hook');
    var elPrice = document.getElementById('spectacle-price');
    var elDuration = document.getElementById('spectacle-duration');
    var elType = document.getElementById('spectacle-type');
    var elSeances = document.getElementById('spectacle-seances');
    var elCredits = document.getElementById('spectacle-credits');
    var sectionCredits = elCredits ? elCredits.closest('section') : null; // +++
    var elGroupName = document.getElementById('spectacle-group-name');
    var elGroupDate = document.getElementById('spectacle-group-date');
    var elMembers = document.getElementById('spectacle-members');

    renderLoading();

    fetch(endpoint, { headers: { 'Accept': 'application/json' } })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (data) {
            if (!data || data.status !== 'success' || !data.spectacle) {
                throw new Error('Réponse invalide');
            }
            renderSpectacle(data.spectacle);
        })
        .catch(function (err) {
            renderError(err.message);
        });

    function renderLoading() {
        elTitle.textContent = 'Chargement…';
        elHook.textContent = '';
        elPrice.textContent = '';
        elDuration.textContent = '';
        elType.textContent = '';
        elSeances.innerHTML = '<li>Chargement…</li>';

        // Masquer la section Crédits pendant le chargement
        if (sectionCredits) sectionCredits.hidden = true;   
        elCredits.innerHTML = '';                

        elGroupName.textContent = '';
        elGroupDate.textContent = '';
        elMembers.innerHTML = '<li>Chargement…</li>';
    }

    function renderError(msg) {
        elTitle.textContent = 'Erreur';
        elHook.textContent = msg;
        elSeances.innerHTML = '';

        // Ne pas afficher la section Crédits en cas d’erreur
        if (sectionCredits) sectionCredits.hidden = true;
        elCredits.innerHTML = ''; 

        elMembers.innerHTML = '';
    }

    function renderSpectacle(sp) {
        elTitle.textContent = safe(sp.nom_spectacle) || 'Spectacle';
        elHook.textContent = sp.texte_accroche_spectacle || '';
        elPrice.textContent = formatPrice(sp.prix_spectacle);
        elDuration.textContent = formatDuration(sp.duree_minutes_spectacle);
        elType.textContent = safe(sp.nom_type_spectacle) || '—';

        renderSeances(sp.seances || []);
        renderCredits(sp.nom_auteur, sp.nom_metteur_en_scene); // -- utilise la logique ci-dessous

        elGroupName.textContent = safe(sp.nom_groupe) || '—';
        elGroupDate.textContent = formatDate(frDate(sp.date_formation_groupe));
        renderMembers(sp.performeurs || []);
    }

    function renderSeances(items) {
        if (!items.length) {
            elSeances.innerHTML = '<li>Aucune séance programmée.</li>';
            return;
        }
        elSeances.innerHTML = '';
        items.forEach(function (s) {
            var li = document.createElement('li');
            li.textContent = formatDate(frDate(s.date_soiree_seance));
            elSeances.appendChild(li);
        });
    }

    // Afficher UNIQUEMENT si auteur ET metteur existent
    function renderCredits(auteur, metteur) {
        var a = safe(auteur).trim();
        var m = safe(metteur).trim();
        var show = !!(a && m);

        if (!sectionCredits) return;

        if (!show) {
            sectionCredits.hidden = true;      // masquer toute la section
            elCredits.innerHTML = '';          // pas de “Non renseigné.”
            return;
        }

        // Afficher + remplir quand les 2 sont là
        sectionCredits.hidden = false;
        elCredits.innerHTML = ''
            + '<div>Auteur&nbsp;: ' + a + '</div>'
            + '<div>Metteur en scène&nbsp;: ' + m + '</div>';
    }

    function renderMembers(items) {
        if (!items.length) {
            elMembers.innerHTML = '<li>Aucun membre.</li>';
            return;
        }
        elMembers.innerHTML = '';
        items.forEach(function (p) {
            var li = document.createElement('li');
            var nom = [p.prenom_performeur, p.nom_performeur].filter(Boolean).join(' ');
            var role = p.nom_role_performeur ? ' (' + p.nom_role_performeur + ')' : '';
            li.textContent = (nom || 'Membre') + role;
            elMembers.appendChild(li);
        });
    }

    // Utils
    function safe(v) { return v == null ? '' : String(v); }
    function formatPrice(val) {
        var n = Number(val);
        return isFinite(n) ? n.toFixed(2).replace('.', ',') + ' €' : '—';
    }
    function formatDuration(min) {
        var m = parseInt(min, 10);
        if (!isFinite(m) || m < 0) return '—';
        var h = Math.floor(m / 60), r = m % 60;
        if (h && r) return h + ' h ' + r + ' min';
        if (h) return h + ' h';
        return m + ' min';
    }
    function frDate(iso) {
        if (!iso) return '';
        var p = String(iso).split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : String(iso);
    }
    function formatDate(str) { return str || '—'; }
});
