<?php
// resultPanel.php — Page résultats unifiée BROC / MIXTE / FRIPE
// Tableau comparatif compact : 3 colonnes (Jour/Semaine/Mois) × lignes métriques
$fmt = function($n) { return number_format((float)$n, 2, ',', ' '); };
$tab = $_GET['tab'] ?? 'mixte';
if (!in_array($tab, ['broc','fripe','mixte'], true)) $tab = 'mixte';
$prevDate = $prevDate ?? (new DateTime($selectedDate ?? 'now'))->modify('-1 day')->format('Y-m-d');
$nextDate = $nextDate ?? (new DateTime($selectedDate ?? 'now'))->modify('+1 day')->format('Y-m-d');
$periods = [
    'day'   => ['Jour',   $data['day']['label']   ?? date('d/m/Y', strtotime($selectedDate))],
    'week'  => ['Semaine',$data['week']['label']  ?? ''],
    'month' => ['Mois',   $data['month']['label'] ?? ''],
];
ob_start();
?>

<div class="sale-panel result-panel" id="result-report">

<!-- Navigation date -->
<div class="result-nav" data-print="hide">
    <a href="index.php?action=resultPanel&amp;date=<?php echo $prevDate; ?>" class="btn-nav">≪ Jour préc.</a>
    <span class="nav-center"><?php echo date('d/m/Y', strtotime($selectedDate)); ?></span>
    <a href="index.php?action=resultPanel&amp;date=<?php echo $nextDate; ?>" class="btn-nav">Jour suiv. ≫</a>
</div>

<!-- ONGLET ACTIVITÉ -->
<div class="result-activity-tabs" data-print="hide">
    <div class="tab-row">
        <a href="index.php?action=resultPanel&amp;tab=broc&amp;date=<?php echo $selectedDate; ?>"
           class="tab-activity <?php echo ($tab === 'broc') ? 'tab-activity--active' : ''; ?>">Brocante</a>
        <a href="index.php?action=resultPanel&amp;tab=mixte&amp;date=<?php echo $selectedDate; ?>"
           class="tab-activity <?php echo ($tab === 'mixte') ? 'tab-activity--active' : ''; ?>">Mixte</a>
        <a href="index.php?action=resultPanel&amp;tab=fripe&amp;date=<?php echo $selectedDate; ?>"
           class="tab-activity <?php echo ($tab === 'fripe') ? 'tab-activity--active' : ''; ?>">Friperie</a>
    </div>
</div>

<!-- TABLEAU COMPARATIF -->
<div class="result-compare-table">
    <div class="result-table-header">
        <div class="result-table-cell result-table-label" style="background:transparent;border:none;"></div>
        <?php foreach ($periods as $key=>$info): ?>
        <div class="result-table-cell result-table-col-header">
            <strong><?php echo $info[0]; ?></strong>
            <span class="result-table-sub"><?php echo $info[1]; ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <?php
    $lines = [
        ['CA TTC',        'total_ca',            '€'],
        ['Espèces',       'total_cash',          '€'],
        ['Carte bancaire','total_card',            '€'],
        ['Chèques',       'total_cheque',        '€'],
        ['Virements',      'total_bank_transfer', '€'],
        ['Ventes',         'nb_sales',            ''],
        ['Articles',       'nb_articles',         ''],
    ];
    foreach ($lines as $idx=>$line):
        $label = $line[0];
        $key   = $line[1];
        $unit  = $line[2];
        $isMoney = ($unit === '€');
    ?>
    <div class="result-table-row <?php echo ($idx % 2 === 0) ? 'result-table-row--even' : 'result-table-row--odd'; ?>">
        <div class="result-table-cell result-table-label">
            <span class="row-label"><?php echo $label; ?></span>
        </div>
        <?php foreach ($periods as $pkey=>$pinfo): ?>
        <div class="result-table-cell result-table-value">
            <?php if ($isMoney): ?>
            <span class="value-amount"><?php echo $fmt($data[$pkey][$tab][$key]); ?></span>
            <span class="value-unit">€</span>
            <?php else: ?>
            <span class="value-amount"><?php echo number_format($data[$pkey][$tab][$key]); ?></span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Bouton impression -->
<div class="result-actions" data-print="hide">
    <button id="btn-print" class="btn btn-primary">🖨️ Imprimer / PDF</button>
</div>

<script>
document.getElementById('btn-print')?.addEventListener('click', function() { window.print(); });
</script>

</div>

<?php
$content = ob_get_clean();
$title = 'Résultats — ' . date('d/m/Y', strtotime($selectedDate));
$jsFile = '';
$newJsFile = '';
require('view/template/mainTemplate.php');
