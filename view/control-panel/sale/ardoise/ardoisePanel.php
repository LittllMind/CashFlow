<?php ob_start(); ?>
<div class="sale-form-wrapper sale-panel ardoise-panel">
    <input type="hidden" id="hiddenSelectedDate" value="<?= $selectedDate ?? date('Y-m-d') ?>">

    <header class="sp-header">
        <div class="sp-header-nav">
            <button type="button" class="sp-btn-icon" onclick="window.location.href='index.php?action=salePanel'" aria-label="Retour ventes">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <div class="sp-header-center">
                <h1 class="sp-header-title">Ardoises clients</h1>
                <span class="sp-header-subtitle"><?= $total_sales ?> ardoise<?= $total_sales > 1 ? 's' : '' ?> en cours</span>
            </div>
            <div class="sp-placeholder"></div>
        </div>
    </header>

    <div class="sp-stats-row">
        <div class="sp-stat-card sp-stat-card--daily">
            <div class="sp-stat-card__top">
                <span class="sp-stat-card__label">Total ardoises</span>
            </div>
            <div class="sp-stat-card__value"><?= number_format($total_amount, 2, ',', ' ') ?> €</div>
            <div class="sp-stat-card__meta">
                <span><?= $total_sales ?> ventes non réglées</span>
            </div>
        </div>
    </div>

    <div class="sp-table-wrapper">
        <div class="sp-table-header ardoise-list-header">
            <h2>Ardoises en cours</h2>
            <span class="sp-table-count"><?= $total_sales ?> transaction<?= $total_sales > 1 ? 's' : '' ?></span>
        </div>
        <?php if ($total_sales > 0): ?>
        <div class="ardoise-list">
            <?php foreach ($ardoises as $ardoise): ?>
            <a href="index.php?action=newSaleForm&amp;sale_id=<?= $ardoise['id'] ?>" class="ardoise-card">
                <div class="ardoise-card__left">
                    <div class="ardoise-card__client">
                        <span class="ardoise-card__client-name"><?= htmlspecialchars($ardoise['client_name'] ?? 'Client #'.$ardoise['client_id']) ?></span>
                        <?php if (!empty($ardoise['client_phone'])): ?>
                        <span class="ardoise-card__phone"><?= htmlspecialchars($ardoise['client_phone']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="ardoise-card__meta">
                        <span class="ardoise-card__date">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <?= $ardoise['sale_date'] ?>
                        </span>
                        <span class="ardoise-card__articles"><?= $ardoise['article_count'] ?? 0 ?> article<?= ($ardoise['article_count'] ?? 0) > 1 ? 's' : '' ?></span>
                    </div>
                </div>
                <div class="ardoise-card__right">
                    <span class="ardoise-card__amount"><?= number_format($ardoise['total_amount'], 2, ',', ' ') ?> €</span>
                    <svg class="ardoise-card__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="sp-empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <p>Aucune ardoise en cours</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const title = document.querySelector('.sp-header-title');
        if (title) {
            title.textContent = 'Ardoises clients';
        }
    });
</script>

<?php $content = ob_get_clean(); ?>
<?php require('view/template/mainTemplate.php'); ?>
