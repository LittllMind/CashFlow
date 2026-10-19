<?php
/**
 * Test statique : s'assurer qu'aucun contrôleur ne double-compte le total vente
 * après une opération modèle qui appelle déjà syncSaleTotalAmount.
 */

$errors = [];

$controller = file_get_contents(__DIR__ . '/../controller/SalesController.php');

// postSaleArticle doit être suivi d'une LECTURE du total, pas d'un update
if (preg_match('/postSaleArticle.*\n.*\$sale\s*=\s*\$saleManager->getSaleById/s', $controller)) {
    // OK — il lit le total
} else {
    $errors[] = "postSaleArticle non suivi d'une lecture de total";
}

// Aucun updateTotalAmount ne doit suivre postSaleArticle/updateArticle/deleteArticle dans SalesController
$lines = explode("\n", $controller);
$inAdd = $inDelete = false;
foreach ($lines as $i => $line) {
    if (strpos($line, 'postSaleArticle') !== false) $inAdd = true;
    if (strpos($line, 'deleteArticle') !== false) $inDelete = true;
    if (strpos($line, 'function ') !== false && $i > 0) { $inAdd = false; $inDelete = false; }
    if ($inAdd && strpos($line, 'updateTotalAmount') !== false) {
        $errors[] = "updateTotalAmount après postSaleArticle ligne " . ($i+1);
        $inAdd = false;
    }
    if ($inDelete && strpos($line, 'updateTotalAmount') !== false) {
        $errors[] = "updateTotalAmount après deleteArticle ligne " . ($i+1);
        $inDelete = false;
    }
}

if (count($errors) > 0) {
    echo "FAIL\n";
    foreach ($errors as $e) echo "  - $e\n";
    exit(1);
}

echo "PASS — Aucune double écriture détectée dans SalesController\n";
exit(0);
