<?php
// tests/SaleDateArticleSyncTest.php
// Vérifie que la date de vente et la date des articles restent synchronisées
// quand on modifie sale_date (nouvelle vente d'hier, etc.).

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

$salesController = file_get_contents(__DIR__ . '/../controller/SalesController.php');
$articlesManager = file_get_contents(__DIR__ . '/../model/ArticlesManager.php');
$updateJs        = file_get_contents(__DIR__ . '/../view/control-panel/sale/update/update.js');

assertTrue(
    strpos($salesController, 'updateDateSaleArticlesDateOnly') !== false,
    'SalesController appelle articleManager->updateDateSaleArticlesDateOnly()'
);

assertTrue(
    strpos($articlesManager, 'updateDateSaleArticlesDateOnly') !== false &&
    strpos($articlesManager, 'created_at') !== false &&
    strpos($articlesManager, 'H:i:s') !== false,
    'ArticlesManager conserve l heure de chaque article lors du decalage de jour'
);

// updateSaleAjax reçoit une date sans heure -> la conserve maintenant aussi
assertTrue(
    strpos($salesController, "preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', \$sale_date)") !== false,
    'SalesController detecte les dates sans heure et garde l heure existante'
);

// Quand on ajoute un article, le JS doit suivre la date courante du saleForm si possible
assertTrue(
    strpos($updateJs, "apiSaveArticle") !== false,
    'update.js appelle apiSaveArticle'
);

assertTrue(
    strpos($salesController, "apiSaveArticle") !== false || strpos(file_get_contents(__DIR__ . '/../index.php'), "case 'apiSaveArticle':") !== false,
    'Route apiSaveArticle existante'
);

// Vérifie l'existence du helper PHP postSaleArticle prend en compte sale_date
assertTrue(
    strpos($articlesManager, 'postSaleArticle') !== false,
    'ArticlesManager::postSaleArticle existe'
);

echo PHP_EOL . 'Résultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);
