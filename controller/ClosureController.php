<?php
/**
 * ClosureController.php — Gestion des clôtures quotidiennes par activité.
 *
 * Routage via index.php :
 *   closurePanel              → panneau de liste et génération
 *   generateDailyClosure      → POST : créer une clôture
 *   apiGetClosureForDate      → JSON : récupérer une clôture existante
 *   apiLockClosure            → JSON : verrouiller une clôture
 *   apiUpdateClosureNotesCash → JSON : mettre à jour comptage/notes
 */

require_once('model/ClosuresManager.php');
require_once('model/SalesManager.php');
require_once('model/PurchasesManager.php');
require_once('model/ArticlesManager.php');

use OpenClassroom\Blog\Model\ClosuresManager;
use OpenClassroom\Blog\Model\SalesManager;
use OpenClassroom\Blog\Model\PurchasesManager;
use OpenClassroom\Blog\Model\ArticlesManager;

const CLOSURE_OWNERS = [
    'broc' => 'BROC',
    'fripe'     => 'FRIPE',
];

/**
 * Vérifie l'authentification et renvoie l'utilisateur courant.
 *
 * @return array utilisateur ['id','pseudo','fonction','user_status', 'display_name']
 */
function closureRequireAuth(): array
{
    $validation = userValidation();
    if ($validation !== 'Allowed' || empty($_SESSION['id'])) {
        header('Location: index.php?action=login');
        exit;
    }
    return [
        'id'           => (int)$_SESSION['id'],
        'pseudo'       => $_SESSION['pseudo'] ?? '',
        'fonction'     => $_SESSION['fonction'] ?? '',
        'user_status'  => $_SESSION['user_status'] ?? '',
        'display_name' => $_SESSION['pseudo'] ?? 'Opérateur',
    ];
}

/**
 * Panneau principal des clôtures journalières.
 */
function closurePanel($selectedDate = null, $message = '')
{
    $user = closureRequireAuth();

    if (empty($selectedDate)) {
        $selectedDate = date('Y-m-d');
    }

    $closuresManager = new ClosuresManager();
    $closures = $closuresManager->getClosuresForDate($selectedDate);

    // Liste des journées non clôturées pour les 30 derniers jours
    $unclosedDays = $closuresManager->getUnclosedDays(
        array_keys(CLOSURE_OWNERS),
        date('Y-m-d', strtotime('-30 days')),
        date('Y-m-d')
    );

    $title = 'Clôtures journalières — ' . date('d/m/Y', strtotime($selectedDate));
    $jsFile = 'control-panel/closure/control-panel.js';
    require('view/control-panel/closure/closurePanel.php');
}

/**
 * Génère une clôture pour owner + date, idempotente.
 */
function generateDailyClosure()
{
    $user = closureRequireAuth();

    $owner = !empty($_POST['owner']) && in_array($_POST['owner'], array_keys(CLOSURE_OWNERS), true)
        ? $_POST['owner']
        : null;
    $closureDate = !empty($_POST['closure_date']) ? valid_datas($_POST['closure_date']) : date('Y-m-d');

    if (!$owner) {
        throw new Exception('Activité requise pour la clôture.');
    }

    $closuresManager = new ClosuresManager();
    $existing = $closuresManager->getClosureByOwnerAndDate($owner, $closureDate);
    if ($existing && !empty($existing['locked_at'])) {
        throw new Exception('Clôture déjà verrouillée.');
    }

    // Calculs
    $stats = computeClosureStats($owner, $closureDate);

    // Numéro de clôture : BROC-YYYY-MM-000001
    $year = date('Y', strtotime($closureDate));
    $sequence = $closuresManager->getNextClosureNumber($owner, $year);
    $closureNumber = sprintf('%s-%s-%06d', strtoupper($owner === 'broc' ? 'BROC' : 'FRIPE'), $year, $sequence);

    // Chaîne de hash : hash du contenu + hash de la clôture précédente
    $previous = $closuresManager->getLastLockedClosure($owner);
    $previousHash = $previous ? ($previous['content_hash'] ?? '') : '';

    $contentHash = hash('sha256', json_encode([
        $owner,
        $closureDate,
        $closureNumber,
        $stats['total_sales'],
        $stats['total_cash'],
        $stats['total_card'],
        $stats['total_cheque'],
        $stats['total_items'],
        $stats['total_sales_count'],
        $stats['total_purchases'] ?? 0,
        $stats['total_purchases_count'] ?? 0,
        $previousHash,
        time(),
    ]));

    $data = array_merge($stats, [
        'owner'               => $owner,
        'closure_date'        => $closureDate,
        'closure_number'      => $closureNumber,
        'generated_by'        => $user['id'],
        'content_hash'        => $contentHash,
        'previous_closure_hash' => $previousHash,
    ]);

    $actorIp = getClientIp();
    if ($existing) {
        $closuresManager->updateClosure($existing['id'], $data);
        $closureId = $existing['id'];
        $closuresManager->logAudit($closureId, 'update', (int)$user['id'], $actorIp, $previousHash, $contentHash, ['closure_number' => $closureNumber]);
    } else {
        $closureId = $closuresManager->createClosure($data);
        $closuresManager->logAudit($closureId, 'create', (int)$user['id'], $actorIp, $previousHash, $contentHash, ['closure_number' => $closureNumber]);
    }

    header('Location: index.php?action=closurePanel&date=' . $closureDate . '&message=Cl%C3%B4ture%20' . urlencode($closureNumber) . '%20g%C3%A9n%C3%A9r%C3%A9e');
}

/**
 * Récupère les stats de base pour une clôture.
 */
function computeClosureStats(string $owner, string $closureDate): array
{
    $salesManager = new SalesManager();
    $purchasesManager = new PurchasesManager();
    $articlesManager = new ArticlesManager();

    // Classification FRIPE/BROC via colonne activity des articles
    $activityFilter = $owner === 'broc' ? 'BROC' : 'FRIPE';

    $sales = $salesManager->getSalesForClosure($closureDate, $activityFilter);
    $purchases = $purchasesManager->getPurchasesForClosure($closureDate);

    $totals = [
        'first_sale_id'         => null,
        'last_sale_id'          => null,
        'first_purchase_id'     => null,
        'last_purchase_id'      => null,
        'total_sales'           => 0,
        'total_cash'            => 0,
        'total_card'            => 0,
        'total_cheque'          => 0,
        'total_bank_transfer'   => 0,
        'total_items'           => 0,
        'total_sales_count'     => count($sales),
        'total_purchases'       => 0,
        'total_purchases_count' => count($purchases),
        'total_refunds'         => 0,
        'total_cancellations'   => 0,
        'total_corrections'     => 0,
        'opening_cash_float'    => null,
        'expected_cash'         => null,
        'counted_cash'          => null,
        'cash_difference'       => null,
        'notes'                 => '',
        'pdf_path'              => null,
        'csv_path'              => null,
    ];

    if (empty($sales)) {
        return $totals;
    }

    $ids = array_column($sales, 'id');
    $totals['first_sale_id'] = min($ids);
    $totals['last_sale_id']  = max($ids);

    foreach ($sales as $sale) {
        $amount = floatval($sale['total_amount']);
        $totals['total_sales'] += $amount;

        switch ($sale['payment_method']) {
            case 'cash':
                $totals['total_cash'] += $amount;
                break;
            case 'card':
                $totals['total_card'] += $amount;
                break;
            case 'check':
                $totals['total_cheque'] += $amount;
                break;
            case 'transfer':
                $totals['total_bank_transfer'] += $amount;
                break;
        }
    }

    $articles = $articlesManager->getArticlesBySaleIds($ids);
    $totals['total_items'] = array_sum(array_column($articles, 'quantity'));

    // Récap des achats du jour
    if (!empty($purchases)) {
        $purchaseIds = array_column($purchases, 'id');
        $totals['first_purchase_id'] = min($purchaseIds);
        $totals['last_purchase_id']  = max($purchaseIds);

        foreach ($purchases as $purchase) {
            $totals['total_purchases'] += floatval($purchase['total_amount']);
        }
    }

    return $totals;
}

/**
 * API JSON : récupère la clôture d'une journée/owner.
 */
function apiGetClosureForDate()
{
    header('Content-Type: application/json');
    closureRequireAuth();

    $owner = $_GET['owner'] ?? '';
    $closureDate = $_GET['closure_date'] ?? date('Y-m-d');

    if (!in_array($owner, array_keys(CLOSURE_OWNERS), true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Activité invalide']);
        exit;
    }

    $mgr = new ClosuresManager();
    $closure = $mgr->getClosureByOwnerAndDate($owner, $closureDate);

    echo json_encode([
        'success' => (bool)$closure,
        'closure' => $closure,
        'label'   => CLOSURE_OWNERS[$owner] ?? '',
    ]);
    exit;
}

/**
 * API JSON : verrouille une clôture.
 */
function apiLockClosure()
{
    header('Content-Type: application/json');
    $user = closureRequireAuth();

    $owner = $_POST['owner'] ?? '';
    $closureDate = $_POST['closure_date'] ?? '';

    if (!$owner || !$closureDate) {
        http_response_code(400);
        echo json_encode(['error' => 'Paramètres manquants']);
        exit;
    }

    $mgr = new ClosuresManager();
    $closure = $mgr->getClosureByOwnerAndDate($owner, $closureDate);
    if (!$closure) {
        http_response_code(404);
        echo json_encode(['error' => 'Clôture introuvable']);
        exit;
    }

    $previousHash = $closure['content_hash'] ?? null;
    $mgr->lockClosure($closure['id']);
    $actorIp = getClientIp();
    $mgr->logAudit($closure['id'], 'lock', (int)$user['id'], $actorIp, $previousHash, $previousHash, ['closure_number' => $closure['closure_number']]);
    echo json_encode(['success' => true]);
    exit;
}

/**
 * API JSON : met à jour comptage caisse, fond de caisse, notes, archive papier.
 */
function apiUpdateClosureNotesCash()
{
    header('Content-Type: application/json');
    $user = closureRequireAuth();

    $owner = $_POST['owner'] ?? '';
    $closureDate = $_POST['closure_date'] ?? '';

    $fields = [
        'opening_cash_float'    => 'float',
        'counted_cash'          => 'float',
        'notes'                 => 'string',
        'signed_paper_archived' => 'bool',
    ];

    $data = [];
    foreach ($fields as $field => $type) {
        if (!isset($_POST[$field])) {
            continue;
        }
        $value = $_POST[$field];
        if ($type === 'float') {
            $data[$field] = is_numeric($value) ? floatval($value) : null;
        } elseif ($type === 'bool') {
            $data[$field] = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        } else {
            $data[$field] = valid_datas($value);
        }
    }

    // Recalcule expected_cash si le fond est renseigné
    if (isset($data['counted_cash']) && isset($data['opening_cash_float'])) {
        $mgr = new ClosuresManager();
        $closure = $mgr->getClosureByOwnerAndDate($owner, $closureDate);
        if ($closure) {
            $data['expected_cash'] = floatval($closure['total_cash']) + floatval($data['opening_cash_float']);
            $data['cash_difference'] = floatval($data['counted_cash']) - $data['expected_cash'];
        }
    }

    $mgr = new ClosuresManager();
    $closure = $mgr->getClosureByOwnerAndDate($owner, $closureDate);
    if (!$closure) {
        http_response_code(404);
        echo json_encode(['error' => 'Clôture introuvable']);
        exit;
    }

    if (!empty($closure['locked_at'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Clôture verrouillée']);
        exit;
    }

    $previousHash = $closure['content_hash'] ?? null;
    $mgr->updateClosure($closure['id'], $data);

    $actorIp = getClientIp();
    $updatedClosure = $mgr->getClosureById($closure['id']);
    $newHash = $updatedClosure['content_hash'] ?? null;
    $mgr->logAudit(
        $closure['id'],
        'update',
        (int)$user['id'],
        $actorIp,
        $previousHash,
        $newHash,
        ['closure_number' => $closure['closure_number'], 'fields' => array_keys($data)]
    );

    echo json_encode(['success' => true, 'closure' => $updatedClosure]);
    exit;
}

/**
 * API JSON : log d'un export PDF ou CSV effectué côté client.
 */
function apiLogClosureExport()
{
    header('Content-Type: application/json');
    $user = closureRequireAuth();

    $owner = $_POST['owner'] ?? '';
    $closureDate = $_POST['closure_date'] ?? '';
    $type = $_POST['type'] ?? '';

    if (!in_array($owner, array_keys(CLOSURE_OWNERS), true) || !in_array($type, ['pdf', 'csv'], true) || !$closureDate) {
        http_response_code(400);
        echo json_encode(['error' => 'Paramètres invalides']);
        exit;
    }

    $mgr = new ClosuresManager();
    $closure = $mgr->getClosureByOwnerAndDate($owner, $closureDate);
    if (!$closure) {
        http_response_code(404);
        echo json_encode(['error' => 'Clôture introuvable']);
        exit;
    }

    $action = $type === 'pdf' ? 'export_pdf' : 'export_csv';
    $actorIp = getClientIp();
    $hash = $closure['content_hash'] ?? null;
    $mgr->logAudit(
        $closure['id'],
        $action,
        (int)$user['id'],
        $actorIp,
        $hash,
        $hash,
        ['closure_number' => $closure['closure_number'], 'file' => $_POST['file'] ?? null]
    );
    echo json_encode(['success' => true]);
    exit;
}

/**
 * API JSON : retourne l'historique d'audit d'une clôture.
 */
function apiGetClosureAudit()
{
    header('Content-Type: application/json');
    closureRequireAuth();

    $owner = $_GET['owner'] ?? '';
    $closureDate = $_GET['closure_date'] ?? '';

    if (!in_array($owner, array_keys(CLOSURE_OWNERS), true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Activité invalide']);
        exit;
    }

    $mgr = new ClosuresManager();
    $closure = $mgr->getClosureByOwnerAndDate($owner, $closureDate);
    if (!$closure) {
        http_response_code(404);
        echo json_encode(['error' => 'Clôture introuvable']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'closure_id' => $closure['id'],
        'closure_number' => $closure['closure_number'],
        'content_hash' => $closure['content_hash'],
        'logs' => $mgr->getAuditLogs($closure['id']),
    ]);
    exit;
}
