let performeurCount = 0;

function createRoleSelect() {
    const select = document.createElement('select');
    select.name = 'role_performeur_id[]';
    select.required = true;

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Sélectionner un rôle';
    placeholder.disabled = true;
    placeholder.selected = true;
    select.appendChild(placeholder);

    Object.entries(window.ROLES).forEach(([id, nom]) => {
        const option = document.createElement('option');
        option.value = id;
        option.textContent = nom;
        select.appendChild(option);
    });

    return select;
}

function addPerformeurField() {
    const container = document.getElementById('performeursFields');
    const div = document.createElement('div');
    div.className = 'performeur-block';

    const nomInput = document.createElement('input');
    nomInput.type = 'text';
    nomInput.name = 'nom_performeur[]';
    nomInput.placeholder = 'Nom';
    nomInput.required = true;

    const prenomInput = document.createElement('input');
    prenomInput.type = 'text';
    prenomInput.name = 'prenom_performeur[]';
    prenomInput.placeholder = 'Prénom';
    prenomInput.required = true;

    const roleSelect = createRoleSelect();
    roleSelect.name = 'role_performeur_id[]';

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'remove-performeur';
    removeBtn.textContent = '🗑️ Supprimer';

    removeBtn.addEventListener('click', () => {
        if (performeurCount > 1) {
            div.remove();
            performeurCount--;
            checkPerformeurCount();
        }
    });

    div.append(nomInput, prenomInput, roleSelect, removeBtn);
    container.appendChild(div);

    performeurCount++;
    checkPerformeurCount();
}

function checkPerformeurCount() {
    document.getElementById('submitBtn').disabled = (performeurCount === 0);
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('createGroupForm');
    const resultDiv = document.getElementById('createGroupResult');

    // 1 performeur par défaut
    addPerformeurField();

    document.getElementById('addPerformeurBtn').addEventListener('click', addPerformeurField);

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const data = new FormData();
        data.append('nom_groupe', form.nom_groupe.value.trim());
        data.append('csrf_token', form.csrf_token.value);

        const noms = form.querySelectorAll('[name="nom_performeur[]"]');
        const prenoms = form.querySelectorAll('[name="prenom_performeur[]"]');
        const roles = form.querySelectorAll('[name="role_performeur_id[]"]');

        for (let i = 0; i < noms.length; i++) {
            const nom = noms[i].value.trim();
            const prenom = prenoms[i].value.trim();
            const roleId = roles[i].value;

            if (!nom || !prenom || !roleId) {
                resultDiv.textContent = 'Tous les champs des performeurs doivent être remplis.';
                return;
            }

            // Clés pour que PHP les lise comme $_POST['performeurs'][$i]['nom_performeur'], etc.
            data.append(`performeurs[${i}][nom_performeur]`, nom);
            data.append(`performeurs[${i}][prenom_performeur]`, prenom);
            data.append(`performeurs[${i}][role_performeur_id]`, roleId);
        }

        fetch(BASE_URL + '/groupe/add', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(json => {
            resultDiv.textContent = json.message || 'Groupe créé avec succès.';
            form.reset();
            document.getElementById('performeursFields').innerHTML =
                '<h3>Performeurs</h3><button type="button" id="addPerformeurBtn">➕ Ajouter un performeur</button>';
            performeurCount = 0;
            document.getElementById('addPerformeurBtn').addEventListener('click', addPerformeurField);
            addPerformeurField();
        })
        .catch(err => {
            console.error(err);
            resultDiv.textContent = 'Erreur lors de la création du groupe.';
        });
    });
});

function handleSimpleCreate(formId, resultId, endpoint) {
    const form = document.getElementById(formId);
    const resultDiv = document.getElementById(resultId);

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const nom = form.nom.value.trim();
        const prenom = form.prenom.value.trim();
        const csrfToken = form.csrf_token.value;

        if (!nom || !prenom) {
            resultDiv.textContent = 'Nom et prénom sont requis.';
            return;
        }

        const data = new FormData();
        data.append('nom', nom);
        data.append('prenom', prenom);
        data.append('csrf_token', csrfToken);

        fetch(`${BASE_URL}${endpoint}`, {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(json => {
            resultDiv.textContent = json.message || 'Création réussie.';
            form.reset();
        })
        .catch(err => {
            console.error(err);
            resultDiv.textContent = 'Erreur lors de la création.';
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    handleSimpleCreate('createAuteurForm', 'createAuteurResult', '/auteur/add');
    handleSimpleCreate('createMetteurForm', 'createMetteurResult', '/metteur/add');
});

