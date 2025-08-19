const spectacleListEl = document.getElementById('spectacleList');
const generateBtn = document.getElementById('generateSelectedPdf');
const itemTemplate = document.getElementById('spectacleItemTemplate');
let selectedSpectacleId = null;

// Switch onglets
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

function createSpectacleElement(s) {
    const clone = itemTemplate.content.cloneNode(true);
    clone.querySelector('.spectacle-item').dataset.id = s.spectacle_id;

    // Titre & accroche
    clone.querySelector('.titre').textContent = s.nom_spectacle;
    clone.querySelector('.accroche').textContent = s.texte_accroche_spectacle || '';

    // Infos clés
    clone.querySelector('.infos-cle').innerHTML = `
        <span><strong>💰 Prix :</strong> ${Number(s.prix_spectacle).toFixed(2)} €</span> —
        <span><strong>⏱ Durée :</strong> ${s.duree_minutes_spectacle} min</span> —
        <span><strong>📅 Séances :</strong> ${s.nb_seances}</span>
    `;

    // Groupe + performeurs
    let groupeHTML = `<strong>🎭 Groupe :</strong> ${s.nom_groupe}`;
    if (Array.isArray(s.performeurs) && s.performeurs.length) {
        groupeHTML += '<ul>' + s.performeurs.map(p =>
            `<li>${p.prenom_performeur} ${p.nom_performeur} — <em>${p.nom_role_performeur}</em></li>`
        ).join('') + '</ul>';
    }
    clone.querySelector('.groupe').innerHTML = groupeHTML;

    // Auteur & metteur si présent
    if (s.auteur_metteur) {
        clone.querySelector('.auteur').innerHTML = `
            <strong>Auteur :</strong> ${s.auteur_metteur.prenom_auteur} ${s.auteur_metteur.nom_auteur} —
            <strong>Metteur en scène :</strong> ${s.auteur_metteur.prenom_metteur_scene} ${s.auteur_metteur.nom_metteur_scene}
        `;
    }

    // Événement de sélection
    clone.querySelector('.spectacle-item').addEventListener('click', e => {
        document.querySelectorAll('.spectacle-item').forEach(i => i.classList.remove('selected'));
        e.currentTarget.classList.add('selected');
        selectedSpectacleId = s.spectacle_id;
        generateBtn.disabled = false;
    });

    return clone;
}

// Charger la liste
fetch(`${BASE_URL}/spectacle/list`)
    .then(res => res.json())
    .then(data => {
        if (!data || data.status !== 'success' || !Array.isArray(data.spectacles)) {
            spectacleListEl.innerHTML = '<div class="spectacle-item">Impossible de récupérer la liste des spectacles.</div>';
            return;
        }
        if (data.spectacles.length === 0) {
            spectacleListEl.innerHTML = '<div class="spectacle-item">Aucun spectacle en cours pour le moment.</div>';
            return;
        }

        // Injection des spectacles
        spectacleListEl.innerHTML = '';
        data.spectacles.forEach(s => {
            spectacleListEl.appendChild(createSpectacleElement(s));
        });
    })
    .catch(() => {
        spectacleListEl.innerHTML = '<div class="spectacle-item">Erreur lors du chargement des spectacles.</div>';
    });

// Générer PDF spectacle choisi
generateBtn.addEventListener('click', () => {
    if (selectedSpectacleId) {
        window.location.href = `${BASE_URL}/spectacle/print?id=${encodeURIComponent(selectedSpectacleId)}`;
    }
});
