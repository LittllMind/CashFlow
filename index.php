<?php
// --- Configuration session : 24h pour eviter de reconnecter toute la journee ---
$sessionLifetime = 86400; // 24 heures
ini_set('session.gc_maxlifetime', (string)$sessionLifetime);
ini_set('session.cookie_lifetime', (string)$sessionLifetime);
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

// Auto-detection HTTPS pour cookie_secure
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
ini_set('session.cookie_secure', $isHttps ? '1' : '0');

// Répertoire de sessions local pour php -S (pas de droits sur /var/lib/php/sessions)
if (PHP_SAPI === 'cli-server') {
    $localSessionDir = __DIR__ . '/sessions';
    if (!is_dir($localSessionDir)) {
        mkdir($localSessionDir, 0750, true);
    }
    session_save_path($localSessionDir);
}

ob_start();
if (file_exists(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
}
session_start();
if (file_exists(__DIR__ . '/env.php')) {
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
} else {
    ini_set("display_errors", 0);
    ini_set("display_startup_errors", 0);
    error_reporting(0);
}

require('controller/MainController.php');
require('controller/UserController.php');
require('controller/SalesController.php');
require('controller/ExportSaleController.php');
require('controller/PurchasesController.php');

require('controller/StatisticController.php');
require('controller/GlobalStatisticController.php');

require('controller/MessageController.php');

require('controller/ResultController.php');
require('controller/ArticlesController.php'); // Make sure this file defines the ArticlesController class
require('controller/ClosureController.php');
require('controller/CbBalanceController.php');
require('controller/UrssafController.php');



function valid_datas($datas)
{
    $datas = trim($datas);
    $datas = stripslashes($datas);
    $datas = htmlspecialchars($datas);
    return $datas;
}

try {
    if (empty($_GET['action'])) {
        $action = 'index';
    } else {
        $action = $_GET['action'];
    }
    switch ($action) {
        // ----------- U S E R S ---------
        case 'index':
            index();
            break;
        case 'login':
            login();
            break;
        case 'memberConnexion':
            $stayConnect = 'on';
            if (!empty($_POST['pseudo']) && !empty($_POST['password']) && !empty($stayConnect)) {
                $pseudo = valid_datas($_POST['pseudo']);
                $password = valid_datas($_POST['password']);

                userConnection($pseudo, $password, $stayConnect);
            } else {

                $errorMessage = 'Tous les champs ne sont pas remplis';
                throw new Exception('tous les champs ne sont pas remplis !');
            }
            break;
        case 'deconnexion':
            deconnexion();
            break;
        case 'userForm':
            $message = '';
            userForm($message);
            break;
        case 'addUser':
            if (!empty($_POST['pseudo']) && !empty($_POST['password']) && !empty($_POST['confirmPassword']) && !empty($_POST['user_status']) && !empty($_POST['fonction'])) {
                $pseudo = valid_datas($_POST['pseudo']);
                $password = valid_datas($_POST['password']);
                $confirmPassword = valid_datas($_POST['confirmPassword']);
                $user_status = valid_datas($_POST['user_status']);
                $fonction = valid_datas($_POST['fonction']);
                addUser($pseudo, $password, $confirmPassword, $user_status, $fonction);
            } else {
                $message = 'Tous les champs ne sont pas remplis';
                header('Location: index.php?action=userForm');
            }
            break;
        case 'manageUser':
            if (!empty($_GET['user_id'])) {
                $user_id = valid_datas($_GET['user_id']);
                $message = 'Mise à jour de l\'utilisateur';
                manageUser($user_id, $message);
            } else {
                $errorMessage = 'Addresse invalide';
                throw new Exception('Addresse invalide');
            }
            break;

        // ------------------------ M E S S A G E S -------------------
        case 'postMessage':
            if (!empty($_POST['message_text'])) {
                $message_text = valid_datas($_POST['message_text']);
                $message_date = valid_datas($_POST['message_date']);
                $selectedDate = valid_datas($_POST['selectedDate']);

                postMessage($message_text, $message_date, $selectedDate);
            } else {
                $errorMessage = 'Message vide';
                throw new Exception('Message vide');
            }
            break;

        case 'deleteMessage':
            if (!empty($_GET['message_id'])) {
                $message_id = $_GET['message_id'];
                $selectedDate = $_GET['selectedDate'];
                deleteMessage($message_id, $selectedDate);
            } else {
                $errorMessage = 'Erreur : ID du message manquant';
                throw new Exception('ID du message manquant');
            }
            break;

        case 'updateUser':
            if (!empty($_POST['user_id']) && !empty($_POST['pseudo']) && !empty($_POST['password']) && !empty($_POST['user_status']) && !empty($_POST['fonction'])) {
                userValidation();

                $user_id = valid_datas($_POST['user_id']);
                $pseudo = valid_datas($_POST['pseudo']);
                $password = valid_datas($_POST['password']);

                if (empty($_POST['newPassword'] && empty($_POST['confirmNewPassword']))) {
                    $new_password = valid_datas($_POST['newPassword']);
                    $confirm_new_password = valid_datas($_POST['confirmNewPassword']);
                } else {
                    $new_password = valid_datas($_POST['password']);
                    $confirm_new_password = valid_datas($_POST['password']);

                    if ($new_password == $confirm_new_password) {
                    }
                }

                $user_status = valid_datas($_POST['user_status']);
                $fonction = valid_datas($_POST['fonction']);

                if ($_SESSION['user_status'] == 'admin' && userValidation() == 'Allowed') {
                    updateUser($user_id, $pseudo, $password, $user_status, $fonction, $new_password, $confirm_new_password);
                } else {
                    $message = 'Vous n\'avez pas les droits requis';
                    header('Location: index.php?action=userForm');
                }
            }
            break;
        case 'deleteUser':
            if (!empty($_GET['user_id'])) {
                $user_id = $_GET['user_id'];
                deleteUser($user_id);
            } else {
                $message = '';
                userForm($message);
            }
            break;

        // ----------------------- S T A T I S T I C S -----------

        // ------------------------ A R T I C L E S -------------------

        case 'articles':
            userValidation();
            showArticlesForm();
            break;
        case 'articlesFetch':
            $category = $_GET['category'] ?? null;
            $subcategory = $_GET['subcategory'] ?? null;

            if ($category && $subcategory) {
                fetchArticles($category, $subcategory);
            } else {
                echo json_encode(['error' => 'Catégorie ou sous-catégorie manquante.']);
            }
            break;

        case 'articlesFetchByYear':
            $category = $_GET['category'] ?? null;
            $subcategory = $_GET['subcategory'] ?? null;
            $year = $_GET['year'] ?? 'all';

            if ($category && $subcategory) {
                userValidation();
                fetchArticlesByYear($category, $subcategory, $year);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Catégorie ou sous-catégorie manquante.']);
            }
            break;



        case 'statistic':

            if (isset($_GET['date'])) {
                $selectedDate = $_GET['date'];
                $message = 'Statistiques du mois en cours';
                statistic($selectedDate, $message);
            } else {
                $selectedDate = date('Y-m-d');
                $message = 'Statistiques du mois en cours';
                statistic($selectedDate, $message);
            }
            break;

        case 'globalStatistic':

            if (isset($_GET['date'])) {
                $selectedDate = $_GET['date'];
                $message = 'Statistiques globales';

                globalStatistic($selectedDate, $message);
            } else {
                $selectedDate = date('Y-m-d');
                $message = 'Statistiques globales';
                globalStatistic($selectedDate, $message);
            }
            break;

        // ----------------------- R E S U L T S  -----------
        case 'resultPanel':
            $selectedDate = $_GET['date'] ?? date('Y-m-d');
            resultPanel($selectedDate);
            break;
        case 'dayBrocResult':
            if (isset($_GET['date']) && isset($_GET['monthView'])) {
                $selectedDate = $_GET['date'];
                $monthViewCheckbox = $_GET['monthView'];
                $message = 'Résultats du jour';
                dayBrocResult($selectedDate, $monthViewCheckbox, $message,);
            } else {
                $selectedDate = date('Y-m-d');
                $monthViewCheckbox = 'false';
                $message = 'Résultats du jour';
                dayBrocResult($selectedDate, $monthViewCheckbox,  $message);
            }
            break;
        case 'dayFripeResult':
            if (isset($_GET['date']) && isset($_GET['monthView'])) {
                $selectedDate = $_GET['date'];
                $monthViewCheckbox = $_GET['monthView'];
                $message = 'Résultats du jour';
                dayFripeResult($selectedDate, $monthViewCheckbox, $message);
            } else {
                $selectedDate = date('Y-m-d');
                $monthViewCheckbox = 'false';
                $message = 'Résultats du jour';
                dayFripeResult($selectedDate, $monthViewCheckbox, $message);
            }
            break;
        case 'monthBrocResult':
            if (isset($_GET['month']) && isset($_GET['year'])) {
                $month = $_GET['month'];
                $year = $_GET['year'];
                $message = 'Résultats du mois';
                // Construire la date au format YYYY-MM-01
                $selectedDate = $year . '-' . $month . '-01';
                monthBrocResult($selectedDate, $message);
            } else {
                $selectedDate = date('Y-m-01'); // Par défaut, le premier jour du mois en cours
                $message = 'Résultats du mois';
                monthBrocResult($selectedDate, $message);
            }
            break;
        case 'monthFripeResult':
            if (isset($_GET['month']) && isset($_GET['year'])) {
                $month = $_GET['month'];
                $year = $_GET['year'];
                $message = 'Résultats du mois';
                $selectedDate = $year . '-' . $month . '-01';
                monthFripeResult($selectedDate, $message);
            } else {
                $selectedDate = date('Y-m-01'); // Par défaut, le premier jour du mois en cours
                $message = 'Résultats du mois';
                monthFripeResult($selectedDate, $message);
            }
            break;

        // ----------------------- P U R C H A S E S  -----------

        case 'purchasePanel':

            if (isset($_GET['date'])) {
                $selectedDate = $_GET['date'];
                $message = '';
                purchasePanel($selectedDate, $message);
            } else {
                $selectedDate = date('Y-m-d');
                $message = '';
                purchasePanel($selectedDate, $message);
            }
            break;

        case 'newPurchaseForm':
            if (!empty($_GET['purchase_id'])) {
                $purchase_id = $_GET['purchase_id'];
                $message = 'Modification de l\'achat';
                updatePurchaseForm($purchase_id, $message);
            } else {
                $purchase_id = '';
                $message = 'Nouvel achat';
                createPurchaseForm($message);
            }
            break;

        case 'updatePurchase':
            if (!empty($_POST['purchase_id']) && !empty($_POST['purchase_date']) && !empty($_POST['payment_method'])) {
                $purchase_id = $_POST['purchase_id'];
                $purchase_date = $_POST['purchase_date'];
                $provider_id = (!empty($_POST['provider_id']) && is_numeric($_POST['provider_id'])) ? intval($_POST['provider_id']) : null;
                $paid_amount = valid_datas($_POST['paid_amount']);
                $payment_method = $_POST['payment_method'];
                $message = "Modification de l'achat";
                $comment = valid_datas($_POST['comment'] ?? '');

                updatePurchase($purchase_id, $purchase_date, $provider_id, $paid_amount, $payment_method, $comment, $message);
            } else {
                $message = 'Certains champs sont manquants';
                $selectedDate = date('Y-m-d');
                purchasePanel($selectedDate, $message);
            }
            break;


        case 'addPurchase':
            if (!empty($_POST['purchase_date']) && !empty($_POST['payment_method'])) {
                $purchase_date = valid_datas($_POST['purchase_date']);
                $paid_amount = empty($_POST['paid_amount']) ? 0 : floatval(valid_datas($_POST['paid_amount']));
                $payment_method = valid_datas($_POST['payment_method']);
                $provider_id = (!empty($_POST['provider_id']) && is_numeric($_POST['provider_id'])) ? intval($_POST['provider_id']) : null;
                $comment = valid_datas($_POST['comment'] ?? '');

                addPurchase($purchase_date, $provider_id, $paid_amount, $payment_method, $comment);
            } else {
                $message = 'Certains champs sont manquants';
                createPurchaseForm($message);
            }
            break;

        case 'deletePurchase':
            if (!empty($_GET['purchase_id'])) {
                $purchase_id = $_GET['purchase_id'];
                deletePurchase($purchase_id);
            } else {
                $message = 'Erreur : ID de la vente manquant';
                $selectedDate = date('Y-m-d');
                purchasePanel($selectedDate, $message);
            }
            break;
        //------------------------ S A L E S ----------------------------
        case 'salePanel':

            if (isset($_GET['date'])) {
                $selectedDate = $_GET['date'];
                $message = '';
                salePanel($selectedDate, $message);
            } else {
                $selectedDate = date('Y-m-d');
                $message = '';
                salePanel($selectedDate, $message);
            }
            break;
        case 'newSaleForm':
            if (!empty($_GET['sale_id'])) {
                $sale_id = $_GET['sale_id'];
                $message = 'Modification de la vente';
                updateForm($sale_id, $message);
            } else {
                $selectedDate = !empty($_POST['selectedDate']) ? $_POST['selectedDate']
                    : (!empty($_GET['selectedDate']) ? $_GET['selectedDate'] : date('Y-m-d'));
                $sale_id = '';
                $message = 'Nouvelle vente';
                createForm($message, $selectedDate);
            }
            break;
        case 'addSale':
            if (!empty($_POST['sale_id'])) {
                if (!empty($_POST['sale_date']) && !empty($_POST['total_amount']) && !empty($_POST['payment_method'])) {
                    $sale_id = valid_datas($_POST['sale_id']);
                    $sale_date = valid_datas($_POST['sale_date']);
                    $total_amount = valid_datas($_POST['total_amount']);
                    $payment_method = valid_datas($_POST['payment_method']);
                    $client_id = valid_datas($_POST['client_id']);
                    updateSale($sale_id, $sale_date, $total_amount, $payment_method, $client_id);
                } else {
                    $message = 'Certains champs sont manquants';
                    $selectedDate = date('Y-m-d');
                    salePanel($selectedDate, $message);
                }
            } else {
                // Ajout d'une nouvelle vente
                if (!empty($_POST['sale_date']) && !empty($_POST['payment_method'])) {
                    $sale_date = valid_datas($_POST['sale_date']);
                    $payment_method = valid_datas($_POST['payment_method']);
                    $new_sale_id = addSale($sale_date, 0, $payment_method);
                    header('Location: index.php?action=newSaleForm&sale_id=' . (int)$new_sale_id . '&created=1');
                    exit;
                } else {
                }
            }
            break;
        case 'deleteSale':
            if (!empty($_GET['sale_id'])) {
                $sale_id = $_GET['sale_id'];
                deleteSale($sale_id);
            } else {
                $message = 'Erreur : ID de la vente manquant';
                $selectedDate = date('Y-m-d');
                salePanel($selectedDate, $message);
            }
            break;
        case 'exportSale':
            if (!empty($_GET['sale_id'])) {
                $sale_id = (int)$_GET['sale_id'];
                exportSale($sale_id);
            } else {
                $message = 'Erreur : ID de la vente manquant';
                $selectedDate = date('Y-m-d');
                salePanel($selectedDate, $message);
            }
            break;
        case 'ardoisePanel':
            $message = '';
            ardoisePanel($message);
            break;
        case 'stockPanel':
            $message = '';
            stockPanel($message);
            break;
        case 'stockAdjustAjax':
            stockAdjustAjax();
            break;
        case 'stockAddEntry':
            stockAddEntry();
            break;
        // -------------------- C L O S U R E S -------------------------
        case 'closurePanel':
            if (isset($_GET['date'])) {
                closurePanel($_GET['date'], $_GET['message'] ?? '');
            } else {
                closurePanel();
            }
            break;
        case 'generateDailyClosure':
            generateDailyClosure();
            break;
        case 'apiGetClosureForDate':
            apiGetClosureForDate();
            break;
        case 'apiLockClosure':
            apiLockClosure();
            break;
        case 'apiUpdateClosureNotesCash':
            apiUpdateClosureNotesCash();
            break;
        case 'apiLogClosureExport':
            apiLogClosureExport();
            break;
        case 'apiGetClosureAudit':
            apiGetClosureAudit();
            break;
        // -------------------- C B  B A L A N C E -----------------------
        case 'cbBalancePanel':
            cbBalancePanel();
            break;
        case 'apiCbBalanceData':
            header('Content-Type: application/json');
            apiCbBalanceData();
            break;
        case 'apiRecordTransfer':
            header('Content-Type: application/json');
            apiRecordTransfer();
            break;
        case 'apiCbDocument':
            apiCbDocument();
            break;
        // -------------------- U R S S A F -----------------------------
        case 'urssafPanel':
            urssafPanel();
            break;
        case 'apiUrssafDeclare':
            header('Content-Type: application/json');
            apiUrssafDeclare();
            break;
        // -------------------- A R T I C L E -------------------------
        case 'saveArticle':
            // Vérifier si les clés existent dans $_POST
            if (isset($_POST['purchase_id'])) {
                if (isset($_POST['category'], $_POST['subcategory'], $_POST['quantity'], $_POST['unit_price'])) {
                    $purchase_id = $_POST['purchase_id'];
                    $category = $_POST['category'];
                    $subcategory = $_POST['subcategory'];
                    $price = $_POST['unit_price'];
                    if (isset($_POST['comment'])) {
                        $comment = $_POST['comment'];
                    } else {
                        $comment = '';
                    }

                    $quantity = $_POST['quantity'];
                    // Traitement des données de l'article
                    savePurchaseArticle($purchase_id, $category, $subcategory, $quantity, $price, $comment);
                } else {
                    // Gérer le cas où des données sont manquantes
                    echo 'Erreur : Données manquantes';
                }
            } else if (isset($_POST['sale_id'])) {
                if (isset($_POST['category'], $_POST['subcategory'], $_POST['quantity'], $_POST['unit_price'])) {
                    $sale_id = $_POST['sale_id'];
                    $category = $_POST['category'];
                    $subcategory = $_POST['subcategory'];
                    $price = $_POST['unit_price'];
                    if (isset($_POST['comment'])) {
                        $comment = $_POST['comment'];
                    } else {
                        $comment = '';
                    }

                    $quantity = $_POST['quantity'];
                    // Traitement des données de l'article
                    saveSaleArticle($sale_id, $category, $subcategory, $quantity, $price, $comment);
                } else {
                    // Gérer le cas où des données sont manquantes
                    echo 'Erreur : Données manquantes';
                }
            } else {
                // Gérer le cas où les clés ne sont pas définies
                echo 'Erreur : ID de la vente ou achat manquant';
            }

            break;
        case 'updateArticle':
            if (isset($_POST['article_id'], $_POST['quantity'], $_POST['unit_price'])) {
                if (isset($_POST['purchase_id'])) {
                    $purchase_id = $_POST['purchase_id'];
                    $article_id = $_POST['article_id'];
                    $quantity = $_POST['quantity'];
                    $price = $_POST['unit_price'];
                    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
                    updatePurchaseArticle($purchase_id, $article_id, $quantity, $price, $comment);
                } else if (isset($_POST['sale_id'])) {
                    $sale_id = $_POST['sale_id'];
                    $article_id = $_POST['article_id'];
                    $quantity = $_POST['quantity'];
                    $price = $_POST['unit_price'];
                    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
                    updateSaleArticle($sale_id, $article_id, $quantity, $price, $comment);
                }
            } else {
                echo 'Erreur : Données manquantes';
            }
            break;
        case 'deleteArticle':

            if (isset($_POST['purchase_id'])) {
                $purchase_id = $_POST['purchase_id'];
                $article_id = $_POST['article_id'];

                deletePurchaseArticle($purchase_id, $article_id);
                // Maintenant, vous pouvez appeler votre fonction de suppression d'article avec $article_id et $purchase_id
            } else if (isset($_POST['sale_id'])) {
                $sale_id = $_POST['sale_id'];
                $article_id = $_POST['article_id'];

                deleteSaleArticle($sale_id, $article_id);
                // Maintenant, vous pouvez appeler votre fonction de suppression d'article avec $article_id et $sale_id
            } else {
                echo 'Erreur : Données manquantes';
            }
            break;

        // -------------------- A J A X  A P I -------------------------
        case 'apiSaveArticle':
            header('Content-Type: application/json');
            if (isset($_POST['sale_id'], $_POST['category'], $_POST['subcategory'], $_POST['quantity'], $_POST['unit_price'])) {
                $sale_id = $_POST['sale_id'];
                $category = $_POST['category'];
                $subcategory = $_POST['subcategory'];
                $quantity = $_POST['quantity'];
                $price = $_POST['unit_price'];
                $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
                saveSaleArticleAjax($sale_id, $category, $subcategory, $quantity, $price, $comment);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Données manquantes']);
            }
            break;
        case 'apiDeleteArticle':
            header('Content-Type: application/json');
            if (isset($_POST['sale_id'], $_POST['article_id'])) {
                $sale_id = $_POST['sale_id'];
                $article_id = $_POST['article_id'];
                deleteSaleArticleAjax($sale_id, $article_id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Données manquantes']);
            }
            break;
        case 'apiGetSaleArticles':
            if (isset($_GET['sale_id'])) {
                getSaleArticlesJson($_GET['sale_id']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'sale_id manquant']);
            }
            break;
        case 'apiGetSale':
            if (isset($_GET['sale_id'])) {
                getSaleJson($_GET['sale_id']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'sale_id manquant']);
            }
            break;
        case 'apiUpdateArticle':
            header('Content-Type: application/json');
            if (isset($_POST['article_id'], $_POST['quantity'], $_POST['unit_price'])) {
                $article_id = $_POST['article_id'];
                $quantity = $_POST['quantity'];
                $price = $_POST['unit_price'];
                $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
                updateSaleArticleAjax($article_id, $quantity, $price, $comment);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Données manquantes']);
            }
            break;
        case 'apiUpdateSale':
            header('Content-Type: application/json');
            try {
                if (isset($_POST['sale_id'])) {
                    $sale_id = $_POST['sale_id'];
                    $sale_date = isset($_POST['sale_date']) ? $_POST['sale_date'] : null;
                    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : null;
                    $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : null;
                    updateSaleAjax($sale_id, $sale_date, $payment_method, $client_id);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'sale_id manquant']);
                }
            } catch (Throwable $e) {
                $logFile = __DIR__ . '/logs/debug_ajax.log';
                @mkdir(dirname($logFile), 0755, true);
                file_put_contents($logFile, date('c') . ' ERR ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;

        // -------------------- A J A X  A P I   P U R C H A S E -------------------------
        case 'apiSavePurchaseArticle':
            header('Content-Type: application/json');
            if (isset($_POST['purchase_id'], $_POST['category'], $_POST['subcategory'], $_POST['quantity'], $_POST['unit_price'])) {
                $purchase_id = $_POST['purchase_id'];
                $category = $_POST['category'];
                $subcategory = $_POST['subcategory'];
                $quantity = $_POST['quantity'];
                $price = $_POST['unit_price'];
                $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
                apiSavePurchaseArticle($purchase_id, $category, $subcategory, $quantity, $price, $comment);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Données manquantes']);
            }
            break;

        case 'apiDeletePurchaseArticle':
            header('Content-Type: application/json');
            if (isset($_POST['article_id'])) {
                $article_id = $_POST['article_id'];
                apiDeletePurchaseArticle($article_id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'article_id manquant']);
            }
            break;

        case 'apiUpdatePurchaseArticle':
            header('Content-Type: application/json');
            if (isset($_POST['article_id'], $_POST['quantity'], $_POST['unit_price'])) {
                $article_id = $_POST['article_id'];
                $quantity = $_POST['quantity'];
                $price = $_POST['unit_price'];
                $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
                apiUpdatePurchaseArticle($article_id, $quantity, $price, $comment);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Données manquantes']);
            }
            break;

        case 'apiUpdatePurchase':
            header('Content-Type: application/json');
            try {
                if (isset($_POST['purchase_id'])) {
                    $purchase_id = $_POST['purchase_id'];
                    $purchase_date = isset($_POST['purchase_date']) ? $_POST['purchase_date'] : null;
                    $provider_id = isset($_POST['provider_id']) ? $_POST['provider_id'] : null;
                    $paid_amount = isset($_POST['paid_amount']) ? $_POST['paid_amount'] : null;
                    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : null;
                    $comment = isset($_POST['comment']) ? $_POST['comment'] : null;
                    apiUpdatePurchase($purchase_id, $purchase_date, $provider_id, $paid_amount, $payment_method, $comment);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'purchase_id manquant']);
                }
            } catch (Throwable $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;

        case 'apiAddProvider':
            header('Content-Type: application/json');
            apiAddProvider();
            break;
        case 'apiCreateClient':
            header('Content-Type: application/json');
            if (isset($_POST['name'])) {
                $name = $_POST['name'];
                $phone = isset($_POST['phone']) ? $_POST['phone'] : null;
                $clientManager = new \OpenClassroom\Blog\Model\ClientsManager();
                $newId = $clientManager->createClient($name, $phone);
                echo json_encode(['success' => true, 'client_id' => $newId, 'name' => $name]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Nom manquant']);
            }
            break;
        // -------------------------------------------------------------
        default:
            index();
    }
} catch (Exception $e) {
    require('view/error/errorView.php');
}
