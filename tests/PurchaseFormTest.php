<?php
// tests/PurchaseFormTest.php — Vérifie le formulaire d'achat (thème salePanel + mode lot global)


class PurchaseFormTest
{
    private $failed = 0;
    private $passed = 0;

    function assertTrue($condition, $message)
    {
        if ($condition) {
            $this->passed++;
            echo "  PASS : $message\n";
        } else {
            $this->failed++;
            echo "  FAIL : $message\n";
        }
    }

    function runTest()
    {
        echo "=== " . __FILE__ . " ===\n";

        define('ROOT', __DIR__ . '/../');

        $updateHtml    = file_get_contents(ROOT . 'view/control-panel/purchase/update.php');
        $createHtml    = file_get_contents(ROOT . 'view/control-panel/purchase/create.php');
        $js            = file_get_contents(ROOT . 'view/control-panel/purchase/update/update.js');
        $controller    = file_get_contents(ROOT . 'controller/PurchasesController.php');
        $index         = file_get_contents(ROOT . 'index.php');
        $css           = file_get_contents(ROOT . 'assets/css/sale-modern.css');
        $providersMgr  = file_get_contents(ROOT . 'model/ProvidersManager.php');
        $purchasesMgr  = file_get_contents(ROOT . 'model/PurchasesManager.php');
        $articlesMgr   = file_get_contents(ROOT . 'model/ArticlesManager.php');
        $mainTemplate  = file_get_contents(ROOT . 'view/template/mainTemplate.php');

        // update.php structure
        $this->assertTrue(strpos($updateHtml, 'purchase-page') !== false, 'update.php : racine purchase-page');
        $this->assertTrue(strpos($updateHtml, 'receipt-line') !== false, 'update.php : receipt-line cards');
        $this->assertTrue(strpos($updateHtml, 'card-stepper') !== false, 'update.php : steppers inline');
        $this->assertTrue(strpos($updateHtml, 'name="comment"') !== false, 'update.php : commentaire inline');
        $this->assertTrue(strpos($updateHtml, 'payment-btn') !== false, 'update.php : payment buttons tiles');
        $this->assertTrue(strpos($updateHtml, 'purchase-tile-total') !== false, 'update.php : tile total');
        $this->assertTrue(strpos($updateHtml, 'purchase-tile-date') !== false, 'update.php : tile date');

        // Mode lot global
        $this->assertTrue(strpos($updateHtml, '$lotMode = floatval($purchase[\'paid_amount\']') !== false, 'update.php : flag mode lot global');
        $this->assertTrue(strpos($updateHtml, 'lot-banner') !== false, 'update.php : bannière mode lot');
        $this->assertTrue(strpos($js, 'globalLot = (typeof lotModeFlag') !== false, 'update.js : flag globalLot');
        $this->assertTrue(strpos($js, 'function addPurchaseLine') !== false, 'update.js : helper addPurchaseLine');
        $this->assertTrue(strpos($js, 'function refreshLotLines') !== false, 'update.js : refreshLotLines');
        $this->assertTrue(strpos($updateHtml, 'const categoryEmojis =') === false, 'update.php : pas de const categoryEmojis inline');
        $this->assertTrue(strpos($updateHtml, 'const subcategories') !== false, 'update.php : données JS sous-catégories');
        $this->assertTrue(strpos($js, 'const categoryEmojis = {') === false && strpos($js, 'var categoryEmojis = {') !== false, 'update.js : mapping emoji categories en var (pas const)');
        $this->assertTrue(strpos($js, 'const subcategoryEmojis = {') === false && strpos($js, 'var subcategoryEmojis = {') !== false, 'update.js : mapping emoji sous-categories en var');
        $this->assertTrue(strpos($js, 'subcategoryEmojis[subcategory.subcategory_name]') !== false, 'update.js : emoji sous-categorie dans boutons');
        $this->assertTrue(strpos($js, "categoryEmojis[art.category]") !== false, 'update.js : emoji catégorie dans cards');
        $this->assertTrue(strpos($purchasesMgr, 'function normalizeLotUnitPrices') !== false, 'PurchasesManager.php : normalisation lot');

        // create.php
        $this->assertTrue(strpos($createHtml, 'purchase-page') !== false, 'create.php : racine purchase-page');
        $this->assertTrue(strpos($createHtml, 'function setPayment') !== false, 'create.php : setPayment inline');
        $this->assertTrue(strpos($createHtml, 'Particulier / Don / Vide-grenier') !== false, 'create.php : option particulier/don/vide-grenier');
        $this->assertTrue(strpos($createHtml, 'quickProviderPopup') !== false, 'create.php : popup fournisseur');

        // Fournisseur à la volée
        $this->assertTrue(strpos($updateHtml, 'openAddProviderPopup') !== false, 'update.php : bouton fournisseur à la volée');
        $this->assertTrue(strpos($controller, 'function apiAddProvider') !== false, 'PurchasesController.php : apiAddProvider');
        $this->assertTrue(strpos($providersMgr, 'function postProvider') !== false, 'ProvidersManager.php : postProvider');
        $this->assertTrue(strpos($index, "case 'apiAddProvider':") !== false, 'index.php : route apiAddProvider');

        // Contrat API achat
        $this->assertTrue(strpos($controller, 'function apiSavePurchaseArticle') !== false, 'PurchasesController.php : API sauvegarde article achat');
        $this->assertTrue(strpos($controller, 'function apiDeletePurchaseArticle') !== false, 'PurchasesController.php : API suppression article achat');
        $this->assertTrue(strpos($controller, 'function apiUpdatePurchaseArticle') !== false, 'PurchasesController.php : API mise à jour article achat');
        $this->assertTrue(strpos($controller, 'function apiUpdatePurchase') !== false, 'PurchasesController.php : API mise à jour achat');
        $this->assertTrue(strpos($index, "case 'apiSavePurchaseArticle':") !== false, 'index.php : route apiSavePurchaseArticle');
        $this->assertTrue(strpos($index, "case 'apiDeletePurchaseArticle':") !== false, 'index.php : route apiDeletePurchaseArticle');
        $this->assertTrue(strpos($index, "case 'apiUpdatePurchaseArticle':") !== false, 'index.php : route apiUpdatePurchaseArticle');
        $this->assertTrue(strpos($index, "case 'apiUpdatePurchase':") !== false, 'index.php : route apiUpdatePurchase');
        $this->assertTrue(strpos($controller, 'userValidation') !== false, 'PurchasesController.php : sécurité API présente');

        // ArticlesManager retourne le nouvel ID
        $this->assertTrue(strpos($articlesMgr, 'lastInsertId') !== false, 'ArticlesManager.php : postPurchaseArticle retourne lastInsertId');

        // Styles
        $this->assertTrue(strpos($css, '.purchase-page') !== false, 'sale-modern.css : .purchase-page');
        $this->assertTrue(strpos($css, '.purchase-info-row') !== false || strpos($css, 'purchase-info-row') !== false, 'sale-modern.css : rangée info achat');
        $this->assertTrue(strpos($css, '.lot-banner') !== false, 'sale-modern.css : bannière lot');

        // mainTemplate
        $this->assertTrue(strpos($mainTemplate, 'purchase/update/update.js') !== false, 'mainTemplate.php : référence JS achat');
        $this->assertTrue(preg_match('/purchase\/update\/update\.js\?v=(\d+)/', $mainTemplate, $m) && intval($m[1]) >= 3, 'mainTemplate.php : cache-bust purchase JS >= v=3');

        // HTML valide : aucune balise fermante tronquée
        $this->assertTrue(strpos($updateHtml, "</div\n") === false, 'update.php : pas de tag </div tronqué');

        // Pas de conflit JS map emojis (vue PHP et JS externe)
        $this->assertTrue(strpos($updateHtml, 'const categoryEmojis') === false, 'update.php : pas de const categoryEmojis inline (évite doublon const avec JS externe)');
        $this->assertTrue(strpos($mainTemplate, 'const categoryEmojis') === false, 'mainTemplate.php : pas de categoryEmojis inline');
        $this->assertTrue(strpos($js, 'const categoryEmojis') === false, 'update.js : declarations var (évite doublon const)');

        // Securite API update achat
        $apiUpdateStart = strpos($controller, 'function apiUpdatePurchase(');
        $nextFn = strpos($controller, 'function apiAddProvider()', $apiUpdateStart);
        $apiUpdateBody = substr($controller, $apiUpdateStart, $nextFn - $apiUpdateStart);
        $this->assertTrue(strpos($apiUpdateBody, 'userValidation()') !== false, 'PurchasesController.php : apiUpdatePurchase protégé par userValidation()');

        // Syntaxe
        $this->assertTrue(strpos(shell_exec('php -l ' . ROOT . 'view/control-panel/purchase/update.php'), 'No syntax errors') !== false, 'Syntaxe OK : update.php');
        $this->assertTrue(strpos(shell_exec('php -l ' . ROOT . 'view/control-panel/purchase/create.php'), 'No syntax errors') !== false, 'Syntaxe OK : create.php');
        $this->assertTrue(strpos(shell_exec('php -l ' . ROOT . 'controller/PurchasesController.php'), 'No syntax errors') !== false, 'Syntaxe OK : PurchasesController.php');
        $this->assertTrue(strpos(shell_exec('php -l ' . ROOT . 'model/PurchasesManager.php'), 'No syntax errors') !== false, 'Syntaxe OK : PurchasesManager.php');
        $this->assertTrue(strpos(shell_exec('php -l ' . ROOT . 'model/ArticlesManager.php'), 'No syntax errors') !== false, 'Syntaxe OK : ArticlesManager.php');
        $jsCheck = shell_exec('node --check ' . ROOT . 'view/control-panel/purchase/update/update.js 2>&1');
        $this->assertTrue($jsCheck === null || $jsCheck === '', 'Syntaxe JS OK : purchase/update.js');

        echo "\nRésultat : " . $this->passed . " pass, " . $this->failed . " fail\n";
        return $this->failed === 0;
    }
}

$test = new PurchaseFormTest();
exit($test->runTest() ? 0 : 1);
