
<?php

require_once('model/PurchasesManager.php');
require_once('model/CategoriesManager.php');
require_once('model/SubcategoriesManager.php');
require_once('model/ArticlesManager.php');
require_once('model/ProvidersManager.php');
require_once('model/ProvidersManager.php');
require_once('controller/MainController.php');

require_once('controller/UtilsController.php');

use \OpenClassroom\Blog\Model\PurchasesManager;
use \OpenClassroom\Blog\Model\ProvidersManager;
use \OpenClassroom\Blog\Model\CategoriesManager;
use \OpenClassroom\Blog\Model\ArticlesManager;
use \OpenClassroom\Blog\Model\MessageManager;
use \OpenClassroom\Blog\Model\SubcategoriesManager;

function purchasePanel($selectedDate, $message)
{
    userValidation();

    $title = "Panneau des Achats";

    $purchasesManager = new PurchasesManager();
    $providersManager = new ProvidersManager();
    $articleManager = new ArticlesManager();

    if (isset($user_fonction) && $user_fonction != '') {
        setcookie('fonction', $user_fonction, [
            'expires' => time() + 365 * 24 * 3600,
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        $user_fonction = $_SESSION['fonction'];
    }

    // Validation de la date
    if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $selectedDate) || !strtotime($selectedDate)) {
        $selectedDate = date('Y-m-d');
    }

    // Achats du jour (itérable)
    $list_of_purchases = $purchasesManager->getPurchasesOfTheDay($selectedDate)->fetchAll(PDO::FETCH_ASSOC);

    $total_amount = 0;
    $total_purchases = 0;
    foreach ($list_of_purchases as $purchase) {
        $total_amount += $purchase['total_amount'];
        $total_purchases++;
    }

    // Achats de la semaine
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
    $list_of_purchases_weekly = $purchasesManager->getPurchasesBetweenDates($startOfWeek, $endOfWeek)->fetchAll(PDO::FETCH_ASSOC);

    $total_amount_weekly = 0;
    $total_purchases_weekly = 0;
    foreach ($list_of_purchases_weekly as $purchase) {
        $total_amount_weekly += $purchase['total_amount'];
        $total_purchases_weekly++;
    }

    // Achats du mois
    $startOfMonth = date('Y-m-01');
    $endOfMonth = date('Y-m-t');
    $list_of_purchases_monthly = $purchasesManager->getPurchasesBetweenDates($startOfMonth, $endOfMonth)->fetchAll(PDO::FETCH_ASSOC);

    $total_amount_monthly = 0;
    $total_purchases_monthly = 0;
    foreach ($list_of_purchases_monthly as $purchase) {
        $total_amount_monthly += $purchase['total_amount'];
        $total_purchases_monthly++;
    }

    // Articles des achats du jour (récap)
    $list_of_purchases_articles = $articleManager->getPurchasesArticlesByDate($selectedDate);

    // Agrégation par catégorie / sous-catégorie pour le récap
    $aggregatedArticles = [];
    foreach ($list_of_purchases_articles as $article) {
        $key = $article['category'] . '|' . $article['subcategory'];
        if (!isset($aggregatedArticles[$key])) {
            $aggregatedArticles[$key] = [
                'category' => $article['category'],
                'subcategory' => $article['subcategory'],
                'total_quantity' => 0,
                'total_amount' => 0.0,
                'avg_unit_price' => 0.0,
                'count' => 0,
            ];
        }
        $aggregatedArticles[$key]['total_quantity'] += (int)$article['quantity'];
        $aggregatedArticles[$key]['total_amount'] += (float)$article['unit_price'] * (int)$article['quantity'];
        $aggregatedArticles[$key]['count']++;
    }
    foreach ($aggregatedArticles as $key => $row) {
        $aggregatedArticles[$key]['avg_unit_price'] = $row['total_quantity'] > 0
            ? $row['total_amount'] / $row['total_quantity']
            : 0.0;
    }

    // Totaux globaux
    $dailyTotals = [
        'total_amount' => $total_amount,
        'total_purchases' => $total_purchases,
        'total_articles' => array_sum(array_column($list_of_purchases_articles, 'quantity')) ?: 0,
    ];
    $weeklyTotals = [
        'total_amount' => $total_amount_weekly,
        'total_purchases' => $total_purchases_weekly,
    ];
    $monthlyTotals = [
        'total_amount' => $total_amount_monthly,
        'total_purchases' => $total_purchases_monthly,
    ];

    // Tendance J-1
    $yesterday = date('Y-m-d', strtotime($selectedDate . ' -1 day'));
    $yesterdayPurchases = $purchasesManager->getPurchasesOfTheDay($yesterday)->fetchAll(PDO::FETCH_ASSOC);
    $yesterdayAmount = array_sum(array_column($yesterdayPurchases, 'total_amount'));
    $trend_j1 = null;
    if ($yesterdayAmount > 0) {
        $diff = $total_amount - $yesterdayAmount;
        $pct = round(($diff / $yesterdayAmount) * 100, 1);
        $trend_j1 = [
            'pct' => abs($pct),
            'symbol' => $diff >= 0 ? '+' : '−',
            'class' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat'),
        ];
    }

    require('view/control-panel/purchase/control-panel.php');
}

// --------------------------- P O S T
function createPurchaseForm($message)
{
    userValidation();

    $providersManager = new ProvidersManager();

    $purchase_date = date("Y-m-d");
    $total_amount = 0;
    $payment_method = '';
    $created_at = date("Y-m-d H:i:s");
    $updated_at = date("Y-m-d H:i:s");

    $providers = $providersManager->getProviders();

    require('view/control-panel/purchase/create.php');
}

function addPurchase($purchase_date, $provider_id, $paid_amount, $payment_method, $comment)
{
    userValidation();

    $purchaseManager = new PurchasesManager();

    date_default_timezone_set('Europe/Paris');

    $created_at = date("Y-m-d H:i:s");
    $updated_at = date("Y-m-d H:i:s");

    $postPurchase = $purchaseManager->postPurchase($purchase_date, $provider_id, $paid_amount, $payment_method, 1, $created_at, $updated_at, $comment);

    if ($postPurchase) {
        $lastPurchase = $purchaseManager->getLastPurchase();
        $purchase_id = $lastPurchase['id'];
        updatePurchaseForm($purchase_id, "L'achat a bien été créé");
    } else {
        $message = "Erreur lors de la création de l'achat";
        createPurchaseForm($message);
    }
}

// --------------------------- U P D A T E

function updatePurchaseForm($purchase_id, $message)
{
    userValidation();

    $purchaseManager = new PurchasesManager();
    $articlesManager = new ArticlesManager();
    $providersManager = new ProvidersManager();
    $categoriesManager = new CategoriesManager();
    $subcategoriesManager = new SubcategoriesManager();

    $purchase = $purchaseManager->getPurchaseById($purchase_id);
    $providers = $providersManager->getProviders();
    $categories = $categoriesManager->getCategoriesByArticleCount();
    $subcategories = $subcategoriesManager->getSubcategoriesByArticleCount();

    $articles_list = $articlesManager->getArticlesByPurchaseId($purchase_id);

    // Le montant payé reste manuel ; on ne l'écrase pas avec le total des articles
    $title = $purchase['comment'] . ' - ' . $purchase['total_amount'] . ' €';

    $jsFile = 'purchase/update.js';

    require('view/control-panel/purchase/update.php');
}

function updatePurchase($purchase_id, $purchase_date, $provider_id, $total_amount, $payment_method, $comment, $message)
{

    userValidation();

    $purchaseManager = new PurchasesManager();

    date_default_timezone_set('Europe/Paris');

    $updated_at = date("Y-m-d H:i:s");

    $included = 1;

    $updatedPurchase = $purchaseManager->updatePurchase($purchase_id, $purchase_date, $provider_id, $total_amount, $payment_method, $included, $updated_at, $comment);

    $jsFile = 'purchase/update.js';

    $title = $comment;

    if ($updatedPurchase != false) {
        $message = 'L\'Achat à bien été modifiée';
        $selectedDate = date('Y-m-d');
        updatePurchaseForm($purchase_id, $message);
    }
}

function savePurchaseArticle($purchase_id, $category, $subcategory, $quantity, $price, $comment)
{
    userValidation();

    $purchaseManager = new PurchasesManager();
    $articleManager = new ArticlesManager();
    $calegoriesManager = new CategoriesManager();
    $subcategoriesManager = new SubcategoriesManager();

    $purchase = $purchaseManager->getPurchaseById($purchase_id);

    $total_purchase_total_amount = $purchase['total_amount'] + ($price * $quantity);

    $comment = strtoupper($comment);

    $purchaseArticleToAdd = $articleManager->postPurchaseArticle($purchase_id,  $category, $subcategory, $quantity, $price, $comment);

    $updatePurchaseAmount = $purchaseManager->updatePurchaseTotalAmount($purchase_id, $total_purchase_total_amount);

    $updatePurchaseUpdatedAt = $purchaseManager->updatePurchaseUpdatedAt($purchase_id, date("Y-m-d H:i:s"));

    if ($purchaseArticleToAdd != false) {
        $message = $subcategory . '<br> ajoutée a l\'achat';
        updatePurchaseForm($purchase_id, $message);
    }
}

function updatePurchaseArticle($purchase_id, $article_id, $quantity, $price, $comment)
{
    userValidation();

    $purchaseManager = new PurchasesManager();
    $articleManager = new ArticlesManager();

    $article = $articleManager->getArticleById($article_id);

    // update article quantity and price
    $updatedArticle = $articleManager->updateArticle($article_id, $quantity, $price, $comment);

    // update purchase total amount
    $purchase = $purchaseManager->getPurchaseById($purchase_id);
    $purchase_total_amount = $purchase['total_amount'] + ($price * $quantity) - ($article['unit_price'] * $article['quantity']);

    $updatePurchaseAmount = $purchaseManager->updatePurchaseTotalAmount($purchase_id, $purchase_total_amount);

    if ($updatedArticle != false) {
        $message = 'L\'article à bien été modifié';
        updatePurchaseForm($purchase_id, $message);
    }
}

// ------------------------ D E L E T E

function deletePurchaseArticle($purchase_id, $article_id)
{
    userValidation();

    $articlesManager = new ArticlesManager();

    $articleToDelete = $articlesManager->deleteArticle($article_id);

    // update purchase

    if ($articleToDelete != false) {
        $message = 'L\'article à bien été supprimé';

        updatePurchaseForm($purchase_id, $message);
    }
}

function deletePurchase($purchase_id)
{
    userValidation();

    $purchaseManager = new PurchasesManager();

    $purchaseToDelete = $purchaseManager->deletePurchase($purchase_id);

    if ($purchaseToDelete != false) {
        $message = "L'Achat à bien été supprimée";
        $selectedDate = date('Y-m-d');

        purchasePanel($selectedDate, $message);
    }
}

// ============================================================
// AJAX : API JSON pour achats sans rechargement
// ============================================================

function apiSavePurchaseArticle($purchase_id, $category, $subcategory, $quantity, $price, $comment)
{
    userValidation();

    $purchaseManager = new PurchasesManager();
    $articlesManager = new ArticlesManager();

    $comment = strtoupper($comment);

    // Si achat en mode lot (montant payé > 0), on ignore le prix saisi et on normalise après insertion
    $purchase = $purchaseManager->getPurchaseById($purchase_id);
    $isLot = floatval($purchase['paid_amount'] ?? 0) > 0;
    $savePrice = $isLot ? 0.00 : floatval($price);

    $articleId = $articlesManager->postPurchaseArticle($purchase_id, $category, $subcategory, $quantity, $savePrice, $comment);

    if ($articleId === false) {
        http_response_code(500);
        echo json_encode(['error' => 'insert failed']);
        exit;
    }

    if ($isLot) {
        $normalized = $purchaseManager->normalizeLotUnitPrices($purchase_id);
        header('Content-Type: application/json');
        echo json_encode($normalized + ['article_id' => $articleId, 'category' => $category, 'subcategory' => $subcategory]);
        exit;
    }

    $lineTotal = floatval($price) * floatval($quantity);
    $newTotal = floatval($purchase['total_amount']) + $lineTotal;
    $purchaseManager->updatePurchaseTotalAmount($purchase_id, $newTotal);
    $purchaseManager->updatePurchaseUpdatedAt($purchase_id, date("Y-m-d H:i:s"));

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'article_id' => $articleId,
        'category' => $category,
        'subcategory' => $subcategory,
        'quantity' => $quantity,
        'unit_price' => $price,
        'comment' => $comment,
        'line_total' => $lineTotal,
        'purchase_total' => $newTotal,
        'normalized' => false
    ]);
    exit;
}

function apiDeletePurchaseArticle($article_id)
{
    userValidation();

    $purchaseManager = new PurchasesManager();
    $articlesManager = new ArticlesManager();

    $article = $articlesManager->getArticleById($article_id);
    if (!$article) {
        http_response_code(404);
        echo json_encode(['error' => 'article not found']);
        exit;
    }

    $purchase_id = $article['purchase_id'];
    $purchase = $purchaseManager->getPurchaseById($purchase_id);
    $isLot = floatval($purchase['paid_amount'] ?? 0) > 0;

    $deleted = $articlesManager->deleteArticle($article_id);
    if (!$deleted) {
        http_response_code(500);
        echo json_encode(['error' => 'delete failed']);
        exit;
    }

    if ($isLot) {
        $normalized = $purchaseManager->normalizeLotUnitPrices($purchase_id);
        header('Content-Type: application/json');
        echo json_encode($normalized + ['article_id' => $article_id]);
        exit;
    }

    $lineTotal = floatval($article['unit_price']) * floatval($article['quantity']);
    $newTotal = max(0.00, floatval($purchase['total_amount']) - $lineTotal);

    $purchaseManager->updatePurchaseTotalAmount($purchase_id, $newTotal);
    $purchaseManager->updatePurchaseUpdatedAt($purchase_id, date("Y-m-d H:i:s"));

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'article_id' => $article_id,
        'purchase_total' => $newTotal,
        'normalized' => false
    ]);
    exit;
}

function apiUpdatePurchaseArticle($article_id, $quantity, $price, $comment)
{
    userValidation();

    $articlesManager = new ArticlesManager();
    $purchaseManager = new PurchasesManager();

    $article = $articlesManager->getArticleById($article_id);
    if (!$article) {
        http_response_code(404);
        echo json_encode(['error' => 'article not found']);
        exit;
    }

    $purchase_id = $article['purchase_id'];
    $purchase = $purchaseManager->getPurchaseById($purchase_id);
    $isLot = floatval($purchase['paid_amount'] ?? 0) > 0;

    if ($isLot) {
        // En mode lot on ne met à jour que la quantité (le prix est recalculé globalement)
        $updated = $articlesManager->updateArticle($article_id, $quantity, $article['unit_price'], strtoupper($comment));
        if (!$updated) {
            http_response_code(500);
            echo json_encode(['error' => 'update failed']);
            exit;
        }
        $normalized = $purchaseManager->normalizeLotUnitPrices($purchase_id);
        header('Content-Type: application/json');
        echo json_encode($normalized + ['article_id' => $article_id]);
        exit;
    }

    $oldLineTotal = floatval($article['unit_price']) * floatval($article['quantity']);
    $newLineTotal = floatval($price) * floatval($quantity);

    $updated = $articlesManager->updateArticle($article_id, $quantity, $price, strtoupper($comment));
    if (!$updated) {
        http_response_code(500);
        echo json_encode(['error' => 'update failed']);
        exit;
    }

    $newTotal = floatval($purchase['total_amount']) - $oldLineTotal + $newLineTotal;
    $purchaseManager->updatePurchaseTotalAmount($purchase_id, $newTotal);
    $purchaseManager->updatePurchaseUpdatedAt($purchase_id, date("Y-m-d H:i:s"));

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'article_id' => $article_id,
        'quantity' => $quantity,
        'unit_price' => $price,
        'comment' => strtoupper($comment),
        'line_total' => $newLineTotal,
        'purchase_total' => $newTotal,
        'normalized' => false
    ]);
    exit;
}

function apiUpdatePurchase($purchase_id, $purchase_date = null, $provider_id = null, $paid_amount = null, $payment_method = null, $comment = null)
{
    userValidation();

    if (empty($_SESSION['pseudo'])) {
        echo json_encode(['success' => false, 'error' => 'Session expirée']);
        exit;
    }

    $purchaseManager = new PurchasesManager();
    $purchase = $purchaseManager->getPurchaseById($purchase_id);
    if (!$purchase) {
        echo json_encode(['success' => false, 'error' => 'achat introuvable']);
        exit;
    }

    // Si la date recue ne contient pas d'heure, conserver l'heure de l'achat existant
    if ($purchase_date !== null) {
        $purchase_date = trim($purchase_date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $purchase_date)) {
            $existingTime = '00:00:00';
            if (!empty($purchase['purchase_date'])) {
                $parts = explode(' ', $purchase['purchase_date']);
                if (isset($parts[1]) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $parts[1])) {
                    $existingTime = $parts[1];
                }
            }
            $purchase_date .= ' ' . $existingTime;
        }
    }

    $updatePurchase = $purchaseManager->updatePurchase(
        $purchase_id,
        $purchase_date ?? $purchase['formatted_purchase_date'],
        $provider_id ?? $purchase['provider_id'],
        floatval($purchase['total_amount']),
        $payment_method ?? $purchase['payment_method'],
        1,
        date("Y-m-d H:i:s"),
        $comment ?? ($purchase['comment'] ?? '')
    );

    if ($updatePurchase && $paid_amount !== null) {
        $purchaseManager->updatePurchasePaidAmount($purchase_id, floatval($paid_amount));
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => (bool)$updatePurchase]);
    exit;
}

function apiAddProvider()
{
    userValidation();

    if (empty($_POST['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Nom manquant']);
        exit;
    }

    $providersManager = new ProvidersManager();
    $providerId = $providersManager->postProvider(
        trim($_POST['name'] ?? ''),
        trim($_POST['city'] ?? '') ?: null,
        trim($_POST['contact_email'] ?? '') ?: null,
        trim($_POST['contact_phone'] ?? '') ?: null
    );

    if ($providerId === false || !is_numeric($providerId)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'insert failed']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'provider_id' => (int)$providerId,
        'name' => trim($_POST['name'] ?? ''),
        'city' => trim($_POST['city'] ?? '')
    ]);
    exit;
}
