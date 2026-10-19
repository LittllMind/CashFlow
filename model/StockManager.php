<?php

namespace OpenClassroom\Blog\Model;

use PDO;

require_once(__DIR__ . '/BddManager.php');

class StockManager extends BddManager
{
    /**
     * Retourne le solde theorique par categorie/sous-categorie.
     * @return array [['category' => ..., 'subcategory' => ..., 'balance' => ...], ...]
     */
    public function getStockBalances($category = null, $subcategory = null)
    {
        $bdd = $this->dbConnect();
        $where = [];
        $params = [];
        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }
        if ($subcategory) {
            $where[] = 'subcategory = ?';
            $params[] = $subcategory;
        }
        $sql = 'SELECT category, subcategory, SUM(quantity) AS balance'
             . ' FROM stock_movements';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' GROUP BY category, subcategory ORDER BY category, subcategory';

        $req = $bdd->prepare($sql);
        $req->execute($params);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Solde d'une seule sous-categorie.
     */
    public function getBalance($category, $subcategory)
    {
        $rows = $this->getStockBalances($category, $subcategory);
        return (int) ($rows[0]['balance'] ?? 0);
    }

    /**
     * Ajoute un mouvement de stock (achat, vente, inventaire, ajustement).
     */
    public function addMovement($category, $subcategory, $movementType, $quantity, $unitPrice = null, $referenceId = null, $referenceType = null, $comment = null, $userId = null)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare('
            INSERT INTO stock_movements
            (category, subcategory, movement_type, quantity, unit_price, reference_id, reference_type, comment, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $req->execute([
            $category,
            $subcategory,
            $movementType,
            $quantity,
            $unitPrice,
            $referenceId,
            $referenceType,
            $comment,
            $userId,
        ]);
        return $bdd->lastInsertId();
    }

    /**
     * Liste les mouvements d'une categorie/sous-categorie.
     */
    public function getMovements($category = null, $subcategory = null, $limit = 100)
    {
        $bdd = $this->dbConnect();
        $where = [];
        $params = [];
        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }
        if ($subcategory) {
            $where[] = 'subcategory = ?';
            $params[] = $subcategory;
        }
        $sql = 'SELECT * FROM stock_movements';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC LIMIT ?';
        $params[] = (int) $limit;

        $req = $bdd->prepare($sql);
        $req->execute($params);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }
}
