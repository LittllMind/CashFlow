<?php
// tests/StockPanelTest.php — Vérifie le panneau de gestion des stocks

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

// 1. Table SQL de migration
$migration = file_get_contents(__DIR__ . '/../scripts/migrate_stock_movements.php');
assertTrue(
    strpos($migration, 'CREATE TABLE IF NOT EXISTS stock_movements') !== false,
    'migration : table stock_movements definie'
);
assertTrue(
    strpos($migration, 'movement_type') !== false,
    'migration : colonne movement_type definie'
);
assertTrue(
    strpos($migration, '--rollback') !== false,
    'migration : option --rollback presente'
);

// 2. Modele StockManager
assertTrue(
    file_exists(__DIR__ . '/../model/StockManager.php'),
    'model/StockManager.php existe'
);
$stockManager = file_get_contents(__DIR__ . '/../model/StockManager.php');
assertTrue(
    strpos($stockManager, 'getStockBalances') !== false,
    'StockManager : getStockBalances presente'
);
assertTrue(
    strpos($stockManager, 'addMovement') !== false,
    'StockManager : addMovement presente'
);

// 3. Route stockPanel
$index = file_get_contents(__DIR__ . '/../index.php');
assertTrue(
    strpos($index, "case 'stockPanel'") !== false,
    'index.php : route stockPanel declaree'
);
assertTrue(
    strpos($index, "case 'stockAdjustAjax'") !== false,
    'index.php : route stockAdjustAjax declaree'
);
assertTrue(
    strpos($index, "case 'stockAddEntry'") !== false,
    'index.php : route stockAddEntry declaree'
);

// 4. Controller
$controller = file_get_contents(__DIR__ . '/../controller/SalesController.php');
assertTrue(
    strpos($controller, 'function stockPanel') !== false,
    'SalesController.php : fonction stockPanel presente'
);
assertTrue(
    strpos($controller, 'function stockAdjustAjax') !== false,
    'SalesController.php : fonction stockAdjustAjax presente'
);
assertTrue(
    strpos($controller, 'function stockAddEntry') !== false,
    'SalesController.php : fonction stockAddEntry presente'
);
assertTrue(
    strpos($controller, 'userValidation();') !== false,
    'SalesController.php : appel a userValidation present'
);
assertTrue(
    strpos($controller, "\$validTypes = ['purchase', 'sale', 'inventory', 'adjustment', 'donation'];") !== false,
    'SalesController.php : whitelist des types de mouvement presente'
);
assertTrue(
    strpos($controller, "\$userId = !empty(\$_SESSION['id']) ? (int) \$_SESSION['id'] : null;") !== false,
    'SalesController.php : user_id depuis session present'
);
assertTrue(
    strpos($controller, "echo json_encode(['success' => true, 'movement_id' => \$id]);
    exit;") !== false,
    'SalesController.php : exit apres reponse JSON de stockAdjustAjax'
);

// 5. Vue et JS
assertTrue(
    file_exists(__DIR__ . '/../view/control-panel/stock/stockPanel.php'),
    'Vue stockPanel.php existe'
);
assertTrue(
    file_exists(__DIR__ . '/../view/control-panel/stock/stockPanel.js'),
    'Vue stockPanel.js existe'
);
$js = file_get_contents(__DIR__ . '/../view/control-panel/stock/stockPanel.js');
assertTrue(
    strpos($js, 'prepareQuickEntry') !== false,
    'stockPanel.js : prepareQuickEntry present'
);
assertTrue(
    strpos($js, 'updatePriceVisibility') !== false,
    'stockPanel.js : updatePriceVisibility present'
);


// 6b. Test fonctionnel : mouvement donation à 0 €
require_once __DIR__ . '/../env.php';
$host = getenv('DATABASE_HOST') ?: 'localhost';
$db   = getenv('DATABASE_NAME') ?: 'cashflow_db';
$user = getenv('DATABASE_USER') ?: 'root';
$pass = getenv('DATABASE_PASSWORD') !== false ? getenv('DATABASE_PASSWORD') : '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS stock_movements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category VARCHAR(255) NOT NULL,
            subcategory VARCHAR(255) NOT NULL,
            movement_type ENUM('purchase','sale','inventory','adjustment','donation') NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) DEFAULT NULL,
            reference_id INT DEFAULT NULL,
            reference_type VARCHAR(50) DEFAULT NULL,
            comment TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            user_id INT DEFAULT NULL,
            KEY idx_category (category),
            KEY idx_subcategory (subcategory),
            KEY idx_movement_type (movement_type),
            KEY idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    echo '  SKIP : base de donnees indisponible pour test fonctionnel (' . $e->getMessage() . ')' . PHP_EOL;
}
require_once __DIR__ . '/../model/StockManager.php';
$sm = new OpenClassroom\Blog\Model\StockManager();
$before = $sm->getBalance('Bougies', 'Chat (3€)');
$sm->addMovement('Bougies', 'Chat (3€)', 'donation', 2, 0.00, null, null, 'test donation 0eur');
$after = $sm->getBalance('Bougies', 'Chat (3€)');
assertTrue((int)$after === ((int)$before + 2), 'StockManager : donation de 2 unités augmente le solde de 2');
$sm->addMovement('Bougies', 'Chat (3€)', 'donation', -2, 0.00, null, null, 'rollback test donation');
assertTrue((int)$sm->getBalance('Bougies', 'Chat (3€)') === (int)$before, 'StockManager : rollback test donation restaure le solde initial');

// 6. Syntaxe
$output = [];
$return = 0;
exec('php -l ' . escapeshellarg(__DIR__ . '/../model/StockManager.php') . ' 2>&1', $output, $return);
assertTrue($return === 0, 'Syntaxe OK : StockManager.php');

exec('php -l ' . escapeshellarg(__DIR__ . '/../view/control-panel/stock/stockPanel.php') . ' 2>&1', $output, $return);
assertTrue($return === 0, 'Syntaxe OK : stockPanel.php');

exec('node --check ' . escapeshellarg(__DIR__ . '/../view/control-panel/stock/stockPanel.js') . ' 2>&1', $output, $return);
assertTrue($return === 0, 'Syntaxe JS OK : stockPanel.js');

echo PHP_EOL . 'Résultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);

$css = file_get_contents(__DIR__ . '/../assets/css/sale-modern.css');
assertTrue(
    strpos($css, '.stock-panel') !== false,
    'sale-modern.css : theme stock-panel present'
);
assertTrue(
    strpos($css, '.stock-table') !== false,
    'sale-modern.css : table stock-table presente'
);
