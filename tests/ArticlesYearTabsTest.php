<?php
// tests/ArticlesYearTabsTest.php — RED→GREEN : onglets années + historique sur la page Articles

$root = dirname(__DIR__);
$errors = [];

function check($label, $condition) {
    global $errors;
    if ($condition) {
        echo "✓ $label" . PHP_EOL;
    } else {
        echo "✗ $label" . PHP_EOL;
        $errors[] = $label;
    }
}

function fileContains($path, $needle) {
    return file_exists($path) && strpos(file_get_contents($path), $needle) !== false;
}

function syntaxCheck($path) {
    $output = [];
    $return = 0;
    exec('php -l ' . escapeshellarg($path) . ' 2>&1', $output, $return);
    return $return === 0;
}

function jsSyntaxCheck($path) {
    $output = [];
    $return = 0;
    exec('node --check ' . escapeshellarg($path) . ' 2>&1', $output, $return);
    return $return === 0;
}

$files = [
    'model'       => $root . '/model/ArticlesManager.php',
    'controller'  => $root . '/controller/ArticlesController.php',
    'view'        => $root . '/view/articles/articles.php',
];

check('ArticlesManager.php est lisible', file_exists($files['model']));
check('ArticlesController.php est lisible', file_exists($files['controller']));
check('view/articles/articles.php est lisible', file_exists($files['view']));

// 1. Manager : nouveau compteur/méthode annuel
$managerContent = file_get_contents($files['model']);
check('ArticlesManager expose getArticlesByCategoryAndSubcategoryByYear', strpos($managerContent, 'function getArticlesByCategoryAndSubcategoryByYear') !== false);
check('ArticlesManager expose les années disponibles (getDistinctArticleYears)', strpos($managerContent, 'function getDistinctArticleYears') !== false);
check('getArticlesByCategoryAndSubcategoryByYear accepte "all" comme valeur', strpos($managerContent, "'all'") !== false || strpos($managerContent, '"all"') !== false);
check('Requête annuelle SARGable (range dates prepared)', strpos($managerContent, 'a.created_at >= :dateStart') !== false && strpos($managerContent, 'a.created_at < :dateEnd') !== false);

// 2. Route dans index.php
$indexContent = file_get_contents($root . '/index.php');
check("Route 'articlesFetchByYear' existe", strpos($indexContent, "'articlesFetchByYear'") !== false || strpos($indexContent, 'articlesFetchByYear') !== false);
check("Route 'articlesFetchByYear' passe le paramètre year", strpos($indexContent, '$year') !== false);

// 3. Controller
$controllerContent = file_get_contents($files['controller']);
check('fetchArticlesByYear appelle le manager', strpos($controllerContent, 'fetchArticlesByYear') !== false);
check('fetchArticlesByYear retourne JSON', strpos($controllerContent, "json_encode(") !== false);

// 4. Vue : design system sale-modern + onglets
$viewContent = file_get_contents($files['view']);
check('Vue utilise le wrapper sale-form-wrapper/sale-panel', strpos($viewContent, 'sale-form-wrapper') !== false && strpos($viewContent, 'sale-panel') !== false);
check('Vue contient des onglets années (.year-tabs)', strpos($viewContent, 'year-tabs') !== false);
check('Vue a un onglet "Historique"', strpos($viewContent, 'Historique') !== false);
check('Vue expose les années disponibles (availableYears)', strpos($viewContent, 'availableYears') !== false);
check('Vue expose l\'année sélectionnée (selectedYear)', strpos($viewContent, 'selectedYear') !== false);
check('Vue affiche un badge de synthèse (.sp-stat-card)', strpos($viewContent, 'sp-stat-card') !== false);

// 5. JS : fonction de fetch et résumé
preg_match('/<script>(.*?)<\/script>/s', $viewContent, $scriptMatch);
$script = $scriptMatch[1] ?? '';
check('JS contient fetchArticlesByYear()', strpos($script, 'fetchArticlesByYear') !== false);
check('JS bascule l\'année active', strpos($script, 'setYear') !== false && strpos($script, 'active') !== false);
check('JS injecte le résumé achat/vente/bilan', strpos($script, 'summary-achats-total') !== false && strpos($script, 'summary-ventes-total') !== false && strpos($script, 'summary-bilan') !== false);
check('JS appelle la route articlesFetchByYear', strpos($script, 'articlesFetchByYear') !== false);

// 6. Syntaxes
foreach ($files as $label => $path) {
    check("Syntaxe PHP OK ($label)", syntaxCheck($path));
}

// 7. Cache-buster style.css après modification
$mainTemplate = file_get_contents($root . '/view/template/mainTemplate.php');
// Le style.css n'a pas de cache-buster actuel ; on s'assure juste que sale-modern.css charge
$hasSaleModern = strpos($mainTemplate, "'sale-modern.css'" ) !== false || strpos($mainTemplate, 'sale-modern.css') !== false;
check('mainTemplate charge sale-modern.css pour les routes articles', $hasSaleModern && strpos($mainTemplate, "'articles'") !== false);

// 8. SCSS/CSS articles spécifique
$css = $root . '/assets/css/articles.css';
check('articles.css existe', file_exists($css));
check('articles.css contient des classes year-tabs', fileContains($css, 'year-tabs'));
check('articles.css hérite du design system (sp-stats-row / sp-stat-card)', fileContains($css, 'sp-stat-card') || fileContains($css, 'sp-stats-row'));

if (empty($errors)) {
    echo PHP_EOL . 'RESULT: PASS' . PHP_EOL;
    exit(0);
} else {
    echo PHP_EOL . 'RESULT: FAIL (' . count($errors) . ')' . PHP_EOL;
    exit(1);
}
