<?php
/**
 * Test statique : URSSAF Report Panel
 * Executer : php tests/UrssafReportTest.php
 */

$base = __DIR__ . '/..';
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "$base/model/BddManager.php";
require_once "$base/model/SalesManager.php";
require_once "$base/model/UrssafManager.php";

use OpenClassroom\Blog\Model\UrssafManager;

$errors = [];
function assertTrue($cond, $msg) {
    global $errors;
    if (!$cond) {
        $errors[] = "FAIL: $msg";
        echo "FAIL: $msg\n";
    } else {
        echo "PASS: $msg\n";
    }
}

$urssafManager = new UrssafManager();

// 1. UrssafManager instance OK
assertTrue($urssafManager !== null, 'UrssafManager instancie');

// 2. Donnees mensuelles BROC (ex: juin 2026)
$monthlyBroc = $urssafManager->getMonthlyCa('broc', '2026-06-01');
assertTrue(isset($monthlyBroc['total_ca']), 'BROC mensuel a total_ca');
assertTrue(isset($monthlyBroc['nb_sales']), 'BROC mensuel a nb_sales');
assertTrue(is_numeric($monthlyBroc['total_ca']), 'total_ca est numerique');
assertTrue($monthlyBroc['total_ca'] >= 0, 'total_ca >= 0');

echo "BROC juin 2026 CA: {$monthlyBroc['total_ca']}, Ventes: {$monthlyBroc['nb_sales']}, Articles: {$monthlyBroc['nb_articles']}\n";

// 3. Donnees trimestrielles FRIPE (ex: Q2 2026)
$quarterlyFripe = $urssafManager->getQuarterlyCa('fripe', '2026-04-01');
assertTrue(isset($quarterlyFripe['total_ca']), 'FRIPE trimestre a total_ca');
assertTrue(isset($quarterlyFripe['nb_sales']), 'FRIPE trimestre a nb_sales');
assertTrue(is_numeric($quarterlyFripe['total_ca']), 'FRIPE total_ca numerique');
assertTrue($quarterlyFripe['total_ca'] >= 0, 'FRIPE total_ca >= 0');

// 4. Donnees annuelles BROC (ex: 2026)
$annualBroc = $urssafManager->getAnnualCa('broc', '2026-01-01');
assertTrue(isset($annualBroc['total_ca']), 'BROC annuel a total_ca');
assertTrue(isset($annualBroc['nb_sales']), 'BROC annuel a nb_sales');
assertTrue(is_numeric($annualBroc['total_ca']) && $annualBroc['total_ca'] >= 0, 'BROC annuel total_ca >= 0');
echo "BROC annee 2026 : CA=" . $annualBroc['total_ca'] . ", Ventes=" . $annualBroc['nb_sales'] . "\n";

// Verification coherences : annuel = somme des 12 mois
$totalMonths = 0;
for ($m = 1; $m <= 12; $m++) {
    $mData = $urssafManager->getMonthlyCa('broc', sprintf('2026-%02d-01', $m));
    $totalMonths += $mData['total_ca'];
}
echo "Somme mensuelle BROC 2026 : " . number_format($totalMonths, 2) . "\n";
echo "Annuel BROC 2026       : " . number_format($annualBroc['total_ca'], 2) . "\n";
$diff = abs($annualBroc['total_ca'] - $totalMonths);
assertTrue($diff < 0.02, 'CA annuel ~ somme des 12 mois (diff=' . $diff . ')');
// 4. Check deja declare (ne doit pas crash si table vide)
$isDeclared = $urssafManager->isPeriodDeclared('broc', '2026-06-01', '2026-06-30');
assertTrue(is_bool($isDeclared), 'isPeriodDeclared retourne bool');

// 5. Jours avec ventes
$daysWithSales = $urssafManager->getDaysWithSales('broc', '2026-06-01', '2026-06-30');
assertTrue(is_array($daysWithSales), 'getDaysWithSales retourne array');

if (count($errors) === 0) {
    echo "\nALL PASSED\n";
    exit(0);
} else {
    echo "\n" . count($errors) . " ERROR(S)\n";
    exit(1);
}
