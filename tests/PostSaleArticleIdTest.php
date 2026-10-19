<?php
// tests/PostSaleArticleIdTest.php — postSaleArticle doit retourner le vrai id insere
require_once __DIR__ . '/../model/BddManager.php';
require_once __DIR__ . '/../model/ArticlesManager.php';
require_once __DIR__ . '/../model/SalesManager.php';
require_once __DIR__ . '/../env.php';

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

$saleId = 11924; // vente de test locale existante
$category = 'TEST_' . uniqid();
$articlesManager = new OpenClassroom\Blog\Model\ArticlesManager();
$salesManager = new OpenClassroom\Blog\Model\SalesManager();
$db = $salesManager->getConnection();

$retourId = $articlesManager->postSaleArticle($saleId, $category, $category, 1, 1.00, '');

$row = $db->prepare('SELECT id FROM articles WHERE sale_id = ? AND category = ? ORDER BY id DESC LIMIT 1');
$row->execute([$saleId, $category]);
$realId = $row->fetchColumn();

assertTrue($retourId !== false && intval($retourId) > 0, 'postSaleArticle retourne un id > 0');
assertTrue(intval($retourId) === intval($realId), 'id retourne correspond a la ligne inseree (' . $realId . ')');

// Nettoyage
if ($realId) {
    $db->prepare('DELETE FROM articles WHERE id = ?')->execute([$realId]);
    $db->prepare('UPDATE sales s LEFT JOIN (SELECT sale_id, SUM(quantity * unit_price) AS total FROM articles WHERE sale_id = ? GROUP BY sale_id) a ON s.id = a.sale_id SET s.total_amount = COALESCE(a.total, 0) WHERE s.id = ?')
        ->execute([$saleId, $saleId]);
}

echo PHP_EOL . 'Resultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);
