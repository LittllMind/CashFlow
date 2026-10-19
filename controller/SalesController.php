<?php
require_once('model/SalesManager.php');
require_once('model/CategoriesManager.php');
require_once('model/SubcategoriesManager.php');
require_once('model/ArticlesManager.php');
require_once('model/MessageManager.php');
require_once('controller/MainController.php');
require_once('model/ClientsManager.php');

require_once('controller/UtilsController.php');

use \OpenClassroom\Blog\Model\SalesManager;
use \OpenClassroom\Blog\Model\CategoriesManager;
use \OpenClassroom\Blog\Model\SubcategoriesManager;
use \OpenClassroom\Blog\Model\ArticlesManager;
use \OpenClassroom\Blog\Model\MessageManager;
use \OpenClassroom\Blog\Model\ClientsManager;


function salePanel($selectedDate, $message = '')
{
    // Validation de l'utilisateur via session

    // Validation de la date
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate) || !strtotime($selectedDate)) {
        $selectedDate = date('Y-m-d');
    }

    $title = "Panneau des ventes";

    $salesManager = new SalesManager();
    $articlesManager = new ArticlesManager();
    $messageManager = new MessageManager();

    // Gestion des cookies pour la fonction utilisateur
    $userFunction = $_SESSION['fonction'] ?? $_SESSION['user_function'] ?? '';
    if ($userFunction) {
        setcookie('fonction', $userFunction, [
            'expires' => time() + 365 * 24 * 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    // Calcul des totaux pour différentes périodes
    $dailyTotals = calculateTotal($salesManager->getSalesByDate($selectedDate), $articlesManager);
    $weeklyTotals = calculateTotal($salesManager->getSalesBetweenDates(
        date('Y-m-d', strtotime('monday this week')),
        date('Y-m-d', strtotime('sunday this week'))
    ), $articlesManager);
    $monthlyTotals = calculateTotal($salesManager->getSalesBetweenDates(
        date('Y-m-01'),
        date('Y-m-t')
    ), $articlesManager);

    // Tendance J-1 (comparaison vs veille)
    $yesterday = date('Y-m-d', strtotime($selectedDate . ' -1 day'));
    $yesterdayTotals = calculateTotal($salesManager->getSalesByDate($yesterday), $articlesManager);
    $trend_j1 = null;
    if ($yesterdayTotals['total_amount'] > 0) {
        $diff = $dailyTotals['total_amount'] - $yesterdayTotals['total_amount'];
        $pct = round(($diff / $yesterdayTotals['total_amount']) * 100, 1);
        $trend_j1 = [
            'pct' => abs($pct),
            'class' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat'),
            'symbol' => $pct > 0 ? '▲' : ($pct < 0 ? '▼' : '—')
        ];
    } elseif ($dailyTotals['total_amount'] > 0) {
        $trend_j1 = ['pct' => '∞', 'class' => 'up', 'symbol' => '▲'];
    }

    // Variables à passer à la vue pour le tableau
    $total_amount = $dailyTotals['total_amount'];
    $total_sales = $dailyTotals['total_sales'];
    $total_articles = $dailyTotals['total_articles'];

    $total_amount_weekly = $weeklyTotals['total_amount'];
    $total_sales_weekly = $weeklyTotals['total_sales'];
    $total_articles_weekly = $weeklyTotals['total_articles'];

    $total_amount_monthly = $monthlyTotals['total_amount'];
    $total_sales_monthly = $monthlyTotals['total_sales'];
    $total_articles_monthly = $monthlyTotals['total_articles'];

    // Recuperation des donnees pour l'affichage
    $dailySales = $salesManager->getSalesByDate($selectedDate)->fetchAll();
    $aggregatedArticles = $articlesManager->getAggregatedArticlesByDate($selectedDate);
    $chronologyArticles = $articlesManager->getArticlesByDate($selectedDate, true);
    $messages = $messageManager->getMessages();

    // Pre-chargement des articles par vente pour l'apercu rapide (pas de fetch AJAX)
    $salePreviewData = [];
    foreach ($dailySales as $sale) {
        $saleArticles = $articlesManager->getArticlesBySaleId($sale['id']);
        $salePreviewData[$sale['id']] = [
            'id'             => $sale['id'],
            'sale_date'      => $sale['sale_date'] ?? $selectedDate,
            'sale_time'      => $sale['sale_time'] ?? '',
            'payment_method' => $sale['payment_method'] ?? 'cash',
            'total_amount'   => $sale['total_amount'],
            'articles'       => $saleArticles,
        ];
    }
    $json_salePreviewData = json_encode($salePreviewData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

    // Exportation CSV
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sales_' . $selectedDate . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Date', 'Montant', 'Nombre d\'articles']);
        foreach ($dailySales as $sale) {
            $articles = $articlesManager->getArticlesBySaleId($sale['id']);
            fputcsv($output, [$sale['id'], $sale['date'], $sale['total_amount'], count($articles)]);
        }
        fclose($output);
        exit;
    }

    $list_of_sales = $salesManager->getSalesByDate($selectedDate);
    $list_of_articles = $articlesManager->getArticlesByDate($selectedDate);

    // Juste avant l'inclusion de la vue
    $totals = [
        'daily' => $dailyTotals,
        'weekly' => $weeklyTotals,
        'monthly' => $monthlyTotals,
    ];

    $json_totals = json_encode($totals);


    // Inclusion du fichier JS et rendu de la vue
    $newJsFile = 'control-panel/sale/control-panel/control-panel.js';
    require 'view/control-panel/sale/control-panel/control-panel.php';
 }

function ardoisePanel($message = '')
{
    $title = "Ardoises clients";

    $salesManager = new SalesManager();
    $articlesManager = new ArticlesManager();

    $ardoises = $salesManager->getPendingArdoises();
    $totals = $salesManager->getTotalPendingArdoises();
    $total_amount = floatval($totals['total_amount'] ?? 0);
    $total_sales = intval($totals['nb_sales'] ?? 0);

    // Ajoute les articles de chaque ardoise
    foreach ($ardoises as &$ardoise) {
        $ardoise['articles'] = $articlesManager->getArticlesBySaleId($ardoise['id']);
    }
    unset($ardoise);

    $newJsFile = 'control-panel/sale/ardoise/ardoisePanel.js';
    require 'view/control-panel/sale/ardoise/ardoisePanel.php';
}

// --------------------------- P O S T
function createForm($message, $selectedDate)
{
    userValidation();

    $sale_id = addSale($selectedDate, 0, 'cash');

    // Après création, rediriger vers le formulaire d'édition avec l'ID réel
    // pour éviter le mode "add" générique et permettre la modification immédiate.
    header('Location: index.php?action=newSaleForm&sale_id=' . (int)$sale_id . '&created=1');
    exit;
}

function addSale($sale_date, $total_amount, $payment_method)
{
    userValidation();

    $SaleManager = new SalesManager();

    date_default_timezone_set('Europe/Paris');

    $created_at = date("Y-m-d H:i:s");
    $updated_at = date("Y-m-d H:i:s");

    $selectedDate = date("Y-m-d", strtotime($sale_date));

    // Obtenir l'heure actuelle
    $currentTime = date("H:i:s");

    // Combinez la date selectionnee avec l'heure actuelle
    $combinedDateTime = $selectedDate . " " . $currentTime;

    $included = 1;

    $postSale = $SaleManager->postSale($combinedDateTime, 0, $payment_method, $included, $created_at, $updated_at);

    $sale = $SaleManager->getLastSale();

    $sale_id = $sale['id'];

    if ($postSale != false) {
        $message = 'La vente à bien été ajoutée <br> ajout des articles';
    }

    return $sale_id;
}

// --------------------------- U P D A T E

function updateForm($sale_id, $message)
{
    $saleManager = new SalesManager();
    $articlesManager = new ArticlesManager();
    $categoriesManager = new CategoriesManager();
    $subcategoriesManager = new SubcategoriesManager();
    $clientsManager = new ClientsManager();

    $categories = $categoriesManager->getCategoriesByArticleCount();
    $subcategories = $subcategoriesManager->getSubcategoriesByArticleCount();
    $subcategoryScores = $articlesManager->getSubcategorySaleCountsByMonth();

    $articles_list = $articlesManager->getArticlesBySaleId($sale_id);

    // Le total est déjà synchronisé par syncSaleTotalAmount dans le Modèle
    // (appelé par postSaleArticle/updateArticle/deleteArticle)
    $sale = $saleManager->getSaleById($sale_id);
    $previous_sale = $saleManager->getPreviousSale($sale_id);
    $next_sale = $saleManager->getNextSale($sale_id);
    $articles_list = $articlesManager->getArticlesBySaleId($sale_id);
    $clients = $clientsManager->getClients();

    $newJsFile = 'control-panel/sale/update/update.js';

    require('view/control-panel/sale/update/update.php');
}

function updateSale($sale_id, $sale_date, $total_amount, $payment_method, $client_id = null)
{
    userValidation();

    $saleManager = new SalesManager();
    $articleManager = new ArticlesManager();

    date_default_timezone_set('Europe/Paris');

    $updated_at = date("Y-m-d H:i:s");

    $selectedDate = date("Y-m-d", strtotime($sale_date));

    // Si une heure est deja presente dans la vente, la conserver au lieu de l'ecraser par minuit
    $existingTime = null;
    $saleCurrent = $saleManager->getSaleById($sale_id);
    if (!empty($saleCurrent['sale_date'])) {
        $parts = explode(' ', $saleCurrent['sale_date']);
        if (isset($parts[1]) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $parts[1])) {
            $existingTime = $parts[1];
        }
    }
    $saleDateToUpdate = $existingTime ? $selectedDate . ' ' . $existingTime : $selectedDate;

    $updateSale = $saleManager->updateSale($sale_id, $saleDateToUpdate, $total_amount, $payment_method, $updated_at);

    // Si la date de vente change effectivement d'un jour, on decale le jour des articles existants
    // sans ecraser l'heure de creation de chaque article.
    $saleCurrentDate = !empty($saleCurrent['sale_date']) ? date('Y-m-d', strtotime($saleCurrent['sale_date'])) : null;
    if ($saleCurrentDate !== null && $saleCurrentDate !== $selectedDate) {
        $articleManager->updateDateSaleArticlesDateOnly($sale_id, $selectedDate);
    }

    // Mettre a jour updated_at des articles egalement pour tracer la modification
    $articleManager->updateDateSaleArticlesUpdatedAt($sale_id, $updated_at);

    if ($updateSale != false) {
        $message = 'La vente à bien été modifiée';

        updateForm($sale_id, $message);
    }
}


function saveSaleArticle($sale_id, $category, $subcategory, $quantity, $price, $comment)
{
    userValidation();

    $saleManager = new SalesManager();
    $articlesManager = new ArticlesManager();
    $categoriesManager = new CategoriesManager();
    $subcategoriesManager = new SubcategoriesManager();

    $comment = strtoupper($comment);

    $articleToAdd = $articlesManager->postSaleArticle($sale_id, $category, $subcategory, $quantity, $price, $comment);

    $saleManager->updateUpdatedAt($sale_id, date("Y-m-d H:i:s"));

    if ($articleToAdd != false) {
        $message = $subcategory . '<br> ajouté à la vente.';

        updateForm($sale_id, $message);
    }
}

function updateSaleArticle($sale_id, $article_id, $quantity, $price, $comment)
{
    userValidation();

    $articlesManager = new ArticlesManager();

    // get the sale_id
    $article = $articlesManager->getArticleById($article_id);

    $sale_id = $article['sale_id'];

    $articleToUpdate = $articlesManager->updateArticle($article_id, $quantity, $price, $comment);

    if ($articleToUpdate != false) {
        $message = 'L\'article à bien été modifié';

        updateForm($sale_id, $message);
    }
}

// ------------------------ D E L E T E

function deleteSaleArticle($sale_id, $article_id)
{
    userValidation();

    $articlesManager = new ArticlesManager();

    $articleToDelete = $articlesManager->deleteArticle($article_id);

    if ($articleToDelete != false) {
        $message = 'L\'article à bien été supprimé';

        updateForm($sale_id, $message);
    }
}

function deleteSale($sale_id)
{
    userValidation();

    $saleManager = new SalesManager();
    $articlesManager = new ArticlesManager();

    $articlesToDelete = $articlesManager->deleteArticlesBySaleId($sale_id);
    $saleToDelete = $saleManager->deleteSale($sale_id);

    if ($saleToDelete != false) {
        $message = 'La vente à bien été supprimée';
        $selectedDate = date('Y-m-d');

        salePanel($selectedDate, $message);
    }
}

// Fonction utilitaire pour calculer les totaux
function calculateTotal($sales, ArticlesManager $articlesManager)
{
    $totals = [
        'total_amount' => 0,
        'total_sales' => 0,
        'total_articles' => 0,
        'avg_price_per_article' => 0
    ];

    foreach ($sales as $sale) {
        // Exclure les ventes ardoise (client_id non null)
        if (!empty($sale['client_id'])) {
            continue;
        }
        $totals['total_amount'] += $sale['total_amount'];
        $totals['total_sales']++;
        $totals['total_articles'] += intval($sale['article_count'] ?? 0);
    }

    if ($totals['total_articles'] > 0) {
        $totals['avg_price_per_article'] = $totals['total_amount'] / $totals['total_articles'];
    }

    return $totals;
}

// ============================================================
// AJAX : API JSON pour ajout/suppression article sans rechargement
// ============================================================
function saveSaleArticleAjax($sale_id, $category, $subcategory, $quantity, $price, $comment)
{
    userValidation();

    $saleManager = new SalesManager();
    $articlesManager = new ArticlesManager();

    $comment = strtoupper($comment);
    $articleId = $articlesManager->postSaleArticle($sale_id, $category, $subcategory, $quantity, $price, $comment);

    if ($articleId === false) {
        http_response_code(500);
        echo json_encode(['error' => 'insert failed']);
        return;
    }

    $lineTotal = floatval($price) * floatval($quantity);
    $sale = $saleManager->getSaleById($sale_id);
    $saleManager->updateUpdatedAt($sale_id, date("Y-m-d H:i:s"));

    echo json_encode([
        'success' => true,
        'article_id' => $articleId,
        'category' => $category,
        'subcategory' => $subcategory,
        'quantity' => $quantity,
        'unit_price' => $price,
        'comment' => $comment,
        'line_total' => $lineTotal,
        'sale_total' => floatval($sale['total_amount'])
    ]);
}

function deleteSaleArticleAjax($sale_id, $article_id)
{
    userValidation();

    $saleManager = new SalesManager();
    $articlesManager = new ArticlesManager();

    $article = $articlesManager->getArticleById($article_id);
    if (!$article) {
        http_response_code(404);
        echo json_encode(['error' => 'article not found']);
        return;
    }

    $deleted = $articlesManager->deleteArticle($article_id);
    if (!$deleted) {
        http_response_code(500);
        echo json_encode(['error' => 'delete failed']);
        return;
    }

    $sale = $saleManager->getSaleById($sale_id);
    $saleManager->updateUpdatedAt($sale_id, date("Y-m-d H:i:s"));

    echo json_encode([
        'success' => true,
        'article_id' => $article_id,
        'sale_total' => floatval($sale['total_amount'])
    ]);
}

function getSaleArticlesJson($sale_id)
{
    userValidation();
    $articlesManager = new ArticlesManager();
    $articles = $articlesManager->getArticlesBySaleId($sale_id);
    header('Content-Type: application/json');
    echo json_encode($articles);
}

function getSaleJson($sale_id)
{
    userValidation();
    $saleManager = new SalesManager();
    $sale = $saleManager->getSaleById($sale_id);
    header('Content-Type: application/json');
    echo json_encode($sale);
}

function updateSaleArticleAjax($article_id, $quantity, $price, $comment)
{
    userValidation();

    $articlesManager = new ArticlesManager();
    $saleManager = new SalesManager();

    $article = $articlesManager->getArticleById($article_id);
    if (!$article) {
        http_response_code(404);
        echo json_encode(['error' => 'article not found']);
        return;
    }

    $sale_id = $article['sale_id'];
    $oldLineTotal = floatval($article['unit_price']) * floatval($article['quantity']);
    $newLineTotal = floatval($price) * floatval($quantity);

    $updated = $articlesManager->updateArticle($article_id, $quantity, $price, $comment);
    if (!$updated) {
        http_response_code(500);
        echo json_encode(['error' => 'update failed']);
        return;
    }

    $sale = $saleManager->getSaleById($sale_id);
    $saleManager->updateUpdatedAt($sale_id, date("Y-m-d H:i:s"));

    echo json_encode([
        'success' => true,
        'article_id' => $article_id,
        'quantity' => $quantity,
        'unit_price' => $price,
        'comment' => $comment,
        'line_total' => $newLineTotal,
        'sale_total' => floatval($sale['total_amount'])
    ]);
}
// API — update sale metadata (payment, date, client) via AJAX
function updateSaleAjax($sale_id, $sale_date = null, $payment_method = null, $client_id = null)
{
    // Sécurité minimale : session active requise, sans userValidation() qui casse le JSON
    if (empty($_SESSION['pseudo'])) {
        echo json_encode(['success' => false, 'error' => 'Session expirée']);
        return;
    }

    $saleManager = new OpenClassroom\Blog\Model\SalesManager();
    $updated_at = date("Y-m-d H:i:s");

    // Get current sale data
    $sale = $saleManager->getSaleById($sale_id);
    if (!$sale) {
        echo json_encode(['success' => false, 'error' => 'Vente introuvable']);
        return;
    }

    // Build fields to update (only provided ones)
    $fields = [];
    $values = [];

    if ($sale_date !== null) {
        // Si la date recue ne contient pas d'heure, conserver l'heure de la vente existante
        $sale_date = trim($sale_date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $sale_date)) {
            $existingTime = '00:00:00';
            if (!empty($sale['sale_date'])) {
                $parts = explode(' ', $sale['sale_date']);
                if (isset($parts[1]) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $parts[1])) {
                    $existingTime = $parts[1];
                }
            }
            $sale_date .= ' ' . $existingTime;
        }
        $fields[] = 'sale_date = ?';
        $values[] = $sale_date;
    }
    if ($payment_method !== null) {
        $fields[] = 'payment_method = ?';
        $values[] = $payment_method;
    }
    if ($client_id !== null) {
        $fields[] = 'client_id = ?';
        $values[] = $client_id;
    }

    if (empty($fields)) {
        echo json_encode(['success' => false, 'error' => 'Aucun champ à mettre à jour']);
        return;
    }

    $fields[] = 'updated_at = ?';
    $values[] = $updated_at;
    $values[] = $sale_id;

    $sql = 'UPDATE sales SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $req = $saleManager->getConnection()->prepare($sql);
    $req->execute($values);

    // Si la date de vente a change, synchroniser le jour des articles
    if ($sale_date !== null) {
        $articleManager = new OpenClassroom\Blog\Model\ArticlesManager();
        $newDate = date('Y-m-d', strtotime($sale_date));
        $currentDate = !empty($sale['sale_date']) ? date('Y-m-d', strtotime($sale['sale_date'])) : null;
        if ($currentDate !== null && $currentDate !== $newDate) {
            $articleManager->updateDateSaleArticlesDateOnly($sale_id, $newDate);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Vente mise à jour']);
}


function stockPanel($message = '')
{
    userValidation();

    require_once('model/StockManager.php');
    $stockManager = new \OpenClassroom\Blog\Model\StockManager();
    $subcategoriesManager = new SubcategoriesManager();

    $category = !empty($_GET['category']) ? $_GET['category'] : 'Bougies';

    $balances = $stockManager->getStockBalances($category);
    $subcategories = $subcategoriesManager->getSubcategories();

    $newJsFile = 'control-panel/stock/stockPanel.js';
    require('view/control-panel/stock/stockPanel.php');
}

function stockAdjustAjax()
{
    userValidation();
    header('Content-Type: application/json');

    require_once('model/StockManager.php');
    $stockManager = new \OpenClassroom\Blog\Model\StockManager();

    $validTypes = ['purchase', 'sale', 'inventory', 'adjustment', 'donation'];
    $category = !empty($_POST['category']) ? $_POST['category'] : '';
    $subcategory = !empty($_POST['subcategory']) ? $_POST['subcategory'] : '';
    $qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 0;
    $type = !empty($_POST['type']) && in_array($_POST['type'], $validTypes, true) ? $_POST['type'] : 'adjustment';
    $price = !empty($_POST['price']) ? (float) str_replace(',', '.', $_POST['price']) : null;
    $comment = !empty($_POST['comment']) ? $_POST['comment'] : null;
    $userId = !empty($_SESSION['id']) ? (int) $_SESSION['id'] : null;

    if ($category === '' || $subcategory === '' || $qty === 0) {
        echo json_encode(['success' => false, 'error' => 'Données invalides']);
        exit;
    }

    $id = $stockManager->addMovement($category, $subcategory, $type, $qty, $price, null, 'manual', $comment, $userId);
    echo json_encode(['success' => true, 'movement_id' => $id]);
    exit;
}

function stockAddEntry()
{
    userValidation();
    require_once('model/StockManager.php');
    $stockManager = new \OpenClassroom\Blog\Model\StockManager();

    $validTypes = ['purchase', 'sale', 'inventory', 'adjustment', 'donation'];
    $category = !empty($_POST['category']) ? $_POST['category'] : '';
    $subcategory = !empty($_POST['subcategory']) ? $_POST['subcategory'] : '';
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;
    $type = !empty($_POST['entry_type']) && in_array($_POST['entry_type'], $validTypes, true) ? $_POST['entry_type'] : 'donation';
    $price = !empty($_POST['unit_price']) ? (float) str_replace(',', '.', $_POST['unit_price']) : null;
    $comment = !empty($_POST['comment']) ? $_POST['comment'] : null;
    $userId = !empty($_SESSION['id']) ? (int) $_SESSION['id'] : null;

    if ($category === '' || $subcategory === '' || $quantity <= 0) {
        $message = 'Données invalides : catégorie, sous-catégorie et quantité obligatoires.';
        stockPanel($message);
        return;
    }

    if ($type === 'donation') {
        $price = 0.00;
    }

    $id = $stockManager->addMovement($category, $subcategory, $type, $quantity, $price, null, 'manual', $comment, $userId);
    $message = 'Entrée enregistrée (id ' . $id . ').';
    stockPanel($message);
}
