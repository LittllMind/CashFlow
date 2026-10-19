<?php

namespace OpenClassroom\Blog\Model;

require_once ("model/BddManager.php");

class SubcategoriesManager extends BddManager
{
    // ------------------------ G E T

    public function getSubcategories()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT id, category_id, category_name, subcategory_name
        FROM subcategories
        ORDER BY subcategory_name ASC'
        );

        $categories = $req->fetchAll();
        return $categories;
    }

    public function getSubcategoriesByArticleCount()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            "SELECT sc.id, sc.category_id, sc.category_name, sc.subcategory_name, COUNT(a.id) AS article_count,
                COALESCE(SUM(CASE WHEN a.purchase_id IS NOT NULL THEN a.quantity ELSE 0 END), 0)
                - COALESCE(SUM(CASE WHEN a.sale_id IS NOT NULL THEN a.quantity ELSE 0 END), 0) AS stock_score
         FROM subcategories sc
         LEFT JOIN articles a ON sc.subcategory_name = a.subcategory AND sc.category_name = a.category
         GROUP BY sc.id
         ORDER BY article_count DESC"
        );

        $subcategories = $req->fetchAll();
        return $subcategories;
    }

}