(function()
{
    // Config minimale
    var PAGE_SIZE = 10;

    // Éléments
    var form = document.querySelector('.search-form');
    var input = form ? form.querySelector('input[name="query"]') : null;
    var listEl = document.getElementById('search-list');
    var pagerEl = document.getElementById('search-pager');
    var summaryEl = document.getElementById('search-summary');

    // Si la page ne possède pas les conteneurs, on ne monte rien
    if (!form || !input || !listEl || !pagerEl || !summaryEl)
    {
        return;
    }

    // État
    var state =
    {
        query: '',
        page: 1,
        pageSize: PAGE_SIZE,
        endpoint: form.getAttribute('action') || BASE_URL + '/search'
    };

    // Init à partir de l’URL
    var params = new URLSearchParams(window.location.search);
    state.query = (params.get('query') || '').trim();
    state.page = parseInt(params.get('page') || '1', 10) || 1;
    input.value = state.query;

    // Écouteurs
    form.addEventListener('submit', function(e)
    {
        e.preventDefault();
        state.query = (input.value || '').trim();
        state.page = 1;
        updateUrl();
        load();
    });

    pagerEl.addEventListener('click', function(e)
    {
        var btn = e.target.closest('button[data-page]');
        if (!btn)
        {
            return;
        }
        var next = parseInt(btn.getAttribute('data-page'), 10);
        if (!next || next === state.page)
        {
            return;
        }
        state.page = next;
        updateUrl();
        load();
    });

    // Démarrage
    if (state.query)
    {
        load();
    }

    // --- Fonctions ---

    function buildUrl()
    {
        var url = state.endpoint +
                  '?query=' + encodeURIComponent(state.query) +
                  '&page=' + state.page +
                  '&pageSize=' + state.pageSize;
        return url;
    }

    function httpGetJson(url)
    {
        return fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function(res)
        {
            if (!res.ok)
            {
                throw new Error('HTTP ' + res.status);
            }
            return res.json();
        });
    }

    function load()
    {
        renderLoading();

        httpGetJson(buildUrl())
        .then(function(data)
        {
            var items = Array.isArray(data.results) ? data.results : [];
            renderSummary(items);
            renderList(items);
            renderPager(items.length);
        })
        .catch(function(err)
        {
            renderError(err.message);
        });
    }

    function renderLoading()
    {
        summaryEl.textContent = '';
        listEl.innerHTML = '<li class="search-item--muted">Chargement…</li>';
        pagerEl.innerHTML = '';
    }

    function renderError(msg)
    {
        summaryEl.textContent = '';
        listEl.innerHTML = '<li class="search-item--muted">Erreur: ' + escapeHtml(msg) + '</li>';
        pagerEl.innerHTML = '';
    }

    function renderSummary(items)
    {
        if (!items.length)
        {
            summaryEl.textContent = 'Aucun résultat';
            return;
        }
        summaryEl.textContent = items.length + ' résultat' + (items.length > 1 ? 's' : '');
    }

    function renderList(items)
    {
        if (!items.length)
        {
            listEl.innerHTML = '<li class="search-item--muted">Aucun spectacle trouvé.</li>';
            return;
        }

        listEl.innerHTML = '';

        items.forEach(function(it)
        {
            var li = document.createElement('li');
            li.className = 'search-card';
            li.innerHTML =
                '<h3 class="search-card__title">' + escapeHtml(it.nom_spectacle || it.nom || 'Spectacle') + '</h3>' +
                '<div class="search-card__meta">' +
                    (it.texte_accroche_spectacle ? escapeHtml(it.texte_accroche_spectacle) : '') +
                '</div>';
            listEl.appendChild(li);
        });
    }

    function renderPager(countOnPage)
    {
        pagerEl.innerHTML = '';

        var hasPrev = state.page > 1;
        var hasNext = countOnPage === state.pageSize; // sans total global

        var prev = makeBtn('← Précédent', hasPrev ? state.page - 1 : null, false);
        var info = document.createElement('span');
        info.className = 'search-pageinfo';
        info.textContent = 'Page ' + state.page;
        var next = makeBtn('Suivant →', hasNext ? state.page + 1 : null, false);

        pagerEl.appendChild(prev);
        pagerEl.appendChild(info);
        pagerEl.appendChild(next);
    }

    function makeBtn(label, pageValue, isActive)
    {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'search-pagebtn' + (isActive ? ' is-active' : '');
        btn.textContent = label;

        if (pageValue)
        {
            btn.setAttribute('data-page', String(pageValue));
        }
        else
        {
            btn.disabled = true;
        }

        return btn;
    }

    function updateUrl()
    {
        var p = new URLSearchParams();
        if (state.query)
        {
            p.set('query', state.query);
        }
        p.set('page', String(state.page));
        p.set('pageSize', String(state.pageSize));
        var newUrl = window.location.pathname + '?' + p.toString();
        window.history.replaceState(null, '', newUrl);
    }

    function escapeHtml(str)
    {
        if (typeof str !== 'string')
        {
            return '';
        }
        return str.replace(/[&<>"']/g, function(c)
        {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[c];
        });
    }
}());
