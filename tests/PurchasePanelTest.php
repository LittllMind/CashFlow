<?php
// tests/PurchasePanelTest.php — Vérifie le panneau des achats (thème salePanel)

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

// 1. Route purchasePanel présente
$index = file_get_contents(__DIR__ . '/../index.php');
assertTrue(strpos($index, "case 'purchasePanel'") !== false, 'index.php : route purchasePanel');

// 2. Controller exposé
$controller = file_get_contents(__DIR__ . '/../controller/PurchasesController.php');
assertTrue(strpos($controller, 'function purchasePanel') !== false, 'PurchasesController.php : fonction purchasePanel');

// 3. Vue
assertTrue(file_exists(__DIR__ . '/../view/control-panel/purchase/control-panel.php'), 'vue purchase control-panel.php existe');
$view = file_get_contents(__DIR__ . '/../view/control-panel/purchase/control-panel.php');
assertTrue(strpos($view, 'purchase-panel') !== false, 'vue : racine purchase-panel');
assertTrue(strpos($view, 'sp-header') !== false, 'vue : header theme salePanel');
assertTrue(strpos($view, 'sp-table') !== false, 'vue : tableau theme salePanel');
assertTrue(strpos($view, 'sp-stat-card') !== false, 'vue : cartes stats theme salePanel');

// 4. JS dédié
assertTrue(file_exists(__DIR__ . '/../view/control-panel/purchase/control-panel.js'), 'JS dédié purchase control-panel.js existe');
$js = file_get_contents(__DIR__ . '/../view/control-panel/purchase/control-panel.js');
assertTrue(strpos($js, 'purchasePanel') !== false || strpos($js, 'selectedDate') !== false, 'JS : navigation date purchasePanel');

// 5. Syntaxe
assertTrue(substr($view, 0, 5) === '<?php', 'vue : commence par PHP ob_start');
assertTrue(strpos($view, '$content = ob_get_clean();') !== false, 'vue : buffer obtenu');
assertTrue(strpos($view, "require('view/template/mainTemplate.php');") !== false, 'vue : layout mainTemplate');

// 6. Bouton nouvel achat et navigation date
assertTrue(strpos($view, 'Nouvel achat') !== false || strpos($view, '+ Achat') !== false || strpos($view, 'newPurchaseForm') !== false, 'vue : accès nouveau formulaire achat');
assertTrue(strpos($view, 'selectedDate') !== false, 'vue : champ date pour navigation');

$phpCheck = shell_exec('php -l ' . __DIR__ . '/../view/control-panel/purchase/control-panel.php 2>&1');
assertTrue(strpos($phpCheck, 'No syntax errors') !== false, 'Syntaxe OK : control-panel.php');

$jsCheck = shell_exec('node --check ' . __DIR__ . '/../view/control-panel/purchase/control-panel.js 2>&1');
assertTrue(strpos($jsCheck, 'SyntaxError') === false, 'Syntaxe OK : control-panel.js');

echo PHP_EOL . 'Résultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);
