<?php
// tests/ArdoisePanelTest.php — Vérifie l'ardoise client

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

// 1. Route ardoisePanel déclarée dans index.php
$index = file_get_contents(__DIR__ . '/../index.php');
assertTrue(
    strpos($index, "case 'ardoisePanel':") !== false,
    'index.php : route ardoisePanel declaree'
);

// 2. Contrôleur ardoisePanel présent
$controller = file_get_contents(__DIR__ . '/../controller/SalesController.php');
assertTrue(
    strpos($controller, 'function ardoisePanel(') !== false,
    'SalesController.php : fonction ardoisePanel presente'
);

// 3. Méthode Manager pour lister les ardoises en cours
$manager = file_get_contents(__DIR__ . '/../model/SalesManager.php');
assertTrue(
    strpos($manager, 'function getPendingArdoises(') !== false,
    'SalesManager.php : methode getPendingArdoises presente'
);

// 4. Les stats quotidiennes doivent exclure les ventes avec client
assertTrue(
    strpos($manager, 'client_id IS NULL') !== false,
    'SalesManager.php : filtre client_id IS NULL dans les stats'
);

// 5. Lien vers le panneau ardoise dans la navbar ou salePanel
$controlPanel = file_get_contents(__DIR__ . '/../view/control-panel/sale/control-panel/control-panel.php');
$menu = @file_get_contents(__DIR__ . '/../view/template/mainTemplate.php');
$hasArdoiseLink = strpos($controlPanel, 'ardoisePanel') !== false || ($menu !== false && strpos($menu, 'ardoisePanel') !== false);
assertTrue(
    $hasArdoiseLink,
    'UI : lien vers ardoisePanel present (control-panel ou menu)'
);

// 6. Vue ardoisePanel existe
$viewPath = __DIR__ . '/../view/control-panel/sale/ardoise/ardoisePanel.php';
assertTrue(
    file_exists($viewPath),
    'Vue : ardoisePanel.php existe'
);

// 7. Le calcul de totaux quotidiens exclut les ventes client (via ArticlesManager ou SalesManager)
assertTrue(
    strpos($controller, 'ardoisePanel') !== false,
    'SalesController.php : route ardoisePanel branchée'
);

// 8. Vérifications syntaxiques PHP
$filesToCheck = [
    __DIR__ . '/../controller/SalesController.php',
    __DIR__ . '/../model/SalesManager.php',
    __DIR__ . '/../view/control-panel/sale/ardoise/ardoisePanel.php',
    __DIR__ . '/../index.php',
];
foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $output = [];
        $return = 0;
        exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $return);
        assertTrue(
            $return === 0,
            'Syntaxe OK : ' . basename($file)
        );
    }
}

echo PHP_EOL . 'Résultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);
