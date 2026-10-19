<?php
/**
 * Test statique : modale aperçu rapide d'une vente (salePanel)
 * Vérifie que chaque ligne du tableau expose data-sale-id et que la modale existe.
 */

$viewFile = __DIR__ . '/../view/control-panel/sale/control-panel/control-panel.php';
$html = file_get_contents($viewFile);

$errors = [];

// 1. Chaque tr doit avoir data-sale-id (ou la cellule action)
if (strpos($html, 'data-sale-id') === false) {
    $errors[] = 'Aucun data-sale-id trouvé sur les lignes de vente';
}

// 2. Présence de la modale
if (strpos($html, 'id="salePreviewModal"') === false) {
    $errors[] = 'Modale #salePreviewModal absente';
}

// 3. Bouton fermer dans la modale
if (strpos($html, 'salePreviewClose') === false && strpos($html, 'onclick="closeSalePreview') === false) {
    $errors[] = 'Bouton fermer de la modale manquant';
}

// 4. JS de fetch/fill présent
if (strpos($html, 'openSalePreview') === false) {
    $errors[] = 'Fonction JS openSalePreview absente';
}

// 5. Pas de régression dropdown (doit rester absent)
if (strpos($html, 'sp-dropdown-menu') !== false) {
    $errors[] = 'sp-dropdown-menu encore présent (régression)';
}

if (!empty($errors)) {
    echo "FAIL\n" . implode("\n", $errors) . "\n";
    exit(1);
}

echo "PASS salePanel modal — " . count($errors) . " erreurs\n";
