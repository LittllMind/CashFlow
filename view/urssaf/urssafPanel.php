<?php
// urssafPanel.php — panneau URSSAF a double onglets
// Niveau 1 : activite (broc / fripe)
// Niveau 2 : periodicite (mensuel / trimestriel / annuel)
// Variables : $mode, $period, $owner, $activity, $data, $isDeclared, $declarationInfo,
// $periodLabel, $periodStart, $periodEnd, $daysWithSales, $navPrev*, $navNext*, $year, $month, $quarter

$fmt = function($n) {
    return number_format((float)$n, 2, ',', ' ');
};

$printDate = date('d/m/Y \u00e0 H:i');
ob_start();
?>

<div class="urssaf-wrapper" id="urssaf-report">

<!-- En-tete impression -->
<div class="urssaf-header">
    <h1>Déclaration URSSAF</h1>
    <div class="urssaf-meta">
        <span class="urssaf-entity"><?php echo ($mode === 'broc') ? 'CashFlow — Brocante' : 'CashFlow — Friperie'; ?></span>
        <span class="urssaf-regime"><?php echo ucfirst($period); ?></span>
    </div>
    <div class="urssaf-period-label">
        <span>Période :</span> <strong><?php echo $periodLabel; ?></strong>
        <span>(<?php echo date('d/m/Y', strtotime($periodStart)); ?> – <?php echo date('d/m/Y', strtotime($periodEnd)); ?>)</span>
    </div>
</div>

<!-- = = = ONGLET NIVEAU 1 : ACTIVITÉ = = = -->
<div class="urssaf-tabs-activity" data-print="hide">
    <div class="tab-row">
        <a href="index.php?action=urssafPanel&amp;mode=broc&amp;period=<?php echo $period; ?>&amp;year=<?php echo $year; ?>&amp;month=<?php echo $month; ?>&amp;quarter=<?php echo $quarter; ?>"
           class="tab-activity <?php echo ($mode === 'broc') ? 'tab-activity--active' : ''; ?>">Brocante (Aurélien)</a>
        <a href="index.php?action=urssafPanel&amp;mode=fripe&amp;period=<?php echo $period; ?>&amp;year=<?php echo $year; ?>&amp;month=<?php echo $month; ?>&amp;quarter=<?php echo $quarter; ?>"
           class="tab-activity <?php echo ($mode === 'fripe') ? 'tab-activity--active' : ''; ?>">Friperie (Fripe)</a>
    </div>
</div>

<!-- = = = ONGLET NIVEAU 2 : PÉRIODICITÉ = = = -->
<div class="urssaf-tabs-period" data-print="hide">
    <div class="tab-row">
        <?php
        $periods = ['mensuel' => 'Mensuel', 'trimestriel' => 'Trimestriel', 'annuel' => 'Annuel'];
        foreach ($periods as $pKey => $pLabel):
            $isActive = ($period === $pKey);
            // Conserve mois/trimestre courant
            $url = "index.php?action=urssafPanel&amp;mode=" . $mode
                 . "&amp;period=" . $pKey
                 . "&amp;year=" . $year
                 . "&amp;month=" . $month
                 . "&amp;quarter=" . $quarter;
        ?>
        <a href="<?php echo $url; ?>"
           class="tab-period <?php echo $isActive ? 'tab-period--active' : ''; ?>"><?php echo $pLabel; ?></a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Navigation (masquee a l'impression) -->
<div class="urssaf-nav" data-print="hide">
    <?php if ($period === 'mensuel') { ?>
        <a href="index.php?action=urssafPanel&amp;mode=<?php echo $mode; ?>&amp;period=mensuel&amp;year=<?php echo $navPrevYear; ?>&amp;month=<?php echo $navPrevMonth; ?>" class="btn-nav">≪b Mois préc.</a>
        <span class="nav-center"><?php echo MONTHS_FR[$month] . ' ' . $year; ?></span>
        <a href="index.php?action=urssafPanel&amp;mode=<?php echo $mode; ?>&amp;period=mensuel&amp;year=<?php echo $navNextYear; ?>&amp;month=<?php echo $navNextMonth; ?>" class="btn-nav">Mois suiv. »</a>
    <?php } elseif ($period === 'trimestriel') { ?>
        <a href="index.php?action=urssafPanel&amp;mode=<?php echo $mode; ?>&amp;period=trimestriel&amp;year=<?php echo $navPrevYear; ?>&amp;quarter=<?php echo $navPrevQuarter; ?>" class="btn-nav">≪b Trim. préc.</a>
        <span class="nav-center">Trimestre <?php echo $quarter; ?> <?php echo $year; ?></span>
        <a href="index.php?action=urssafPanel&amp;mode=<?php echo $mode; ?>&amp;period=trimestriel&amp;year=<?php echo $navNextYear; ?>&amp;quarter=<?php echo $navNextQuarter; ?>" class="btn-nav">Trim. suiv. »</a>
    <?php } else { // annuel ?>
        <a href="index.php?action=urssafPanel&amp;mode=<?php echo $mode; ?>&amp;period=annuel&amp;year=<?php echo $navPrevYear; ?>" class="btn-nav">≪b Année préc.</a>
        <span class="nav-center">Année <?php echo $year; ?></span>
        <a href="index.php?action=urssafPanel&amp;mode=<?php echo $mode; ?>&amp;period=annuel&amp;year=<?php echo $navNextYear; ?>" class="btn-nav">Année suiv. »</a>
    <?php } ?>
</div>

<!-- Alerte si deja declare -->
<?php if ($isDeclared && $declarationInfo) { ?>
<div class="urssaf-alert urssaf-alert--info" data-print="hide">
    <span>Déclaré le <?php echo date('d/m/Y \u00e0 H:i', strtotime($declarationInfo['declared_at'])); ?> par <?php echo htmlspecialchars($declarationInfo['declared_by'] ?? ''); ?></span>
</div>
<?php } ?>

<!-- Donnees principales -->
<div class="urssaf-cards">
    <div class="urssaf-card urssaf-card--ca">
        <span class="card-label">CA total TTC</span>
        <span class="card-value"><?php echo $fmt($data['total_ca']); ?> <span class="card-unit">€</span></span>
    </div>
    <div class="urssaf-card urssaf-card--cash">
        <span class="card-label">Espèces</span>
        <span class="card-value"><?php echo $fmt($data['total_cash']); ?> <span class="card-unit">€</span></span>
    </div>
    <div class="urssaf-card urssaf-card--card">
        <span class="card-label">Carte bancaire</span>
        <span class="card-value"><?php echo $fmt($data['total_card']); ?> <span class="card-unit">€</span></span>
    </div>
    <div class="urssaf-card urssaf-card--cheque">
        <span class="card-label">Chèques</span>
        <span class="card-value"><?php echo $fmt($data['total_cheque']); ?> <span class="card-unit">€</span></span>
    </div>
    <div class="urssaf-card urssaf-card--transfer">
        <span class="card-label">Virements</span>
        <span class="card-value"><?php echo $fmt($data['total_bank_transfer']); ?> <span class="card-unit">€</span></span>
    </div>
</div>

<!-- Indicateurs -->
<div class="urssaf-metrics">
    <div class="metric">
        <span class="metric-val"><?php echo number_format($data['nb_sales']); ?></span>
        <span class="metric-label">Ventes</span>
    </div>
    <div class="metric">
        <span class="metric-val"><?php echo number_format($data['nb_articles']); ?></span>
        <span class="metric-label">Articles</span>
    </div>
    <div class="metric">
        <span class="metric-val"><?php echo number_format($data['nb_purchases']); ?></span>
        <span class="metric-label">Achats fse</span>
    </div>
    <div class="metric">
        <span class="metric-val"><?php echo $fmt($data['total_purchases']); ?> €</span>
        <span class="metric-label">Total achats</span>
    </div>
    <div class="metric">
        <span class="metric-val"><?php echo count($daysWithSales); ?></span>
        <span class="metric-label">Jours actifs</span>
    </div>
</div>

<!-- Details par mode de paiement -->
<div class="urssaf-detail">
    <h3>Détail par mode de paiement</h3>
    <div class="detail-table">
        <div class="detail-row">
            <span class="detail-name">Espèces</span>
            <span class="detail-value"><?php echo $fmt($data['total_cash']); ?> €</span>
        </div>
        <div class="detail-row">
            <span class="detail-name">Carte bancaire</span>
            <span class="detail-value"><?php echo $fmt($data['total_card']); ?> €</span>
        </div>
        <div class="detail-row">
            <span class="detail-name">Chèques</span>
            <span class="detail-value"><?php echo $fmt($data['total_cheque']); ?> €</span>
        </div>
        <div class="detail-row">
            <span class="detail-name">Virements bancaires</span>
            <span class="detail-value"><?php echo $fmt($data['total_bank_transfer']); ?> €</span>
        </div>
        <div class="detail-row detail-row--total">
            <span class="detail-name">Total</span>
            <span class="detail-value"><?php echo $fmt($data['total_ca']); ?> €</span>
        </div>
    </div>
</div>

<!-- Frais CB nets -->
<?php if ($data['total_card'] > 0) {
    $cbFees = round($data['total_card'] * 0.0146, 2);
    $cbNet  = $data['total_card'] - $cbFees;
?>
<div class="urssaf-note">
    <span class="note-icon">⚠️</span>
    <span>Carte bancaire brute : <?php echo $fmt($data['total_card']); ?> €. Frais CB estimés (1,46 %) : <strong>–<?php echo $fmt($cbFees); ?> €</strong>. Net versé : <strong><?php echo $fmt($cbNet); ?> €</strong>.
    <br>&gt;Le montant à déclarer à l'URSSAF est le CA TTC (brut).</span>
</div>
<?php } ?>

<!-- Actions (masquees a l'impression) -->
<div class="urssaf-actions" data-print="hide">
    <button id="btn-print" class="btn btn-primary"><span class="btn-icon">🖨️</span> Imprimer / PDF</button>
    <?php if (!$isDeclared) { ?>
    <button id="btn-declare" class="btn btn-success"
            data-mode="<?php echo $mode; ?>"
            data-start="<?php echo $periodStart; ?>"
            data-end="<?php echo $periodEnd; ?>"
            data-total-ca="<?php echo $data['total_ca']; ?>"
            data-total-cash="<?php echo $data['total_cash']; ?>"
            data-total-card="<?php echo $data['total_card']; ?>"
            data-total-cheque="<?php echo $data['total_cheque']; ?>"
            data-total-transfer="<?php echo $data['total_bank_transfer']; ?>"
            data-nb-sales="<?php echo $data['nb_sales']; ?>"
            data-nb-articles="<?php echo $data['nb_articles']; ?>">
        <span class="btn-icon">✅</span> Marquer comme déclaré
    </button>
    <?php } ?>
</div>

<!-- Pied de page impression -->
<div class="urssaf-footer-print">
    Document généré par CashFlow — CashFlow — <?php echo $printDate; ?> — Impression résultats URSSAF.
</div>

</div><!-- /urssaf-wrapper -->

<script>
(function() {
    var btnPrint = document.getElementById('btn-print');
    if (btnPrint) btnPrint.addEventListener('click', function() { window.print(); });

    var btnDeclare = document.getElementById('btn-declare');
    if (btnDeclare) {
        btnDeclare.addEventListener('click', function() {
            var btn = this;
            if (btn.disabled) return;

            var formData = new URLSearchParams();
            formData.append('action', 'apiUrssafDeclare');
            formData.append('mode', btn.dataset.mode);
            formData.append('period_start', btn.dataset.start);
            formData.append('period_end', btn.dataset.end);
            formData.append('total_ca', btn.dataset.totalCa);
            formData.append('total_cash', btn.dataset.totalCash);
            formData.append('total_card', btn.dataset.totalCard);
            formData.append('total_cheque', btn.dataset.totalCheque);
            formData.append('total_bank_transfer', btn.dataset.totalTransfer);
            formData.append('nb_sales', btn.dataset.nbSales);
            formData.append('nb_articles', btn.dataset.nbArticles);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    btn.textContent = '✅ Déclaré !';
                    btn.disabled = true;
                    btn.classList.add('btn--disabled');
                    alert('Période marquée comme déclarée.');
                    location.reload();
                } else {
                    alert(data.error || 'Erreur lors de la déclaration.');
                }
            })
            .catch(function(err) {
                alert('Erreur réseau. Vérifiez votre session.');
            });
        });
    }
})();
</script>

<?php
$content = ob_get_clean();
$title = 'URSSAF — ' . URSSAF_LABELS[$mode];
$jsFile = '';
$newJsFile = '';
require('view/template/mainTemplate.php');
?>
