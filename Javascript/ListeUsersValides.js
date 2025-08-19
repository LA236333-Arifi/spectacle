const API_LIST_URL = BASE_URL + '/user/data/users';
const API_APPLY_URL = BASE_URL + '/user/toggle';

// Récupération de la config depuis le HTML
var CONFIG_EL = document.getElementById('config');
var USER_STATUTS = JSON.parse(CONFIG_EL.dataset.userstatuts);
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

var currentToggleUser = null;
var originalStatusId = null;

function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, function (m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

function fetchUsers() {
    clearAlert();
    const params = new URLSearchParams
        ({
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
    els.pageIndicator.textContent = 'Page ' + state.currentPage + ' sur ' + state.totalPages;
    els.prevBtn.disabled = state.currentPage <= 1;
    els.nextBtn.disabled = state.currentPage >= state.totalPages;

    if (state.users.length === 0) {
        els.tbody.innerHTML = '<tr><td colspan="6">Aucun utilisateur</td></tr>';
        return;
    }

    els.tbody.innerHTML = state.users.map(u => {
        var statutId = Number(u.statut_utilisateur_id);
        return `
        <tr data-key="${escapeHtml(String(u.utilisateur_id))}" data-statut="${statutId}">
            <td>${escapeHtml(u.nom_utilisateur)}</td>
            <td>${escapeHtml(u.prenom_utilisateur)}</td>
            <td>${escapeHtml(u.mail_utilisateur)}</td>
            <td>${escapeHtml(u.nom_role_utilisateur)}</td>
            <td>
                ${renderStatusPill(statutId)}
                <button class="switch ${statutId === 3 ? 'on' : ''}" data-action="toggle"></button>
            </td>
        </tr>
    `;
    }).join('');

    bindRowEvents();
}

function bindRowEvents() {
    document.querySelectorAll('[data-action="toggle"]').forEach(btn => {
        btn.addEventListener('click', e => {
            var tr = e.target.closest('tr');
            tr.classList.add('row-pending'); // surlignage visuel
            var key = tr.getAttribute('data-key');
            originalStatusId = Number(tr.getAttribute('data-statut'));
            currentToggleUser = state.users.find(u => String(u.utilisateur_id) === key);

            els.popupText.textContent = 'Voulez-vous vraiment changer le statut de cet utilisateur ?';
            els.popup.style.display = 'flex';
        });
    });
}

// Confirmation
els.popupYes.addEventListener('click', function () {
    if (!currentToggleUser) return;

    const formData = new FormData();
    formData.append('id', currentToggleUser.utilisateur_id);
    formData.append('csrf_token', CSRF_TOKEN);

    fetch(API_APPLY_URL, {
        method: 'POST',
        headers:
        {
            'Accept': 'application/json',
        },
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                var tr = els.tbody.querySelector(`tr[data-key="${currentToggleUser.utilisateur_id}"]`);
                tr.setAttribute('data-statut', data.actif);
                var pillContainer = tr.querySelector('td:nth-child(5)');
                var switchBtn = tr.querySelector('.switch');

                pillContainer.querySelector('.status-pill').outerHTML = renderStatusPill(data.actif);
                switchBtn.className = 'switch ' + (data.actif === 3 ? 'on' : '');

                tr.classList.remove('row-pending');
                showAlert(data.message, false, 3000);
            } else {
                throw new Error();
            }
        })
        .catch(() => {
            revertToggleUI();
            showAlert('Une erreur s\'est produite. Veuillez réessayer plus tard.', true);
        })
        .finally(() => {
            els.popup.style.display = 'none';
            currentToggleUser = null;
            originalStatusId = null;
        });
});

els.popupNo.addEventListener('click', function () {
    revertToggleUI();
    els.popup.style.display = 'none';
    currentToggleUser = null;
    originalStatusId = null;
});

function renderStatusPill(statutId) {
    var isActive = (statutId === 3);
    var text = isActive ? USER_STATUTS[3] : USER_STATUTS[2];
    var cssClass = 'status-pill ' + (isActive ? 'active' : 'inactive');
    return `<span class="${cssClass}">${escapeHtml(text)}</span>`;
}

function revertToggleUI() {
    if (!currentToggleUser) return;
    var tr = els.tbody.querySelector(`tr[data-key="${currentToggleUser.utilisateur_id}"]`);
    tr.setAttribute('data-statut', originalStatusId);
    var isActive = (originalStatusId === 3);
    var pill = tr.querySelector('.status-pill');
    pill.textContent = isActive ? USER_STATUTS[3] : USER_STATUTS[2];
    pill.className = 'status-pill ' + (isActive ? 'active' : 'inactive');
    var switchBtn = tr.querySelector('.switch');
    switchBtn.className = 'switch ' + (isActive ? 'on' : '');
    tr.classList.remove('row-pending');
}

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
        setTimeout(function () { els.alert.style.display = 'none'; }, autoHideMs);
    }
}

function clearAlert() {
    els.alert.style.display = 'none';
    els.alert.textContent = '';
}

// Pagination events
els.prevBtn.addEventListener('click', function () { goTo(state.currentPage - 1); });
els.nextBtn.addEventListener('click', function () { goTo(state.currentPage + 1); });

// Init
fetchUsers();
