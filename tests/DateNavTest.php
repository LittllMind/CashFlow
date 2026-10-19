<?php
// tests/DateNavTest.php

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

$index = file_get_contents(__DIR__ . '/../index.php');
assertTrue(
    strpos($index, 'selectedDate') !== false,
    'index.php newSaleForm : gestion selectedDate presente'
);

$updateJs = file_get_contents(__DIR__ . '/../view/control-panel/sale/update/update.js');
assertTrue(
    strpos($updateJs, 'newSaleFormNav') !== false,
    'update.js : helper newSaleFormNav present'
);
assertTrue(
    strpos($updateJs, 'selectedDate') !== false && strpos($updateJs, "sale_date'") !== false,
    'update.js : newSaleFormNav fallback sale_date quand selectedDate absent'
);

$controlPanel = file_get_contents(__DIR__ . '/../view/control-panel/sale/control-panel.php');
assertTrue(
    strpos($controlPanel, 'newSaleForm') !== false && strpos($controlPanel, 'selectedDate') !== false,
    'control-panel.php : lien nouvelle vente propage selectedDate'
);

echo PHP_EOL . 'Résultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);
