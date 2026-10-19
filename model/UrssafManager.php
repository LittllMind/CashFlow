<?php

namespace OpenClassroom\Blog\Model;

use \PDO;

require_once("model/BddManager.php");

/**
 * UrssafManager — calcul URSSAF par articles (subcategory → activity).
 *
 * CA par activite = SUM(articles.unit_price × articles.quantity)
 * Modes de paiement = indicatifs (basés sur les ventes contenant l'activité).
 */
class UrssafManager extends BddManager
{
    public function getConnection() { return $this->dbConnect(); }

    public function getAnnualCa(string $owner, string $yearDate): array
    {
        $year = (int) date('Y', strtotime($yearDate));
        return $this->getPeriodCaArticles($owner,
            sprintf('%04d-01-01', $year),
            sprintf('%04d-12-31', $year));
    }

    public function getMonthlyCa(string $owner, string $monthDate): array
    {
        return $this->getPeriodCaArticles($owner,
            date('Y-m-01', strtotime($monthDate)),
            date('Y-m-t', strtotime($monthDate)));
    }

    public function getQuarterlyCa(string $owner, string $quarterDate): array
    {
        $month = (int) date('m', strtotime($quarterDate));
        $year  = (int) date('Y', strtotime($quarterDate));
        $q     = (int) ceil($month / 3);
        return $this->getPeriodCaArticles($owner,
            sprintf('%04d-%02d-01', $year, ($q - 1) * 3 + 1),
            date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $q * 3))));
    }

    /**
     * Calcul par articles (unit_price × quantity) et sous-catégories.
     * activityFilter: 'BROC' | 'FRIPE'
     */
    public function getPeriodCaArticles(string $owner, string $startDate, string $endDate): array
    {
        $activity = $owner === 'broc' ? 'BROC' : 'FRIPE';
        $bdd = $this->dbConnect();

        // -----------------------------------------------------------------
        // 1. CA par articles (vérité) et nombre d'articles
        // -----------------------------------------------------------------
        $artSql = "SELECT
            COALESCE(SUM(a.unit_price * a.quantity), 0) AS total_ca,
            COUNT(a.id) AS nb_articles,
            COUNT(DISTINCT a.sale_id) AS nb_sales
        FROM articles a
        WHERE a.sale_id IS NOT NULL
          AND a.subcategory IN (
              SELECT sub.subcategory_name FROM subcategories sub WHERE sub.activity = :activity
          )
          AND EXISTS (
              SELECT 1 FROM sales s
              WHERE s.id = a.sale_id
                AND s.client_id IS NULL AND s.included = 1
                AND s.sale_date >= :start AND s.sale_date < DATE_ADD(:end, INTERVAL 1 DAY)
          )";

        $stmt = $bdd->prepare($artSql);
        $stmt->execute([':activity' => $activity, ':start' => $startDate, ':end' => $endDate]);
        $art = $stmt->fetch(PDO::FETCH_ASSOC);

        $result = [
            'total_ca'     => floatval($art['total_ca'] ?? 0),
            'nb_articles'  => (int) ($art['nb_articles'] ?? 0),
            'nb_sales'     => (int) ($art['nb_sales'] ?? 0),
        ];

        // -----------------------------------------------------------------
        // 2. Modes de paiement (indicatifs — basés sur les ventes contenant l'activité)
        // -----------------------------------------------------------------
        $paySql = "SELECT
            COALESCE(SUM(CASE WHEN s.payment_method = 'cash'    THEN s.total_amount * pct.share ELSE 0 END), 0) AS total_cash,
            COALESCE(SUM(CASE WHEN s.payment_method = 'card'    THEN s.total_amount * pct.share ELSE 0 END), 0) AS total_card,
            COALESCE(SUM(CASE WHEN s.payment_method = 'check'   THEN s.total_amount * pct.share ELSE 0 END), 0) AS total_cheque,
            COALESCE(SUM(CASE WHEN s.payment_method = 'transfer' THEN s.total_amount * pct.share ELSE 0 END), 0) AS total_bank_transfer
        FROM sales s
        JOIN (
            -- Ratio CA activite / CA total par vente
            SELECT a.sale_id,
                   SUM(a.unit_price * a.quantity) / NULLIF(tot.ca_total, 0) AS share
            FROM articles a
            JOIN (
                SELECT sale_id, SUM(unit_price * quantity) AS ca_total
                FROM articles
                WHERE sale_id IS NOT NULL
                GROUP BY sale_id
            ) tot ON tot.sale_id = a.sale_id
            WHERE a.sale_id IS NOT NULL
              AND a.subcategory IN (
                  SELECT sub.subcategory_name FROM subcategories sub WHERE sub.activity = :activity
              )
            GROUP BY a.sale_id, tot.ca_total
        ) pct ON pct.sale_id = s.id
        WHERE s.client_id IS NULL AND s.included = 1
          AND s.sale_date >= :start AND s.sale_date < DATE_ADD(:end, INTERVAL 1 DAY)
";

        $pStmt = $bdd->prepare($paySql);
        $pStmt->execute([':start' => $startDate, ':end' => $endDate, ':activity' => $activity]);
        $pay = $pStmt->fetch(PDO::FETCH_ASSOC);

        $result['total_cash']          = floatval($pay['total_cash'] ?? 0);
        $result['total_card']          = floatval($pay['total_card'] ?? 0);
        $result['total_cheque']        = floatval($pay['total_cheque'] ?? 0);
        $result['total_bank_transfer'] = floatval($pay['total_bank_transfer'] ?? 0);

        // -----------------------------------------------------------------
        // 3. Achats fournisseurs
        // -----------------------------------------------------------------
        $buySql = "SELECT
            COALESCE(SUM(paid_amount), 0) AS total_purchases,
            COUNT(*) AS nb_purchases
        FROM purchases
        WHERE DATE(purchase_date) BETWEEN :start AND :end AND included = 1";

        $bStmt = $bdd->prepare($buySql);
        $bStmt->execute([':start' => $startDate, ':end' => $endDate]);
        $buy = $bStmt->fetch(PDO::FETCH_ASSOC);
        $result['total_purchases'] = floatval($buy['total_purchases'] ?? 0);
        $result['nb_purchases']    = (int) ($buy['nb_purchases'] ?? 0);

        return $result;
    }

    /**
     * Jours avec au moins un article de l'activité vendu.
     */
    public function getDaysWithSales(string $owner, string $startDate, string $endDate): array
    {
        $activity = $owner === 'broc' ? 'BROC' : 'FRIPE';
        $bdd = $this->dbConnect();

        $sql = "SELECT DISTINCT DATE(s.sale_date)
            FROM sales s
            WHERE s.client_id IS NULL AND s.included = 1
              AND s.sale_date >= :start AND s.sale_date < DATE_ADD(:end, INTERVAL 1 DAY)
              AND s.id IN (
                  SELECT DISTINCT a.sale_id FROM articles a
                  WHERE a.sale_id IS NOT NULL
                    AND a.subcategory IN (
                        SELECT sub.subcategory_name FROM subcategories sub WHERE sub.activity = :activity
                    )
              )
            ORDER BY s.sale_date ASC";

        $stmt = $bdd->prepare($sql);
        $stmt->execute([':start' => $startDate, ':end' => $endDate, ':activity' => $activity]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Vérifie si période déclarée.
     */
    public function isPeriodDeclared(string $activity, string $startDate, string $endDate): bool
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare(
            "SELECT COUNT(*) FROM urssaf_declarations
             WHERE activity = :activity AND period_start = :start AND period_end = :end AND declared_at IS NOT NULL"
        );
        $stmt->execute([':activity' => $activity, ':start' => $startDate, ':end' => $endDate]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Détails d'une déclaration existante.
     */
    public function getDeclarationInfo(string $activity, string $startDate, string $endDate): ?array
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare(
            "SELECT * FROM urssaf_declarations
             WHERE activity = :activity AND period_start = :start AND period_end = :end AND declared_at IS NOT NULL"
        );
        $stmt->execute([':activity' => $activity, ':start' => $startDate, ':end' => $endDate]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Marque une période comme déclarée (upsert).
     */
    public function markAsDeclared(string $activity, string $startDate, string $endDate, array $data): int
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare(
            "INSERT INTO urssaf_declarations
             (activity, period_start, period_end, total_ca, total_cash, total_card, total_cheque, total_bank_transfer, nb_sales, nb_articles, declared_at, declared_by)
             VALUES (:activity, :start, :end, :total_ca, :total_cash, :total_card, :total_cheque, :total_bank_transfer, :nb_sales, :nb_articles, NOW(), :declared_by)
             ON DUPLICATE KEY UPDATE
                 total_ca   = :total_ca,
                 total_cash = :total_cash,
                 total_card = :total_card,
                 total_cheque = :total_cheque,
                 total_bank_transfer = :total_bank_transfer,
                 nb_sales = :nb_sales,
                 nb_articles = :nb_articles,
                 declared_at = NOW(),
                 declared_by = :declared_by"
        );
        $stmt->execute([
            ':activity'            => $activity,
            ':start'               => $startDate,
            ':end'                 => $endDate,
            ':total_ca'            => $data['total_ca'] ?? 0,
            ':total_cash'          => $data['total_cash'] ?? 0,
            ':total_card'          => $data['total_card'] ?? 0,
            ':total_cheque'        => $data['total_cheque'] ?? 0,
            ':total_bank_transfer' => $data['total_bank_transfer'] ?? 0,
            ':nb_sales'            => $data['nb_sales'] ?? 0,
            ':nb_articles'         => $data['nb_articles'] ?? 0,
            ':declared_by'        => $data['declared_by'] ?? 'système',
        ]);
        return (int) $bdd->lastInsertId();
    }
}
