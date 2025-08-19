document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('spectacle-select');
    const btnView = document.getElementById('btn-view');
    const btnCloturer = document.getElementById('btn-cloturer');
    const message = document.getElementById('action-message');

    // Chargement de la liste des spectacles
    fetch(`${BASE_URL}/spectacle/data/all`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                select.innerHTML = '<option value="">-- Sélectionnez un spectacle --</option>';
                data.spectacles.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.spectacle_id;
                    opt.textContent = s.nom_spectacle;
                    select.appendChild(opt);
                });
            } else {
                select.innerHTML = '<option value="">Aucun spectacle trouvé</option>';
            }
        })
        .catch(() => {
            select.innerHTML = '<option value="">Erreur de chargement</option>';
        });

    // Bouton Voir les infos
    btnView.addEventListener('click', () => {
        const id = select.value;
        if (!id) {
            message.textContent = "Veuillez sélectionner un spectacle.";
            return;
        }
        window.open(`${BASE_URL}/spectacle/view?id=${id}`, '_blank');
    });

    // Bouton Clôturer
    btnCloturer.addEventListener('click', () => {
        const id = select.value;
        if (!id) 
        {
            message.textContent = "Veuillez sélectionner un spectacle.";
            return;
        }

        const formElement = document.getElementById('spectacle-form');
        const formData = new FormData(formElement);

        fetch(`${BASE_URL}/spectacle/cloturer`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    message.style.color = "green";
                    message.textContent = "Spectacle clôturé avec succès.";
                } else {
                    message.style.color = "red";
                    message.textContent = "Une erreur est survenue.";
                }
            })
            .catch(() => {
                message.style.color = "red";
                message.textContent = "Erreur réseau.";
            });
    });
});
