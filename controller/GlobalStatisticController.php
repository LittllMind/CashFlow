<?php

require_once('model/SalesManager.php');
require_once('model/CategoriesManager.php');
require_once('model/SubcategoriesManager.php');
require_once('model/ArticlesManager.php');
require_once('model/PurchasesManager.php');

require_once('controller/UtilsController.php');

use \OpenClassroom\Blog\Model\SalesManager;
use \OpenClassroom\Blog\Model\CategoriesManager;
use \OpenClassroom\Blog\Model\SubcategoriesManager;
use \OpenClassroom\Blog\Model\ArticlesManager;
use \OpenClassroom\Blog\Model\PurchasesManager;

function globalStatistic($selectedDate = null, $message = '')
{
    // Sécurisation
    userValidation();

    $currentYear = (int) date('Y');

    $salesManager     = new SalesManager();
    $articlesManager  = new ArticlesManager();
    $purchasesManager = new PurchasesManager();

    $selectedCategory    = (!empty($_GET['category']))    ? $_GET['category']    : '';
    $selectedSubcategory = (!empty($_GET['subcategory'])) ? $_GET['subcategory'] : '';

    // --- KPI de l'année en cours
    $salesByYear     = $salesManager->getAnnualSalesStats()->fetchAll(PDO::FETCH_ASSOC);
    $purchasesByYear = $purchasesManager->getAnnualPurchasesStats()->fetchAll(PDO::FETCH_ASSOC);

    $yearStats = ['total_sales' => 0.0, 'nb_sales' => 0, 'open_days' => 0];
    foreach ($salesByYear as $row) {
        if ((int) $row['year'] === $currentYear) {
            $yearStats['total_sales'] = (float) $row['total_sales'];
            $yearStats['nb_sales']    = (int) $row['nb_sales'];
            $yearStats['open_days']   = (int) $row['open_days'];
            break;
        }
    }

    $purchasesTotal = 0.0;
    foreach ($purchasesByYear as $row) {
        if ((int) $row['year'] === $currentYear) {
            $purchasesTotal = (float) $row['total_purchases'];
            break;
        }
    }

    $totalSales  = (float) $yearStats['total_sales'];
    $nbSales     = (int) $yearStats['nb_sales'];
    $openDays    = (int) $yearStats['open_days'];
    $avgBasket   = $nbSales > 0 ? round($totalSales / $nbSales, 2) : 0.0;
    $avgDay      = $openDays > 0 ? round($totalSales / $openDays, 2) : 0.0;
    $grossMargin = round($totalSales - $purchasesTotal, 2);
    $marginRate  = $totalSales > 0 ? round(($grossMargin / $totalSales) * 100, 1) : 0.0;

    // --- Détail par catégorie / sous-catégorie (année en cours)
    $articlesStats = $articlesManager->getCategoryStatsByYear($currentYear)->fetchAll(PDO::FETCH_ASSOC);

    $categoryQty    = [];
    $categoryAmount = [];
    $topList        = [];

    foreach ($articlesStats as $row) {
        $category    = $row['category'];
        $subcategory = $row['subcategory'];
        $qty         = (int) $row['total_qty'];
        $amount      = (float) $row['total_amount'];

        $categoryQty[$category]    = ($categoryQty[$category] ?? 0) + $qty;
        $categoryAmount[$category] = ($categoryAmount[$category] ?? 0.0) + $amount;

        $topList[] = [
            'category'    => $category,
            'subcategory' => $subcategory,
            'qty'         => $qty,
            'amount'      => $amount,
            'avg_price'   => $qty > 0 ? round($amount / $qty, 2) : 0.0,
        ];
    }

    usort($topList, function ($a, $b) {
        return $b['qty'] <=> $a['qty'];
    });
    $topSubcategories = array_slice($topList, 0, 10);

    // --- Répartition FRIPE / BROC
    $fripeCategories = ['Femmes', 'Hommes', 'Enfants', 'Accessoires'];
    $caFripe = $caBroc = 0.0;
    foreach ($categoryAmount as $cat => $amt) {
        if (in_array($cat, $fripeCategories, true)) {
            $caFripe += $amt;
        } else {
            $caBroc += $amt;
        }
    }
    $fripeShare = $totalSales > 0 ? round(($caFripe / $totalSales) * 100, 1) : 0.0;
    $brocShare  = $totalSales > 0 ? round(($caBroc  / $totalSales) * 100, 1) : 0.0;
    $purchasesFripe = $totalSales > 0 ? round($purchasesTotal * ($caFripe / $totalSales), 2) : 0.0;
    $purchasesBroc  = $totalSales > 0 ? round($purchasesTotal * ($caBroc  / $totalSales), 2) : 0.0;

    // --- Graphique d'évolution mensuelle (année en cours, filtrable)
    $rawTrend = $articlesManager->getMonthlySalesTrend($selectedCategory, $selectedSubcategory);
    $monthlyTrend = ['labels' => [], 'qty' => [], 'ca' => []];
    foreach ($rawTrend['labels'] as $idx => $label) {
        if (strpos($label, (string) $currentYear) === 0) {
            $monthlyTrend['labels'][] = $label;
            $monthlyTrend['qty'][]    = $rawTrend['qty'][$idx];
            $monthlyTrend['ca'][]     = $rawTrend['ca'][$idx];
        }
    }

    // --- Modes de paiement
    $paymentStats = $salesManager->getPaymentDistributionByYear($currentYear);

    // --- Listes catégories / sous-catégories pour les selects
    $allCategories = (new CategoriesManager())->getCategoriesWithSubcategories();

    // --- Préparation graphiques simples
    $categoryChart = [
        'labels' => array_keys($categoryAmount),
        'data'   => array_values($categoryAmount),
    ];

    $year = $currentYear;
    $title = 'Statistiques · ' . $year;
    require('view/statistic/globalStatistic.php');
}
