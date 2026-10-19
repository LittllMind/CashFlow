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

function statistic($selectedDate, $message)
{
    userValidation();

    $salesManager = new SalesManager();
    $articlesManager = new ArticlesManager();
    $purchasesManager = new PurchasesManager();

    // Récupérer le mois et l'année à partir de la date sélectionnée ou de la date actuelle
    $month = $selectedDate ? date('m', strtotime($selectedDate)) : date('m');
    $year = $selectedDate ? date('Y', strtotime($selectedDate)) : date('Y');

    // Obtenir les achats du mois en cours
    $monthlyPurchases = $purchasesManager->getPurchasesByMonth($month, $year)->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le total des achats du mois
    $totalPurchasesAmount = 0;
    $totalPurchases = 0;

    foreach ($monthlyPurchases as $purchase) {
        $totalPurchasesAmount += $purchase['total_amount'];
        $totalPurchases++;
    }

    // calculer la moyenne des ventes par jour ouverts

    // Autres statistiques que vous avez déjà obtenues
    $dailySales = getDailySales($salesManager, $articlesManager, $month, $year);

    $totalMonth = getTotalMonthSales($dailySales);
    $weeklyStats = getWeeklySales($salesManager, $articlesManager);
    $monthlyStats = getMonthlySales($salesManager, $articlesManager);
    $list_of_sales = $salesManager->getSalesByMonth($selectedDate);

    $totalHistoricSalesAmount = $salesManager->getTotalSalesAmount();
    $totalHistoricPurchasesAmount = $purchasesManager->getTotalPurchasesAmount();


    $openedDays = 0;
    $lastSaleDate = null; // Variable to keep track of the last processed sale date


    foreach ($list_of_sales as $sale) {
        // Access the data in the $sale array
        // Ensure created_at is treated as a DateTime object
        $createdAt = new DateTime($sale['created_at']);
        $saleDay = $sale['sale_date'];

        // Check if the sale day is different from the last processed sale day
        if ($saleDay !== $lastSaleDate) {
            $openedDays++;
            $lastSaleDate = $saleDay; // Update the last processed sale date
        }
    }

    if ($openedDays > 0) {
        $averageDay = number_format($totalMonth['total_month'] / $openedDays, 1);
        $averageSales = number_format($totalMonth['total_sales'] / $openedDays, 1);
    } else {
        $averageDay = 0;
        $averageSales = 0;
    }
    $list_of_articles = $articlesManager->getArticlesByMonth($year, $month);
    // $list_of_articles = $articlesManager->getArticles();

    $categoryCounts = [];
    foreach ($list_of_articles as $article) {
        if (!isset($categoryCounts[$article['category']])) {
            $categoryCounts[$article['category']] = 0;
        }
        $categoryCounts[$article['category']] += $article['quantity'];
    }

    // Préparer les données pour le graphique
    $category_chart = [
        'labels' => array_keys($categoryCounts),
        'data' => array_values($categoryCounts)
    ];

    $categoryAmount = [];
    foreach ($list_of_articles as $article) {
        if (!isset($categoryAmount[$article['category']])) {
            $categoryAmount[$article['category']] = 0;
        }
        $categoryAmount[$article['category']] += $article['quantity'] * $article['unit_price'];
    }

    $category_amount_chart = [
        'labels' => array_keys($categoryAmount),
        'data' => array_values($categoryAmount)
    ];

    $menSubcategoryCounts = [];
    foreach ($list_of_articles as $article) {
        if ($article['category'] === 'Hommes') {
            if (!isset($menSubcategoryCounts[$article['subcategory']])) {
                $menSubcategoryCounts[$article['subcategory']] = 0;
            }
            $menSubcategoryCounts[$article['subcategory']] += $article['quantity'];
        }
    }

    $men_subcategory_chart = [
        'labels' => array_keys($menSubcategoryCounts),
        'data' => array_values($menSubcategoryCounts)
    ];

    $menSubcategoryAmount = [];
    foreach ($list_of_articles as $article) {
        if ($article['category'] === 'Hommes') {
            if (!isset($menSubcategoryAmount[$article['subcategory']])) {
                $menSubcategoryAmount[$article['subcategory']] = 0;
            }
            $menSubcategoryAmount[$article['subcategory']] += $article['quantity'] * $article['unit_price'];
        }
    }

    $men_subcategory_amount_chart = [
        'labels' => array_keys($menSubcategoryAmount),
        'data' => array_values($menSubcategoryAmount)
    ];

    $womenSubcategoryCounts = [];
    foreach ($list_of_articles as $article) {
        if ($article['category'] === 'Femmes') {
            if (!isset($womenSubcategoryCounts[$article['subcategory']])) {
                $womenSubcategoryCounts[$article['subcategory']] = 0;
            }
            $womenSubcategoryCounts[$article['subcategory']] += $article['quantity'];
        }
    }

    $women_subcategory_chart = [
        'labels' => array_keys($womenSubcategoryCounts),
        'data' => array_values($womenSubcategoryCounts)
    ];

    $womenSubcategoryAmount = [];
    foreach ($list_of_articles as $article) {
        if ($article['category'] === 'Femmes') {
            if (!isset($womenSubcategoryAmount[$article['subcategory']])) {
                $womenSubcategoryAmount[$article['subcategory']] = 0;
            }
            $womenSubcategoryAmount[$article['subcategory']] += $article['quantity'] * $article['unit_price'];
        }
    }

    $women_subcategory_amount_chart = [
        'labels' => array_keys($womenSubcategoryAmount),
        'data' => array_values($womenSubcategoryAmount)
    ];

    $womenSubcategoryAmount = [];
    foreach ($list_of_articles as $article) {
        if ($article['category'] === 'Femmes') {
            if (!isset($womenSubcategoryAmount[$article['subcategory']])) {
                $womenSubcategoryAmount[$article['subcategory']] = 0;
            }
            $womenSubcategoryAmount[$article['subcategory']] += $article['quantity'] * $article['unit_price'];
        }
    }

    $women_subcategory_chart = [
        'labels' => array_keys($womenSubcategoryCounts),
        'data' => array_values($womenSubcategoryCounts)
    ];

    $fleaSubcategoryCounts = [];
    foreach ($list_of_articles as $article) {
        if ($article['category'] === 'Brocante') {
            if (!isset($fleaSubcategoryCounts[$article['subcategory']])) {
                $fleaSubcategoryCounts[$article['subcategory']] = 0;
            }
            $fleaSubcategoryCounts[$article['subcategory']] += $article['quantity'];
        }
    }

    $flea_subcategory_chart = [
        'labels' => array_keys($fleaSubcategoryCounts),
        'data' => array_values($fleaSubcategoryCounts)
    ];

    $fleaSubcategoryAmount = [];
    foreach ($list_of_articles as $article) {
        if ($article['category'] === 'Brocante') {
            if (!isset($fleaSubcategoryAmount[$article['subcategory']])) {
                $fleaSubcategoryAmount[$article['subcategory']] = 0;
            }
            $fleaSubcategoryAmount[$article['subcategory']] += $article['quantity'] * $article['unit_price'];
        }
    }

    $flea_subcategory_amount_chart = [
        'labels' => array_keys($fleaSubcategoryAmount),
        'data' => array_values($fleaSubcategoryAmount)
    ];

    // Passer les données à la vue
    $title = 'Statistique';
    require('view/statistic/statistic.php');
}



function getWeeklySales($salesManager, $articlesManager)
{
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

    $weeklySales = $salesManager->getSalesBetweenDates($startOfWeek, $endOfWeek)->fetchAll(PDO::FETCH_ASSOC);

    return calculateTotals($weeklySales, $articlesManager);
}

function getMonthlySales($salesManager, $articlesManager)
{
    $startOfMonth = date('Y-m-01');
    $endOfMonth = date('Y-m-t');

    $monthlySales = $salesManager->getSalesBetweenDates($startOfMonth, $endOfMonth)->fetchAll(PDO::FETCH_ASSOC);

    return calculateTotals($monthlySales, $articlesManager);
}

function calculateTotals($sales, $articlesManager)
{
    $totalAmount = 0;
    $totalSales = 0;
    $totalArticles = 0;

    foreach ($sales as $sale) {
        $totalSales++;
        $totalAmount += $sale['total_amount'];
        $totalArticles += intval($sale['article_count'] ?? 0);
    }

    return ['total_amount' => $totalAmount, 'total_sales' => $totalSales, 'total_articles' => $totalArticles];
}


function calculateTotalsSalesPurchases($sales, $purchases, $articlesManager)
{
    $totalSalesAmount = 0;
    $totalSalesCount = count($sales);
    $totalPurchasesAmount = 0;
    $totalPurchasesCount = count($purchases);
    $totalArticlesSold = 0;
    $totalArticlesPurchased = 0;

    // Calculer le total des ventes
    foreach ($sales as $sale) {
        $totalSalesAmount += $sale['total_amount'];
        $totalArticlesSold += intval($sale['article_count'] ?? 0);
    }

    // Calculer le total des achats
    foreach ($purchases as $purchase) {
        $totalPurchasesAmount += $purchase['total_amount'];
        $articles = $articlesManager->getArticlesByPurchaseId($purchase['id']);
        foreach ($articles as $article) {
            // Ajouter la quantité de chaque article acheté au total des articles achetés
            $totalArticlesPurchased += $article['quantity'];
        }
    }

    return [
        'total_sales_amount' => $totalSalesAmount,
        'total_sales_count' => $totalSalesCount,
        'total_purchases_amount' => $totalPurchasesAmount,
        'total_purchases_count' => $totalPurchasesCount,
        'total_articles_sold' => $totalArticlesSold,
        'total_articles_purchased' => $totalArticlesPurchased
    ];
}


function getDailySales($salesManager, $articlesManager, $month, $year)
{
    $results = $salesManager->getSalesByMonthGrouped($month, $year);
    $dailySales = [];
    foreach ($results as $row) {
        $dailySales[$row['sale_day']] = [
            'total_amount'  => floatval($row['total_amount']),
            'total_sales'   => intval($row['total_sales']),
            'total_articles' => intval($row['total_articles']),
        ];
    }
    $numDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    for ($day = 1; $day <= $numDays; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        if (!isset($dailySales[$date])) {
            $dailySales[$date] = ['total_amount' => 0, 'total_sales' => 0, 'total_articles' => 0];
        }
    }
    ksort($dailySales);
    return $dailySales;
}

function getYearlySales($salesManager, $articlesManager, $year)
{
    $results = $salesManager->getSalesByYearGrouped($year);
    $yearlySales = [];
    foreach ($results as $row) {
        $yearlySales[$row['sale_day']] = [
            'total_amount'  => floatval($row['total_amount']),
            'total_sales'   => intval($row['total_sales']),
            'total_articles' => intval($row['total_articles']),
        ];
    }
    $numDaysInYear = ($year % 4 === 0 && ($year % 100 !== 0 || $year % 400 === 0)) ? 366 : 365;
    for ($day = 1; $day <= $numDaysInYear; $day++) {
        $date = date('Y-m-d', strtotime(sprintf('%04d-01-01 +%d days', $year, $day - 1)));
        if (!isset($yearlySales[$date])) {
            $yearlySales[$date] = ['total_amount' => 0, 'total_sales' => 0, 'total_articles' => 0];
        }
    }
    ksort($yearlySales);
    return $yearlySales;
}



function getDailySalesPurchases($salesManager, $purchasesManager, $articlesManager, $month, $year)
{

    $numDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $dailySales = [];

    for ($day = 1; $day <= $numDays; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $sales = $salesManager->getSalesByDate($date)->fetchAll(PDO::FETCH_ASSOC);
        $purchases = $purchasesManager->getPurchasesByDate($date)->fetchAll(PDO::FETCH_ASSOC);
        $dailySales[$date] = calculateTotalsSalesPurchases($sales, $purchases, $articlesManager);
    }

    return $dailySales;
}
function getMonthlyPurchases($purchasesManager, $month, $year)
{
    $numDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $monthlyPurchases = [];

    for ($day = 1; $day <= $numDays; $day++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $purchases = $purchasesManager->getPurchasesByMonth($month)->fetchAll(PDO::FETCH_ASSOC);
        $monthlyPurchases[$date] = $purchases;
    }

    return $monthlyPurchases;
}

function getTotalMonthSales($dailySales)
{
    $totalSales = 0;
    $totalArticles = 0;
    $totalMonth = 0;
    $totalDays = 0;

    $averageDay = 0;


    foreach ($dailySales as $salesInfo) {

        $totalSales += $salesInfo['total_sales'];
        $totalArticles += $salesInfo['total_articles'];
        $totalMonth += $salesInfo['total_amount'];
    }

    return ['total_sales' => $totalSales, 'total_articles' => $totalArticles, 'total_month' => $totalMonth];
}


function getTotalMonthPurchases($purchasesManager, $month, $year)
{
    $totalAmount = 0;
    $totalPurchases = 0;

    $list_of_purchases = $purchasesManager->getPurchasesByMonth($month)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($list_of_purchases as $purchase) {
        $totalAmount += $purchase['total_amount'];
        $totalPurchases++;
    }

    return ['total_amount' => $totalAmount, 'total_purchases' => $totalPurchases];
}
