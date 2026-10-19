<?php
// tests/UxPolishTest.php

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

$controlPanel = file_get_contents(__DIR__ . '/../view/control-panel/sale/control-panel/control-panel.php');
assertTrue(
    strpos($controlPanel, 'spFormatFrenchDate') !== false,
    'control-panel.php : helper localisation date FR present'
);
assertTrue(
    strpos($controlPanel, 'spConfirmDelete') !== false,
    'control-panel.php : spConfirmDelete present'
);
assertTrue(
    strpos($controlPanel, 'hiddenSelectedDate') !== false,
    'control-panel.php : hiddenSelectedDate pour JS'
);

$index = file_get_contents(__DIR__ . '/../index.php');
assertTrue(
    strpos($index, "case 'deleteSale':") !== false,
    'index.php deleteSale : route presente'
);

echo PHP_EOL . 'Résultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);
