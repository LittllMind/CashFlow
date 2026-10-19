<?php ob_start(); ?>

<div class="stats-app">

    <header class="stats-top">
        <h1>Statistiques <?= (int)$year ?></h1>
        <p class="stats-top__hint">Année en cours uniquement · mobile friendly</p>
    </header>

    <form class="stats-filters" method="get" action="index.php">
        <input type="hidden" name="action" value="globalStatistic">

        <div class="stats-filters__field">
            <label for="categoryFilter">Catégorie</label>
            <select id="categoryFilter" name="category">
                <option value="" <?= empty($selectedCategory) ? 'selected' : '' ?>>Toutes</option>
                <?php foreach ($allCategories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($selectedCategory === $cat['name']) ? 'selected' : '' ?>
                            data-subs="<?= htmlspecialchars(json_encode(array_column($cat['subcategories'], 'name'))) ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="stats-filters__field">
            <label for="subcategoryFilter">Sous-catégorie</label>
            <select id="subcategoryFilter" name="subcategory">
                <option value="" <?= empty($selectedSubcategory) ? 'selected' : '' ?>>Toutes</option>
                <?php
                foreach ($allCategories as $cat) {
                    if ($selectedCategory === $cat['name'] || empty($selectedCategory)) {
                        foreach ($cat['subcategories'] as $sub) {
                            $sel = ($selectedSubcategory === $sub['name']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($sub['name']) . '" data-cat="' . htmlspecialchars($cat['name']) . '" ' . $sel . '>'
                                . htmlspecialchars($sub['name']) . '</option>';
                        }
                    }
                }
                ?>
            </select>
        </div>

        <div class="stats-filters__actions">
            <button type="submit" class="stats-btn">Filtrer</button>
            <a class="stats-btn stats-btn--ghost" href="index.php?action=globalStatistic">Reset</a>
        </div>
    </form>

    <!-- KPI simples -->
    <section class="kpi-row">
        <div class="kpi">
            <span class="kpi__value"><?= number_format($totalSales, 2, ',', ' ') ?> €</span>
            <span class="kpi__label">CA total</span>
        </div>
        <div class="kpi">
            <span class="kpi__value"><?= (int)$nbSales ?></span>
            <span class="kpi__label">Ventes</span>
        </div>
        <div class="kpi">
            <span class="kpi__value"><?= number_format($avgBasket, 2, ',', ' ') ?> €</span>
            <span class="kpi__label">Panier moyen</span>
        </div>
        <div class="kpi">
            <span class="kpi__value"><?= number_format($avgDay, 2, ',', ' ') ?> €</span>
            <span class="kpi__label">CA / jour</span>
        </div>
        <div class="kpi">
            <span class="kpi__value"><?= number_format($marginRate, 1, ',', ' ') ?> %</span>
            <span class="kpi__label">Marge</span>
        </div>
    </section>

    <!-- Graphique principal mensuel -->
    <section class="card">
        <div class="card__head">
            <h2>Évolution mensuelle <?= (int)$year ?></h2>
            <p>
                <?php if (!empty($selectedCategory) && !empty($selectedSubcategory)): ?>
                    <?= htmlspecialchars($selectedCategory) ?> · <?= htmlspecialchars($selectedSubcategory) ?>
                <?php elseif (!empty($selectedCategory)): ?>
                    <?= htmlspecialchars($selectedCategory) ?>
                <?php else: ?>
                    Toutes catégories<?php endif; ?>
            </p>
        </div>
        <div class="mini-chart mini-chart--trend">
            <canvas id="monthlyTrendChart"></canvas>
        </div>
    </section>

    <!-- Petits graphiques -->
    <section class="cards-grid">
        <div class="card">
            <h3>Répartition par catégorie</h3>
            <div class="mini-chart">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>FRIPE vs BROC</h3>
            <div class="mini-chart">
                <canvas id="fripeBrocChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>Paiements</h3>
            <div class="mini-chart">
                <canvas id="paymentChart"></canvas>
            </div>
        </div>
    </section>

    <!-- Top 10 -->
    <section class="card">
        <h3>Top 10 sous-catégories</h3>
        <ul class="top-list">
            <?php foreach ($topSubcategories as $i => $row): ?>
                <li class="top-list__item">
                    <span class="top-list__rank"><?= $i + 1 ?></span>
                    <div class="top-list__info">
                        <span class="top-list__name"><?= htmlspecialchars($row['subcategory']) ?></span>
                        <span class="top-list__cat"><?= htmlspecialchars($row['category']) ?> · <?= (int)$row['qty'] ?> articles</span>
                    </div>
                    <span class="top-list__amount"><?= number_format($row['amount'], 0, ',', ' ') ?> €</span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const palette = ['#4e79a7', '#f28e2c', '#e15759', '#76b7b2', '#59a14f', '#edc949'];

        function chartDefaults() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 11 }, padding: 8 }
                    },
                    title: { display: false }
                }
            };
        }

        function makeBar(canvasId, labels, data, label = '€') {
            const el = document.getElementById(canvasId);
            if (!el || !labels.length) return;
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: '#4e79a7',
                        borderRadius: 4
                    }]
                },
                options: {
                    ...chartDefaults(),
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { size: 10 } } },
                        x: { ticks: { font: { size: 10 } }, grid: { display: false } }
                    }
                }
            });
        }

        function makeDoughnut(canvasId, labels, data) {
            const el = document.getElementById(canvasId);
            if (!el || !labels.length) return;
            new Chart(el.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: labels.map((_, i) => palette[i % palette.length]),
                        borderWidth: 1
                    }]
                },
                options: chartDefaults()
            });
        }

        // 1. Évolution mensuelle (bars + ligne)
        const trend = <?= json_encode($monthlyTrend ?? ['labels' => [], 'qty' => [], 'ca' => []]); ?>;
        const trendEl = document.getElementById('monthlyTrendChart');
        if (trendEl && trend.labels.length) {
            new Chart(trendEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: trend.labels.map(l => {
                        const [y, m] = l.split('-');
                        return m + '/' + y;
                    }),
                    datasets: [
                        {
                            label: 'Quantité',
                            data: trend.qty,
                            backgroundColor: '#4e79a7',
                            yAxisID: 'y',
                            borderRadius: 3,
                            order: 2
                        },
                        {
                            label: 'CA (€)',
                            data: trend.ca,
                            type: 'line',
                            borderColor: '#e15759',
                            backgroundColor: '#e15759',
                            borderWidth: 2,
                            pointRadius: 3,
                            tension: 0.2,
                            yAxisID: 'y1',
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                    },
                    scales: {
                        x: { ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 0 }, grid: { display: false } },
                        y: { display: true, position: 'left', beginAtZero: true, ticks: { font: { size: 10 } } },
                        y1: { display: true, position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // 2. Répartition catégorie
        makeDoughnut('categoryChart',
            <?= json_encode(array_keys($categoryAmount)); ?>,
            <?= json_encode(array_values($categoryAmount)); ?>
        );

        // 3. FRIPE vs BROC
        makeDoughnut('fripeBrocChart',
            ['FRIPE', 'BROC'],
            [
                <?= (float)($caFripe ?? 0.0); ?>,
                <?= (float)($caBroc ?? 0.0); ?>
            ]
        );

        // 4. Modes de paiement (top 3, sans doublons)
        const payments = <?= json_encode($paymentStats); ?>;
        const payLabels = payments.map(p => ({
            cash: 'Espèces',
            card: 'Carte',
            check: 'Chèque',
            bank_transfer: 'Virement'
        }[p.payment_method] || p.payment_method));
        makeBar('paymentChart', payLabels, payments.map(p => p.ca), '€');

        // Cascade catégorie → sous-catégorie
        const catSel = document.getElementById('categoryFilter');
        const subSel = document.getElementById('subcategoryFilter');
        if (catSel && subSel) {
            const all = Array.from(subSel.options).slice(1);
            catSel.addEventListener('change', function () {
                subSel.innerHTML = '<option value="">Toutes</option>';
                if (!this.value) {
                    all.forEach(o => subSel.appendChild(o.cloneNode(true)));
                } else {
                    all.filter(o => o.dataset.cat === this.value).forEach(o => subSel.appendChild(o.cloneNode(true)));
                }
            });
        }
    });
</script>

<style>
    .stats-app { max-width: 720px; margin: 0 auto; padding: 16px; font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif; }
    .stats-top { margin-bottom: 18px; }
    .stats-top h1 { font-size: 1.35rem; color: #1a1a2e; margin: 0; }
    .stats-top__hint { font-size: 0.8rem; color: #6b7280; margin: 4px 0 0; }

    .stats-filters {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 14px;
    }
    .stats-filters__field { display: flex; flex-direction: column; gap: 3px; }
    .stats-filters__field label { font-size: 0.75rem; color: #6b7280; font-weight: 600; }
    .stats-filters__field select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.95rem;
        background: #fff;
    }
    .stats-filters__actions { display: flex; gap: 8px; }
    .stats-btn {
        flex: 1;
        padding: 10px;
        border-radius: 8px;
        border: none;
        background: #2563eb;
        color: #fff;
        font-size: 0.9rem;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        display: block;
    }
    .stats-btn--ghost { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }

    .kpi-row {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }
    .kpi {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 4px;
        text-align: center;
    }
    .kpi__value { display: block; font-size: 0.85rem; font-weight: 700; color: #111827; white-space: nowrap; }
    .kpi__label { display: block; font-size: 0.65rem; color: #6b7280; margin-top: 2px; }

    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 14px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .card h2, .card h3 { font-size: 1rem; color: #1f2937; margin: 0 0 10px; }
    .card__head p { font-size: 0.8rem; color: #6b7280; margin: -6px 0 10px; }

    .mini-chart { height: 180px; }
    .mini-chart--trend { height: 260px; }

    .cards-grid { display: grid; grid-template-columns: 1fr; gap: 14px; }

    .top-list { list-style: none; padding: 0; margin: 0; }
    .top-list__item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .top-list__item:last-child { border-bottom: none; }
    .top-list__rank {
        min-width: 22px; height: 22px;
        display: grid; place-items: center;
        background: #374151; color: #fff;
        border-radius: 50%;
        font-size: 0.7rem; font-weight: 700;
    }
    .top-list__info { flex: 1; }
    .top-list__name { display: block; font-size: 0.9rem; font-weight: 600; color: #1f2937; }
    .top-list__cat { display: block; font-size: 0.75rem; color: #6b7280; }
    .top-list__amount { font-size: 0.9rem; font-weight: 700; color: #2563eb; }

    /* Desktop : 2 colonnes pour les petits graphiques */
    @media (min-width: 640px) {
        .cards-grid { grid-template-columns: 1fr 1fr; }
        .kpi__value { font-size: 1rem; }
    }
    @media (min-width: 900px) {
        .stats-app { max-width: 900px; }
        .kpi__value { font-size: 1.1rem; }
    }
</style>

<?php $content = ob_get_clean(); ?>
<?php require('view/template/mainTemplate.php'); ?>
