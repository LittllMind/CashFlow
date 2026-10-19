<?php ob_start(); ?>
<div class="sale-form-wrapper sale-panel">
    <input type="hidden" id="hiddenSelectedDate" value="<?= $selectedDate ?? date('Y-m-d') ?>">

    <!-- ===== HEADER : Date + Navigation + Nouvelle vente ===== -->
    <header class="sp-header">
        <div class="sp-header-nav">
            <button type="button" class="sp-btn-icon" id="prevDay" aria-label="Jour précédent">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <div class="sp-header-center">
                <h1 class="sp-header-title" onclick="document.getElementById('datePickerInput').showPicker()"><?= date('l j F Y', strtotime($selectedDate)) ?></h1>
                <input type="date" id="datePickerInput" value="<?= $selectedDate ?>" hidden onchange="window.location.href='index.php?action=salePanel&date='+this.value">
                <span class="sp-header-subtitle"><?= $total_sales ?> vente<?= $total_sales > 1 ? 's' : '' ?> — <?= $total_articles ?> article<?= $total_articles > 1 ? 's' : '' ?></span>
            </div>
            <button type="button" class="sp-btn-icon" id="nextDay" aria-label="Jour suivant">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>
        <div class="sp-header-actions">
            <button type="button" class="sp-btn-today" onclick="window.location.href='index.php?action=salePanel&date=<?= date('Y-m-d') ?>'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Aujourd'hui
            </button>
            <button type="button" class="sp-btn-new" onclick="window.location.href='index.php?action=newSaleForm&selectedDate=<?= $selectedDate ?>'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                + Vente
            </button>
            <a href="index.php?action=ardoisePanel" class="sp-btn-ardoise" title="Ardoises clients">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Ardoises
            </a>
        </div>
    </header>

    <!-- ===== TABLEAU DES VENTES ===== -->
    <div class="sp-table-wrapper">
        <div class="sp-table-header">
            <h2>Ventes du jour</h2>
            <span class="sp-table-count"><?= $total_sales ?> transaction<?= $total_sales > 1 ? 's' : '' ?></span>
        </div>
        <?php if ($total_sales > 0): ?>
        <?php
            // Générer tableau + cartes en une seule passe
            $salesTableRows = '';
            $salesCards = '';
            // Convertir le curseur en tableau pour itérer plusieurs fois si besoin
            $salesList = is_array($list_of_sales) ? $list_of_sales : $list_of_sales->fetchAll();
            foreach ($salesList as $sale) {
                $articlePlural = ($sale['article_count'] ?? 0) > 1 ? 's' : '';
                $payMethod = $sale['payment_method'] ?? 'cash';
                $payLabel = spPaymentLabel($payMethod);
                $amount = number_format($sale['total_amount'], 2, ',', ' ');
                $articleCount = (string)($sale['article_count'] ?? 0);
                $salesTableRows .= <<<HTML
                    <tr data-sale-id="{$sale['id']}" class="sp-sale-row">
                        <td class="sp-td-time">
                            <a href="index.php?action=newSaleForm&sale_id={$sale['id']}" class="sp-time-link" onclick="event.stopPropagation()">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {$sale['sale_time']}
                            </a>
                        </td>
                        <td class="sp-td-method">
                            <span class="sp-badge sp-badge--{$payMethod}">
                                {$payLabel}
                            </span>
                        </td>
                        <td class="sp-td-articles">{$articleCount}</td>
                        <td class="sp-td-amount">{$amount} €</td>
                        <td class="sp-td-action">
                            <button type="button" class="sp-btn-delete" onclick="event.stopPropagation(); spConfirmDelete({$sale['id']}, {$sale['total_amount']}, '{$sale['sale_time']}')" title="Supprimer">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </td>
                    </tr>
HTML;
            }
        ?>
        <table class="sp-table">
            <thead>
                <tr>
                    <th class="sp-th-time">Heure</th>
                    <th class="sp-th-method">Paiement</th>
                    <th class="sp-th-articles">Articles</th>
                    <th class="sp-th-amount">Montant</th>
                    <th class="sp-th-action"></th>
                </tr>
            </thead>
            <tbody>
                <?= $salesTableRows ?>
            </tbody>
        </table>

        <!-- ===== CARTES MOBILE ===== -->
        <div class="sp-sales-cards">
            <?php foreach ($salesList as $sale): ?>
                <?php
                    $payMethod = $sale['payment_method'] ?? 'cash';
                    $payLabel = spPaymentLabel($payMethod);
                    $amount = number_format($sale['total_amount'], 2, ',', ' ');
                    $articleCount = (int)($sale['article_count'] ?? 0);
                    $articlePlural = $articleCount > 1 ? 's' : '';
                    $saleHref = "index.php?action=newSaleForm&sale_id={$sale['id']}";
                ?>
                <div class="sp-sale-card" data-sale-id="<?= $sale['id'] ?>">
                    <div class="sp-sale-card__summary">
                        <div class="sp-sale-card__time">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span class="sp-sale-card__hour"><?= $sale['sale_time'] ?></span>
                            <span class="sp-badge sp-badge--<?= $payMethod ?>"><?= $payLabel ?></span>
                        </div>
                        <div class="sp-sale-card__body">
                            <span class="sp-sale-card__count"><?= $articleCount ?> art.<?= $articlePlural ?></span>
                            <span class="sp-sale-card__amount"><?= $amount ?> €</span>
                        </div>
                        <div class="sp-sale-card__actions">
                            <button type="button" class="sp-btn-delete" onclick="event.stopPropagation(); spConfirmDelete(<?= $sale['id'] ?>, <?= $sale['total_amount'] ?>, '<?= $sale['sale_time'] ?>')" title="Supprimer">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="sp-sale-card__details" hidden>
                        <div class="sp-details__articles" data-sale-id="<?= $sale['id'] ?>"></div>
                        <div class="sp-details__footer">
                            <a href="<?= $saleHref ?>" class="sp-btn-edit">Modifier la vente</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="sp-empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <p>Aucune vente ce jour</p>
            <button type="button" class="sp-btn-new" onclick="window.location.href='index.php?action=newSaleForm&selectedDate=<?= $selectedDate ?>'">Créer une vente</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== STATS CARDS (peuplées en PHP) ===== -->
    <div class="sp-stats-row">
        <div class="sp-stat-card sp-stat-card--daily">
            <div class="sp-stat-card__top">
                <span class="sp-stat-card__label">Jour</span>
                <?php if (isset($trend_j1)): ?>
                    <span class="sp-stat-card__trend <?= $trend_j1['class'] ?>"><?= $trend_j1['symbol'] ?> <?= $trend_j1['pct'] ?>%</span>
                <?php endif; ?>
            </div>
            <div class="sp-stat-card__value"><?= number_format($dailyTotals['total_amount'], 2, ',', ' ') ?> €</div>
            <div class="sp-stat-card__meta">
                <span><?= $dailyTotals['total_sales'] ?> ventes</span>
                <span><?= $dailyTotals['total_articles'] ?> articles</span>
            </div>
        </div>
        <div class="sp-stat-card">
            <div class="sp-stat-card__top">
                <span class="sp-stat-card__label">Semaine</span>
            </div>
            <div class="sp-stat-card__value"><?= number_format($weeklyTotals['total_amount'], 2, ',', ' ') ?> €</div>
            <div class="sp-stat-card__meta">
                <span><?= $weeklyTotals['total_sales'] ?> ventes</span>
                <span><?= $weeklyTotals['total_articles'] ?> articles</span>
            </div>
        </div>
        <div class="sp-stat-card">
            <div class="sp-stat-card__top">
                <span class="sp-stat-card__label">Mois</span>
            </div>
            <div class="sp-stat-card__value"><?= number_format($monthlyTotals['total_amount'], 2, ',', ' ') ?> €</div>
            <div class="sp-stat-card__meta">
                <span><?= $monthlyTotals['total_sales'] ?> ventes</span>
                <span><?= $monthlyTotals['total_articles'] ?> articles</span>
            </div>
        </div>
    </div>

    <!-- ===== RÉCAP ARTICLES DU JOUR ===== -->
    <div class="sp-table-wrapper sp-articles-recap">
        <div class="sp-table-header">
            <h2>Récap articles</h2>
            <span class="sp-table-count"><?= count($aggregatedArticles) ?> type<?= count($aggregatedArticles) > 1 ? 's' : '' ?></span>
        </div>
        <?php if (!empty($aggregatedArticles)): ?>
        <table class="sp-table sp-table--articles">
            <thead>
                <tr>
                    <th class="sp-th-icon"></th>
                    <th class="sp-th-item">Article</th>
                    <th class="sp-th-qty sp-sortable" data-sort="qty" title="Trier par quantité">Qté <span class="sp-sort-arrow">⇅</span></th>
                    <th class="sp-th-amt sp-sortable" data-sort="amount" title="Trier par montant">Montant <span class="sp-sort-arrow">⇅</span></th>
                    <th class="sp-th-avg">Prix moy.</th>
                </tr>
            </thead>
            <tbody id="articlesRecapBody">
                <?php foreach ($aggregatedArticles as $row): ?>
                <?php $hasPriceWarning = (float)$row['avg_unit_price'] <= 0; ?>
                <tr class="<?= $hasPriceWarning ? 'sp-row-warning' : '' ?>" data-qty="<?= $row['total_quantity'] ?>" data-amount="<?= $row['total_amount'] ?>">
                    <td class="sp-td-icon"><?= spArticleIcon($row['category'], $row['subcategory']) ?></td>
                    <td class="sp-td-item">
                        <div class="sp-item-name"><?= htmlspecialchars($row['subcategory']) ?></div>
                        <div class="sp-item-cat"><?= htmlspecialchars($row['category']) ?></div>
                        <?php if (!empty($row['comments'])): ?>
                        <div class="sp-item-comment"><?= htmlspecialchars($row['comments']) ?></div>
                        <?php endif; ?>
                        <?php if ($hasPriceWarning): ?>
                        <span class="sp-price-warning">prix 0 / négatif détecté</span>
                        <?php endif; ?>
                    </td>
                    <td class="sp-td-qty"><?= $row['total_quantity'] ?></td>
                    <td class="sp-td-amt"><?= number_format($row['total_amount'], 2, ',', ' ') ?> €</td>
                    <td class="sp-td-avg"><?= number_format($row['avg_unit_price'], 2, ',', ' ') ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="sp-tf-label">Total</td>
                    <td class="sp-tf-qty"><?= array_sum(array_column($aggregatedArticles, 'total_quantity')) ?></td>
                    <td class="sp-tf-amt"><?= number_format(array_sum(array_column($aggregatedArticles, 'total_amount')), 2, ',', ' ') ?> €</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div class="sp-empty sp-empty--small">
            <p>Aucun article vendu ce jour</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== CHRONOLOGIE DES ARTICLES VENDUS ===== -->
    <div class="sp-table-wrapper sp-chronology">
        <div class="sp-table-header">
            <h2>Chronologie</h2>
            <span class="sp-table-count"><?= count($chronologyArticles) ?> article<?= count($chronologyArticles) > 1 ? 's' : '' ?></span>
        </div>
        <?php if (!empty($chronologyArticles)): ?>
        <table class="sp-table sp-table--chronology">
            <thead>
                <tr>
                    <th class="sp-th-time"></th>
                    <th class="sp-th-icon"></th>
                    <th class="sp-th-item">Article</th>
                    <th class="sp-th-qty">Qté</th>
                    <th class="sp-th-amt">Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chronologyArticles as $article):
                    $articleTotal = floatval($article['quantity']) * floatval($article['unit_price']);
                    $articleTime = date('H:i', strtotime($article['created_at']));
                ?>
                <tr>
                    <td class="sp-td-time"><span class="sp-chrono-time"><?= $articleTime ?></span></td>
                    <td class="sp-td-icon"><?= spArticleIcon($article['category'], $article['subcategory']) ?></td>
                    <td class="sp-td-item">
                        <div class="sp-item-name"><?= htmlspecialchars($article['subcategory']) ?></div>
                        <div class="sp-item-cat"><?= htmlspecialchars($article['category']) ?></div>
                        <?php if (!empty($article['comment'])): ?>
                        <div class="sp-item-comment"><?= htmlspecialchars($article['comment']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="sp-td-qty"><?= $article['quantity'] ?></td>
                    <td class="sp-td-amt"><?= number_format($articleTotal, 2, ',', ' ') ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="sp-tf-label">Total</td>
                    <td class="sp-tf-qty"><?= array_sum(array_column($chronologyArticles, 'quantity')) ?></td>
                    <td class="sp-tf-amt"><?= number_format(array_sum(array_map(function($a){ return floatval($a['quantity'])*floatval($a['unit_price']); }, $chronologyArticles)), 2, ',', ' ') ?> €</td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div class="sp-empty sp-empty--small">
            <p>Aucun article vendu ce jour</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== INPUT DATE CACHÉ (pour JS navigation) ===== -->
    <input type="date" id="selectedDate" value="<?= $selectedDate ?>" hidden>
</div>

<!-- ===== MODALE APERCU VENTE ===== -->
<div id="salePreviewModal" class="sp-modal" hidden>
    <div class="sp-modal__overlay" onclick="closeSalePreview()">
        <div class="sp-modal__panel" onclick="event.stopPropagation()">
            <div class="sp-modal__header">
                <h3 id="spPreviewTitle">Vente #<span></span></h3>
                <button type="button" class="sp-modal__close" onclick="closeSalePreview()" aria-label="Fermer">×</button>
            </div>
            <div class="sp-modal__body" id="spPreviewBody">
                <div class="sp-preview__meta"></div>
                <table class="sp-table sp-table--compact">
                    <thead>
                        <tr><th>Article</th><th class="sp-td-qty">Qté</th><th>Prix</th><th class="sp-td-amt">Total</th></tr>
                    </thead>
                    <tbody id="spPreviewArticles"></tbody>
                </table>
                <div class="sp-preview__total"></div>
            </div>
            <div class="sp-preview__footer">
                <a href="#" id="spPreviewEditLink" class="sp-btn-tiny">Modifier la vente</a>
            </div>
        </div>
    </div>
</div>

<script>
    window.__salePreviewData = <?= $json_salePreviewData ?>;

    function openSalePreview(saleId) {
        try {
            document.documentElement.style.overflow = 'hidden';
            const modal = document.getElementById('salePreviewModal');
            const body = document.getElementById('spPreviewBody');
            const editLink = document.getElementById('spPreviewEditLink');
            const payMap = { cash: 'Espèces', card: 'Carte bancaire', check: 'Chèque', transfer: 'Virement' };

            editLink.href = 'index.php?action=newSaleForm\u0026sale_id=' + saleId;
            modal.removeAttribute('hidden');

            if (!window.__salePreviewData || !window.__salePreviewData[saleId]) {
                body.innerHTML = '<div class="sp-preview__loading">Données indisponibles.</div>';
                return;
            }

            const sale = window.__salePreviewData[saleId];
            const dateTime = sale.sale_date ? new Date(sale.sale_date + 'T' + (sale.sale_time || '00:00')) : null;
            const dateStr = dateTime ? dateTime.toLocaleDateString('fr-FR') : '';
            const timeStr = dateTime ? dateTime.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' }) : '';

            let rows = '';
            let total = 0;
            (Array.isArray(sale.articles) ? sale.articles : []).forEach(a => {
                const lineTotal = parseFloat(a.quantity || 0) * parseFloat(a.unit_price || 0);
                total += lineTotal;
                rows += `<tr><td>${escapeHtml(a.subcategory) || '-'}</td><td class="sp-td-qty">${a.quantity}</td><td>${parseFloat(a.unit_price || 0).toFixed(2).replace('.', ',')} €</td><td class="sp-td-amt">${lineTotal.toFixed(2).replace('.', ',')} €</td></tr>`;
            });

            document.getElementById('spPreviewTitle').querySelector('span').textContent = saleId;
            body.innerHTML = `
                <div class="sp-preview__meta">
                    <span>${dateStr} à ${timeStr}</span>
                    <span class="sp-badge sp-badge--${sale.payment_method || 'cash'}">${payMap[sale.payment_method] || sale.payment_method}</span>
                </div>
                <table class="sp-table sp-table--compact"><thead><tr><th>Article</th><th class="sp-td-qty">Qté</th><th>Prix</th><th class="sp-td-amt">Total</th></tr></thead><tbody>${rows}</tbody></table>
                <div class="sp-preview__total">TOTAL : ${total.toFixed(2).replace('.', ',')} €</div>
            `;
        } catch (err) {
            alert('Erreur chargement apercu : ' + err.message);
        }
    }

    function closeSalePreview() {
        document.getElementById('salePreviewModal').setAttribute('hidden', 'true');
        document.documentElement.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSalePreview();
    });

    /* ===== Init : date FR ===== */
    document.addEventListener("DOMContentLoaded", function() {
        const dateTitle = document.querySelector('.sp-header-title');
        const hiddenDate = document.getElementById('hiddenSelectedDate');
        if (dateTitle && hiddenDate && hiddenDate.value) {
            dateTitle.textContent = spFormatFrenchDate(hiddenDate.value);
        }

        const dateInput = document.getElementById("selectedDate");
        const prevBtn = document.getElementById("prevDay");
        const nextBtn = document.getElementById("nextDay");

        function fmt(d) { return d.toISOString().split("T")[0]; }

        if (prevBtn) prevBtn.addEventListener("click", function() {
            let d = new Date(dateInput.value); d.setDate(d.getDate() - 1);
            window.location.href = `index.php?action=salePanel&date=${fmt(d)}`;
        });
        if (nextBtn) nextBtn.addEventListener("click", function() {
            let d = new Date(dateInput.value); d.setDate(d.getDate() + 1);
            window.location.href = `index.php?action=salePanel&date=${fmt(d)}`;
        });

        /* ─── Apercu rapide vente (click ligne tableau) ─── */
        document.querySelectorAll('.sp-sale-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.closest('.sp-btn-delete, .sp-time-link')) return;
                const id = row.dataset.saleId;
                if (id) openSalePreview(id);
            });
        });

        /* Tri récap articles */
        const artTable = document.querySelector('.sp-table--articles');
        if (artTable) {
            const tbody = artTable.querySelector('tbody');
            const headers = artTable.querySelectorAll('.sp-sortable');
            let sortDir = { qty: -1, amount: -1 };
            headers.forEach(th => {
                th.addEventListener('click', function() {
                    const key = this.dataset.sort;
                    sortDir[key] *= -1;
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    rows.sort((a, b) => {
                        const av = parseFloat(a.dataset[key]) || 0;
                        const bv = parseFloat(b.dataset[key]) || 0;
                        return (av - bv) * sortDir[key];
                    });
                    rows.forEach(r => tbody.appendChild(r));
                    headers.forEach(h => h.querySelector('.sp-sort-arrow').textContent = '⇅');
                    this.querySelector('.sp-sort-arrow').textContent = sortDir[key] === 1 ? '↑' : '↓';
                });
            });
        }

        /* Toggle détails carte mobile (pas de navigation globale) */
        function toggleSaleCardDetails(card) {
            const details = card.querySelector('.sp-sale-card__details');
            if (!details) return;
            const isHidden = details.hidden;
            document.querySelectorAll('.sp-sale-card__details').forEach(d => { if (d !== details) d.hidden = true; });
            details.hidden = !isHidden;
            if (isHidden) {
                const container = details.querySelector('.sp-details__articles');
                const saleId = container?.dataset.saleId;
                if (saleId && window.__salePreviewData && window.__salePreviewData[saleId]) {
                    renderSalePreview(container, window.__salePreviewData[saleId]);
                }
            }
        }

        function renderSalePreview(container, sale) {
            if (!sale.articles || !sale.articles.length) {
                container.innerHTML = '<div class="sp-details--empty">Aucun article</div>';
                return;
            }
            let html = '<div class="sp-details__list">';
            const payMap = { cash: 'Espèces', card: 'Carte bancaire', check: 'Chèque', transfer: 'Virement' };
            sale.articles.forEach(a => {
                const cat = a.category || '-';
                const sub = a.subcategory || '-';
                const qty = a.quantity || 1;
                const price = parseFloat(a.unit_price || 0).toFixed(2).replace('.', ',');
                const lineTotal = (parseFloat(a.unit_price || 0) * qty).toFixed(2).replace('.', ',');
                const comm = a.comment ? escapeHtml(a.comment) : '';
                html += '<div class="sp-details__item">';
                html += '<span class="sp-details__name">' + escapeHtml(sub) + '</span>';
                html += '<span class="sp-details__meta">×' + qty + ' — ' + price + ' € = ' + lineTotal + ' €</span>';
                if (comm) html += '<div class="sp-details__comm">' + comm + '</div>';
                html += '</div>';
            });
            html += '</div>';
            html += '<div class="sp-details__total">TOTAL : ' + parseFloat(sale.total_amount || 0).toFixed(2).replace('.', ',') + ' €</div>';
            container.innerHTML = html;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        /* Clic carte mobile -> toggle détails */
        document.querySelectorAll('.sp-sale-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (e.target.closest('.sp-btn-delete, .sp-sale-card__actions, .sp-sale-card__details, .sp-btn-edit')) return;
                toggleSaleCardDetails(card);
            });
        });
    });

    /* ===== Helpers globaux ===== */
    function spFormatFrenchDate(dateStr) {
        const d = new Date(dateStr + 'T12:00:00');
        const days = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
        const months = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
        return days[d.getDay()] + ' ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function spConfirmDelete(saleId, amount, time) {
        const msg = `Annuler la vente de ${time} (${amount.toFixed(2).replace('.', ',')} €) ?`;
        if (confirm(msg)) {
            window.location.href = `index.php?action=deleteSale&sale_id=${saleId}`;
        }
    }

    // Pas de dropdowns — suppression directe via sp-btn-delete
</script>

<?php
/**
 * Helper : icône d'article selon la sous-catégorie (fallback catégorie)
 */
function spArticleIcon($category, $subcategory = '') {
    $subMap = [
        // ── VÊTEMENTS ──
        'Top' => '👕', 'T-shirt' => '👕',
        'Chemise' => '👔', 'Chemises' => '👔',
        'Pull' => '🧥',
        'Veste' => '🧥',
        'Manteau' => '🧥',
        'Gilet' => '🦺',
        'Polaire' => '🧥',
        'Imperméable' => '🧥',
        'Combi/salopette' => '👷',
        'Robe' => '👗',
        'Jupe' => '👗',
        'Pantalon' => '👖',
        'Jean' => '👖',
        'Short' => '🩳',
        'Jogging' => '👖',
        'Legging' => '🩱',
        'Maillots' => '🩱',
        'Sous vêtements' => '🩲',
        'Vêtement' => '👕',
        // ── ACCESSOIRES ──
        'Chaussures' => '👟',
        'Sac' => '👜',
        'Ceinture' => '🎀',
        'Couvre chef' => '🧢',
        'Chapeau' => '🎩',
        'Lunettes' => '🕶️',
        'Porte-Clef' => '🔑',
        'Portefeuilles' => '👛',
        'Montre' => '⌚',
        'Foulards' => '🧣',
        'Gants' => '🧤',
        'Chaussettes' => '🧦',
        'Gourde' => '💧',
        'Mouchoir' => '🤧',
        'Pin s Portes clefs' => '🔑',
        'Serviette' => '🧻',
        // ── BIJOUX ──
        'Bague' => '💍', 'Bagues' => '💍',
        'Boucles d\'O' => '👂',
        'Pendentif' => '📿',
        'Bracelet' => '📿',
        'Collier' => '📿',
        'Broche' => '📌',
        'Ambre' => '💎',
        'Cheville' => '📿',
        // ── LIVRES ──
        'Livre' => '📖', 'Livres' => '📚',
        'BD' => '📚',
        'Magazines' => '📰',
        'Vinyle' => '🎵', 'Vinyl' => '🎵',
        'CDF' => '💿',
        'DVD' => '📀',
        'Musique' => '🎵',
        'Photographie' => '📷',
        'Enfant' => '👶',
        'Ado' => '🧑‍🎤',
        'Adulte' => '👤',
        'Poche' => '👜',
        'Ancien' => '🏺',
        'Journaux' => '📰',
        'Jeux Société' => '🎲',
        // ── DÉCORATION / ART DE LA TABLE ──
        'Deco' => '🖼️', 'Décoration' => '🖼️',
        'Tableau Miroir' => '🖼️',
        'Lampe' => '💡',
        'Plante' => '🪴',
        'Mobilier' => '🪑',
        'Art de la table' => '🍽️',
        'Vaisselle' => '🍽️',
        'Assiette' => '🍽️',
        'Verre' => '🥃',
        'Bol' => '🥣',
        'Tasse' => '☕',
        'Théière' => '🫖',
        'Cafetière' => '☕',
        'Carafe' => '🍶',
        'Pot' => '🏺',
        'Bocal' => '🫙',
        'Bouteille' => '🍾',
        'Couvert' => '🍽️',
        'Nappe' => '🧺',
        'Torchon' => '🧹',
        'Linge de lit' => '🛏️',
        'Moulin' => '🏭',
        'Service' => '🔧',
        'Divers' => '📦',
        'Couteau' => '🔪',
        // ── BROCHANTE ──
        'Bricabrac' => '🏺',
        'Bibelot' => '🏺',
        'Anciens' => '📜',
        'Souvenirs' => '🎁',
        'Figurines' => '🗿',
        'Véhicules miniatures' => '🚗',
        'Playmobil' => '🏰',
        'Lego' => '🧱',
        'Peluches' => '🧸',
        'Puzzle' => '🧩',
        'Jouet' => '🧸', 'Jouets' => '🧸',
        'Dino' => '🦕',
        'Barbies et AM' => '👸',
        'Carte Postale' => '🎑',
        'Metallerie' => '⚙️',
        'Allumettes' => '🦟',
        'Pin\'s' => '🧷',
        'Papeterie' => '📝',
        'Tampon' => '🟩',
        'Éléctroménager' => '🔌',
        // ── MAROC ──
        'Miel' => '🍯',
        'Café' => '☕', 'Thé' => '🍵',
        'Eau de Rose' => '🌹',
        'Huile Argan' => '🧴',
        'Creme Argan' => '🧴',
        'Huile Barbarie' => '🌵',
        'Creme Barbarie' => '🧴',
        'Savon Noir' => '🧼',
        'Savon' => '🧼',
        'Arnica' => '🌿',
        'Argile' => '🏺',
        'Pierre Gommage' => '🪨',
        'Gant Gommage' => '🧤',
        'Porte Savon' => '🧼',
        'Ambre' => '💎',
        'Bol' => '🥣',
        'Tasse' => '☕',
        // ── STICKERS ──
        'Sticker' => '🏷️',
        'Tarn' => '🏷️',
        'Jonte' => '🏷️',
        'Jonte new' => '🏷️',
        'Fantaisie' => '🎭',
        'Anciens (Sticker)' => '📜',
        // ── MERCERIE ──
        'Mercerie' => '🧵',
        'Tissus' => '🧵', 'Tissu' => '🧵',
        'Fil' => '🧵',
        'Broderie' => '🧵',
        'Boutton' => '🔘',
        'Cuir' => '👜',
        // ── COLLAB ──
        'CDF' => '💿',
        'Vinyle' => '🎵',
        'Éplucheur' => '🔪',
        'Miel' => '🍯',
        // ── BOUGIES ──
        'Chat (3€)' => '🐱',
        'Crapaud (3€)' => '🐸',
        'Arbre (3€)' => '🌳',
        'Chandelle (3€)' => '🕯️',
        'Lune-Soleil (6€)' => '🌙',
        'Ganesh (6€)' => '🐘',
        'Mère-Fille (6€)' => '👩‍👧',
        'Visage rond (6€)' => '☺️',
        'Double visage (7€)' => '🎭',
        'Moto (7€)' => '🏍️',
        'Indien (8€)' => '🪶',
        'Sirène (8€)' => '🧜',
        'Aigle (9€)' => '🦅',
        'Fleur (9€)' => '🌸',
        'Groot (9€)' => '🌱',
        'Forêt (10€)' => '🌲',
        'Abeille (10€)' => '🐝',
        'Serpent (10€)' => '🐍',
        'Serpent (10€)' => '🐍', // duplicate fix later
        'Clown (13€)' => '🤡',
        'Renaud (15€)' => '🦊',
        'Tortue (10€)' => '🐢',
        'Sorcière (10€)' => '🧙',
        'Vierge (9€)' => '🙏',
        'Chat (5€)' => '🐱',
        'Crapaud (5€)' => '🐸',
        'Arbre (4€)' => '🌳',
        // ── ÉLECTROMÉNAGER / OUTILS ──
        'Éléctroménager' => '🔌',
        'Metallerie' => '⚙️',
        'Couteau' => '🔪',
        // ── AUTRES ──
        'Autres' => '📦',
        'Autre' => '📦',
    ];

    if ($subcategory && isset($subMap[$subcategory])) {
        return $subMap[$subcategory];
    }

    $catMap = [
        'Hommes' => '👕',
        'Femmes' => '👚',
        'Enfants' => '👶',
        'Accessoires' => '🎩',
        'Livres' => '📚',
        'Brocante' => '🏺',
        'Collab' => '🤝',
        'Services' => '🔧',
        'Bijoux' => '💍',
        'Décoration' => '🖼️',
        'Art de la Table' => '🍽️',
        'Maroc' => '🇲🇦',
        'Sticker' => '🏷️',
        'Mercerie' => '🧵',
        'Jouets' => '🧸',
        'Matériel' => '🔧',
        'Neuf' => '🆕',
        'Bougies' => '🕯️',
    ];

    return $catMap[$category] ?? '❓';
}

/**
 * Helper : label de méthode de paiement
 */
function spPaymentLabel($method) {
    $map = ['cash' => 'Espèces', 'card' => 'Carte', 'check' => 'Chèque'];
    return $map[$method] ?? ucfirst($method);
}
?>

<?php $content = ob_get_clean(); ?>
<?php require('view/template/mainTemplate.php'); ?>
