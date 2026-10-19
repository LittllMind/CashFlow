<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Résumé journalier - Brocante</title>
    <link rel="stylesheet" href="assets/css/sale-modern.css" />
    <style>

    </style>
</head>

<body>

    <h1>Brocante Chez Paul</h1>
    <h2>Résumé Journalier</h2>

    <div class="summary">
        <p><strong>Date :</strong> <?= $formattedDate ?></p>
        <p><strong>Heure d'impression :</strong> <?= $printTime ?> </p>
        <p><strong>Total ventes :</strong> <?= $salesCount ?> — <strong>Montant :</strong> <?= $totalSales ?> €</p>
        <p><strong>Total achats :</strong> <?= $purchasesCount ?> — <strong>Montant :</strong> <?= $totalPurchases ?> €</p>
        <p><strong>Bénéfice :</strong> <?= $totalSales - $totalPurchases ?> €</p>
    </div>

    <h2>Détail des ventes</h2>
    <table>
        <thead>
            <tr>
                <th>Heure</th>
                <th>Article</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Prix total</th>
                <th>Commentaire</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (empty($brocante)): ?>
                <p class="no-articles">Aucun article vendu ce jour.</p>
            <?php else: ?>
                <?php foreach ($brocante as $item):
                    if (isset($item['sale_id']) && !empty($item['sale_id'])): ?>

                        <tr>

                            <td><?= date('H:i', strtotime($item['created_at'])) ?></td>

                            <td><?= htmlspecialchars($item['category']) ?> - <?= htmlspecialchars($item['subcategory']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td><?= htmlspecialchars($item['unit_price']) ?> €</td>
                            <td><?= htmlspecialchars($item['unit_price'] * $item['quantity']) ?> €</td>
                            <td><?= htmlspecialchars($item['comment']) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>

        </tbody>
    <?php endif; ?>
    <!-- autres lignes dynamiques -->
    </table>

    <h2>Détail des achats</h2>
    <table>
        <thead>
            <tr>
                <th>Heure</th>
                <th>Article</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Prix total</th>
                <th>Fournisseur</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (empty($brocante)): ?>
                <p class="no-articles">Aucun article acheté ce jour.</p>
            <?php else: ?>
                <?php foreach ($brocante as $item):
                    if (isset($item['purchase_id']) && !empty($item['purchase_id'])): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($item['created_at'])) ?></td>

                            <td><?= htmlspecialchars($item['category']) ?> - <?= htmlspecialchars($item['subcategory']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td><?= htmlspecialchars($item['unit_price']) ?> €</td>
                            <td><?= htmlspecialchars($item['unit_price'] * $item['quantity']) ?> €</td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- autres lignes dynamiques -->
        </tbody>
    </table>

    <div class="signature">
        <div>
            <strong>Observations :</strong>
            <p style="border-top: 1px solid #000; margin-top: 3rem;">&nbsp;</p>
        </div>
        <div>
            <strong>Signature :</strong>
            <p style="border-top: 1px solid #000; margin-top: 3rem;">&nbsp;</p>
        </div>
    </div>

    <div class="footer">
        <p>Document généré automatiquement — La BROC</p>
    </div>

</body>

</html>



<?php ob_start(); ?>


<div class="result-panel">
    <div class="result-panel-head">
        <?php if (isset($message)): ?>
            <h6 class="alert-message">
                <?= $message ?>
            </h6>
        <?php endif; ?>
    </div>

    <div class="result-panel-form">
        <div class="date-selectors">
            <?php if ($selectedDate): ?>
                <input type="date" id="selectedDate" name="day" value="<?= $selectedDate ?>">
            <?php else: ?>
                <input type="date" id="selectedDate" name="day" value="<?= date('Y-m-d') ?>">
            <?php endif; ?>
        </div>
    </div>

    <?php $content = ob_get_clean(); ?>

    <?php require('view/template/mainTemplate.php'); ?>