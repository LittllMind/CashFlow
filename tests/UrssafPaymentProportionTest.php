<?php
/**
 * Test : répartition proportionnelle des paiements par activité.
 * Scénario : vente mixte BROC+FRIPE de 100€ payée en espèces.
 * Attendu : chaque activité reçoit sa part proportionnelle du paiement.
 */

require_once __DIR__ . '/../model/BddManager.php';
require_once __DIR__ . '/../model/UrssafManager.php';

use OpenClassroom\Blog\Model\UrssafManager;

// --- Connexion BDD ---
$manager = new UrssafManager();
$bdd = $manager->getConnection();

// --- Créer une vente mixte pour le test ---
$bdd->beginTransaction();

try {
    // Insérer une vente test
    $stmt = $bdd->prepare("INSERT INTO sales (sale_date, total_amount, payment_method, client_id, included, created_at)
                           VALUES ('2026-07-15 10:00:00', 100.00, 'cash', NULL, 1, NOW())");
    $stmt->execute();
    $saleId = (int) $bdd->lastInsertId();

    // Insérer 2 articles : 1 BROC, 1 FRIPE
    // BROC : subcategory 'Vêtements' (activity BROC supposée)
    // FRIPE : subcategory 'Accessoires' (activity FRIPE supposée)
    // NOTE: adapter les subcategory selon les données réelles

    // Requête pour trouver une subcategory BROC et une FRIPE
    $subBroc = $bdd->query("SELECT subcategory_name FROM subcategories WHERE activity = 'BROC' LIMIT 1")->fetchColumn();
    $subFripe = $bdd->query("SELECT subcategory_name FROM subcategories WHERE activity = 'FRIPE' LIMIT 1")->fetchColumn();

    if (!$subBroc || !$subFripe) {
        echo "ERREUR : pas de subcategories BROC/FRIPE trouvées.\n";
        $bdd->rollBack();
        exit(1);
    }

    $stmt = $bdd->prepare("INSERT INTO articles (sale_id, category, subcategory, quantity, unit_price, comment, created_at, updated_at)
                           VALUES (:sale, 'TEST', :sub, 1, 50.00, 'test urssaf prop', NOW(), NOW())");
    $stmt->execute([':sale' => $saleId, ':sub' => $subBroc]);
    $articleBrocId = (int) $bdd->lastInsertId();

    $stmt = $bdd->prepare("INSERT INTO articles (sale_id, category, subcategory, quantity, unit_price, comment, created_at, updated_at)
                           VALUES (:sale, 'TEST', :sub, 1, 50.00, 'test urssaf prop', NOW(), NOW())");
    $stmt->execute([':sale' => $saleId, ':sub' => $subFripe]);
    $articleFripeId = (int) $bdd->lastInsertId();

    // --- Calcul URSSAF pour la période ---
    $dataBroc = $manager->getPeriodCaArticles('broc', '2026-07-01', '2026-07-31');
    $dataFripe = $manager->getPeriodCaArticles('fripe', '2026-07-01', '2026-07-31');

    echo "=== APRES CORRECTION (vente mixte 100€ cash) ===\n";
    echo "BROC  → CA: {$dataBroc['total_ca']} € | Cash: {$dataBroc['total_cash']} € | Card: {$dataBroc['total_card']} €\n";
    echo "FRIPE → CA: {$dataFripe['total_ca']} € | Cash: {$dataFripe['total_cash']} € | Card: {$dataFripe['total_card']} €\n";
    echo "Somme paiements BROC : " . ($dataBroc['total_cash'] + $dataBroc['total_card'] + $dataBroc['total_cheque'] + $dataBroc['total_bank_transfer']) . " €\n";
    echo "Somme paiements FRIPE: " . ($dataFripe['total_cash'] + $dataFripe['total_card'] + $dataFripe['total_cheque'] + $dataFripe['total_bank_transfer']) . " €\n";

    // --- Assertions ---
    $tolerance = 0.01;
    $errors = [];

    $sumBrocPaiements = $dataBroc['total_cash'] + $dataBroc['total_card'] + $dataBroc['total_cheque'] + $dataBroc['total_bank_transfer'];
    if (abs($sumBrocPaiements - $dataBroc['total_ca']) > $tolerance) {
        $errors[] = "BROC : somme paiements ({$sumBrocPaiements}) ≠ CA ({$dataBroc['total_ca']})";
    }

    $sumFripePaiements = $dataFripe['total_cash'] + $dataFripe['total_card'] + $dataFripe['total_cheque'] + $dataFripe['total_bank_transfer'];
    if (abs($sumFripePaiements - $dataFripe['total_ca']) > $tolerance) {
        $errors[] = "FRIPE : somme paiements ({$sumFripePaiements}) ≠ CA ({$dataFripe['total_ca']})";
    }

    if (!empty($errors)) {
        echo "\n❌ ECHEC :\n" . implode("\n", $errors) . "\n";
    } else {
        echo "\n✅ OK — Somme des paiements = CA pour chaque activité.\n";
    }

    // Nettoyage
    $bdd->prepare("DELETE FROM articles WHERE id IN (:broc, :fripe)")->execute([':broc' => $articleBrocId, ':fripe' => $articleFripeId]);
    $bdd->prepare("DELETE FROM sales WHERE id = :id")->execute([':id' => $saleId]);
    $bdd->commit();

} catch (Exception $e) {
    $bdd->rollBack();
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
