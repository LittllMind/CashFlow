<?php
// tests/BougiesSubcategoriesTest.php — Vérifie affichage/tri/couleurs des Bougies

$passed = 0;
$failed = 0;

function assertTrue($condition, $msg) {
    global $passed, $failed;
    if ($condition) {
        echo '  PASS : ' . $msg . PHP_EOL;
        $passed++;
    } else {
        echo '  FAIL : ' . $msg . PHP_EOL;
        $failed++;
    }
}

$js = file_get_contents(__DIR__ . '/../view/control-panel/sale/update/update.js');

// 1. Pas de filtre article_count > 5 pour Bougies (ou autre catégorie)
assertTrue(
    strpos($js, 'subcat.article_count > 5') === false,
    'update.js : filtre article_count > 5 supprimé'
);

// 2. Tri Bougies par prix croissant
assertTrue(
    strpos($js, "category === 'Bougies'") !== false && strpos($js, 'parsePrice(a.subcategory_name)') !== false,
    'update.js : tri Bougies par prix croissant présent'
);

// 3. Fonction parsePrice
assertTrue(
    strpos($js, 'function parsePrice(subcategoryName)') !== false,
    'update.js : helper parsePrice() présent'
);

// 4. getPriceGradient restreint à Bougies
assertTrue(
    strpos($js, 'function getPriceGradient(subcategoryName)') !== false,
    'update.js : getPriceGradient() présent'
);
assertTrue(
    strpos($js, "selectedCategory !== 'Bougies'") !== false,
    'update.js : dégradés réservés à la catégorie Bougies'
);

// 5. Whitelist des modèles Bougies
assertTrue(
    strpos($js, 'BOUGIES_TARGET') !== false,
    'update.js : whitelist BOUGIES_TARGET présente'
);
foreach (['Arbre (3€)', 'Boîte métal (6€)', 'Cylindre (6€)', 'Ganesh (6€)', 'Cylindre abeille (9€)', 'Fleur Lotus (9€)', 'Clown (13€)'] as $model) {
    assertTrue(
        strpos($js, $model) !== false,
        "update.js : modèle $model dans la whitelist"
    );
}

// 6. Classes de prix 3/6/9/13 dans le JS
foreach ([3, 6, 9, 13] as $price) {
    assertTrue(
        strpos($js, "'price-$price'") !== false || strpos($js, '"price-' . $price . '"') !== false,
        "update.js : classe price-$price référencée"
    );
}

// 7. CSS : les 4 dégradés de prix
$css = file_get_contents(__DIR__ . '/../assets/css/sale-modern.css');
foreach ([3, 6, 9, 13] as $price) {
    assertTrue(
        strpos($css, ".price-$price") !== false,
        "sale-modern.css : dégradé .price-$price présent"
    );
}

// 8. Scores de stock
$subcatManager = file_get_contents(__DIR__ . '/../model/SubcategoriesManager.php');
assertTrue(
    strpos($subcatManager, 'stock_score') !== false,
    'SubcategoriesManager.php : stock_score calculé'
);
assertTrue(
    strpos($js, 'stock_score') !== false,
    'update.js : stock_score consommé'
);

// 9. Syntaxe JS
$output = [];
$return = 0;
exec('node --check ' . escapeshellarg(__DIR__ . '/../view/control-panel/sale/update/update.js') . ' 2>&1', $output, $return);
assertTrue(
    $return === 0,
    'Syntaxe JS OK : update.js'
);

echo PHP_EOL . 'Résultat : ' . $passed . ' pass, ' . $failed . ' fail' . PHP_EOL;
exit($failed > 0 ? 1 : 0);
