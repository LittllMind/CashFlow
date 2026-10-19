<?php ob_start(); ?>
<div class="sale-form-wrapper sale-panel articles-panel">
    <header class="sp-header">
        <div class="sp-header-nav">
            <div class="sp-header-center">
                <h1 class="sp-header-title">Articles par sous-catégorie</h1>
                <span class="sp-header-subtitle">Suivi des achats et ventes par ligne de stock</span>
            </div>
        </div>
    </header>

    <!-- Filtres -->
    <div class="filter-container articles-filter">
        <div class="form-group">
            <label for="category">Catégorie</label>
            <select id="category" class="form-control">
                <option value="">-- Choisir --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['category_name']) ?>">
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="subcategory-select">Sous-catégorie</label>
            <select id="subcategory-select" class="form-control" disabled>
                <option value="">-- Catégorie d'abord --</option>
            </select>
        </div>
    </div>

    <!-- Onglets années -->
    <nav class="year-tabs" id="year-tabs" aria-label="Filtrer par année" style="display:none;">
        <button type="button" class="year-tab" data-year="all" onclick="setYear('all')">Historique</button>
        <?php foreach ($availableYears as $yr): ?>
            <button type="button" class="year-tab" data-year="<= (int) $yr ?>" onclick="setYear('<?= (int) $yr ?>')">
                <?= (int) $yr ?>
            </button>
        <?php endforeach; ?>
    </nav>

    <!-- Synthèse -->
    <div class="sp-stats-row" id="summary-container" style="display:none;">
        <div class="sp-stat-card sp-stat-card--danger">
            <div class="sp-stat-card__top">
                <span class="sp-stat-card__label">Achats</span>
            </div>
            <div class="sp-stat-card__value" id="summary-achats-total">—</div>
            <div class="sp-stat-card__meta">
                <span id="summary-achats-qty">— articles</span>
                <span id="summary-achats-avg">prix moyen —</span>
            </div>
        </div>

        <div class="sp-stat-card sp-stat-card--daily">
            <div class="sp-stat-card__top">
                <span class="sp-stat-card__label">Ventes</span>
            </div>
            <div class="sp-stat-card__value" id="summary-ventes-total">—</div>
            <div class="sp-stat-card__meta">
                <span id="summary-ventes-qty">— articles</span>
                <span id="summary-ventes-avg">prix moyen —</span>
            </div>
        </div>

        <div class="sp-stat-card sp-stat-card--bilan">
            <div class="sp-stat-card__top">
                <span class="sp-stat-card__label">Bilan ventes − achats</span>
            </div>
            <div class="sp-stat-card__value" id="summary-bilan">—</div>
            <div class="sp-stat-card__meta">
                <span id="summary-count">— lignes</span>
            </div>
        </div>
    </div>

    <!-- Tableau détail -->
    <div class="sp-table-wrapper" id="articles-wrapper" style="display:none;">
        <div class="sp-table-header">
            <span class="sp-table-title" id="articles-count">Détail des mouvements</span>
        </div>
        <table class="sale-table" id="articles-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Quantité</th>
                    <th>Prix unit.</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Commentaire</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="sp-empty" id="empty-state">
        <span class="sp-empty__icon">📦</span>
        <p>Sélectionnez une catégorie et une sous-catégorie pour afficher les mouvements.</p>
    </div>
</div>

<script>
    const subcategoriesData = <?= $subcategoriesJson ?>;
    const availableYears = <?= json_encode($availableYears ?? []) ?>;
    const selectedYear = <?= json_encode($selectedYear ?? 'all', JSON_HEX_TAG) ?>;
    let currentYear = selectedYear || 'all';

    const categorySelect = document.getElementById('category');
    const subcategorySelect = document.getElementById('subcategory-select');
    const yearTabs = document.getElementById('year-tabs');
    const summaryContainer = document.getElementById('summary-container');
    const articlesWrapper = document.getElementById('articles-wrapper');
    const emptyState = document.getElementById('empty-state');
    const articlesTableBody = document.querySelector('#articles-table tbody');

    categorySelect.addEventListener('change', function() {
        const selectedCategory = this.value;
        subcategorySelect.innerHTML = '';
        subcategorySelect.disabled = true;

        if (!selectedCategory) {
            subcategorySelect.innerHTML = '<option value="">-- Catégorie d\'abord --</option>';
            resetView();
            return;
        }

        const filtered = subcategoriesData.filter(sub => sub.category_name === selectedCategory);

        if (filtered.length === 0) {
            subcategorySelect.innerHTML = '<option value="">Aucune sous-catégorie</option>';
            return;
        }

        subcategorySelect.innerHTML = '<option value="">-- Choisir --</option>';
        filtered.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.subcategory_name;
            option.textContent = sub.subcategory_name;
            subcategorySelect.appendChild(option);
        });

        subcategorySelect.disabled = false;
    });

    subcategorySelect.addEventListener('change', function() {
        if (this.value) {
            yearTabs.style.display = availableYears.length > 0 ? 'flex' : 'none';
            updateYearTabs();
            fetchArticlesByYear(currentYear);
        } else {
            resetView();
        }
    });

    function setYear(year) {
        currentYear = year;
        updateYearTabs();
        if (subcategorySelect.value) {
            fetchArticlesByYear(year);
        }
    }

    function updateYearTabs() {
        document.querySelectorAll('.year-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.year === String(currentYear));
        });
    }

    function resetView() {
        yearTabs.style.display = 'none';
        summaryContainer.style.display = 'none';
        articlesWrapper.style.display = 'none';
        emptyState.style.display = 'flex';
        articlesTableBody.innerHTML = '';
    }

    function formatMoney(value) {
        return Number(value).toFixed(2) + ' €';
    }

    function fetchArticlesByYear(year) {
        const category = categorySelect.value;
        const subcategory = subcategorySelect.value;

        if (!category || !subcategory) {
            resetView();
            return;
        }

        const url = `index.php?action=articlesFetchByYear&category=${encodeURIComponent(category)}&subcategory=${encodeURIComponent(subcategory)}&year=${encodeURIComponent(year || 'all')}`;

        fetch(url, { credentials: 'same-origin' })
            .then(response => response.json())
            .then(data => {
                const articles = data.articles || [];
                const summary = data.summary || { achats: {}, ventes: {}, bilan: 0 };

                emptyState.style.display = 'none';
                summaryContainer.style.display = 'grid';
                articlesWrapper.style.display = 'block';

                // Synthèse
                document.getElementById('summary-achats-total').textContent = formatMoney(summary.achats.total || 0);
                document.getElementById('summary-achats-qty').textContent = (summary.achats.qty || 0).toFixed(2) + ' articles';
                document.getElementById('summary-achats-avg').textContent = 'prix moyen ' + formatMoney(summary.achats.avg || 0);

                document.getElementById('summary-ventes-total').textContent = formatMoney(summary.ventes.total || 0);
                document.getElementById('summary-ventes-qty').textContent = (summary.ventes.qty || 0).toFixed(2) + ' articles';
                document.getElementById('summary-ventes-avg').textContent = 'prix moyen ' + formatMoney(summary.ventes.avg || 0);

                const bilan = summary.bilan || 0;
                const bilanEl = document.getElementById('summary-bilan');
                bilanEl.textContent = formatMoney(bilan);
                bilanEl.style.color = bilan >= 0 ? 'var(--sp-success)' : 'var(--sp-danger)';

                document.getElementById('summary-count').textContent = articles.length + ' ligne' + (articles.length > 1 ? 's' : '');

                // Tableau détail
                articlesTableBody.innerHTML = '';
                document.getElementById('articles-count').textContent = articles.length + ' mouvement' + (articles.length > 1 ? 's' : '');

                if (articles.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="6" style="text-align:center;">Aucun mouvement pour cette période.</td>';
                    articlesTableBody.appendChild(row);
                    return;
                }

                articles.forEach(article => {
                    const isVente = article.type === 'vente';
                    const row = document.createElement('tr');
                    row.className = isVente ? 'vente' : 'achat';
                    row.innerHTML = `
                        <td><span class="badge-type ${isVente ? 'badge-vente' : 'badge-achat'}">${article.type}</span></td>
                        <td>${Number(article.quantity).toFixed(2)}</td>
                        <td>${formatMoney(article.unit_price)}</td>
                        <td>${formatMoney(article.total)}</td>
                        <td>${article.date || '-'}</td>
                        <td>${article.comment ? escapeHtml(article.comment) : '-'}</td>
                    `;
                    articlesTableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Erreur fetch :', error);
                articlesTableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:var(--sp-danger);">Erreur lors du chargement.</td></tr>`;
            });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialisation des onglets si une sous-catégorie est passée en query string
    if (subcategorySelect.value) {
        yearTabs.style.display = availableYears.length > 0 ? 'flex' : 'none';
        updateYearTabs();
        fetchArticlesByYear(currentYear);
    } else {
        resetView();
    }
</script>

<?php $content = ob_get_clean(); ?>
<?php require('view/template/mainTemplate.php'); ?>
