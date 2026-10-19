<?php

namespace OpenClassroom\Blog\Model;

use PDO;

require_once("model/BddManager.php");

class CategoriesManager extends BddManager
{
    // ------------------------ P O S T

    // ------------------------ G E T

    public function getCategories()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT id, category_name
        FROM categories
        ORDER BY category_name ASC'
        );

        return $req;
    }

    public function getAllCategories()
    {
        $stmt = $this->dbConnect()->query('SELECT * FROM categories');
        return $stmt->fetchAll();
    }

    public function getAllSubcategories()
    {
        $stmt = $this->dbConnect()->query('SELECT * FROM subcategories');
        return $stmt->fetchAll();
    }
    
    public function getCategoriesByArticleCount()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT c.id, c.category_name, COUNT(a.id) AS article_count
         FROM categories c
         LEFT JOIN articles a ON c.category_name = a.category
         GROUP BY c.id
         ORDER BY article_count DESC'
        );

        return $req->fetchAll();
    }

    public function getCategoriesWithSubcategories(): array
    {
        $db = $this->dbConnect();
        $sql = "
        SELECT c.id AS category_id, c.category_name, s.id AS subcategory_id, s.subcategory_name
        FROM categories c
        LEFT JOIN subcategories s ON c.id = s.category_id
        ORDER BY c.category_name, s.subcategory_name
    ";

        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Structure groupée : [cat_id => [ 'name' => ..., 'subcategories' => [...]]]
        $categories = [];
        foreach ($rows as $row) {
            $catId = $row['category_id'];
            if (!isset($categories[$catId])) {
                $categories[$catId] = [
                    'id' => $catId,
                    'name' => $row['category_name'],
                    'subcategories' => []
                ];
            }

            if (!empty($row['subcategory_id'])) {
                $categories[$catId]['subcategories'][] = [
                    'id' => $row['subcategory_id'],
                    'name' => $row['subcategory_name'],
                ];
            }
        }

        return array_values($categories); // Reset index
    }
}
