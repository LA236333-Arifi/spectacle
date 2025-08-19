function $(id) { return document.getElementById(id); }
function showMessage(node, text, type) {
    node.textContent = text;
    node.className = 'message ' + (type || 'info');
    node.style.display = text ? 'block' : 'none';
}
function fetchJson(url) {
    return fetch(url).then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    });
}

/* Tabs */
document.addEventListener('DOMContentLoaded', function () {
    var tabButtons = document.querySelectorAll('.tab-btn');
    var panels = document.querySelectorAll('.tab-panel');
    tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            tabButtons.forEach(function (b) { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
            panels.forEach(function (p) { p.classList.remove('active'); p.setAttribute('aria-hidden', 'true'); });
            btn.classList.add('active'); btn.setAttribute('aria-selected', 'true');
            var id = btn.getAttribute('data-tab');
            var panel = document.getElementById(id);
            panel.classList.add('active'); panel.setAttribute('aria-hidden', 'false');
        });
    });
});

/* Chargement des spectacles (réutilisable) */
function loadSpectaclesInto(select) {
    select.innerHTML = '<option value="">-- Chargement... --</option>';
    fetchJson(BASE_URL + '/spectacle/data/all')
        .then(function (json) {
            if (json.status !== 'success' || !Array.isArray(json.spectacles)) {
                select.innerHTML = '<option value="">Aucun spectacle</option>';
                return;
            }
            var opts = ['<option value="">-- Sélectionnez un spectacle --</option>'];
            json.spectacles.forEach(function (s) {
                opts.push('<option value="' + String(s.spectacle_id) + '">' + s.nom_spectacle + '</option>');
            });
            select.innerHTML = opts.join('');
        })
        .catch(function () {
            select.innerHTML = '<option value="">Erreur de chargement</option>';
        });
}

/* Chargement des séances (dates) d’un spectacle */
function loadSeanceDates(select, spectacleId) {
  // État initial
  select.disabled = true;
  select.innerHTML = '<option value="">Chargement des dates...</option>';

  fetch(BASE_URL + '/seance/date?spectacle_id=' + encodeURIComponent(spectacleId))
    .then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    })
    .then(function (json) {
      if (json.status !== 'success' || !Array.isArray(json.seances) || json.seances.length === 0) {
        select.innerHTML = '<option value="">Aucune séance programmée</option>';
        return;
      }

      // On construit les options : value = seance_id, texte = date lisible
      var opts = ['<option value="">-- Sélectionnez une séance --</option>'];
      json.seances.forEach(function (s) {
        opts.push('<option value="' + s.seance_id + '">' + s.date_soiree_seance + '</option>');
      });

      select.innerHTML = opts.join('');
      select.disabled = false;
    })
    .catch(function () {
      select.innerHTML = '<option value="">Erreur de chargement</option>';
    });
}

/* DOM ready logique principale */
document.addEventListener('DOMContentLoaded', function () {
    var selAdd = $('spectacle-select-add');
    var selManage = $('spectacle-select-manage');
    var selDate = $('seance-date-select');

    var msgAdd = $('msg-add');
    var msgManage = $('msg-manage');

    var btnAdd = $('btn-add-seance');
    var btnMove = $('btn-move-seance');
    var btnCancel = $('btn-cancel-seance');

    // Charger les listes de spectacles dans les deux onglets
    loadSpectaclesInto(selAdd);
    loadSpectaclesInto(selManage);

    // Quand on choisit un spectacle dans l’onglet gérer, charger ses dates
    selManage.addEventListener('change', function (e) {
        var id = e.target.value;
        if (!id) {
            selDate.innerHTML = '<option value="">Sélectionnez d’abord un spectacle</option>';
            selDate.disabled = true;
            return;
        }
        loadSeanceDates(selDate, id);
    });

    /* Ajouter une séance */
    btnAdd.addEventListener('click', function () {
        showMessage(msgAdd, '', 'info');

        var form = $('form-add-seance');
        if (!selAdd.value) {
            showMessage(msgAdd, 'Veuillez sélectionner un spectacle.', 'error');
            return;
        }
        var dateAdd = $('date-seance-add').value;
        if (!dateAdd) {
            showMessage(msgAdd, 'Veuillez choisir une date.', 'error');
            return;
        }

        var fd = new FormData(form);
        // Renommer les clés pour le backend
        fd.set('spectacle_id', fd.get('spectacle'));
        fd.delete('spectacle');
        // Date alignée avec la colonne/endpoint (adapter si le backend attend un autre nom)
        fd.set('date_soiree_seance', dateAdd);
        fd.delete('date_seance');

        btnAdd.disabled = true;
        fetch(BASE_URL + '/seance/add', { method: 'POST', body: fd })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.status === 'success') {
                    showMessage(msgAdd, json.message || 'Séance ajoutée avec succès.', 'success');
                    form.reset();
                } else {
                    showMessage(msgAdd, json.message || 'Une erreur est survenue lors de l’ajout.', 'error');
                }
            })
            .catch(function () {
                showMessage(msgAdd, 'Erreur réseau. Réessayez plus tard.', 'error');
            })
            .finally ? // safeguard if .finally not supported
            btnAdd.disabled = false
            : (function () { btnAdd.disabled = false; })();
    });

    /* Déplacer une séance */
    btnMove.addEventListener('click', function () {
        showMessage(msgManage, '', 'info');

        var form = $('form-manage-seance');
        var spectacleId = selManage.value;
        var oldDate = selDate.value;
        var newDate = $('date-seance-new').value;

        if (!spectacleId) {
            showMessage(msgManage, 'Veuillez sélectionner un spectacle.', 'error');
            return;
        }
        if (!oldDate) {
            showMessage(msgManage, 'Veuillez sélectionner une séance programmée à déplacer.', 'error');
            return;
        }
        if (!newDate) {
            showMessage(msgManage, 'Veuillez indiquer la nouvelle date.', 'error');
            return;
        }

        var fd = new FormData(form);
        fd.set('spectacle_id', fd.get('spectacle'));
        fd.delete('spectacle');

        // Noms de champs attendus par l'API move
        fd.set('seance_id', oldDate); // oldDate contient en fait le seance_id
        fd.set('nouvelle_date_soiree', newDate);
        fd.delete('date_seance_existante');
        fd.delete('nouvelle_date_seance');

        btnMove.disabled = true;
        fetch(BASE_URL + '/seance/move', { method: 'POST', body: fd })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.status === 'success') {
                    showMessage(msgManage, json.message || 'Séance déplacée avec succès.', 'success');
                    // Rafraîchir la liste des dates
                    loadSeanceDates(selDate, spectacleId);
                    $('date-seance-new').value = '';
                } else {
                    showMessage(msgManage, json.message || 'Impossible de déplacer la séance.', 'error');
                }
            })
            .catch(function () {
                showMessage(msgManage, 'Erreur réseau. Réessayez plus tard.', 'error');
            })
            .finally ? btnMove.disabled = false : (function () { btnMove.disabled = false; })();
    });

    /* Annuler une séance */
    btnCancel.addEventListener('click', function () {
        showMessage(msgManage, '', 'info');

        var form = $('form-manage-seance');
        var spectacleId = selManage.value;
        var dateToCancel = selDate.value;

        if (!spectacleId) {
            showMessage(msgManage, 'Veuillez sélectionner un spectacle.', 'error');
            return;
        }
        if (!dateToCancel) {
            showMessage(msgManage, 'Veuillez sélectionner une séance à annuler.', 'error');
            return;
        }

        if (!confirm("Confirmer l'annulation ? Cette action est irréversible.")) {
            return;
        }

        var fd = new FormData(form);
        fd.set('spectacle_id', fd.get('spectacle'));
        fd.delete('spectacle');
        fd.set('seance_id', dateToCancel); // dateToCancel contient en fait le seance_id
        fd.delete('date_seance_existante');
        fd.delete('nouvelle_date_seance');

        btnCancel.disabled = true;
        fetch(BASE_URL + '/seance/cancel', { method: 'POST', body: fd })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.status === 'success') {
                    showMessage(msgManage, json.message || 'Séance annulée avec succès.', 'success');
                    // Mettre à jour la liste (la date annulée ne devrait plus apparaître)
                    loadSeanceDates(selDate, spectacleId);
                } else {
                    showMessage(msgManage, json.message || 'Impossible d’annuler la séance.', 'error');
                }
            })
            .catch(function () {
                showMessage(msgManage, 'Erreur réseau. Réessayez plus tard.', 'error');
            })
            .finally ? btnCancel.disabled = false : (function () { btnCancel.disabled = false; })();
    });
});
