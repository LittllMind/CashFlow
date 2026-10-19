<?php

namespace OpenClassroom\Blog\Model;

require_once("model/BddManager.php");

use PDO;

class ArticlesManager extends BddManager
{


    // ------------------------ P O S T

    /**
     * Recalcule et met a jour sales.total_amount depuis les articles d'une vente.
     */
    private function syncSaleTotalAmount(int $sale_id): void
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare('
            UPDATE sales s
            LEFT JOIN (
                SELECT sale_id, SUM(quantity * unit_price) AS total
                FROM articles
                WHERE sale_id = ?
                GROUP BY sale_id
            ) a ON s.id = a.sale_id
            SET s.total_amount = COALESCE(a.total, 0)
            WHERE s.id = ?
        ');
        $req->execute([$sale_id, $sale_id]);
    }

    public function postSaleArticle($sale_id, $category, $subcategory, $quantity, $unit_price, $comment)
    {
        // Synchroniser la date de creation sur le jour de la vente pour que
        // recap/chronologie tombent au bon jour si sale_date a ete modifiee.
        $bdd = $this->dbConnect();
        $saleDateReq = $bdd->prepare('SELECT sale_date FROM sales WHERE id = ?');
        $saleDateReq->execute(array($sale_id));
        $saleRow = $saleDateReq->fetch();
        $articleDate = !empty($saleRow['sale_date'])
            ? date('Y-m-d', strtotime($saleRow['sale_date'])) . ' ' . date('H:i:s')
            : date('Y-m-d H:i:s');

        $Article = $bdd->prepare(
            'INSERT INTO articles(sale_id, category, subcategory, quantity, unit_price, comment, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $createdArticle = $Article->execute(array($sale_id, $category, $subcategory, $quantity, $unit_price, $comment, $articleDate, date('Y-m-d H:i:s')));

        if ($createdArticle) {
            // Capturer l'id AVANT l'UPDATE de synchronisation : MySQL resette
            // LAST_INSERT_ID() lors d'un UPDATE ... LEFT JOIN sur la meme session.
            $articleId = intval($bdd->lastInsertId());
            $this->syncSaleTotalAmount((int)$sale_id);
            return $articleId;
        }
        return false;
    }

    public function postPurchaseArticle($purchase_id, $category, $subcategory, $quantity, $unit_price, $comment)
    {
        $bdd = $this->dbConnect();

        $purchaseDateReq = $bdd->prepare('SELECT purchase_date FROM purchases WHERE id = ?');
        $purchaseDateReq->execute(array($purchase_id));
        $purchaseRow = $purchaseDateReq->fetch();
        $articleDate = !empty($purchaseRow['purchase_date'])
            ? date('Y-m-d', strtotime($purchaseRow['purchase_date'])) . ' ' . date('H:i:s')
            : date('Y-m-d H:i:s');

        $Article = $bdd->prepare(
            'INSERT INTO articles(purchase_id, category, subcategory, quantity, unit_price, comment, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $createdArticle = $Article->execute(array($purchase_id, $category, $subcategory, $quantity, $unit_price, $comment, $articleDate, date('Y-m-d H:i:s')));

        return $createdArticle ? $bdd->lastInsertId() : false;
    }

    // ------------------------ G E T

    public function getArticles()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT id, sale_id, category, subcategory, quantity, unit_price, created_at, updated_at, comment
        FROM articles'
        );

        $articles = $req->fetchAll();

        return $articles;
    }

    public function getArticleById($article_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, sale_id, purchase_id, category, subcategory, quantity, unit_price, created_at, updated_at, comment
        FROM articles
        WHERE id = ?'
        );

        $req->execute(array($article_id));
        $article = $req->fetch();

        return $article;
    }

    public function getArticlesByMonth($year, $month)
    {
        $dateStart = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $dateEnd   = date('Y-m-d 00:00:00', strtotime("$dateStart +1 month"));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, sale_id, category, subcategory, quantity, unit_price, created_at, updated_at
        FROM articles
        WHERE created_at >= ? AND created_at < ? AND sale_id IS NOT NULL
        ORDER BY category ASC'
        );

        $req->execute(array($dateStart, $dateEnd));
        $articles = $req->fetchAll();

        return $articles;
    }

    /**
     * Nombre de ventes par sous-catégorie sur le mois courant (articles liés à une vente).
     * Retourne un tableau indexé par "category|subcategory" avec le nombre de lignes article.
     * Mode bucket : score de fréquence normalisé pour un tri stable par vague.
     */
    public function getSubcategorySaleCountsByMonth()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            "SELECT a.category, a.subcategory, COUNT(a.id) AS article_count
             FROM articles a
             INNER JOIN sales s ON s.id = a.sale_id
             WHERE DATE_FORMAT(s.sale_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
               AND s.included = 1
               AND s.client_id IS NULL
             GROUP BY a.category, a.subcategory"
        );

        $rows = $req->fetchAll();
        $scores = [];
        foreach ($rows as $row) {
            $key = $row['category'] . '|' . $row['subcategory'];
            $scores[$key] = (int) $row['article_count'];
        }
        return $scores;
    }


    // public function getArticlesByYear($year)
    // {
    //     $bdd = $this->dbConnect();
    //     $req = $bdd->prepare(
    //         'SELECT id, sale_id, category, subcategory, quantity, unit_price, created_at, updated_at
    //     FROM articles
    //     WHERE YEAR(created_at) = ?
    //     ORDER BY category ASC'
    //     );

    //     $req->execute(array($year));
    //     $articles = $req->fetchAll();

    //     return $articles;
    // }

    public function getArticlesBySaleId($sale_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, sale_id, category, subcategory, quantity, unit_price, created_at, updated_at, comment
        FROM articles
        WHERE sale_id = ?'
        );

        $req->execute(array($sale_id));
        $articles = $req->fetchAll();

        return $articles;
    }

    public function getArticlesByPurchaseId($purchase_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, purchase_id, category, subcategory, quantity, unit_price, created_at, updated_at, comment
        FROM articles
        WHERE purchase_id = ?'
        );

        $req->execute(array($purchase_id));
        $articles = $req->fetchAll();

        return $articles;
    }

    public function getArticlesByDate($date, bool $includeSaleTime = false)
    {
        $dateStart = date('Y-m-d 00:00:00', strtotime($date));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime($date . ' +1 day'));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT a.id, a.sale_id, a.purchase_id, a.category, a.subcategory, a.quantity, a.unit_price, a.created_at, a.updated_at, a.comment
        FROM articles a
        WHERE a.created_at >= ? AND a.created_at < ? AND a.sale_id IS NOT NULL
        ORDER BY a.created_at ASC'
        );

        $req->execute(array($dateStart, $dateEnd));
        $articles = $req->fetchAll();

        return $articles;
    }

    /**
     * Récupère les articles agrégés par catégorie et sous-catégorie pour une date donnée.
     * Retourne un tableau : [category, subcategory, total_quantity, total_amount, avg_unit_price, article_count]
     */
    public function getAggregatedArticlesByDate($date)
    {
        $dateStart = date('Y-m-d 00:00:00', strtotime($date));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime($date . ' +1 day'));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT category, subcategory,
                SUM(quantity) AS total_quantity,
                SUM(quantity * unit_price) AS total_amount,
                AVG(unit_price) AS avg_unit_price,
                COUNT(id) AS article_count,
                GROUP_CONCAT(DISTINCT NULLIF(comment, \'\') SEPARATOR \' · \') AS comments
            FROM articles
            WHERE created_at >= ? AND created_at < ? AND sale_id IS NOT NULL
            AND sale_id NOT IN (SELECT id FROM sales WHERE client_id IS NOT NULL)
            GROUP BY category, subcategory
            ORDER BY total_quantity DESC'
        );

        $req->execute(array($dateStart, $dateEnd));
        return $req->fetchAll();
    }

    // public function getArticlesByDateOnDayResult($date)
    // {
    //     $formattedDate = date('Y-m-d', strtotime($date));
    //     // created_at au format hh:ii
    //     $bdd = $this->dbConnect();
    //     $req = $bdd->prepare(
    //         'SELECT id, sale_id, purchase_id, category, subcategory, quantity, unit_price, created_at, updated_at, comment
    //     FROM articles
    //     WHERE DATE(created_at) = ?
    //     ORDER BY created_at DESC'
    //     );

    //     $req->execute(array($formattedDate));
    //     $articles = $req->fetchAll();

    //     return $articles;
    // }

    public function getArticlesByDateOnDayResult($date)
    {
        $dateStart = date('Y-m-d 00:00:00', strtotime($date));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime($date . ' +1 day'));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT a.id, a.sale_id, a.purchase_id, a.category, a.subcategory, a.quantity, a.unit_price, a.created_at, a.updated_at, a.comment
        FROM articles a
        INNER JOIN sales s ON a.sale_id = s.id
        WHERE a.created_at >= ? AND a.created_at < ? AND s.included = 1
        ORDER BY a.created_at DESC'
        );

        $req->execute(array($dateStart, $dateEnd));
        $articles = $req->fetchAll();

        return $articles;
    }

    // public function getArticlesByMonthOnDayResult($date)
    // {
    //     $formattedDate = date('Y-m', strtotime($date));

    //     $bdd = $this->dbConnect();
    //     $req = $bdd->prepare(
    //         'SELECT id, sale_id, purchase_id, category, subcategory, quantity, unit_price, created_at, updated_at, comment
    //         FROM articles
    //         WHERE DATE_FORMAT(created_at, "%Y-%m") = ?
    //         ORDER BY created_at ASC'
    //     );

    //     $req->execute(array($formattedDate));
    //     $articles = $req->fetchAll();

    //     // Reformater les dates au format jj-mm-aaaa
    //     foreach ($articles as &$article) {
    //         if (!empty($article['created_at'])) {
    //             $article['created_at'] = date('d-m-Y', strtotime($article['created_at']));
    //         }
    //         if (!empty($article['updated_at'])) {
    //             $article['updated_at'] = date('d-m-Y', strtotime($article['updated_at']));
    //         }
    //     }

    //     return $articles;
    // }

    public function getArticlesByMonthOnDayResult($date)
    {
        $dateStart = date('Y-m-01 00:00:00', strtotime($date));
        $dateEnd   = date('Y-m-d 00:00:00', strtotime("$dateStart +1 month"));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT a.id, a.sale_id, a.purchase_id, a.category, a.subcategory, a.quantity, a.unit_price, a.created_at, a.updated_at, a.comment
        FROM articles a
        INNER JOIN sales s ON a.sale_id = s.id
        WHERE a.created_at >= ? AND a.created_at < ? AND s.included = 1
        ORDER BY a.created_at ASC'
        );

        $req->execute(array($dateStart, $dateEnd));
        $articles = $req->fetchAll();

        // Reformater les dates au format jj-mm-aaaa
        foreach ($articles as &$article) {
            if (!empty($article['created_at'])) {
                $article['created_at'] = date('d-m-Y', strtotime($article['created_at']));
            }
            if (!empty($article['updated_at'])) {
                $article['updated_at'] = date('d-m-Y', strtotime($article['updated_at']));
            }
        }

        return $articles;
    }

    public function getArticlesByCategoryAndSubcategory($category, $subcategory)
    {
        $db = $this->dbConnect();
        $stmt = $db->prepare(
            'SELECT *, 
                    (quantity * unit_price) AS total,
                    CASE WHEN sale_id IS NOT NULL THEN "vente" ELSE "achat" END AS type
             FROM articles
             WHERE category = ? AND subcategory = ?'
        );
        $stmt->execute([$category, $subcategory]);
        return $stmt->fetchAll();
    }

    public function getPurchasesArticlesByDate($date)
    {
        $formattedDate = date('Y-m-d', strtotime($date));

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, purchase_id, category, subcategory, quantity, unit_price, created_at, updated_at, comment
        FROM articles
        WHERE DATE(created_at) = ? AND purchase_id IS NOT NULL
        ORDER BY created_at DESC'
        );

        $req->execute(array($formattedDate));
        $articles = $req->fetchAll();

        return $articles;
    }


    public function getLastArticles()
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

    public function updateArticle($article_id, $quantity, $price, $comment)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE articles
            SET quantity = ?, unit_price = ?, comment = ?
            WHERE id = ?'
        );

        $updatedArticle = $req->execute(array($quantity, $price, $comment, $article_id));

        // Resynchroniser le total de la vente
        $saleReq = $bdd->prepare('SELECT sale_id FROM articles WHERE id = ?');
        $saleReq->execute(array($article_id));
        $saleRow = $saleReq->fetch();
        if ($saleRow) {
            $this->syncSaleTotalAmount((int)$saleRow['sale_id']);
        }

        return $updatedArticle;
    }

    public function updateDateSaleArticles($sale_id, $updated_at)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE articles
            SET created_at = ?
            WHERE sale_id = ?'
        );

        $updatedArticles = $req->execute(array($updated_at, $sale_id));

        return $updatedArticles;
    }

    /**
     * Decale le jour des articles d'une vente vers une nouvelle date
     * en conservant l'heure de creation originale de chaque article.
     */
    public function updateDateSaleArticlesDateOnly($sale_id, $newDate)
    {
        $bdd = $this->dbConnect();
        // Recupere chaque article avec son heure actuelle
        $req = $bdd->prepare(
            'SELECT id, created_at FROM articles WHERE sale_id = ?'
        );
        $req->execute(array($sale_id));
        $articles = $req->fetchAll();

        $updateReq = $bdd->prepare('UPDATE articles SET created_at = ? WHERE id = ?');
        foreach ($articles as $article) {
            $existingTime = date('H:i:s', strtotime($article['created_at']));
            $newDateTime = $newDate . ' ' . $existingTime;
            $updateReq->execute(array($newDateTime, $article['id']));
        }

        return true;
    }


    public function deleteArticle($article_id)
    {
        $bdd = $this->dbConnect();

        // Recuperer sale_id avant suppression
        $saleReq = $bdd->prepare('SELECT sale_id FROM articles WHERE id = ?');
        $saleReq->execute(array($article_id));
        $saleRow = $saleReq->fetch();

        $req = $bdd->prepare(
            'DELETE FROM articles
            WHERE id = ?'
        );

        $deletedArticle = $req->execute(array($article_id));

        // Resynchroniser le total de la vente
        if ($saleRow) {
            $this->syncSaleTotalAmount((int)$saleRow['sale_id']);
        }

        return $deletedArticle;
    }

    public function deleteArticlesBySaleId($sale_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'DELETE FROM articles
            WHERE sale_id = ?'
        );

        $deletedArticles = $req->execute(array($sale_id));

        return $deletedArticles;
    }

    public function updateArticleComment($article_id, $comment)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE articles
            SET comment = ?
            WHERE id = ?'
        );

        $updatedArticle = $req->execute(array($comment, $article_id));

        return $updatedArticle;
    }

    /**
     * Met a jour updated_at de tous les articles d'une vente.
     */
    public function updateDateSaleArticlesUpdatedAt($sale_id, $updated_at)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'UPDATE articles
            SET updated_at = ?
            WHERE sale_id = ?'
        );

        $updatedArticles = $req->execute(array($updated_at, $sale_id));

        return $updatedArticles;
    }

    /**
     * Articles liés à une liste de ventes (closure).
     *
     * @param array<int> $saleIds
     * @return array
     */
    public function getArticlesBySaleIds(array $saleIds): array
    {
        if (empty($saleIds)) {
            return [];
        }
        $bdd = $this->dbConnect();
        $placeholders = implode(',', array_fill(0, count($saleIds), '?'));
        $req = $bdd->prepare("SELECT id, sale_id, category, subcategory, quantity, unit_price, created_at, updated_at, comment
            FROM articles
            WHERE sale_id IN ($placeholders)");
        $req->execute($saleIds);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Évolution mensuelle des ventes (quantité + CA), optionnellement filtrée.
     *
     * @param string|null $category
     * @param string|null $subcategory
     * @param int|null    $maxMonths limite les derniers mois retournés (0=tous)
     * @return array{labels: string[], qty: int[], ca: float[]}
     */
    public function getMonthlySalesTrend(?string $category = null, ?string $subcategory = null, ?int $maxMonths = null): array
    {
        $bdd = $this->dbConnect();

        // Sous-requête : tous les mois connus de la base pour lesquels il y a au moins une vente incluse
        $whereFilter  = '';
        $params       = [];
        if (!empty($category)) {
            $whereFilter .= ' AND a.category = :category';
            $params[':category'] = $category;
        }
        if (!empty($subcategory)) {
            $whereFilter .= ' AND a.subcategory = :subcategory';
            $params[':subcategory'] = $subcategory;
        }

        $sql = "
            SELECT DATE_FORMAT(s.sale_date, '%Y-%m') AS month,
                   SUM(a.quantity) AS qty,
                   ROUND(SUM(a.quantity * a.unit_price), 2) AS ca
            FROM articles a
            INNER JOIN sales s ON s.id = a.sale_id
            WHERE s.included = 1 AND s.client_id IS NULL
                  $whereFilter
            GROUP BY DATE_FORMAT(s.sale_date, '%Y-%m')
            ORDER BY month ASC
        ";

        if ($maxMonths !== null && $maxMonths > 0) {
            // Pas de LIMIT ici : on veut le mois courant même incomplet ;
            // on tronquera après remplissage des trous.
        }

        $stmt = $bdd->prepare($sql);
        $stmt->execute($params);
        $rows = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[$r['month']] = [
                'qty' => (int) $r['qty'],
                'ca'  => (float) $r['ca'],
            ];
        }

        if (empty($rows)) {
            return ['labels' => [], 'qty' => [], 'ca' => []];
        }

        // Trouver le premier et dernier mois ; remplir les trous avec 0
        $months = array_keys($rows);
        $first = min($months);
        $last  = date('Y-m');

        $labels = [];
        $qty    = [];
        $ca     = [];

        $current = new \DateTime($first . '-01');
        $end     = new \DateTime($last  . '-01');

        while ($current <= $end) {
            $label = $current->format('Y-m');
            $labels[] = $label;
            $qty[]    = $rows[$label]['qty'] ?? 0;
            $ca[]     = $rows[$label]['ca']  ?? 0.0;
            $current->modify('+1 month');
        }

        // Tronquer aux $maxMonths derniers si demandé
        if ($maxMonths !== null && $maxMonths > 0 && count($labels) > $maxMonths) {
            $labels = array_slice($labels, -$maxMonths);
            $qty    = array_slice($qty,    -$maxMonths);
            $ca     = array_slice($ca,     -$maxMonths);
        }

        return [
            'labels' => $labels,
            'qty'    => $qty,
            'ca'     => $ca,
        ];
    }

    // ------------------------------------------------------------------
    // A R T I C L E S   P A R   C A T E G O R I E / S O U S - C A T E G O R I E
    // ------------------------------------------------------------------

    /**
     * Retourne la liste des annees distinctes presentes dans la table articles,
     * basee sur created_at. Utilisee pour generer les onglets annees.
     *
     * @return int[]
     */
    public function getDistinctArticleYears(): array
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            "SELECT DISTINCT YEAR(created_at) AS yr
             FROM articles
             WHERE created_at IS NOT NULL
             ORDER BY yr DESC"
        );
        $rows = $req->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => (int) $r['yr'], $rows);
    }

    /**
     * Articles (achats + ventes) d'une categorie/sous-categorie, filtres par annee.
     * Si $year vaut 'all', aucun filtre d'annee n'est applique (historique).
     * Retourne dictionnaire [rows, summary].
     */
    public function getArticlesByCategoryAndSubcategoryByYear(
        string $category,
        string $subcategory,
        string $year = 'all'
    ): array {
        $bdd = $this->dbConnect();

        $yearFilter = '';
        $params = ['category' => $category, 'subcategory' => $subcategory];

        if ($year !== 'all' && ctype_digit($year)) {
            $y = (int) $year;
            $yearFilter = ' AND a.created_at >= :dateStart AND a.created_at < :dateEnd';
            $params['dateStart'] = sprintf('%04d-01-01 00:00:00', $y);
            $params['dateEnd']   = sprintf('%04d-01-01 00:00:00', $y + 1);
        }

        $sql = "
            SELECT a.*,
                   CASE WHEN a.sale_id IS NOT NULL THEN 'vente' ELSE 'achat' END AS type,
                   (a.quantity * a.unit_price) AS total,
                   DATE_FORMAT(a.created_at, '%d-%m-%Y') AS date
            FROM articles a
            LEFT JOIN sales s ON s.id = a.sale_id
            WHERE a.category = :category
              AND a.subcategory = :subcategory
              $yearFilter
              AND (a.sale_id IS NULL OR (s.included = 1 AND s.client_id IS NULL))
            ORDER BY a.created_at DESC
        ";

        $stmt = $bdd->prepare($sql);
        $stmt->execute($params);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [
            'achats' => ['qty' => 0, 'avg' => 0.0, 'total' => 0.0, 'count' => 0],
            'ventes' => ['qty' => 0, 'avg' => 0.0, 'total' => 0.0, 'count' => 0],
        ];
        foreach ($articles as $article) {
            $isVente = $article['type'] === 'vente';
            $bucket = $isVente ? 'ventes' : 'achats';
            $summary[$bucket]['qty']   += (float) $article['quantity'];
            $summary[$bucket]['total'] += (float) $article['total'];
            $summary[$bucket]['count'] += 1;
        }
        if ($summary['achats']['count'] > 0) {
            $summary['achats']['avg'] = $summary['achats']['total'] / $summary['achats']['count'];
        }
        if ($summary['ventes']['count'] > 0) {
            $summary['ventes']['avg'] = $summary['ventes']['total'] / $summary['ventes']['count'];
        }
        $summary['bilan'] = $summary['ventes']['total'] - $summary['achats']['total'];

        return [
            'articles' => $articles,
            'summary'  => $summary,
        ];
    }

    // ------------------------ G L O B A L   S T A T S
    public function getCategoryStatsByYear($year)
    {
        $bdd = $this->dbConnect();

        $sql = '
        SELECT 
            a.category,
            a.subcategory,
            SUM(a.quantity) AS total_qty,
            SUM(a.quantity * a.unit_price) AS total_amount
        FROM articles a
        INNER JOIN sales s ON s.id = a.sale_id
        WHERE s.included = 1
          AND s.sale_date >= :dateStart
          AND s.sale_date <  :dateEnd
        GROUP BY a.category, a.subcategory
        ORDER BY a.category, a.subcategory
    ';

        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            'dateStart' => sprintf('%04d-01-01 00:00:00', (int) $year),
            'dateEnd'   => sprintf('%04d-01-01 00:00:00', (int) $year + 1),
        ]);

        return $stmt;
    }
}
