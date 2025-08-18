// Types qui exigent auteur + metteur
var TYPES_NEED_AM = [1, 4, 5];

function el(id) { return document.getElementById(id); }
function show(elm) { elm.style.display = ''; }
function hide(elm) { elm.style.display = 'none'; }

function toggleAuteurMetteurByType(typeId) {
    var needs = TYPES_NEED_AM.includes(parseInt(typeId, 10));
    var auteurC = el('auteurContainer');
    var metteurC = el('metteurContainer');

    if (needs) {
        show(auteurC);
        show(metteurC);
        el('auteur_id').setAttribute('required', 'required');
        el('metteur_id').setAttribute('required', 'required');
    } else {
        hide(auteurC);
        hide(metteurC);
        el('auteur_id').removeAttribute('required');
        el('metteur_id').removeAttribute('required');
        el('auteur_id').value = '';
        el('metteur_id').value = '';
    }
}

function clearSelect(select, placeholder) {
    select.innerHTML = '';
    var opt = document.createElement('option');
    opt.value = '';
    opt.disabled = true;
    opt.selected = true;
    opt.textContent = placeholder || 'Choisir une option';
    select.appendChild(opt);
}

function populateSelectFromArray(select, arr, idKey, labelCb) {
    arr.forEach(function(item) {
        var opt = document.createElement('option');
        opt.value = item[idKey];
        opt.textContent = labelCb(item);
        select.appendChild(opt);
    });
}

function fetchJson(url) {
    return fetch(url).then(function(res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    });
}

function formatDateDDMMYYYY(dateStr) {
    const d = new Date(dateStr);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
}

function loadGroupes(select) {
    fetchJson(BASE_URL + '/groupe/list')
        .then(function(json) {
            clearSelect(select, 'Choisir un groupe');

            json.forEach(function(g) {
                var opt = document.createElement('option');
                opt.value = g.groupe_id;
                // On inclut la date en format lisible si utile :
                opt.textContent = g.nom_groupe + ' (' + formatDateDDMMYYYY(g.date_formation_groupe) + ')';
                select.appendChild(opt);
            });
        })
        .catch(function(err){
            console.error('Erreur chargement groupes:', err);
        });
}

function loadAuteurs(select) {
    fetchJson(BASE_URL + '/auteur/list')
        .then(function(json) {
            clearSelect(select, 'Choisir un auteur');
            json.forEach(function(a) {
                const opt = document.createElement('option');
                opt.value = a.auteur_id;
                opt.textContent = `${a.nom_auteur} ${a.prenom_auteur}`;
                select.appendChild(opt);
            });
        })
        .catch(err => console.error('Erreur chargement auteurs:', err));
}

function loadMetteurs(select) {
    fetchJson(BASE_URL + '/metteur/list')
        .then(function(json) {
            clearSelect(select, 'Choisir un metteur en scène');
            json.forEach(function(m) {
                const opt = document.createElement('option');
                opt.value = m.metteur_scene_id;
                opt.textContent = `${m.nom_metteur_scene} ${m.prenom_metteur_scene}`;
                select.appendChild(opt);
            });
        })
        .catch(err => console.error('Erreur chargement metteurs:', err));
}

function showServerMessage(selector, message, type = 'success') {
    const el = document.querySelector(selector);
    if (!el) return;
    el.textContent = message;
    el.className = type; // 'success' ou 'error'
    el.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    var form = el('createSpectacleForm');
    var resultDiv = el('createSpectacleResult');

    // Charger les listes au chargement
    loadGroupes(el('groupe_id'));
    loadAuteurs(el('auteur_id'));
    loadMetteurs(el('metteur_id'));

    // Gérer le masquage/auteur en fonction du type
    toggleAuteurMetteurByType(el('type_spectacle_id').value);
    el('type_spectacle_id').addEventListener('change', function(e) {
        toggleAuteurMetteurByType(e.target.value);
    });

    // Envoi du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var data = new FormData(form);
        var typeId = parseInt(form.type_spectacle_id.value, 10);
        var needsAM = TYPES_NEED_AM.includes(typeId);

        // Validation minimale côté client
        if (!form.nom_spectacle.value.trim()) return resultDiv.textContent = 'Nom du spectacle requis.';
        if (!form.texte_accroche_spectacle.value.trim()) return resultDiv.textContent = 'Texte d’accroche requis.';
        if (!form.prix_spectacle.value) return resultDiv.textContent = 'Prix requis.';
        if (!form.duree_minutes_spectacle.value) return resultDiv.textContent = 'Durée requise.';
        if (!form.groupe_id.value) return resultDiv.textContent = 'Groupe requis.';

        if (!needsAM) {
            data.delete('auteur_id');
            data.delete('metteur_id');
        } else {
            if (!form.auteur_id.value || !form.metteur_id.value) {
                return resultDiv.textContent = 'Auteur et Metteur en scène requis pour ce type.';
            }
        }

        fetch(BASE_URL + '/spectacle/add', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(json => {
            resultDiv.textContent = json.message || 'Spectacle créé.';
            if (json.status === 'success') {
                form.reset();
                loadGroupes(el('groupe_id'));
                loadAuteurs(el('auteur_id'));
                loadMetteurs(el('metteur_id'));
                toggleAuteurMetteurByType(el('type_spectacle_id').value);
            }
        })
        .catch(err => {
            console.error(err);
            resultDiv.textContent = 'Erreur lors de la création.';
        });
    });
});
