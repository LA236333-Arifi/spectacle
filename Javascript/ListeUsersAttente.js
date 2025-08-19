const API_LIST_URL   = `${BASE_URL}/user/data/access`;
const API_ACCEPT_URL = `${BASE_URL}/user/accept`;
const API_REFUSE_URL = `${BASE_URL}/user/refuse`;

var CONFIG_EL  = document.getElementById('config');
var CSRF_TOKEN = CONFIG_EL.dataset.csrfToken;

var state = {
    users: [],
    totalPages: 1,
    totalUsers: 0,
    currentPage: 1
};

var els = {
    tbody: document.getElementById('usersTbody'),
    userCount: document.getElementById('userCount'),
    pageIndicator: document.getElementById('pageIndicator'),
    prevBtn: document.getElementById('prevBtn'),
    nextBtn: document.getElementById('nextBtn'),
    alert: document.getElementById('alert'),
    popup: document.getElementById('confirmPopup'),
    popupText: document.getElementById('confirmText'),
    popupYes: document.getElementById('confirmYes'),
    popupNo: document.getElementById('confirmNo')
};

var currentAction = null; // 'accept' ou 'refuse'
var currentUser   = null;

function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, m => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
}

function fetchUsers() {
    clearAlert();
    const params = new URLSearchParams({
        page: state.currentPage,
        limit: 10
    });

    fetch(`${API_LIST_URL}?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') throw new Error();
            state.users = data.users || [];
            state.totalPages = data.totalPages || 1;
            state.totalUsers = data.totalUsers || 0;
            state.currentPage = data.currentPage || 1;
            render();
        })
        .catch(() => {
            showAlert('Erreur lors du chargement des données', true);
        });
}

function render() {
    els.userCount.textContent = state.totalUsers;
    els.pageIndicator.textContent = `Page ${state.currentPage} sur ${state.totalPages}`;
    els.prevBtn.disabled = state.currentPage <= 1;
    els.nextBtn.disabled = state.currentPage >= state.totalPages;

    if (state.users.length === 0) {
        els.tbody.innerHTML = '<tr><td colspan="5">Aucun utilisateur à valider</td></tr>';
        return;
    }

    els.tbody.innerHTML = state.users.map(u => `
        <tr data-key="${escapeHtml(String(u.utilisateur_id))}">
            <td>${escapeHtml(u.nom_utilisateur)}</td>
            <td>${escapeHtml(u.prenom_utilisateur)}</td>
            <td>${escapeHtml(u.mail_utilisateur)}</td>
            <td>${escapeHtml(u.nom_role_utilisateur)}</td>
            <td>
                <button class="btn success" data-action="accept">Accepter</button>
                <button class="btn danger"  data-action="refuse">Refuser</button>
            </td>
        </tr>
    `).join('');

    bindRowEvents();
}

function bindRowEvents() {
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', e => {
            var tr = e.target.closest('tr');
            var key = tr.getAttribute('data-key');
            currentUser = state.users.find(u => String(u.utilisateur_id) === key);
            currentAction = e.target.getAttribute('data-action');

            els.popupText.textContent = `Voulez-vous vraiment ${currentAction === 'accept' ? 'accepter' : 'refuser'} cet utilisateur ?`;
            els.popup.style.display = 'flex';
        });
    });
}

els.popupYes.addEventListener('click', function () {
    if (!currentUser || !currentAction) return;

    const endpoint = currentAction === 'accept' ? API_ACCEPT_URL : API_REFUSE_URL;

    const formData = new FormData();
    formData.append('id', currentUser.utilisateur_id);
    formData.append('csrf_token', CSRF_TOKEN);

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showAlert(data.message || `Utilisateur ${currentAction}é avec succès`, false, 3000);
            fetchUsers();
        } else {
            throw new Error();
        }
    })
    .catch(() => {
        showAlert('Une erreur s\'est produite. Veuillez réessayer plus tard.', true);
    })
    .finally(() => {
        els.popup.style.display = 'none';
        currentUser = null;
        currentAction = null;
    });
});

els.popupNo.addEventListener('click', function () {
    els.popup.style.display = 'none';
    currentUser = null;
    currentAction = null;
});

function goTo(page) {
    var p = Math.max(1, Math.min(state.totalPages, page));
    if (p === state.currentPage) return;
    state.currentPage = p;
    fetchUsers();
}

function showAlert(message, isError, autoHideMs) {
    els.alert.textContent = message;
    els.alert.className = 'alert ' + (isError ? 'error' : 'success');
    els.alert.style.display = '';
    if (autoHideMs) {
        setTimeout(() => { els.alert.style.display = 'none'; }, autoHideMs);
    }
}

function clearAlert() {
    els.alert.style.display = 'none';
    els.alert.textContent = '';
}

els.prevBtn.addEventListener('click', () => goTo(state.currentPage - 1));
els.nextBtn.addEventListener('click', () => goTo(state.currentPage + 1));

// Init
fetchUsers();
