<?php

namespace OpenClassroom\Blog\Model;

// pdo exception
use \PDO;

require_once("model/BddManager.php");

class SalesManager extends BddManager
{
    /** Wrapper public pour accéder à la connexion PDO depuis l'extérieur */
    public function getConnection()
    {
        return $this->dbConnect();
    }


    // ------------------------ P O S T


    public function postSale($sale_date, $total_amount, $payment_method, $included, $created_at, $updated_at)
    {
        $bdd = $this->dbConnect();
        $Sale = $bdd->prepare(
            'INSERT INTO sales(sale_date, total_amount, payment_method, included, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?)'
        );

        $createdSale = $Sale->execute(array($sale_date, $total_amount, $payment_method, $included, $created_at, $updated_at));

        return $createdSale;
    }



    // ------------------------ G E T

    public function getSales()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT s.id, DATE_FORMAT(s.sale_date, "%d/%m") AS sale_date, DATE_FORMAT(s.sale_date, "%H:%i") AS sale_time, DATE_FORMAT(s.sale_date, "%Y-%m-%d") AS formatted_sale_date,
            s.total_amount, s.payment_method, s.included, s.created_at, s.updated_at, COUNT(a.id) AS article_count
            FROM sales s
            LEFT JOIN articles a ON s.id = a.sale_id
            GROUP BY s.id
            ORDER BY s.sale_date DESC' // Tri par date de vente décroissante
        );

        return $req;
    }

    public function getTotalSalesAmount()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT SUM(total_amount) AS total_sales_amount
            FROM sales'
        );

        $totalSalesAmount = $req->fetch();
        return $totalSalesAmount;
    }

    public function getSalesByMonth($selectedDate)
    {
        $dateStart = date('Y-m-01 00:00:00', strtotime($selectedDate));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime("$dateStart +1 month"));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT s.id, 
                    DATE_FORMAT(s.sale_date, "%d/%m") AS sale_date, 
                    DATE_FORMAT(s.sale_date, "%H:%i") AS sale_time, 
                    DATE_FORMAT(s.sale_date, "%Y-%m-%d") AS formatted_sale_date,
                    s.total_amount, 
                    s.payment_method, 
                    s.included, 
                    s.created_at, 
                    s.updated_at, 
                    COUNT(a.id) AS article_count
             FROM sales s
             LEFT JOIN articles a ON s.id = a.sale_id
             WHERE s.sale_date >= :dateStart AND s.sale_date < :dateEnd
               AND s.client_id IS NULL
             GROUP BY s.id
             ORDER BY s.sale_date DESC'
        );

        $req->execute(['dateStart' => $dateStart, 'dateEnd' => $dateEnd]);

        $results = $req->fetchAll(PDO::FETCH_ASSOC);

        $req->closeCursor();

        return $results;
    }

    public function getSalesByMonthGrouped(int $month, int $year): array
    {
        $dateStart = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $dateEnd   = date('Y-m-d 00:00:00', strtotime("$dateStart +1 month"));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT DATE(s.sale_date) AS sale_day,
                    COUNT(*) AS total_sales,
                    SUM(s.total_amount) AS total_amount,
                    SUM(IFNULL(ac.article_count, 0)) AS total_articles
             FROM sales s
             LEFT JOIN (
                 SELECT sale_id, COUNT(*) AS article_count
                 FROM articles
                 WHERE sale_id IS NOT NULL
                 GROUP BY sale_id
             ) ac ON s.id = ac.sale_id
             WHERE s.sale_date >= :dateStart AND s.sale_date < :dateEnd
               AND s.client_id IS NULL
               AND s.included = 1
             GROUP BY DATE(s.sale_date)
             ORDER BY sale_day ASC'
        );
        $req->execute(['dateStart' => $dateStart, 'dateEnd' => $dateEnd]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesByYearGrouped(int $year): array
    {
        $dateStart = sprintf('%04d-01-01 00:00:00', $year);
        $dateEnd   = sprintf('%04d-01-01 00:00:00', $year + 1);

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT DATE(s.sale_date) AS sale_day,
                    COUNT(*) AS total_sales,
                    SUM(s.total_amount) AS total_amount,
                    SUM(IFNULL(ac.article_count, 0)) AS total_articles
             FROM sales s
             LEFT JOIN (
                 SELECT sale_id, COUNT(*) AS article_count
                 FROM articles
                 WHERE sale_id IS NOT NULL
                 GROUP BY sale_id
             ) ac ON s.id = ac.sale_id
             WHERE s.sale_date >= :dateStart AND s.sale_date < :dateEnd
               AND s.client_id IS NULL
               AND s.included = 1
             GROUP BY DATE(s.sale_date)
             ORDER BY sale_day ASC'
        );
        $req->execute(['dateStart' => $dateStart, 'dateEnd' => $dateEnd]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesByDate($selectedDate)
    {
        $dateStart = date('Y-m-d 00:00:00', strtotime($selectedDate));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime($selectedDate . ' +1 day'));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            '
        SELECT s.id, DATE_FORMAT(s.sale_date, "%d/%m/%Y") AS sale_date, DATE_FORMAT(s.sale_date, "%H:%i") AS sale_time, DATE_FORMAT(s.sale_date, "%Y-%m-%d") AS formatted_sale_date,
        s.total_amount, s.payment_method, s.included, s.client_id, s.created_at, s.updated_at, COUNT(a.id) AS article_count
        FROM sales s
        LEFT JOIN articles a ON s.id = a.sale_id
        WHERE s.sale_date >= :dateStart AND s.sale_date < :dateEnd
          AND s.client_id IS NULL
          AND s.included = 1
        GROUP BY s.id
        ORDER BY s.sale_date DESC'
        );

        $req->execute(array('dateStart' => $dateStart, 'dateEnd' => $dateEnd));
        return $req;
    }

    public function getSalesByDateStatistic(string $selectedDate): array
    {
        $dateStart = date('Y-m-d 00:00:00', strtotime($selectedDate));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime($selectedDate . ' +1 day'));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare('
            SELECT s.id, 
                   DATE_FORMAT(s.created_at, "%d/%m") AS created_at, 
                   DATE_FORMAT(s.sale_date, "%Y-%m-%d") AS formatted_sale_date,
                   s.total_amount, 
                   s.payment_method, 
                   s.included, 
                   s.created_at, 
                   s.updated_at, 
                   COUNT(a.id) AS article_count
            FROM sales s
            LEFT JOIN articles a ON s.id = a.sale_id
            WHERE s.sale_date >= :dateStart AND s.sale_date < :dateEnd
            GROUP BY s.id
            ORDER BY s.sale_date DESC
        ');

        $req->execute(['dateStart' => $dateStart, 'dateEnd' => $dateEnd]);

        $results = $req->fetchAll(PDO::FETCH_ASSOC);

        $req->closeCursor();

        return $results;
    }

    // public function getSalesByYear($year)
    // {
    //     $bdd = $this->dbConnect();
    //     $req = $bdd->prepare('
    //     SELECT s.id, 
    //            DATE_FORMAT(s.sale_date, "%d/%m") AS sale_date, 
    //            DATE_FORMAT(s.sale_date, "%H:%i") AS sale_time, 
    //            DATE_FORMAT(s.sale_date, "%Y-%m-%d") AS formatted_sale_date,
    //            s.total_amount, 
    //            s.payment_method, 
    //            s.included, 
    //            s.created_at, 
    //            s.updated_at, 
    //            COUNT(a.id) AS article_count
    //     FROM sales s
    //     LEFT JOIN articles a ON s.id = a.sale_id
    //     WHERE DATE_FORMAT(s.sale_date, "%Y") = :year
    //     GROUP BY s.id
    //     ORDER BY s.sale_date DESC
    // ');

    //     $req->execute(['year' => $year]);

    //     $results = $req->fetchAll(PDO::FETCH_ASSOC);

    //     $req->closeCursor();

    //     return $results;
    // }





    public function getSalesBetweenDates($startDate, $endDate)
    {
        $dateStart = date('Y-m-d 00:00:00', strtotime($startDate));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime($endDate . ' +1 day'));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            '
        SELECT s.id, DATE_FORMAT(s.sale_date, "%d/%m") AS sale_date, DATE_FORMAT(s.sale_date, "%H:%i") AS sale_time, DATE_FORMAT(s.sale_date, "%Y-%m-%d") AS formatted_sale_date,
        s.total_amount, s.payment_method, s.included, s.client_id, s.created_at, s.updated_at, COUNT(a.id) AS article_count
        FROM sales s
        LEFT JOIN articles a ON s.id = a.sale_id
        WHERE s.sale_date >= :dateStart AND s.sale_date < :dateEnd
          AND s.client_id IS NULL
          AND s.included = 1
        GROUP BY s.id
        ORDER BY s.sale_date DESC'
        );

        $req->execute(
            array(
                'dateStart' => $dateStart,
                'dateEnd' => $dateEnd
            )
        );
        return $req;
    }


    public function getSaleById($sale_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, sale_date, total_amount, payment_method, included, created_at, updated_at
            FROM sales
            WHERE id = ?'
        );

        $req->execute(array($sale_id));
        $sale = $req->fetch();

        // Formater la date pour l'affichage dans la vue
        $sale['formatted_sale_date'] = date('Y-m-d', strtotime($sale['sale_date']));

        return $sale;
    }

    public function getPreviousSale($current_sale_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, sale_date, total_amount, payment_method, included, created_at, updated_at
            FROM sales
            WHERE id < ?
            ORDER BY id DESC
            LIMIT 1'
        );

        $req->execute(array($current_sale_id));
        $previous_sale = $req->fetch();

        return $previous_sale;
    }

    public function getLastSale()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT id
            FROM sales
            ORDER BY id DESC
            LIMIT 1'
        );

        $sale = $req->fetch();
        return $sale;
    }

    public function getNextSale($current_sale_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, sale_date, total_amount, payment_method, included, created_at, updated_at
            FROM sales
            WHERE id > ?
            ORDER BY id ASC
            LIMIT 1'
        );

        $req->execute(array($current_sale_id));
        $next_sale = $req->fetch();

        return $next_sale;
    }

    //----------------------------- U P D A T E
    public function updateSale($sale_id, $sale_date, $total_amount, $payment_method, $updated_at)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE sales
            SET sale_date = ?, total_amount = ?, payment_method = ?, updated_at = ?
            WHERE id = ?'
        );

        $updatedSale = $req->execute(array($sale_date, $total_amount, $payment_method, $updated_at, $sale_id));

        return $updatedSale;
    }

    public function updateTotalAmount($sale_id, $total_amount)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE sales
            SET total_amount = ?
            WHERE id = ?'
        );

        $updatedSale = $req->execute(array($total_amount, $sale_id));

        return $updatedSale;
    }

    public function updateUpdatedAt($sale_id, $updated_at)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE sales
            SET updated_at = ?
            WHERE id = ?'
        );

        $updatedSale = $req->execute(array($updated_at, $sale_id));

        return $updatedSale;
    }



    public function deleteSale($sale_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'DELETE FROM sales
            WHERE id = ?'
        );

        $deletedSale = $req->execute(array($sale_id));

        return $deletedSale;
    }

    /**
     * Ventes ouvertes (non ardoise) d'une date, filtrées par activity FRIPE/BROC
     * via la table subcategories.
     *
     * @param string $date
     * @param string|null $activityFilter 'FRIPE' | 'BROC' | null (= tout)
     * @return array
     */
    public function getSalesForClosure(string $date, ?string $activityFilter = null): array
    {
        $dateStart = date('Y-m-d 00:00:00', strtotime($date));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime($date . ' +1 day'));

        $bdd = $this->dbConnect();
        $where = 's.sale_date >= :dateStart AND s.sale_date < :dateEnd AND s.client_id IS NULL AND s.included = 1';
        $params = [':dateStart' => $dateStart, ':dateEnd' => $dateEnd];

        if ($activityFilter !== null) {
            $where .= ' AND s.id IN (
                SELECT DISTINCT a2.sale_id FROM articles a2
                WHERE a2.sale_id IS NOT NULL
                  AND a2.subcategory IN (
                      SELECT sub.subcategory_name FROM subcategories sub
                      WHERE sub.activity = :activity
                  )
            )';
            $params[':activity'] = $activityFilter;
        }

        $stmt = $bdd->prepare("SELECT s.id, s.sale_date, s.total_amount, s.payment_method
            FROM sales s
            WHERE $where
            ORDER BY s.id ASC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------ G L O B A L   S T A T I S T I C S
    public function getAnnualSalesStats()
{
    $bdd = $this->dbConnect();

    $sql = '
        SELECT 
            YEAR(sale_date) AS year,
            SUM(total_amount) AS total_sales,
            COUNT(*) AS nb_sales,
            COUNT(DISTINCT DATE(sale_date)) AS open_days
        FROM sales
        WHERE included = 1
          AND client_id IS NULL
        GROUP BY YEAR(sale_date)
        ORDER BY YEAR(sale_date) ASC
    ';

    return $bdd->query($sql);
}

    /**
     * Répartition des modes de paiement pour une année donnée.
     *
     * @param int $year
     * @return array<array{payment_method:string,nb_sales:int,ca:float}>
     */
    public function getPaymentDistributionByYear(int $year): array
    {
        $bdd = $this->dbConnect();
        $sql = '
            SELECT payment_method,
                   COUNT(*) AS nb_sales,
                   ROUND(SUM(total_amount), 2) AS ca
            FROM sales
            WHERE included = 1
              AND client_id IS NULL
              AND YEAR(sale_date) = :year
            GROUP BY payment_method
            ORDER BY ca DESC
        ';
        $stmt = $bdd->prepare($sql);
        $stmt->execute([':year' => $year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $allowed = ['cash', 'card', 'check', 'bank_transfer'];
        foreach ($rows as &$row) {
            $row['nb_sales'] = (int) $row['nb_sales'];
            $row['ca']       = (float) $row['ca'];
        }

        // S'assurer que tous les modes connus apparaissent, même à 0
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['payment_method']] = $row;
        }
        $result = [];
        foreach ($allowed as $method) {
            $result[] = $indexed[$method] ?? [
                'payment_method' => $method,
                'nb_sales'       => 0,
                'ca'             => 0.0,
            ];
        }
        return $result;
    }

    // ------------------------ A R D O I S E S
    public function getPendingArdoises()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query('
            SELECT s.id,
                   DATE_FORMAT(s.sale_date, "%d/%m/%Y") AS sale_date,
                   DATE_FORMAT(s.sale_date, "%H:%i") AS sale_time,
                   DATE_FORMAT(s.sale_date, "%Y-%m-%d") AS formatted_sale_date,
                   s.total_amount,
                   s.payment_method,
                   s.client_id,
                   c.name AS client_name,
                   c.phone AS client_phone,
                   COUNT(a.id) AS article_count
            FROM sales s
            LEFT JOIN articles a ON s.id = a.sale_id
            LEFT JOIN clients c ON s.client_id = c.id
            WHERE s.client_id IS NOT NULL
              AND s.total_amount > 0
            GROUP BY s.id
            ORDER BY s.sale_date DESC
        ');
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalPendingArdoises()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query('
            SELECT SUM(total_amount) AS total_amount, COUNT(*) AS nb_sales
            FROM sales
            WHERE client_id IS NOT NULL
              AND total_amount > 0
        ');
        return $req->fetch(PDO::FETCH_ASSOC);
    }

}
