<?php
/**
 * UrssafController.php — Panneau de declaration URSSAF par activite.
 * Architecture onglets :
 *   Niveau 1 : activite (FRIPE / BROC)
 *   Niveau 2 : periodicite (MENSUEL / TRIMESTRIEL / ANNUEL)
 */

require_once('model/UrssafManager.php');

use OpenClassroom\Blog\Model\UrssafManager;

const URSSAF_LABELS = [
    'broc'  => 'Brocante (Aurélien)',
    'fripe' => 'Friperie (Fripe)',
];

const MONTHS_FR = [
    1  => 'janvier', 2  => 'février', 3  => 'mars', 4  => 'avril',
    5  => 'mai', 6  => 'juin', 7  => 'juillet', 8  => 'août',
    9  => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
];

/** Panneau principal URSSAF. */
function urssafPanel($selectedDate = null, $message = '')
{
    if (!isset($_SESSION['user_status']) || $_SESSION['user_status'] == 'RGPD') {
        header('Location: index.php?action=home');
        exit;
    }

    $mode = $_GET['mode'] ?? '';
    $userFunction = $_SESSION['fonction'] ?? '';

    // Détection automatique activité si pas fourni
    if (empty($mode)) {
        if (stripos($userFunction, 'fripe') !== false || stripos($userFunction, 'fripe') !== false) {
            $mode = 'fripe';
        } else {
            $mode = 'broc';
        }
    }

    $owner    = ($mode === 'fripe') ? 'fripe'     : 'broc';
    $activity = ($mode === 'fripe') ? 'fripe'    : 'broc';

    // Périodité : mensuel | trimestriel | annuel
    $validPeriods = ['mensuel', 'trimestriel', 'annuel'];
    $period = $_GET['period'] ?? '';
    // Défaut par activité
    if (!in_array($period, $validPeriods, true)) {
        $period = ($mode === 'fripe') ? 'trimestriel' : 'mensuel';
    }

    $year = (isset($_GET['year'])  && preg_match('/^\d{4}$/', $_GET['year']))  ? (int) $_GET['year']  : (int) date('Y');
    $month = (isset($_GET['month']) && preg_match('/^\d{1,2}$/', $_GET['month'])) ? (int) $_GET['month'] : (int) date('n');
    $quarter = (isset($_GET['quarter']) && $_GET['quarter'] >= 1 && $_GET['quarter'] <= 4) ? (int) $_GET['quarter'] : (int) ceil((int) date('n') / 3);

    $urssafManager = new UrssafManager();

    // --- Calcul selon la périodité ---
    if ($period === 'mensuel') {
        $selectedDateStr = sprintf('%04d-%02d-01', $year, $month);
        $periodStart = $selectedDateStr;
        $periodEnd   = date('Y-m-t', strtotime($selectedDateStr));
        $periodLabel = MONTHS_FR[$month] . ' ' . $year;

        $navPrevMonth = (int) date('n', strtotime('-1 month', strtotime($selectedDateStr)));
        $navPrevYear  = (int) date('Y', strtotime('-1 month', strtotime($selectedDateStr)));
        $navNextMonth = (int) date('n', strtotime('+1 month', strtotime($selectedDateStr)));
        $navNextYear  = (int) date('Y', strtotime('+1 month', strtotime($selectedDateStr)));

        $data = $urssafManager->getMonthlyCa($owner, $selectedDateStr);
    } elseif ($period === 'trimestriel') {
        $startMonth  = ($quarter - 1) * 3 + 1;
        $selectedDateStr = sprintf('%04d-%02d-01', $year, $startMonth);
        $periodStart = $selectedDateStr;
        $periodEnd   = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $quarter * 3)));
        $periodLabel = 'T' . $quarter . ' ' . $year;

        $navPrevQuarter = ($quarter === 1) ? 4 : $quarter - 1;
        $navPrevYear    = ($quarter === 1) ? $year - 1 : $year;
        $navNextQuarter = ($quarter === 4) ? 1 : $quarter + 1;
        $navNextYear    = ($quarter === 4) ? $year + 1 : $year;

        $data = $urssafManager->getQuarterlyCa($owner, $selectedDateStr);
    } else { // annuel
        $selectedDateStr = sprintf('%04d-06-15', $year); // date interne
        $periodStart = sprintf('%04d-01-01', $year);
        $periodEnd   = sprintf('%04d-12-31', $year);
        $periodLabel = $year;

        $navPrevYear = $year - 1;
        $navNextYear = $year + 1;

        $data = $urssafManager->getAnnualCa($owner, $selectedDateStr);
    }

    $isDeclared = $urssafManager->isPeriodDeclared($activity, $periodStart, $periodEnd);
    $declarationInfo = $isDeclared ? $urssafManager->getDeclarationInfo($activity, $periodStart, $periodEnd) : null;
    $daysWithSales = $urssafManager->getDaysWithSales($owner, $periodStart, $periodEnd);

    $title = 'URSSAF — ' . URSSAF_LABELS[$mode] . ' — ' . ucfirst($period) . ' — ' . $periodLabel;

    require('view/urssaf/urssafPanel.php');
}

/** AJAX : marquer une période comme déclarée. */
function apiUrssafDeclare()
{
    header('Content-Type: application/json');

    if (empty($_SESSION['id'])) {
        echo json_encode(['success' => false, 'error' => 'Session expirée']);
        exit;
    }

    $mode  = valid_datas($_POST['mode'] ?? 'broc');
    $activity = ($mode === 'fripe') ? 'fripe' : 'broc';

    $periodStart = valid_datas($_POST['period_start'] ?? '');
    $periodEnd   = valid_datas($_POST['period_end']   ?? '');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodStart) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodEnd)) {
        echo json_encode(['success' => false, 'error' => 'Dates invalides']);
        exit;
    }

    $urssafManager = new UrssafManager();

    if ($urssafManager->isPeriodDeclared($activity, $periodStart, $periodEnd)) {
        echo json_encode(['success' => false, 'error' => 'Période déjà déclarée']);
        exit;
    }

    $data = [
        'total_ca'            => floatval($_POST['total_ca'] ?? 0),
        'total_cash'          => floatval($_POST['total_cash'] ?? 0),
        'total_card'          => floatval($_POST['total_card'] ?? 0),
        'total_cheque'        => floatval($_POST['total_cheque'] ?? 0),
        'total_bank_transfer' => floatval($_POST['total_bank_transfer'] ?? 0),
        'nb_sales'            => (int) ($_POST['nb_sales'] ?? 0),
        'nb_articles'         => (int) ($_POST['nb_articles'] ?? 0),
        'declared_by'         => $_SESSION['pseudo'] ?? 'système',
    ];

    $id = $urssafManager->markAsDeclared($activity, $periodStart, $periodEnd, $data);
    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}
