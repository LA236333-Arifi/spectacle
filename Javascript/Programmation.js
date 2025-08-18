const tbody = document.querySelector('#seances tbody');
const pagination = document.getElementById('pagination');
const form = document.getElementById('filters');

let currentPage = 1;
let lastFiltersKey = '';
let lastParams = null;

// ---------- Utils dates ----------
function toDMY(iso) {
    if (!iso) return '';
    const [y, m, d] = iso.split('-');
    return `${d}-${m}-${y}`;
}

function toISO(dmy) {
    if (!dmy) return '';
    if (/^\d{2}-\d{2}-\d{4}$/.test(dmy)) {
        const [d, m, y] = dmy.split('-');
        return `${y}-${m}-${d}`;
    }
    // Pour <input type="date">, la valeur est déjà yyyy-mm-dd
    return dmy;
}

// ---------- Construction params + clé ----------
function buildParams() {
    const fd = new FormData(form);
    const params = new URLSearchParams();

    // Dates (normalisées en ISO)
    params.set('dateMin', toISO(fd.get('dateMin') || ''));
    params.set('dateMax', toISO(fd.get('dateMax') || ''));

    // Autres filtres (laisser vide si non renseignés)
    ['prixMin', 'prixMax', 'dureeMin', 'dureeMax', 'typeSpectacle', 'statutSeance'].forEach(k => {
        const v = fd.get(k);
        params.set(k, v !== null ? String(v) : '');
    });

    params.set('page', String(currentPage));
    return params;
}

function filtersKey(params) {
    const clone = new URLSearchParams(params);
    clone.delete('page'); // on ignore la page pour la comparaison
    const entries = Array.from(clone.entries())
        .sort(([a], [b]) => a.localeCompare(b));
    return JSON.stringify(entries);
}

// ---------- Fetch + rendu ----------
form.addEventListener('submit', e => {
    e.preventDefault();
    // On repart page 1 si filtres changent
    currentPage = 1;

    const params = buildParams();
    const key = filtersKey(params);

    if (key === lastFiltersKey) {
        // Rien n’a changé: on ne fetch pas
        return;
    }

    lastFiltersKey = key;
    params.set('page', '1');
    lastParams = new URLSearchParams(params);
    fetchSeances(lastParams);
});

function fetchSeances(params = null) {
    const p = params ? new URLSearchParams(params) : buildParams();
    // On mémorise pour pagination si ce n’est pas déjà fait
    lastParams = new URLSearchParams(p);

    console.log('Params envoyés :', p.toString());
    fetch(`${BASE_URL}/seance/list?` + p.toString())
        .then(res => res.json())
        .then(data => render(data.seances));
}

function render(seances) {
    tbody.innerHTML = '';
    if (!seances.items || seances.items.length === 0) {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 6;
        td.className = 'table-empty';
        td.textContent = 'Aucune séance trouvée';
        tr.appendChild(td);
        tbody.appendChild(tr);
        return;
    }
    else {
        seances.items.forEach(seance => {
            const row1 = document.createElement('tr');
            row1.innerHTML = `
        <td>${toDMY(seance.date_soiree_seance)}</td>
        <td>
            <a href="${BASE_URL}/spectacle/view?id=${seance.spectacle_id}">
                ${seance.nom_spectacle}
            </a>
        </td>
        <td>${seance.nom_type_spectacle}</td>
        <td>${seance.duree_minutes_spectacle} min</td>
        <td>${seance.prix_spectacle} €</td>
        <td>${seance.nom_groupe}</td>
      `;
            tbody.appendChild(row1);

            const row2 = document.createElement('tr');
            row2.innerHTML = `<td colspan="6">${seance.texte_accroche_spectacle}</td>`;
            tbody.appendChild(row2);

            const row3 = document.createElement('tr');
            const btn = document.createElement('button');
            btn.textContent = 'Voir plus d\'informations';
            btn.className = 'toggle';
            const td = document.createElement('td');
            td.colSpan = 6;
            td.appendChild(btn);
            row3.appendChild(td);
            tbody.appendChild(row3);

            const row4 = document.createElement('tr');
            row4.className = 'details';
            row4.style.display = 'none';
            const tdDetails = document.createElement('td');
            tdDetails.colSpan = 6;

            let html = '<strong>Performeurs:</strong><ul>';
            seance.membres.forEach(m => {
                html += `<li>${m.prenom_performeur} ${m.nom_performeur} (${m.nom_role_performeur})</li>`;
            });
            html += '</ul>';

            if (seance.auteur_metteur_scene)
            {
                var a = seance.auteur_metteur_scene;

                html += '<strong>Auteur / Metteur en scène:</strong><ul>';
                html += `<li>${a.prenom_auteur} ${a.nom_auteur} (Auteur)</li>`;
                html += `<li>${a.prenom_metteur_scene} ${a.nom_metteur_scene} (Metteur en scène)</li>`;
                html += '</ul>';
            }

            tdDetails.innerHTML = html;
            row4.appendChild(tdDetails);
            tbody.appendChild(row4);

            // Toggle
            btn.addEventListener('click', () => {
                row4.style.display = row4.style.display === 'none' ? 'table-row' : 'none';
            });
        });
    }

    renderPagination(seances);
}

function renderPagination({ totalPages = 1, currentPage: page = 1 }) {
    pagination.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        if (i === page) btn.disabled = true;
        btn.addEventListener('click', () => {
            currentPage = i; // met à jour la variable globale
            // Utilise les derniers filtres validés pour paginer
            const p = new URLSearchParams(lastParams || buildParams());
            p.set('page', String(currentPage));
            fetchSeances(p);
        });
        pagination.appendChild(btn);
    }
}

// Chargement initial (mémorise les filtres par défaut)
lastParams = buildParams();
lastFiltersKey = filtersKey(lastParams);
fetchSeances(lastParams);
