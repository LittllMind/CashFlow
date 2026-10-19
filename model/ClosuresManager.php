<?php

namespace OpenClassroom\Blog\Model;

use \PDO;

require_once("model/BddManager.php");

/**
 * ClosuresManager — accès table daily_closures.
 */
class ClosuresManager extends BddManager
{
    public function getConnection()
    {
        return $this->dbConnect();
    }

    /**
     * Crée une clôture, retourne l'id inséré.
     *
     * @param  array $data
     * @return int
     */
    public function createClosure(array $data): int
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare('
            INSERT INTO daily_closures (
                owner, closure_date, closure_number,
                first_sale_id, last_sale_id,
                first_purchase_id, last_purchase_id,
                total_sales, total_cash, total_card, total_cheque, total_bank_transfer,
                total_items, total_sales_count,
                total_purchases, total_purchases_count,
                total_refunds, total_cancellations, total_corrections,
                opening_cash_float, expected_cash, counted_cash, cash_difference,
                notes, generated_by, pdf_path, csv_path,
                content_hash, previous_closure_hash, signed_paper_archived
            ) VALUES (
                :owner, :closure_date, :closure_number,
                :first_sale_id, :last_sale_id,
                :first_purchase_id, :last_purchase_id,
                :total_sales, :total_cash, :total_card, :total_cheque, :total_bank_transfer,
                :total_items, :total_sales_count,
                :total_purchases, :total_purchases_count,
                :total_refunds, :total_cancellations, :total_corrections,
                :opening_cash_float, :expected_cash, :counted_cash, :cash_difference,
                :notes, :generated_by, :pdf_path, :csv_path,
                :content_hash, :previous_closure_hash, :signed_paper_archived
            )
        ');

        $stmt->execute([
            ':owner'                => $data['owner'],
            ':closure_date'         => $data['closure_date'],
            ':closure_number'       => $data['closure_number'],
            ':first_sale_id'        => $data['first_sale_id'] ?? null,
            ':last_sale_id'         => $data['last_sale_id'] ?? null,
            ':first_purchase_id'    => $data['first_purchase_id'] ?? null,
            ':last_purchase_id'     => $data['last_purchase_id'] ?? null,
            ':total_sales'          => $data['total_sales'] ?? 0,
            ':total_cash'           => $data['total_cash'] ?? 0,
            ':total_card'          => $data['total_card'] ?? 0,
            ':total_cheque'        => $data['total_cheque'] ?? 0,
            ':total_bank_transfer' => $data['total_bank_transfer'] ?? 0,
            ':total_items'         => $data['total_items'] ?? 0,
            ':total_sales_count'    => $data['total_sales_count'] ?? 0,
            ':total_purchases'     => $data['total_purchases'] ?? 0,
            ':total_purchases_count' => $data['total_purchases_count'] ?? 0,
            ':total_refunds'        => $data['total_refunds'] ?? 0,
            ':total_cancellations'  => $data['total_cancellations'] ?? 0,
            ':total_corrections'    => $data['total_corrections'] ?? 0,
            ':opening_cash_float'   => $data['opening_cash_float'] ?? null,
            ':expected_cash'        => $data['expected_cash'] ?? null,
            ':counted_cash'         => $data['counted_cash'] ?? null,
            ':cash_difference'      => $data['cash_difference'] ?? null,
            ':notes'                => $data['notes'] ?? '',
            ':generated_by'         => $data['generated_by'] ?? null,
            ':pdf_path'            => $data['pdf_path'] ?? null,
            ':csv_path'            => $data['csv_path'] ?? null,
            ':content_hash'         => $data['content_hash'] ?? null,
            ':previous_closure_hash' => $data['previous_closure_hash'] ?? null,
            ':signed_paper_archived' => !empty($data['signed_paper_archived']) ? 1 : 0,
        ]);

        return (int)$bdd->lastInsertId();
    }

    /**
     * Met à jour une clôture.
     *
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function updateClosure(int $id, array $data): bool
    {
        $bdd = $this->dbConnect();

        // Champs protégés, jamais modifiables par updateClosure :
        // - content_hash / previous_closure_hash : chaîne d'intégrité, figés à la création
        // - locked_at : verrouillé uniquement via lockClosure()
        $allowed = [
            'owner','closure_date','closure_number','first_sale_id','last_sale_id',
            'first_purchase_id','last_purchase_id',
            'total_sales','total_cash','total_card','total_cheque','total_bank_transfer',
            'total_items','total_sales_count','total_purchases','total_purchases_count',
            'total_refunds','total_cancellations','total_corrections',
            'opening_cash_float','expected_cash','counted_cash','cash_difference',
            'notes','generated_by','pdf_path','csv_path',
            'signed_paper_archived'
        ];

        $fields = [];
        $values = [':id' => $id];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }
            $fields[] = "$key = :$key";
            $values[":$key"] = is_bool($value) ? (int)$value : $value;
        }

        if (empty($fields)) {
            return false;
        }

        $stmt = $bdd->prepare('UPDATE daily_closures SET ' . implode(', ', $fields) . ' WHERE id = :id');
        return $stmt->execute($values);
    }

    public function getClosureById(int $id): ?array
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare('SELECT * FROM daily_closures WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getClosureByOwnerAndDate(string $owner, string $closureDate): ?array
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare('SELECT * FROM daily_closures WHERE owner = :owner AND closure_date = :closure_date');
        $stmt->execute([':owner' => $owner, ':closure_date' => $closureDate]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getClosuresForDate(string $closureDate): array
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare('SELECT * FROM daily_closures WHERE closure_date = :closure_date ORDER BY owner, created_at');
        $stmt->execute([':closure_date' => $closureDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne les journées non clôturées dans une plage pour une liste d'owners.
     *
     * @param array  $owners
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getUnclosedDays(array $owners, string $startDate, string $endDate): array
    {
        $bdd = $this->dbConnect();

        // Dates avec au moins une vente dans sales, hors client_id (ardoises)
        $inStmt = implode(',', array_fill(0, count($owners), '?'));
        $salesStmt = $bdd->prepare("
            SELECT DISTINCT DATE(sale_date) AS sale_day
            FROM sales
            WHERE DATE(sale_date) BETWEEN ? AND ?
              AND client_id IS NULL
              AND included = 1
        ");
        $salesStmt->execute([$startDate, $endDate]);
        $daysWithSales = $salesStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($daysWithSales)) {
            return [];
        }

        // Jours déjà clôturés par owner
        $closureStmt = $bdd->prepare("
            SELECT owner, closure_date
            FROM daily_closures
            WHERE owner IN ($inStmt)
              AND closure_date BETWEEN ? AND ?
        ");
        $closureStmt->execute(array_merge($owners, [$startDate, $endDate]));
        $closed = [];
        while ($row = $closureStmt->fetch(PDO::FETCH_ASSOC)) {
            $closed[$row['closure_date']][] = $row['owner'];
        }

        $unclosed = [];
        foreach ($daysWithSales as $day) {
            foreach ($owners as $owner) {
                if (!isset($closed[$day]) || !in_array($owner, $closed[$day], true)) {
                    $unclosed[] = ['day' => $day, 'owner' => $owner];
                }
            }
        }
        return $unclosed;
    }

    /**
     * Numéro séquentiel par owner/année pour closure_number.
     */
    public function getNextClosureNumber(string $owner, string $year): int
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare("
            SELECT closure_number
            FROM daily_closures
            WHERE owner = :owner AND YEAR(closure_date) = :year
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([':owner' => $owner, ':year' => $year]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 1;
        }
        $parts = explode('-', (string)$row['closure_number']);
        $last = end($parts);
        return is_numeric($last) ? (int)$last + 1 : 1;
    }

    /**
     * Clôtures journalières d'un owner sur un mois donné, avec agrégations.
     *
     * @param string $owner broc ou fripe
     * @param string $monthDate date au format Y-m-d (seul Y-m est retenu)
     * @return array { closures: array, days_with_closures: int, days_locked: int, totals: array }
     */
    public function getMonthlyClosures(string $owner, string $monthDate): array
    {
        $bdd = $this->dbConnect();
        $yearMonth = date('Y-m', strtotime($monthDate));

        $stmt = $bdd->prepare('
            SELECT *
            FROM daily_closures
            WHERE owner = :owner
              AND DATE_FORMAT(closure_date, "%Y-%m") = :year_month
            ORDER BY closure_date ASC
        ');
        $stmt->execute([':owner' => $owner, ':year_month' => $yearMonth]);
        $closures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totals = [
            'sales'            => 0.0,
            'cash'             => 0.0,
            'card'             => 0.0,
            'cheque'           => 0.0,
            'purchases'        => 0.0,
            'purchase_count'   => 0,
            'sales_count'      => 0,
            'items'            => 0,
            'net'              => 0.0,
            'cash_difference'  => 0.0,
        ];

        $daysLocked = 0;
        foreach ($closures as $c) {
            $totals['sales']          += floatval($c['total_sales']);
            $totals['cash']           += floatval($c['total_cash']);
            $totals['card']           += floatval($c['total_card']);
            $totals['cheque']         += floatval($c['total_cheque']);
            $totals['purchases']      += floatval($c['total_purchases']);
            $totals['purchase_count'] += (int)$c['total_purchases_count'];
            $totals['sales_count']    += (int)$c['total_sales_count'];
            $totals['items']          += (int)$c['total_items'];
            if (!empty($c['cash_difference'])) {
                $totals['cash_difference'] += floatval($c['cash_difference']);
            }
            if (!empty($c['locked_at'])) {
                $daysLocked++;
            }
        }

        $totals['net'] = $totals['sales'] - $totals['purchases'];

        return [
            'closures'            => $closures,
            'days_with_closures'  => count($closures),
            'days_locked'         => $daysLocked,
            'totals'              => $totals,
        ];
    }

    /**
     * Dernière clôture verrouillée pour un owner.
     */
    public function getLastLockedClosure(string $owner): ?array
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare('
            SELECT *
            FROM daily_closures
            WHERE owner = :owner AND locked_at IS NOT NULL
            ORDER BY locked_at DESC
            LIMIT 1
        ');
        $stmt->execute([':owner' => $owner]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function lockClosure(int $id): bool
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare('UPDATE daily_closures SET locked_at = NOW() WHERE id = :id AND locked_at IS NULL');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Enregistre une entrée dans le journal d'audit d'une clôture.
     */
    public function logAudit(int $closureId, string $action, int $actorUserId, string $actorIp, ?string $previousHash = null, ?string $newHash = null, ?array $metadata = null): bool
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare('
            INSERT INTO closure_audit_log
                (closure_id, action, actor_user_id, actor_ip, previous_hash, new_hash, metadata)
            VALUES
                (:closure_id, :action, :actor_user_id, :actor_ip, :previous_hash, :new_hash, :metadata)
        ');
        return $stmt->execute([
            ':closure_id'     => $closureId,
            ':action'         => $action,
            ':actor_user_id'  => $actorUserId,
            ':actor_ip'       => $actorIp,
            ':previous_hash'  => $previousHash,
            ':new_hash'       => $newHash,
            ':metadata'       => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    /**
     * Récupère l'historique d'audit d'une clôture.
     */
    public function getAuditLogs(int $closureId): array
    {
        $bdd = $this->dbConnect();
        $stmt = $bdd->prepare('
            SELECT *
            FROM closure_audit_log
            WHERE closure_id = :closure_id
            ORDER BY action_date DESC
        ');
        $stmt->execute([':closure_id' => $closureId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
