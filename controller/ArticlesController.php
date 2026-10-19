
<?php

require_once('model/PurchasesManager.php');
require_once('model/CategoriesManager.php');
require_once('model/SubcategoriesManager.php');
require_once('model/ArticlesManager.php');
require_once('model/ProvidersManager.php');
require_once('model/ProvidersManager.php');
require_once('controller/MainController.php');

require_once('controller/UtilsController.php');

use \OpenClassroom\Blog\Model\PurchasesManager;
use \OpenClassroom\Blog\Model\ProvidersManager;
use \OpenClassroom\Blog\Model\CategoriesManager;
use \OpenClassroom\Blog\Model\ArticlesManager;
use \OpenClassroom\Blog\Model\MessageManager;
use \OpenClassroom\Blog\Model\SubcategoriesManager;


use \OpenClassroom\Blog\Controller\MainController;
use \OpenClassroom\Blog\Controller\UtilsController;

function showArticlesForm()
{
    $articlesManager = new ArticlesManager();
    $availableYears = $articlesManager->getDistinctArticleYears();
    $selectedYear = $_GET['year'] ?? 'all';

    $categoriesManager = new CategoriesManager();
    $categories = $categoriesManager->getAllCategories();
    $subcategories = $categoriesManager->getAllSubcategories();
    

    // convert subcategories to a format suitable for JavaScript
    $subcategoriesJs = [];
    foreach ($subcategories as $sub) {
        $subcategoriesJs[] = [
            'category_name' => $sub['category_name'],
            'subcategory_name' => $sub['subcategory_name']
        ];
    }
    $subcategoriesJson = json_encode($subcategoriesJs);
         

    require('view/articles/articles.php');


}

function fetchArticles($category, $subcategory)
{
    require_once 'model/ArticlesManager.php';
    $articlesManager = new ArticlesManager();
    $articles = $articlesManager->getArticlesByCategoryAndSubcategory($category, $subcategory);

    header('Content-Type: application/json');
    echo json_encode($articles);
}

function fetchArticlesByYear($category, $subcategory, $year = 'all')
{
    require_once 'model/ArticlesManager.php';
    $articlesManager = new ArticlesManager();
    $result = $articlesManager->getArticlesByCategoryAndSubcategoryByYear($category, $subcategory, $year);

    header('Content-Type: application/json');
    echo json_encode($result);
}
