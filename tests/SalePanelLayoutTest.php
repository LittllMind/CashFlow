<?php
// tests/SalePanelLayoutTest.php — Vérifie l'ordre et les éléments de salePanel

$passed = 0;
$failed = 0;

function assertTrue($condition, $msg) {
    global $passed, $failed;
    if ($condition) {
        echo '  PASS : ' . $msg . PHP_EOL;
        $passed++;
    } else {
        echo '  FAIL : ' . $msg . PHP_EOL;
        $failed++;
    }
}

$panel = file_get_contents(__DIR__ . '/../view/control-panel/sale/control-panel/control-panel.php');

// Normaliser les espaces pour faciliter les positions
$compact = preg_replace('/\s+/', ' ', $panel);

// 1. Tableau des ventes en premier
$posVentes = strpos($compact, 'Ventes du jour');
$posStats = strpos($compact, 'sp-stats-row');
$posRecap = strpos($compact, 'Récap articles');

assertTrue(
    $posVentes !== false && $posStats !== false && $posRecap !== false,
    'salePanel : les 3 sections (ventes, stats, récap) sont présentes'
);

assertTrue(
    $posVentes < $posStats && $posStats < $posRecap,
    'salePanel : ordre Ventes < Stats < Recap articles'
);

// 2. Lien Ardoises dans le header actions
assertTrue(
    strpos($compact, 'ardoisePanel') !== false,
    'salePanel : lien vers ardoisePanel présent'
);

// 3. Total global dans le récap articles
assertTrue(
    strpos($compact, 'sp-tf-amt') !== false,
    'salePanel : total global dans le footer du récap articles'
);

// 4. Date picker à côté du titre
assertTrue(
    (strpos($compact, '<input type="date"') !== false || strpos($compact, 'showDatePicker') !== false),
    'salePanel : date picker présent (input date ou bouton JS)'
);

assertTrue(
    strpos($compact, 'sp-row-warning') !== false,
    'salePanel : alerte prix 0/négatif dans le récap articles'
);

// 7. Carte mobile avec toggle détails au lieu de navigation globale
assertTrue(
    strpos($compact, 'sp-sale-card__summary') !== false && strpos($compact, 'sp-sale-card__details') !== false,
    'salePanel mobile : structure toggle details presente'
);
assertTrue(
    strpos($compact, 'data-sale-id') !== false && strpos($compact, 'data-href') === false,
    'salePanel mobile : carte identifiee par sale_id, plus de navigation globale data-href'
);
assertTrue(
    strpos($compact, 'toggleSaleCardDetails') !== false,
    'salePanel mobile : JS de toggle present'
);
assertTrue(
    strpos($compact, '__salePreviewData') !== false,
    'salePanel mobile : donnees prechargees pour le toggle'
);

// 8. Lien heure n'est plus un <a> ; seul le bouton Modifier la vente ouvre la vente
assertTrue(
    strpos($compact, '<a href="index.php?action=newSaleForm') !== false,
    'salePanel mobile : lien Modifier la vente present'
);
assertTrue(
    strpos($compact, 'class="sp-btn-edit"') !== false,
    'salePanel mobile : bouton modifier la vente est un lien actif'
);
assertTrue(
    strpos($compact, 'class="sp-sale-card__time"') !== false && strpos($compact, 'class="sp-sale-card__time" href=') === false,
    'salePanel mobile : heure n\'est plus un lien'
);

// 9. Syntaxe
$filesToCheck = [
    __DIR__ . '/../view/control-panel/sale/control-panel/control-panel.php',
];
foreach ($filesToCheck as $file) {
    $output = [];
    $return = 0;
    exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $return);
    assertTrue(
        $return === 0,
        'Syntaxe OK : ' . basename($file)
    );
}

echo PHP_EOL . 'Résultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);
