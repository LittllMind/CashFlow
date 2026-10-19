<?php
// tests/PurchasePanelDropdownTest.php — Vérifie l'absence du dropdown legacy sur le panneau achat

$viewFile = __DIR__ . '/../view/control-panel/purchase/control-panel.php';
$view = file_get_contents($viewFile);

$errors = [];

if (strpos($view, 'sp-dropdown-menu') !== false) {
    $errors[] = 'dropdown legacy sp-dropdown-menu encore present';
}

if (strpos($view, 'sp-btn-delete') === false) {
    $errors[] = 'bouton sp-btn-delete absent';
}

if (strpos($view, 'ppConfirmDelete') === false) {
    $errors[] = 'appel ppConfirmDelete absent';
}

if (strpos($view, "aria-label=\"Actions\"") !== false) {
    $errors[] = 'ancien bouton a trois points (aria-label Actions) present';
}

if (!empty($errors)) {
    echo "FAIL\n" . implode("\n", $errors) . "\n";
    exit(1);
}

echo "PASS purchase panel dropdown removed\n";
