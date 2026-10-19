<?php
/**
 * tests/VerifyDualCalculation.php
 * Vérification concurrentielle : méthode A (category) vs méthode B (subcategories.activity)
 * Source de vérité : subcategories.activity doit être alignée avec FRIPE_CATS.
 * À exécuter régulièrement pour détecter toute dérive.
 */
chdir('.');
require 'model/BddManager.php';

$ref = new ReflectionClass('OpenClassroom\Blog\Model\BddManager');
$method = $ref->getMethod('dbConnect');
$method->setAccessible(true);
$bdd = $method->invoke(new OpenClassroom\Blog\Model\BddManager());

// Méthode A : par category (règle métiers hardcodée)
$fripe_cats = ['Femmes','Hommes','Enfants','Accessoires','Mercerie','Bougies'];

// Requêtes ventes CB depuis dernière clôture
$from = '2026-01-01'; // ou LAST_TRANSFER_DATE

$r = $bdd->query('
    SELECT a.category, SUM(a.quantity * a.unit_price) AS total
    FROM articles a
    JOIN sales s ON a.sale_id = s.id
    WHERE s.payment_method = "card" AND s.sale_date >= "' . $from . '"
    GROUP BY a.category'
);

$a_fripe = 0; $a_broc = 0;
foreach ($r as $row) {
    if (in_array($row['category'], $fripe_cats, true)) {
        $a_fripe += (float)$row['total'];
    } else {
        $a_broc += (float)$row['total'];
    }
}

// Méthode B : par subcategories.activity
$r2 = $bdd->query('
    SELECT sc.activity, SUM(a.quantity * a.unit_price) AS total
    FROM articles a
    JOIN sales s ON a.sale_id = s.id
    JOIN subcategories sc ON a.category = sc.category_name AND a.subcategory = sc.subcategory_name
    WHERE s.payment_method = "card" AND s.sale_date >= "' . $from . '"
    GROUP BY sc.activity'
);

$b_fripe = 0; $b_broc = 0;
foreach ($r2 as $row) {
    if ($row['activity'] === 'FRIPE') {
        $b_fripe += (float)$row['total'];
    } else {
        $b_broc += (float)$row['total'];
    }
}

$diff_fripe = abs($a_fripe - $b_fripe);
$diff_broc = abs($a_broc - $b_broc);

echo "=== Vérification FRIPE/BROC (2 méthodes) ===\n";
echo "Méthode A (category):    FRIPE=" . number_format($a_fripe, 2, ',', ' ') . " €  BROC=" . number_format($a_broc, 2, ',', ' ') . " €\n";
echo "Méthode B (subcat.act):  FRIPE=" . number_format($b_fripe, 2, ',', ' ') . " €  BROC=" . number_format($b_broc, 2, ',', ' ') . " €\n";
echo "Écart: " . number_format($diff_fripe + $diff_broc, 2, ',', ' ') . " €\n";

if ($diff_fripe <= 0.01 && $diff_broc <= 0.01) {
    echo "PASS — Les 2 méthodes sont alignées.\n";
    exit(0);
} else {
    echo "FAIL — Écart détecté. Voir Source-Verite-FRIPE-BROC.md pour correction.\n";
    exit(1);
}
