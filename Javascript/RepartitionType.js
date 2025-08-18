// --- Initialisation au chargement ---
document.addEventListener('DOMContentLoaded', () => 
{
    bindYearButtons();

    // Année par défaut = stockage ou année courante
    const savedYear = parseInt(localStorage.getItem('selectedYear'), 10);
    const defaultYear = !isNaN(savedYear) ? savedYear : new Date().getFullYear();

    loadYear(defaultYear);
});

// --- Affichage des stats ---
function renderStats(stats) 
{
    const tbody = document.querySelector('#table-seances tbody');
    tbody.innerHTML = ''; // reset

    stats.seances.forEach(({ type, percent, planned, ended, plannedSinceStart }) => 
    {
        const row = document.createElement('tr');
        row.innerHTML = `
      <td>${type}</td>
      <td>${percent}%</td>
      <td>${planned}</td>
      <td>${ended}</td>
      <td>${plannedSinceStart}</td>
    `;
        tbody.appendChild(row);
    });

    document.querySelector('#total-year').textContent = stats.totalPlannedYear;
}

// --- Chargement des stats pour une année ---
function loadYear(year) 
{
    fetch(`${BASE_URL}/spectacle/data/stats?year=${year}`)
        .then(res => 
        {
            if (!res.ok) 
            {
                throw new Error(`Erreur HTTP ${res.status}`);
            }
            return res.json();
        })
        .then(data => 
        {
            if (data.status === 'success') 
            {
                renderStats(data.stats);
            } else 
            {
                console.error('Erreur API :', data.message);
            }
        })
        .catch(err => console.error('Erreur réseau :', err));
}

// --- Liaison des boutons d'année ---
function bindYearButtons() 
{
    const buttons = document.querySelectorAll('.year-btn');
    buttons.forEach(btn => 
    {
        btn.addEventListener('click', () => 
        {
            const year = parseInt(btn.getAttribute('data-year'), 10);
            localStorage.setItem('selectedYear', year);
            loadYear(year);
        });
    });
}

