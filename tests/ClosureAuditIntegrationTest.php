<?php
// tests/ClosureAuditIntegrationTest.php — teste l'audit log avec une session simulée.

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

$mgr = new ClosuresManager();
$conn = $mgr->getConnection();

// Nettoyage
$conn->query("DELETE FROM closure_audit_log WHERE closure_id IN (SELECT id FROM daily_closures WHERE closure_date='2099-12-28')");
$conn->query("DELETE FROM daily_closures WHERE closure_date='2099-12-28'");

// Crée une clôture et log audit manuellement via le manager
$closureId = $mgr->createClosure([
    'owner' => 'broc',
    'closure_date' => '2099-12-28',
    'closure_number' => 'INT-2099-000001',
    'total_sales' => 50.00,
    'total_cash' => 50.00,
    'total_card' => 0.00,
    'total_cheque' => 0.00,
    'total_items' => 1,
    'total_sales_count' => 1,
    'generated_by' => 1,
    'content_hash' => 'int_hash_A',
    'previous_closure_hash' => 'int_hash_prev_A',
]);

$mgr->logAudit($closureId, 'create', 42, '192.168.1.10', 'int_hash_prev_A', 'int_hash_A', ['closure_number' => 'INT-2099-000001']);
$mgr->logAudit($closureId, 'lock', 42, '192.168.1.10', 'int_hash_A', 'int_hash_A', ['closure_number' => 'INT-2099-000001']);

$logs = $mgr->getAuditLogs($closureId);
assertTrue(count($logs) === 2, 'Deux entrées d\'audit');
assertTrue($logs[0]['action'] === 'lock', 'Dernier log = lock');
assertTrue((int)$logs[0]['actor_user_id'] === 42, 'actor_user_id conservé');
assertTrue($logs[0]['actor_ip'] === '192.168.1.10', 'IP conservée');
assertTrue($logs[1]['action'] === 'create', 'Ancien log = create');

// Test JSON metadata
$decoded = json_decode($logs[0]['metadata'] ?? '{}', true);
assertTrue($decoded['closure_number'] === 'INT-2099-000001', 'Metadata JSON conservé');

// Cleanup
$conn->query("DELETE FROM closure_audit_log WHERE closure_id = $closureId");
$conn->query("DELETE FROM daily_closures WHERE id = $closureId");

echo PHP_EOL . "Résultat : $passed pass, $failed fail" . PHP_EOL;
exit($failed > 0 ? 1 : 0);
