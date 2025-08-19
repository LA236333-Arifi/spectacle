const form = document.getElementById('profileForm');
const saveBtn = document.getElementById('saveBtn');

// Valeurs initiales
const initialValues = {
    nom: document.getElementById('nom').value,
    prenom: document.getElementById('prenom').value,
    email: document.getElementById('email').value,
    password: ''
};

// Champs
const emailInput = document.getElementById('email');
const emailConfirmGroup = document.getElementById('emailConfirmGroup');

const passwordInput = document.getElementById('password');
const passwordConfirmGroup = document.getElementById('passwordConfirmGroup');

// Afficher/masquer confirm email
emailInput.addEventListener('input', () => {
    if (emailInput.value !== initialValues.email) {
        emailConfirmGroup.classList.remove('hidden');
    } else {
        emailConfirmGroup.classList.add('hidden');
    }
    checkChanges();
});

// Afficher/masquer confirm password
passwordInput.addEventListener('input', () => {
    if (passwordInput.value.length > 0) {
        passwordConfirmGroup.classList.remove('hidden');
    } else {
        passwordConfirmGroup.classList.add('hidden');
    }
    checkChanges();
});

// Revert bouton 
document.querySelectorAll('.reset-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const targetId = btn.dataset.target;
        const input = document.getElementById(targetId);
        input.value = initialValues[targetId] || '';
        if (targetId === 'email') emailInput.dispatchEvent(new Event('input'));
        if (targetId === 'password') passwordInput.dispatchEvent(new Event('input'));
        checkChanges();
    });
});

// Détection de changements
form.querySelectorAll('input').forEach(input => {
    input.addEventListener('input', checkChanges);
});

function checkChanges() {
    let changed = false;
    if (document.getElementById('nom').value !== initialValues.nom) changed = true;
    if (document.getElementById('prenom').value !== initialValues.prenom) changed = true;
    if (document.getElementById('email').value !== initialValues.email) changed = true;
    if (document.getElementById('password').value.length > 0) changed = true;

    saveBtn.disabled = !changed;
}

// Soumission
form.addEventListener('submit', e => {
    e.preventDefault();

    const fd = new FormData(form);

    fetch(`${BASE_URL}/profile/update`, {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (!data || !data.message) {
            showNotification("Réponse invalide du serveur.", true);
            return;
        }

        // data.status peut être "success" ou "error"
        const isError = data.status !== 'success';
        showNotification(data.message, isError);

        // Réinitialiser l'état si succès
        if (!isError) {
            saveBtn.disabled = true;
            // mettre à jour les valeurs initiales
            initialValues.nom = document.getElementById('nom').value;
            initialValues.prenom = document.getElementById('prenom').value;
            initialValues.email = document.getElementById('email').value;
            initialValues.password = '';
        }
    })
    .catch(() => {
        showNotification("Impossible de contacter le serveur.", true);
    });
});

// Fonction générique d’affichage de notification
function showNotification(message, isError) {
    let notif = document.querySelector('.notif');
    if (!notif) {
        notif = document.createElement('div');
        notif.className = 'notif';
        document.body.appendChild(notif);
    }
    notif.textContent = message;
    notif.className = `notif ${isError ? 'error' : 'success'}`;

    // Auto-masquer après 4s
    setTimeout(() => {
        notif.remove();
    }, 4000);
}
