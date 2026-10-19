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
        <p><strong>Date :</strong> 14/06/2025</p>
        <p><strong>Heure d'impression :</strong> 14:30</p>
        <p><strong>Total ventes :</strong> 15 — <strong>Montant :</strong> 240,00 €</p>
        <p><strong>Total achats :</strong> 8 — <strong>Montant :</strong> 135,00 €</p>
        <p><strong>Bénéfice :</strong> +105,00 €</p>
    </div>

    <!-- <table class="summary-table">
  <thead>
    <tr>
      <th colspan="4">Résumé journalier</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Date</strong></td>
      <td>14/06/2025</td>
    </tr>
    <tr>
      <td><strong>Heure d'impression</strong></td>
      <td>14:30</td>
    </tr>
    <tr>
      <td><strong>Total ventes</strong></td>
      <td>15</td>
      <td><strong>Montant</strong></td>
        <td>240,00 €</td>
    </tr>
    <tr>
      <td><strong>Total achats</strong></td>
      <td>8</td>
      <td><strong>Montant</strong></td>
        <td>135,00 €</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
      <td><strong>Bénéfice</strong></td>
      <td>+105,00 €</td>
    </tr>
  </tbody>
</table> -->


    <h2>Détail des ventes</h2>
    <table>
        <thead>
            <tr>
                <th>Heure</th>
                <th>Article</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Prix total</th>
                <th>Client</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>09:15</td>
                <td>Chaise vintage</td>
                <td>1</td>
                <td>35,00 €</td>
                <td>35,00 €</td>
                <td>Jean Dupont</td>
            </tr>
            <!-- autres lignes dynamiques -->
        </tbody>
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
            <tr>
                <td>11:00</td>
                <td>Lampe ancienne</td>
                <td>2</td>
                <td>15,00 €</td>
                <td>30,00 €</td>
                <td>Emma B.</td>
            </tr>
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
        <p>Document généré automatiquement — Brocante Chez Paul</p>
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

    <div class="result-panel-list">

        <div class="result-panel-list-form">

            <?php
            if (empty($clothes) && empty($brocante)): ?>
                <p class="no-articles">Aucun article vendu ce jour.</p>
            <?php else: ?>
                <div class="result-panel-list-table">
                    <table>
                        <thead>
                            <tr>
                                <th>FRIPE</th>
                                <th></th>
                                <th>Unités</th>
                                <th>Prix</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clothes as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['category']) ?></td>
                                    <td><?= htmlspecialchars($item['subcategory']) ?></td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td><?= htmlspecialchars($item['unit_price']) ?></td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                </tr>
                                <tr>
                                    <td>Carte</td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th>BROC</th>
                                <th></th>
                                <th>Unités</th>
                                <th>Prix</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($brocante as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['category']) ?></td>
                                    <td><?= htmlspecialchars($item['subcategory']) ?></td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td><?= htmlspecialchars($item['unit_price']) ?></td>
                                </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php $content = ob_get_clean(); ?>

<?php require('view/template/mainTemplate.php'); ?>