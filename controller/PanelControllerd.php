<?php

require_once ('model/SalesManager.php');
require_once ('model/ArticlesManager.php');

use \OpenClassroom\Blog\Model\SalesManager;
use \OpenClassroom\Blog\Model\ArticlesManager;


function controlPanel($selectedDate, $message)
{
    userValidation();

    $title = "Panneau des ventes";

    $salesManager = new SalesManager();
    $articlesManager = new ArticlesManager();


    if (isset($user_fonction) && $user_fonction != '') {
        setcookie('fonction', $user_fonction, time() + 365 * 24 * 3600, null, null, false, true);
    } else {
        $user_fonction = $_SESSION['fonction'];
    }

    // $list_of_sales = $salesManager->getSales();

    // $list_of_sales = $salesManager->getSales()->fetchAll(PDO::FETCH_ASSOC);

    // total amount articles

    $list_of_sales = $salesManager->getSalesByDate($selectedDate);

    $total_amount = 0;
    $total_sales = 0;
    $total_articles = 0;

    while ($sale = $list_of_sales->fetch()) {
        $total_amount += $sale['total_amount'];
        $total_sales++;
        $total_articles += intval($sale['article_count'] ?? 0);
    }

    $list_of_sales->closeCursor();

    // weekly sales

    $startOfWeek = date('Y-m-d', strtotime('monday this week'));

    // Calculer la date de fin de la semaine (dimanche)
    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

    // Récupérer les ventes de la semaine en cours
    $list_of_sales_weekly = $salesManager->getSalesBetweenDates($startOfWeek, $endOfWeek)->fetchAll(PDO::FETCH_ASSOC);

    $total_amount_weekly = 0;
    $total_sales_weekly = 0;
    $total_articles_weekly = 0;


    foreach ($list_of_sales_weekly as $sale) {
        $total_amount_weekly += $sale['total_amount'];
        $total_sales_weekly++;
        $total_articles_weekly += intval($sale['article_count'] ?? 0);
    }

    // month sales

    $startOfMonth = date('Y-m-01');

    $endOfMonth = date('Y-m-t');

    $list_of_sales_monthly = $salesManager->getSalesBetweenDates($startOfMonth, $endOfMonth)->fetchAll(PDO::FETCH_ASSOC);

    $total_amount_monthly = 0;
    $total_sales_monthly = 0;
    $total_articles_monthly = 0;

    foreach ($list_of_sales_monthly as $sale) {
        $total_amount_monthly += $sale['total_amount'];
        $total_sales_monthly++;
        $total_articles_monthly += intval($sale['article_count'] ?? 0);
    }

    $list_of_sales = $salesManager->getSalesByDate($selectedDate)->fetchAll(PDO::FETCH_ASSOC);

    require ('view/control-panel/control-panel.php');
}
