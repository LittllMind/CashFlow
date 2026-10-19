<?php

namespace OpenClassroom\Blog\Model;

require_once("model/BddManager.php");

use PDO;

class PurchasesManager extends BddManager
{

    // ------------------------ P O S T

    public function postPurchase($purchase_date, $provider_id, $paid_amount, $payment_method, $included, $created_at, $updated_at, $comment)
    {

        $total_amount = 0;
        $bdd = $this->dbConnect();
        $purchase = $bdd->prepare(
            'INSERT INTO purchases(purchase_date, provider_id, total_amount, paid_amount, payment_method, included, created_at, updated_at, comment)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $createdPurchase = $purchase->execute(array($purchase_date, $provider_id, $total_amount, $paid_amount, $payment_method, $included, $created_at, $updated_at, $comment));

        return $createdPurchase;
    }

    // ------------------------ G E T
    public function getPurchasesByDate($selectedDate)
    {
        $dateStart = date('Y-m-d 00:00:00', strtotime($selectedDate));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime($selectedDate . ' +1 day'));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            '
        SELECT p.id, DATE_FORMAT(p.purchase_date, "%d/%m") AS purchase_date, DATE_FORMAT(p.purchase_date, "%H:%i") AS purchase_time, DATE_FORMAT(p.purchase_date, "%Y-%m-%d") AS formatted_purchase_date,
        p.total_amount, COUNT(a.id) AS article_count
        FROM purchases p
        LEFT JOIN articles a ON p.id = a.purchase_id
        WHERE p.purchase_date >= :dateStart AND p.purchase_date < :dateEnd
        GROUP BY p.id
        ORDER BY p.purchase_date DESC'
        );

        $req->execute(['dateStart' => $dateStart, 'dateEnd' => $dateEnd]);
        return $req;
    }


    function getPurchasesByMonth($selectedMonth, $selectedYear)
    {
        $dateStart = sprintf('%04d-%02d-01 00:00:00', $selectedYear, $selectedMonth);
        $dateEnd   = date('Y-m-d 00:00:00', strtotime("$dateStart +1 month"));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare('SELECT 
        p.id, 
        DATE_FORMAT(p.purchase_date, "%d/%m") AS purchase_date, 
        DATE_FORMAT(p.purchase_date, "%H:%i") AS purchase_time, 
        DATE_FORMAT(p.purchase_date, "%Y-%m-%d") AS formatted_purchase_date,
        p.total_amount, 
        p.payment_method,
        p.provider_id,
        pr.name AS provider_name,
        p.included, 
        p.created_at, 
        p.updated_at
    FROM 
        purchases p
    LEFT JOIN 
        providers pr ON p.provider_id = pr.id
    WHERE 
        p.purchase_date >= :dateStart AND p.purchase_date < :dateEnd
    ORDER BY 
        p.purchase_date DESC');

        $req->execute(array('dateStart' => $dateStart, 'dateEnd' => $dateEnd));
        return $req;
    }

    public function getPurchasesOfTheDay($selectedDate)
    {
        $dateStart = date('Y-m-d 00:00:00', strtotime($selectedDate));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime($selectedDate . ' +1 day'));

        $bdd = $this->dbConnect();

        // Préparez la requête SQL
        $req = $bdd->prepare(
            'SELECT p.id, 
                DATE_FORMAT(p.purchase_date, "%d/%m") AS purchase_date, 
                DATE_FORMAT(p.purchase_date, "%H:%i") AS purchase_time, 
                DATE_FORMAT(p.purchase_date, "%Y-%m-%d") AS formatted_purchase_date,
                p.total_amount,
                p.payment_method,
                p.provider_id,
                pr.name AS provider_name,
                p.included,
                p.created_at,
                p.updated_at,
                p.comment
         FROM purchases p
         LEFT JOIN providers pr ON p.provider_id = pr.id
         WHERE p.purchase_date >= :dateStart AND p.purchase_date < :dateEnd
         ORDER BY p.purchase_date DESC'
        );

        $req->execute(['dateStart' => $dateStart, 'dateEnd' => $dateEnd]);
        return $req;
    }



    public function getTotalPurchasesAmount()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT SUM(total_amount) AS total_purchases_amount
            FROM purchases'
        );

        $totalPurchasesAmount = $req->fetch();
        return $totalPurchasesAmount;
    }


    public function getPurchasesByYear($selectedYear)
    {
        $dateStart = sprintf('%04d-01-01 00:00:00', $selectedYear);
        $dateEnd   = sprintf('%04d-01-01 00:00:00', $selectedYear + 1);

        $bdd = $this->dbConnect();
        $req = $bdd->prepare('SELECT 
        p.id, 
        DATE_FORMAT(p.purchase_date, "%d/%m") AS purchase_date, 
        DATE_FORMAT(p.purchase_date, "%H:%i") AS purchase_time, 
        DATE_FORMAT(p.purchase_date, "%Y-%m-%d") AS formatted_purchase_date,
        p.total_amount, 
        p.payment_method,
        p.provider_id,
        pr.name AS provider_name,
        p.included, 
        p.created_at, 
        p.updated_at
    FROM 
        purchases p
    LEFT JOIN 
        providers pr ON p.provider_id = pr.id
    WHERE 
        p.purchase_date >= :dateStart AND p.purchase_date < :dateEnd
    ORDER BY 
        p.purchase_date DESC');

        $req->execute(array('dateStart' => $dateStart, 'dateEnd' => $dateEnd));
        return $req;
    }


    public function getPurchases()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT p.id, DATE_FORMAT(p.purchase_date, "%d/%m") AS purchase_date, DATE_FORMAT(p.purchase_date, "%H:%i") AS purchase_time, DATE_FORMAT(p.purchase_date, "%Y-%m-%d") AS formatted_purchase_date,
        p.total_amount, p.payment_method, p.included, p.created_at, p.updated_at, COUNT(a.id) AS article_count
        FROM purchases p
        LEFT JOIN articles a ON p.id = a.purchase_id
        GROUP BY p.id
        ORDER BY p.purchase_date DESC' // Tri par date d'achat décroissante
        );

        return $req;
    }


    public function getMonthlyPurchasesByDate($month, $year)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT p.id, DATE_FORMAT(p.purchase_date, "%d/%m") AS purchase_date, DATE_FORMAT(p.purchase_date, "%H:%i") AS purchase_time, DATE_FORMAT(p.purchase_date, "%Y-%m-%d") AS formatted_purchase_date,
        p.total_amount, p.payment_method, p.included, p.created_at, p.updated_at, COUNT(a.id) AS article_count
        FROM purchases p
        LEFT JOIN articles a ON p.id = a.purchase_id
        WHERE MONTH(p.purchase_date) = :month AND YEAR(p.purchase_date) = :year
        GROUP BY p.id
        ORDER BY p.purchase_date DESC'
        );

        $req->execute(
            array(
                'month' => $month,
                'year' => $year
            )
        );

        return $req;
    }

    public function getPurchasesBetweenDates($start_date, $end_date)
    {
        $dateStart = date('Y-m-d 00:00:00', strtotime($start_date));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime($end_date . ' +1 day'));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT p.id, DATE_FORMAT(p.purchase_date, "%d/%m") AS purchase_date, DATE_FORMAT(p.purchase_date, "%H:%i") AS purchase_time, DATE_FORMAT(p.purchase_date, "%Y-%m-%d") AS formatted_purchase_date,
        p.provider_id, p.total_amount, p.payment_method, p.included, p.created_at, p.updated_at
        FROM purchases p
        WHERE p.purchase_date >= :dateStart AND p.purchase_date < :dateEnd
        ORDER BY p.purchase_date DESC'
        );

        $req->execute(
            array(
                'dateStart' => $dateStart,
                'dateEnd' => $dateEnd
            )
        );
        return $req;
    }



    public function getPurchaseById($purchase_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, purchase_date, provider_id, total_amount, paid_amount, payment_method, included, created_at, updated_at, comment
        FROM purchases
        WHERE id = ?'
        );

        $req->execute(array($purchase_id));
        $purchase = $req->fetch();

        // Formater la date pour l'affichage dans la vue
        $purchase['formatted_purchase_date'] = date('Y-m-d', strtotime($purchase['purchase_date']));

        return $purchase;
    }

    public function getLastPurchase()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT id
        FROM purchases
        ORDER BY id DESC
        LIMIT 1'
        );

        $purchase = $req->fetch();
        return $purchase;
    }


    //----------------------------- U P D A T E

    public function updatePurchase($purchase_id, $purchase_date, $provider_id, $total_amount, $payment_method, $included, $updated_at, $comment)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE purchases
        SET purchase_date = ?, provider_id = ?, total_amount = ?, payment_method = ?, included = ?, updated_at = ?, comment = ?
        WHERE id = ?'
        );

        $updatedPurchase = $req->execute(array($purchase_date, $provider_id, $total_amount, $payment_method, $included, $updated_at, $comment, $purchase_id));

        return $updatedPurchase;
    }


    public function updatePurchaseTotalAmount($purches_id, $total_amount)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE purchases
            SET total_amount = ?
            WHERE id = ?'
        );

        $updatedPurchase = $req->execute(array($total_amount, $purches_id));

        return $updatedPurchase;
    }

    public function updatePurchasePaidAmount($purchase_id, $paid_amount)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE purchases
        SET paid_amount = ?
        WHERE id = ?'
        );

        $updatedPurchase = $req->execute(array($paid_amount, $purchase_id));

        return $updatedPurchase;
    }

    public function updatePurchaseUpdatedAt($purchase_id, $updated_at)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE purchases
        SET updated_at = ?
        WHERE id = ?'
        );

        $updatedPurchase = $req->execute(array($updated_at, $purchase_id));

        return $updatedPurchase;
    }


    public function updateTotalAmount($purchase_id, $total_amount)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE purchases
        SET total_amount = ?
        WHERE id = ?'
        );

        $updatedPurchase = $req->execute(array($total_amount, $purchase_id));

        return $updatedPurchase;
    }

    public function normalizeLotUnitPrices($purchase_id)
    {
        $bdd = $this->dbConnect();

        $purchaseReq = $bdd->prepare('SELECT paid_amount FROM purchases WHERE id = ?');
        $purchaseReq->execute(array($purchase_id));
        $purchase = $purchaseReq->fetch(PDO::FETCH_ASSOC);
        if (!$purchase) {
            return ['success' => false, 'error' => 'achat introuvable'];
        }

        $lotTotal = floatval($purchase['paid_amount']);
        if ($lotTotal <= 0) {
            return ['success' => true, 'normalized' => false, 'articles' => []];
        }

        $articlesReq = $bdd->prepare('SELECT id, quantity FROM articles WHERE purchase_id = ?');
        $articlesReq->execute(array($purchase_id));
        $articles = $articlesReq->fetchAll(PDO::FETCH_ASSOC);

        $totalQty = array_sum(array_map(function($a) { return floatval($a['quantity']); }, $articles));
        if ($totalQty <= 0) {
            return ['success' => true, 'normalized' => false, 'articles' => []];
        }

        $unitPrice = round($lotTotal / $totalQty, 2);

        $updateStmt = $bdd->prepare('UPDATE articles SET unit_price = ? WHERE id = ?');
        foreach ($articles as $article) {
            $updateStmt->execute(array($unitPrice, $article['id']));
        }

        $articlesReq2 = $bdd->prepare(
            'SELECT id, category, subcategory, quantity, unit_price, comment
             FROM articles WHERE purchase_id = ? ORDER BY id ASC'
        );
        $articlesReq2->execute(array($purchase_id));
        $updatedArticles = $articlesReq2->fetchAll(PDO::FETCH_ASSOC);

        $newTotal = array_sum(array_map(function($a) {
            return floatval($a['quantity']) * floatval($a['unit_price']);
        }, $updatedArticles));

        $this->updatePurchaseTotalAmount($purchase_id, $newTotal);
        $this->updatePurchaseUpdatedAt($purchase_id, date('Y-m-d H:i:s'));

        return [
            'success' => true,
            'normalized' => true,
            'unit_price' => $unitPrice,
            'total_quantity' => floatval($totalQty),
            'purchase_total' => floatval($newTotal),
            'articles' => $updatedArticles
        ];
    }

    public function updateUpdatedAt($purchase_id, $updated_at)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE purchases
        SET updated_at = ?
        WHERE id = ?'
        );

        $updatedPurchase = $req->execute(array($updated_at, $purchase_id));

        return $updatedPurchase;
    }


    // ------------------------ D E L E T E



    public function deletePurchase($purchase_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'DELETE FROM purchases
        WHERE id = ?'
        );

        $deletedPurchase = $req->execute(array($purchase_id));

        return $deletedPurchase;
    }

    // ------------------------ G L O B A L S T A T S
    public function getAnnualPurchasesStats()
{
    $bdd = $this->dbConnect();

    $sql = '
        SELECT 
            YEAR(purchase_date) AS year,
            SUM(total_amount) AS total_purchases,
            SUM(paid_amount) AS total_paid,
            COUNT(*) AS nb_purchases
        FROM purchases
        WHERE included = 1
        GROUP BY YEAR(purchase_date)
        ORDER BY YEAR(purchase_date) ASC
    ';

    return $bdd->query($sql);
}



    /**
     * Achats du jour pour la clôture.
     *
     * @param string $date
     * @return array
     */
    public function getPurchasesForClosure(string $date): array
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare('
            SELECT p.id, p.purchase_date, p.total_amount, p.payment_method, p.provider_id, pr.name AS provider_name
            FROM purchases p
            LEFT JOIN providers pr ON p.provider_id = pr.id
            WHERE DATE(p.purchase_date) = :selectedDate
              AND p.included = 1
            ORDER BY p.purchase_date ASC
        ');
        $req->execute([':selectedDate' => $date]);
        $rows = $req->fetchAll(PDO::FETCH_ASSOC);
        // Normaliser payment_method
        foreach ($rows as &$row) {
            $row['payment_method'] = $row['payment_method'] ?? 'cash';
        }
        unset($row);
        return $rows;
    }
}
