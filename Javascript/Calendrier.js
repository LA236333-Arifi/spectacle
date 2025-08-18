(function () {
  var container = document.getElementById('mois-container');
  var tmpl = document.getElementById('mois-template');
  var statusEl = document.getElementById('status');

  var MONTHS = 
  [
    'Janvier','Février','Mars','Avril','Mai','Juin',
    'Juillet','Août','Septembre','Octobre','Novembre','Décembre'
  ];

  function fetchStats(year) {
    statusEl.textContent = 'Chargement...';
    return fetch(BASE_URL + '/spectacle/data/calendrier?year=' + encodeURIComponent(year))
      .then(function (res) { return res.json(); })
      .then(function (data) { statusEl.textContent = ''; return data; })
      .catch(function () { statusEl.textContent = 'Erreur.'; });
  }

  function render(api) {
    if (!api || !api.stats || !api.stats.months) return;
    container.innerHTML = '';
    var monthsData = api.stats.months;

    for (var i = 1; i <= 12; i++) {
      var node = tmpl.content.firstElementChild.cloneNode(true);
      var m = monthsData[i] || { planned: 0, byType: [], percentOfYear: 0 };

      node.querySelector('.mois-nom').textContent = MONTHS[i - 1];
      node.querySelector('.mois-total').textContent =
        m.planned + (m.planned > 1 ? ' séances programmées' : ' séance programmée');

      var typesEl = node.querySelector('.mois-types');
      typesEl.innerHTML = '';

      var typeEntries = Array.isArray(m.byType)
        ? m.byType.map(function (t) { return { label: t.label, count: t.count }; })
        : Object.keys(m.byType || {}).map(function (label) {
            return { label: label, count: m.byType[label] };
          });

      if (typeEntries.length === 0) {
        var li = document.createElement('li');
        li.textContent = 'Aucun type répertorié';
        typesEl.appendChild(li);
      } else {
        typeEntries.forEach(function (t) {
          var li = document.createElement('li');
          li.textContent = t.label + ' : ' + t.count;
          typesEl.appendChild(li);
        });
      }

      node.querySelector('.mois-pourcentage').textContent =
        m.percentOfYear + '% des séances sont planifiées ce mois-ci';
      node.querySelector('.progress-bar').style.width = m.percentOfYear + '%';

      container.appendChild(node);
    }
  }

  document.getElementById('year-selector').addEventListener('click', function (e) {
    if (e.target.tagName === 'BUTTON') {
      var year = e.target.getAttribute('data-year');
      fetchStats(year).then(render);
    }
  });

  // charge l'année du premier bouton par défaut
  var defaultYear = document.querySelector('#year-selector button').getAttribute('data-year');
  fetchStats(defaultYear).then(render);
})();
