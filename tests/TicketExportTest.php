<?php
/**
 * Test statique : Export ticket de vente
 */
chdir('.');
$errors = [];

$entity = file_get_contents('model/EntityProfile.php');
if (strpos($entity, 'SIRET_FRIPE') === false)        $errors[] = "SIRET_FRIPE manquant";
if (strpos($entity, 'SIRET_BROC') === false)   $errors[] = "SIRET_BROC manquant";
if (strpos($entity, 'MENTION_TVA') === false)        $errors[] = "MENTION_TVA manquante";
if (strpos($entity, 'fripeCategories') === false)    $errors[] = "fripeCategories() manquante";

$ctrl = file_get_contents('controller/ExportSaleController.php');
if (strpos($ctrl, 'function exportSale') === false) $errors[] = "exportSale non définie";
if (strpos($ctrl, 'getSiretsForTicket') === false) $errors[] = "getSiretsForTicket() non définie";

$view = file_get_contents('view/control-panel/sale/update/update.php');
if (strpos($view, 'openBuyerPopup') === false)      $errors[] = "openBuyerPopup manquant";
if (strpos($view, '🖨️') === false)                $errors[] = "Icône 🖨️ manquante";
if (strpos($view, '📋') === false)                $errors[] = "Icône 📋 manquante";
if (strpos($view, '✉️') === false)                $errors[] = "Icône ✉️ manquante";

$index = file_get_contents('index.php');
if (strpos($index, "case 'exportSale':") === false) $errors[] = "Route exportSale manquante";

if (count($errors) > 0) {
    echo "FAIL\n"; foreach ($errors as $e) echo "  - $e\n"; exit(1);
}
echo "PASS — Export ticket complet (contrôleur, modèle, vue, route)\n";
exit(0);
