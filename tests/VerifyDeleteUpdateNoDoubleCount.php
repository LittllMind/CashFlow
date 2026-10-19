<?php
/**
 * Test statique : deleteSaleArticleAjax + updateSaleArticleAjax
 * ne doivent pas appeler updateTotalAmount après deleteArticle/updateArticle.
 */

$errors = [];
$controller = file_get_contents(__DIR__ . '/../controller/SalesController.php');

// Vérifier qu'entre deleteArticle et updateTotalAmount il n'y a pas de recalcul
$blocks = [
    'deleteSaleArticleAjax' => ['deleteArticle', 'updateTotalAmount'],
    'updateSaleArticleAjax' => ['updateArticle', 'updateTotalAmount'],
];

foreach ($blocks as $fn => $pair) {
    $start = strpos($controller, "function $fn");
    if ($start === false) { $errors[] = "function $fn non trouvée"; continue; }
    $end = strpos($controller, "\nfunction ", $start + 1);
    if ($end === false) $end = strlen($controller);
    $body = substr($controller, $start, $end - $start);

    $hasFirst = strpos($body, $pair[0]) !== false;
    $hasSecond = strpos($body, $pair[1]) !== false;

    if ($hasFirst && $hasSecond) {
        $errors[] = "$fn : {$pair[0]} ET {$pair[1]} présents — double-comptage possible";
    }
    if (!$hasFirst) {
        $errors[] = "$fn : {$pair[0]} manquant";
    }
}

if (count($errors) > 0) {
    echo "FAIL\n"; foreach ($errors as $e) echo "  - $e\n"; exit(1);
}
echo "PASS — deleteSaleArticleAjax + updateSaleArticleAjax sans updateTotalAmount\n"; exit(0);
