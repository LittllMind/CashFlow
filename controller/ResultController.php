<?php

require_once('model/ArticlesManager.php');
require_once('model/ClosuresManager.php');

use \OpenClassroom\Blog\Model\ArticlesManager;
use \OpenClassroom\Blog\Model\ClosuresManager;
use \OpenClassroom\Blog\Model\PurchasesManager;
use \OpenClassroom\Blog\Model\SalesManager;
use \OpenClassroom\Blog\Model\UsersManager;


function dayFripeResult($selectedDate, $monthViewCheckbox, $message)
{
    // Vérification de l'authentification de l'utilisateur
    if (!isset($_SESSION['user_status']) || $_SESSION['user_status'] == 'RGPD') {
        header('Location: index.php?action=home');
        exit;
    }

    // recuperation des articles vendus ce jour et tri des articles en deux listes distinctes v^étements et brocante

    $articlesManager = new ArticlesManager();
    $salesManager = new SalesManager();
    $purchasesManager = new PurchasesManager();

    if ($monthViewCheckbox === 'false') {
        // Si la case n'est pas cochée on recupere les articles du jour séléctionné
        $articles = $articlesManager->getArticlesByDateOnDayResult($selectedDate);
    } else {
        // Sinon, on recupere les articles du mois de la date selectionée
        $articles = $articlesManager->getArticlesByMonthOnDayResult($selectedDate);
    }

    $womenClothes = array_filter($articles, function ($article) {
        return $article['category'] == 'Femmes';
    });
    $menClothes = array_filter($articles, function ($article) {
        return $article['category'] == 'Hommes';
    });
    $childrenClothes = array_filter($articles, function ($article) {
        return $article['category'] == 'Enfants';
    });
    $Accessories = array_filter($articles, function ($article) {
        return $article['category'] == 'Accessoires';
    });

    // Cette liste concerne les vêtements toutes catégories confondues :
    $filteredArticles = array_merge($womenClothes, $menClothes, $childrenClothes, $Accessories);
    // On détaille les montants totaux pour chaque catégorie de paiement BROCANTE pour chaque sales du jour
    // chaque article est lié a une sales qui comporte le payment_method
    // on prend le montant de l'article et on le multiplie par la quantité vendue et on recupere la methode de paiement dans sales

    $salesCount = 0;
    $totalCardSales = 0;
    $totalCashSales = 0;
    $totalCheckSales = 0;
    $totalSales = 0;

    $purchasesCount = 0;
    $totalCardPurchases = 0;
    $totalCashPurchases = 0;
    $totalCheckPurchases = 0;
    $totalPurchase = 0;

    $purchase_id = 0;
    $sale_id = 0;
    foreach ($filteredArticles as $item) {

        if (isset($item['sale_id']) && !empty($item['sale_id'])) {

            if ($item['sale_id'] !== $sale_id) {
                $sale_id = $item['sale_id'];
                $salesCount++;
            }

            // On récupère la vente associée à l'article
            $sale = $salesManager->getSaleById($item['sale_id']);
            if ($sale) {
                if ($sale['payment_method'] == 'card') {
                    $totalCardSales += $item['unit_price'] * $item['quantity'];
                } elseif ($sale['payment_method'] == 'cash') {
                    $totalCashSales += $item['unit_price'] * $item['quantity'];
                } elseif ($sale['payment_method'] == 'check') {
                    $totalCheckSales += $item['unit_price'] * $item['quantity'];
                }
            }
        } else if (isset($item['purchase_id']) && !empty($item['purchase_id'])) {

            if ($item['purchase_id'] !== $purchase_id) {
                $purchase_id = $item['purchase_id'];
                $purchasesCount++;
            }

            // Si l'article n'est pas associé à un achat, on continue
            $purchase = $purchasesManager->getPurchaseById($item['purchase_id']);
            if ($purchase) {
                if ($purchase['payment_method'] == 'card') {
                    $totalCardPurchases += $item['unit_price'] * $item['quantity'];
                } elseif ($purchase['payment_method'] == 'cash') {
                    $totalCashPurchases += $item['unit_price'] * $item['quantity'];
                } elseif ($purchase['payment_method'] == 'check') {
                    $totalCheckPurchases += $item['unit_price'] * $item['quantity'];
                }
            }
        }
    }

    // RESUME JOURNALIER
    $totalSales = $totalCardSales + $totalCashSales + $totalCheckSales;
    $totalPurchases = $totalCardPurchases + $totalCashPurchases + $totalCheckPurchases;

    // date au format dd/month/yyyy
    $formattedDate = date('d/m/Y', strtotime($selectedDate));
    // heure d'impression heur de paris (now):
    $printTime = date('H:i', time() + 3600); // Ajout d'une heure pour l'heure de Paris

    $entityName = 'FRIPE';

    $titleDate = date('d-m-Y', strtotime($selectedDate));

    $previousDate = (new DateTime($selectedDate))->modify('-1 day')->format('Y-m-d');
    $nextDate = (new DateTime($selectedDate))->modify('+1 day')->format('Y-m-d');


    $title = $titleDate . " - " . $entityName;
    $jsFile = 'result/result.js';

    require('view/result/dayResult.php');
}

function monthFripeResult($selectedDate, $message)
{
    // Vérification de l'authentification de l'utilisateur
    if (!isset($_SESSION['user_status']) || $_SESSION['user_status'] == 'RGPD') {
        header('Location: index.php?action=home');
        exit;
    }

    $owner = 'fripe';
    $entityName = 'FRIPE';

    if (empty($selectedDate)) {
        $selectedDate = date('Y-m-d');
    }

    $yearMonth = date('Y-m', strtotime($selectedDate));
    $firstDayOfMonth = $yearMonth . '-01';

    $closuresManager = new ClosuresManager();
    $monthly = $closuresManager->getMonthlyClosures($owner, $selectedDate);
    $unclosedDays = $closuresManager->getUnclosedDays([$owner], $firstDayOfMonth, date('Y-m-t', strtotime($selectedDate)));

    $formattedDate = date('d/m/Y', strtotime($selectedDate));
    $printTime = date('H:i', time()); // Heure serveur locale
    $titleDate = date('d-m-Y', strtotime($selectedDate));
    $previousDate = (new DateTime($selectedDate))->modify('-1 month')->format('Y-m-d');
    $nextDate = (new DateTime($selectedDate))->modify('+1 month')->format('Y-m-d');
    $title = 'Fripe — mois de ' . date('m/Y', strtotime($selectedDate));

    $jsFile = 'result/monthFripeResult.js';
    $closuresJson = json_encode($monthly['closures'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    require('view/result/monthFripeResult.php');
}



function dayBrocResult($selectedDate, $monthViewCheckbox, $message)
{
    // Vérification de l'authentification de l'utilisateur
    if (!isset($_SESSION['user_status']) || $_SESSION['user_status'] == 'RGPD') {
        header('Location: index.php?action=home');
        exit;
    }

    $articlesManager = new ArticlesManager();
    $salesManager = new SalesManager();
    $purchasesManager = new PurchasesManager();

    if ($monthViewCheckbox === 'false') {
        // Si la case n'est pas cochée on recupere les articles du jour séléctionné
        $articles = $articlesManager->getArticlesByDateOnDayResult($selectedDate);
    } else {
        // Sinon, on recupere les articles du mois de la date selectionée
        $articles = $articlesManager->getArticlesByMonthOnDayResult($selectedDate);
    }

    $womenClothes = array_filter($articles, function ($article) {
        return $article['category'] == 'Femmes';
    });
    $menClothes = array_filter($articles, function ($article) {
        return $article['category'] == 'Hommes';
    });
    $childrenClothes = array_filter($articles, function ($article) {
        return $article['category'] == 'Enfants';
    });
    $Accessories = array_filter($articles, function ($article) {
        return $article['category'] == 'Accessoires';
    });


    $filteredArticles = array_filter($articles, function ($article) {
        return !in_array($article['category'], ['Femmes', 'Hommes', 'Enfants', 'Accessoires']);
    });

    // On détaille les montants totaux pour chaque catégorie de paiement BROCANTE pour chaque sales du jour
    // chaque article est lié a une sales qui comporte le payment_method
    // on prend le montant de l'article et on le multiplie par la quantité vendue et on recupere la methode de paiement dans sales

    $salesCount = 0;
    $totalCardSales = 0;
    $totalCashSales = 0;
    $totalCheckSales = 0;
    $totalSales = 0;

    $purchasesCount = 0;
    $totalCardPurchases = 0;
    $totalCashPurchases = 0;
    $totalCheckPurchases = 0;
    $totalPurchase = 0;

    $purchase_id = 0;
    $sale_id = 0;
    foreach ($filteredArticles as $item) {

        if (isset($item['sale_id']) && !empty($item['sale_id'])) {

            if ($item['sale_id'] !== $sale_id) {
                $sale_id = $item['sale_id'];
                $salesCount++;
            }

            // On récupère la vente associée à l'article
            $sale = $salesManager->getSaleById($item['sale_id']);
            if ($sale) {
                if ($sale['payment_method'] == 'card') {
                    $totalCardSales += $item['unit_price'] * $item['quantity'];
                } elseif ($sale['payment_method'] == 'cash') {
                    $totalCashSales += $item['unit_price'] * $item['quantity'];
                } elseif ($sale['payment_method'] == 'check') {
                    $totalCheckSales += $item['unit_price'] * $item['quantity'];
                }
            }
        } else if (isset($item['purchase_id']) && !empty($item['purchase_id'])) {

            if ($item['purchase_id'] !== $purchase_id) {
                $purchase_id = $item['purchase_id'];
                $purchasesCount++;
            }

            // Si l'article n'est pas associé à un achat, on continue
            $purchase = $purchasesManager->getPurchaseById($item['purchase_id']);
            if ($purchase) {
                if ($purchase['payment_method'] == 'card') {
                    $totalCardPurchases += $item['unit_price'] * $item['quantity'];
                } elseif ($purchase['payment_method'] == 'cash') {
                    $totalCashPurchases += $item['unit_price'] * $item['quantity'];
                } elseif ($purchase['payment_method'] == 'check') {
                    $totalCheckPurchases += $item['unit_price'] * $item['quantity'];
                }
            }
        }
    }

    // RESUME JOURNALIER
    $totalSales = $totalCardSales + $totalCashSales + $totalCheckSales;
    $totalPurchases = $totalCardPurchases + $totalCashPurchases + $totalCheckPurchases;

    // date au format dd/month/yyyy
    $formattedDate = date('d/m/Y', strtotime($selectedDate));
    // heure d'impression heur de paris (now):
    $printTime = date('H:i', time() + 3600); // Ajout d'une heure pour l'heure de Paris

    $entityName = 'BROC';

    $titleDate = date('d-m-Y', strtotime($selectedDate));

    $previousDate = (new DateTime($selectedDate))->modify('-1 day')->format('Y-m-d');
    $nextDate = (new DateTime($selectedDate))->modify('+1 day')->format('Y-m-d');


    $title = $titleDate . " - " . $entityName;
    // On charge le fichier JS pour la page de résultat
    $jsFile = 'result/result.js';

    require('view/result/dayResult.php');
}

/**
 * Page résultats unifiée BROC / MIXTE / FRIPE avec Jour/Semaine/Mois.
 */
function resultPanel($selectedDate = null)
{
    if (!isset($_SESSION['user_status']) || $_SESSION['user_status'] == 'RGPD') {
        header('Location: index.php?action=home');
        exit;
    }

    if (empty($selectedDate)) {
        $selectedDate = date('Y-m-d');
    }

    require_once('model/UrssafManager.php');
    $urssafManager = new \OpenClassroom\Blog\Model\UrssafManager();

    $ts = strtotime($selectedDate);

    $ranges = [
        'day'   => [$selectedDate, $selectedDate, date('d/m/Y', $ts)],
        'week'  => [
            date('Y-m-d', strtotime('monday this week', $ts)),
            date('Y-m-d', strtotime('sunday this week', $ts)),
            'Sem. du ' . date('d/m', strtotime('monday this week', $ts)) . ' au ' . date('d/m', strtotime('sunday this week', $ts))
        ],
        'month' => [
            date('Y-m-01', $ts),
            date('Y-m-t', $ts),
            date('m/Y', $ts)
        ],
    ];

    $data = [];
    foreach ($ranges as $period => $range) {
        $broc  = $urssafManager->getPeriodCaArticles('broc', $range[0], $range[1]);
        $fripe = $urssafManager->getPeriodCaArticles('fripe',     $range[0], $range[1]);
        $mixte = [
            'total_ca'            => $broc['total_ca']  + $fripe['total_ca'],
            'nb_sales'            => $broc['nb_sales']  + $fripe['nb_sales'],
            'nb_articles'         => $broc['nb_articles'] + $fripe['nb_articles'],
            'total_cash'          => $broc['total_cash'] + $fripe['total_cash'],
            'total_card'          => $broc['total_card'] + $fripe['total_card'],
            'total_cheque'        => $broc['total_cheque'] + $fripe['total_cheque'],
            'total_bank_transfer' => $broc['total_bank_transfer'] + $fripe['total_bank_transfer'],
        ];
        $data[$period] = [
            'label' => $range[2],
            'start' => $range[0],
            'end'   => $range[1],
            'broc'  => $broc,
            'fripe' => $fripe,
            'mixte' => $mixte,
        ];
    }

    $prevDate = (new DateTime($selectedDate))->modify('-1 day')->format('Y-m-d');
    $nextDate = (new DateTime($selectedDate))->modify('+1 day')->format('Y-m-d');

    $title = 'Résultats — ' . date('d/m/Y', $ts);
    require('view/result/resultPanel.php');
}

// function monthBrocResult($selectedDate, $monthViewCheckbox, $message)
// {
//     // Vérification de l'authentification de l'utilisateur
//     if (!isset($_SESSION['user_status']) || $_SESSION['user_status'] == 'RGPD') {
//         header('Location: index.php?action=home');
//         exit;
//     }

//     $articlesManager = new ArticlesManager();
//     $salesManager = new SalesManager();
//     $purchasesManager = new PurchasesManager();

//     // On recupere les articles du mois de la date selectionée
//     $articles = $articlesManager->getArticlesByMonthOnDayResult($selectedDate);

//     $womenClothes = array_filter($articles, function ($article) {
//         return $article['category'] == 'Femmes';
//     });
//     $menClothes = array_filter($articles, function ($article) {
//         return $article['category'] == 'Hommes';
//     });
//     $childrenClothes = array_filter($articles, function ($article) {
//         return $article['category'] == 'Enfants';
//     });
//     $Accessories = array_filter($articles, function ($article) {
//         return $article['category'] == 'Accessoires';
//     });

//     // Cette liste concerne la brocante :
//     $filteredArticles = array_filter($articles, function ($article) {
//         return !in_array($article['category'], ['Femmes', 'Hommes', 'Enfants', 'Accessoires']);
//     });

//     // On détaille les montants totaux pour chaque catégorie de paiement BROCANTE pour chaque sales du jour
//     // chaque article est lié a une sales qui comporte le payment_method
//     // on prend le montant de l'article et on le multiplie par la quantité vendue et on recupere la methode de paiement dans sales

//     $salesCount = 0;
//     $totalCardSales = 0;
//     $totalCashSales = 0;
//     $totalCheckSales = 0;
//     $totalSales = 0;

//     $purchasesCount = 0;
//     $totalCardPurchases = 0;
//     $totalCashPurchases = 0;
//     $totalCheckPurchases = 0;
//     $totalPurchase = 0;

//     foreach ($filteredArticles as &$item) { // ← Important : passer par référence &

//         $item['payment_method'] = null; // Valeur par défaut

//         if (!empty($item['sale_id'])) {
//             $sale = $salesManager->getSaleById($item['sale_id']);
//             if ($sale) {
//                 $item['payment_method'] = $sale['payment_method']; // 'card', 'cash' ou 'check'
//             }
//         } elseif (!empty($item['purchase_id'])) {
//             $purchase = $purchasesManager->getPurchaseById($item['purchase_id']);
//             if ($purchase) {
//                 $item['payment_method'] = $purchase['payment_method']; // 'card', 'cash' ou 'check'
//             }
//         }
//     }
//     // RESUME JOURNALIER
//     $totalSales = $totalCardSales + $totalCashSales + $totalCheckSales

//         ;
//     $totalPurchases = $totalCardPurchases + $totalCashPurchases + $totalCheckPurchases;
//     // date au format dd/month/yyyy
//     $formattedDate = date('d/m/Y', strtotime($selectedDate));
//     // heure d'impression heur de paris (now):
//     $printTime = date('H:i', time() + 3600); // Ajout d'une heure pour l'heure de Paris
//     $entityName = 'BROC';
//     $titleDate = date('d-m-Y', strtotime($selectedDate));
//     $previousDate = (new DateTime($selectedDate))->modify('-1 day')->format('Y-m-d');
//     $nextDate = (new DateTime($selectedDate))->modify('+1 day')->format('Y-m-d');
//     $title = $titleDate . " - " . $entityName;
//     // On charge le fichier JS pour la page de résultat
//     $jsFile = 'result/monthResult.js';
//     $jsonArticles = json_encode($filteredArticles);
//     require('view/result/monthResult.php');
// }

function monthBrocResult($selectedDate, $message)
{
    // Vérification de l'authentification de l'utilisateur
    if (!isset($_SESSION['user_status']) || $_SESSION['user_status'] == 'RGPD') {
        header('Location: index.php?action=home');
        exit;
    }

    $owner = 'broc';
    $entityName = 'BROC';

    if (empty($selectedDate)) {
        $selectedDate = date('Y-m-d');
    }

    $yearMonth = date('Y-m', strtotime($selectedDate));
    $firstDayOfMonth = $yearMonth . '-01';

    $closuresManager = new ClosuresManager();
    $monthly = $closuresManager->getMonthlyClosures($owner, $selectedDate);
    $unclosedDays = $closuresManager->getUnclosedDays([$owner], $firstDayOfMonth, date('Y-m-t', strtotime($selectedDate)));

    $formattedDate = date('d/m/Y', strtotime($selectedDate));
    $printTime = date('H:i', time()); // Heure serveur locale
    $titleDate = date('d-m-Y', strtotime($selectedDate));
    $previousDate = (new DateTime($selectedDate))->modify('-1 month')->format('Y-m-d');
    $nextDate = (new DateTime($selectedDate))->modify('+1 month')->format('Y-m-d');
    $title = 'Brocante — mois de ' . date('m/Y', strtotime($selectedDate));

    $jsFile = 'result/monthBrocResult.js';
    $closuresJson = json_encode($monthly['closures'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    require('view/result/monthBrocResult.php');
}
