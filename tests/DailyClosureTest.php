<?php
// tests/DailyClosureTest.php — Validation statique + fonctionnelle du module clôtures.

require_once __DIR__ . '/../model/BddManager.php';
require_once __DIR__ . '/../model/ClosuresManager.php';

use OpenClassroom\Blog\Model\ClosuresManager;

$passed = 0;
$failed = 0;

function assertTrue($cond, $msg) {
    global $passed, $failed;
    if ($cond) {
        $passed++;
        echo "  PASS : $msg" . PHP_EOL;
    } else {
        $failed++;
        echo "  FAIL : $msg" . PHP_EOL;
    }
}

$base = realpath(__DIR__ . '/..');

// 1. Fichiers créés
assertTrue(file_exists("$base/controller/ClosureController.php"), 'ClosureController.php existe');
assertTrue(file_exists("$base/model/ClosuresManager.php"), 'ClosuresManager.php existe');
assertTrue(file_exists("$base/view/control-panel/closure/closurePanel.php"), 'closurePanel.php existe');
assertTrue(file_exists("$base/view/control-panel/closure/control-panel.js"), 'control-panel.js (closure) existe');
assertTrue(file_exists("$base/migrations/2026-06-24_daily_closures.sql"), 'Migration daily_closures SQL existe');
assertTrue(file_exists("$base/migrations/2026-06-24_daily_closures.rollback.sql"), 'Rollback daily_closures SQL existe');

// 2. Syntaxe PHP (les fichiers du dossier controller/model/view racine)
foreach (['controller/ClosureController.php', 'model/ClosuresManager.php', 'view/control-panel/closure/closurePanel.php', 'index.php', 'view/template/mainTemplate.php'] as $f) {
    $output = [];
    $return = 0;
    exec('php -l ' . escapeshellarg("$base/$f"), $output, $return);
    assertTrue($return === 0, "Syntaxe OK : $f");
}

// 3. index.php inclut ClosureController et routes
$index = file_get_contents("$base/index.php");
assertTrue(strpos($index, "require('controller/ClosureController.php')") !== false, 'index.php require ClosureController');
assertTrue(strpos($index, "case 'closurePanel':") !== false, 'index.php route closurePanel');
assertTrue(strpos($index, "case 'generateDailyClosure':") !== false, 'index.php route generateDailyClosure');
assertTrue(strpos($index, "case 'apiLockClosure':") !== false, 'index.php route apiLockClosure');
assertTrue(strpos($index, "case 'apiUpdateClosureNotesCash':") !== false, 'index.php route apiUpdateClosureNotesCash');

// 4. Navbar ajoutée
$tpl = file_get_contents("$base/view/template/mainTemplate.php");
assertTrue(strpos($tpl, "index.php?action=closurePanel") !== false, 'mainTemplate navbar Clôtures');
assertTrue(strpos($tpl, 'closurePanel') !== false, 'mainTemplate sale-modern action liste inclut closurePanel');

// 5. ClosureController sécurité
$ctrl = file_get_contents("$base/controller/ClosureController.php");
assertTrue(strpos($ctrl, 'userValidation()') !== false, 'ClosureController appelle userValidation()');
assertTrue(strpos($ctrl, "header('Content-Type: application/json')") !== false, 'ClosureController API retourne JSON');
assertTrue(strpos($ctrl, 'exit;') !== false, 'ClosureController API termine par exit');
assertTrue(strpos($ctrl, 'CLOSURE_OWNERS') !== false, 'ClosureController whitelist owner');
assertTrue(strpos($ctrl, 'hash(') !== false, 'ClosureController calcule un hash');
assertTrue(strpos($ctrl, 'previous_closure_hash') !== false, 'ClosureController chaine de hash');

// 6. JavaScript syntaxe
$js = file_get_contents("$base/view/control-panel/closure/control-panel.js");
assertTrue(strpos($js, 'window.jspdf') !== false, 'closure JS référence jsPDF');
assertTrue(strpos($js, '.save(') !== false, 'closure JS télécharge PDF/CSV');
assertTrue(strpos($js, 'apiLockClosure') !== false, 'closure JS verrouillage appel');
assertTrue(strpos($js, 'apiUpdateClosureNotesCash') !== false, 'closure JS update notes/cash appel');

// 7. Table daily_closures existe et a la bonne structure
$mgr = new ClosuresManager();
$conn = $mgr->getConnection();
$stmt = $conn->query("SHOW TABLES LIKE 'daily_closures'");
assertTrue($stmt->fetch() !== false, 'Table daily_closures existe en base');

// 8. Test CRUD minimal via manager (cleanup après)
$testData = [
    'owner' => 'broc',
    'closure_date' => '2099-12-31',
    'closure_number' => 'TEST-2099-000001',
    'total_sales' => 123.45,
    'total_cash'  => 100.00,
    'total_card'  => 20.00,
    'total_cheque' => 3.45,
    'total_items' => 5,
    'total_sales_count' => 2,
    'generated_by' => 1,
    'content_hash' => 'sha256test0123456789',
];
$id = $mgr->createClosure($testData);
assertTrue($id > 0, 'ClosuresManager::createClosure retourne un id');

$fetched = $mgr->getClosureById($id);
assertTrue($fetched !== null, 'ClosuresManager::getClosureById retrouve la clôture');
assertTrue($fetched['closure_number'] === 'TEST-2099-000001', 'Numéro de clôture conservé');
assertTrue(floatval($fetched['total_sales']) === 123.45, 'Montant total conservé');

$updated = $mgr->updateClosure($id, ['counted_cash' => 99.99, 'notes' => 'Test note']);
assertTrue($updated, 'ClosuresManager::updateClosure OK');
$fetched2 = $mgr->getClosureById($id);
assertTrue(floatval($fetched2['counted_cash']) === 99.99, 'Comptage caisse mis à jour');
assertTrue($fetched2['notes'] === 'Test note', 'Notes mises à jour');

$locked = $mgr->lockClosure($id);
assertTrue($locked, 'ClosuresManager::lockClosure OK');
$fetched3 = $mgr->getClosureById($id);
assertTrue(!empty($fetched3['locked_at']), 'locked_at renseigné après lock');

// cleanup
$conn->query("DELETE FROM daily_closures WHERE id = $id");

// 9. Unclosed days ne plante pas si pas de ventes récentes
$unclosed = $mgr->getUnclosedDays(['broc', 'fripe'], '2099-01-01', '2099-01-02');
assertTrue(is_array($unclosed), 'getUnclosedDays retourne un tableau');

// 10. getNextClosureNumber séquentiel
$seq1 = $mgr->getNextClosureNumber('broc', '2099');
$seq2 = $mgr->getNextClosureNumber('fripe', '2099');
assertTrue($seq1 >= 1 && $seq2 >= 1, 'Numérotation séquentielle OK');

// 11. Vue contient tokens attendus
$view = file_get_contents("$base/view/control-panel/closure/closurePanel.php");
assertTrue(strpos($view, 'Verrouillée') !== false, 'Vue mention Verrouillée');
assertTrue(strpos($view, 'Papier imprimé et signé') !== false, 'Vue case papier signé');
assertTrue(strpos($view, 'Écart caisse') !== false, 'Vue écart caisse');
assertTrue(strpos($view, 'export-pdf') !== false, 'Vue export PDF');
assertTrue(strpos($view, 'export-csv') !== false, 'Vue export CSV');
assertTrue(strpos($view, 'lock-closure') !== false, 'Vue bouton lock');

// 12. Migration file SQL structure valid
$mig = file_get_contents("$base/migrations/2026-06-24_daily_closures.sql");
assertTrue(strpos($mig, 'daily_closures') !== false, 'Migration crée daily_closures');
assertTrue(strpos($mig, 'owner') !== false, 'Migration colonne owner');
assertTrue(strpos($mig, 'content_hash') !== false, 'Migration colonne content_hash');
assertTrue(strpos($mig, 'previous_closure_hash') !== false, 'Migration colonne previous_closure_hash');
assertTrue(strpos($mig, 'UNIQUE KEY `uniq_owner_date`') !== false, 'Migration unique owner/date');

$rollback = file_get_contents("$base/migrations/2026-06-24_daily_closures.rollback.sql");
assertTrue(strpos($rollback, 'DROP TABLE IF EXISTS `daily_closures`') !== false, 'Rollback supprime daily_closures');

// 13. Hash commercial doit rester stable après update administratif (anti-détournement)
$hashClosure = [
    'owner' => 'broc',
    'closure_date' => '2099-12-30',
    'closure_number' => 'HASH-2099-000001',
    'total_sales' => 100.00,
    'total_cash' => 80.00,
    'total_card' => 20.00,
    'total_cheque' => 0.00,
    'total_items' => 3,
    'total_sales_count' => 2,
    'total_purchases' => 10.00,
    'total_purchases_count' => 1,
    'generated_by' => 1,
    'content_hash' => 'abcdef0123456789',
    'previous_closure_hash' => 'zyx9876543210fedcba',
];
$hashId = $mgr->createClosure($hashClosure);
assertTrue($hashId > 0, 'Hash stability test: createClosure');

$mgr->updateClosure($hashId, ['counted_cash' => 78.50, 'notes' => 'Déplacement billets']);
$hashAfter = $mgr->getClosureById($hashId);
assertTrue($hashAfter['content_hash'] === 'abcdef0123456789', 'content_hash reste inchangé après update admin');
assertTrue($hashAfter['previous_closure_hash'] === 'zyx9876543210fedcba', 'previous_closure_hash reste inchangé après update admin');

// 14. updateClosure doit ignorer / rejeter une tentative de réécriture des hashs et du verrou
$mgr->updateClosure($hashId, [
    'content_hash' => 'HACKED_HASH',
    'previous_closure_hash' => 'HACKED_PREV',
    'locked_at' => '2099-12-31 23:59:59',
    'locked_at' => '2099-12-31 23:59:59',
]);
$hashAfter2 = $mgr->getClosureById($hashId);
assertTrue($hashAfter2['content_hash'] === 'abcdef0123456789', 'content_hash protégé contre injection');
assertTrue($hashAfter2['previous_closure_hash'] === 'zyx9876543210fedcba', 'previous_closure_hash protégé contre injection');
assertTrue(empty($hashAfter2['locked_at']), 'locked_at protégé contre injection via updateClosure');

// 15. Audit log
$auditId = $mgr->createClosure([
    'owner' => 'broc',
    'closure_date' => '2099-12-29',
    'closure_number' => 'AUDIT-2099-000001',
    'total_sales' => 10.00,
    'total_cash' => 10.00,
    'total_card' => 0.00,
    'total_cheque' => 0.00,
    'total_items' => 1,
    'total_sales_count' => 1,
    'generated_by' => 1,
    'content_hash' => 'audit_hash_content',
    'previous_closure_hash' => 'audit_hash_prev',
]);
$logOk = $mgr->logAudit($auditId, 'create', 1, '127.0.0.1', 'audit_hash_prev', 'audit_hash_content', ['closure_number' => 'AUDIT-2099-000001', 'note' => 'test']);
assertTrue($logOk, 'ClosuresManager::logAudit OK');
$logs = $mgr->getAuditLogs($auditId);
assertTrue(count($logs) === 1, 'getAuditLogs retourne un log');
assertTrue($logs[0]['action'] === 'create', 'Action du log = create');
assertTrue((int)$logs[0]['actor_user_id'] === 1, 'actor_user_id conservé');
assertTrue($logs[0]['actor_ip'] === '127.0.0.1', 'IP conservée');

// Test JSON metadata
$decoded = json_decode($logs[0]['metadata'] ?? '{}', true);
assertTrue($decoded['closure_number'] === 'AUDIT-2099-000001', 'Metadata JSON conservé');

$conn->query("DELETE FROM closure_audit_log WHERE closure_id = $auditId");
$conn->query("DELETE FROM daily_closures WHERE id = $auditId");

// 16. index.php et ClosureController exposent bien les nouveaux endpoints audit
$idx = file_get_contents("$base/index.php");
assertTrue(strpos($idx, "case 'apiLogClosureExport':") !== false, 'index.php route apiLogClosureExport');
assertTrue(strpos($idx, "case 'apiGetClosureAudit':") !== false, 'index.php route apiGetClosureAudit');
assertTrue(strpos($ctrl, 'function apiLogClosureExport') !== false, 'ClosureController fonction apiLogClosureExport');
assertTrue(strpos($ctrl, 'function apiGetClosureAudit') !== false, 'ClosureController fonction apiGetClosureAudit');
assertTrue(strpos($ctrl, 'logAudit(') !== false, 'ClosureController appelle logAudit');

// 17. getClientIp existe dans MainController
$mainCtrl = file_get_contents("$base/controller/MainController.php");
assertTrue(strpos($mainCtrl, 'function getClientIp()') !== false, 'MainController expose getClientIp');

// 18. Modèle ClosuresManager
$model = file_get_contents("$base/model/ClosuresManager.php");
assertTrue(strpos($model, 'function logAudit') !== false, 'ClosuresManager logAudit');
assertTrue(strpos($model, 'function getAuditLogs') !== false, 'ClosuresManager getAuditLogs');
assertTrue(strpos($model, 'function getMonthlyClosures') !== false, 'ClosuresManager getMonthlyClosures');

// 19. Broc Mois : vue et controller
assertTrue(file_exists("$base/view/result/monthBrocResult.php"), 'Vue monthBrocResult.php existe');
assertTrue(file_exists("$base/assets/js/result/monthBrocResult.js"), 'JS monthBrocResult.js existe');
$rc = file_get_contents("$base/controller/ResultController.php");
assertTrue(strpos($rc, 'function monthBrocResult') !== false, 'ResultController monthBrocResult existe');
assertTrue(strpos($rc, 'getMonthlyClosures') !== false, 'ResultController appelle getMonthlyClosures');
assertTrue(strpos($rc, 'getUnclosedDays') !== false, 'ResultController récupère les jours non clôturés');
assertTrue(strpos($rc, 'monthBrocResult.php') !== false, 'ResultController charge monthBrocResult.php');

// 20. Agrégation mensuelle via ClosuresManager
$july = $mgr->getMonthlyClosures('broc', '2026-07-01');
assertTrue(is_array($july), 'getMonthlyClosures retourne un tableau');
assertTrue(isset($july['closures']), 'getMonthlyClosures a une clé closures');
assertTrue(isset($july['totals']), 'getMonthlyClosures a une clé totals');
assertTrue(isset($july['days_with_closures']), 'getMonthlyClosures a une clé days_with_closures');
assertTrue(isset($july['days_locked']), 'getMonthlyClosures a une clé days_locked');

$totals = $july['totals'];
$expectedNet = (float)$totals['sales'] - (float)$totals['purchases'];
assertTrue(abs((float)$totals['net'] - $expectedNet) < 0.001, 'Solde net = ventes - achats');

// 21. Fripe Mois : vue et controller
assertTrue(file_exists("$base/view/result/monthFripeResult.php"), 'Vue monthFripeResult.php existe');
assertTrue(file_exists("$base/assets/js/result/monthFripeResult.js"), 'JS monthFripeResult.js existe');
assertTrue(strpos($rc, 'function monthFripeResult') !== false, 'ResultController monthFripeResult existe');
assertTrue(strpos($rc, 'monthFripeResult.php') !== false, 'ResultController charge monthFripeResult.php');
assertTrue(strpos($rc, "getMonthlyClosures(\$owner") !== false, 'ResultController appelle getMonthlyClosures avec owner FRIPE');

$conn->query("DELETE FROM daily_closures WHERE closure_date LIKE '2099-%' OR closure_number LIKE '%-2099-%'");

echo PHP_EOL . "Résultat : $passed pass, $failed fail" . PHP_EOL;
exit($failed > 0 ? 1 : 0);