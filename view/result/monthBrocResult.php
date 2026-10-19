<?php ob_start(); ?>

<div class="result-panel">

  <h2>Brocante — mois de <?= date('m/Y', strtotime($selectedDate)) ?></h2>

  <!-- Récapitulatif mensuel -->
  <div class="card mb-3">
    <div class="card-body">
      <p><strong>Clôtures générées :</strong> <?= $monthly['days_with_closures'] ?> /
        <?= date('t', strtotime($selectedDate)) ?> jours —
        <strong>Verrouillées :</strong> <?= $monthly['days_locked'] ?>
        <?php if ($monthly['days_with_closures'] === 0): ?>
          <span class="badge bg-secondary">Aucune clôture</span>
        <?php elseif ($monthly['days_locked'] !== $monthly['days_with_closures']): ?>
          <span class="badge bg-warning text-dark">En attente de validation</span>
        <?php else: ?>
          <span class="badge bg-success">Validé</span>
        <?php endif; ?>
      </p>
      <p><strong>Total ventes :</strong> <?= number_format($monthly['totals']['sales'], 2, ',', ' ') ?> €
        (<?= $monthly['totals']['sales_count'] ?> ventes / <?= $monthly['totals']['items'] ?> articles)</p>
      <p><strong>Total achats :</strong> <?= number_format($monthly['totals']['purchases'], 2, ',', ' ') ?> €
        (<?= $monthly['totals']['purchase_count'] ?> achats)</p>
      <p><strong>Solde net :</strong> <?= number_format($monthly['totals']['net'], 2, ',', ' ') ?> €</p>
      <p><strong>Écart caisse cumulé :</strong> <?= number_format($monthly['totals']['cash_difference'], 2, ',', ' ') ?> €</p>
    </div>
  </div>

  <!-- Jours non clôturés -->
  <?php if (!empty($unclosedDays)): ?>
    <div class="alert alert-warning">
      <strong>Jours non clôturés :</strong>
      <ul class="mb-0">
        <?php foreach ($unclosedDays as $uc): ?>
          <li>
            <?= date('d/m/Y', strtotime($uc['day'])) ?>
            <a href="index.php?action=closurePanel&amp;date=<?= urlencode($uc['day']) ?>">Clôturer</a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Tableau des clôtures -->
  <div class="table-responsive mb-3">
    <table class="table table-striped table-sm">
      <thead>
        <tr>
          <th>Date</th>
          <th>N° clôture</th>
          <th>État</th>
          <th class="text-end">Ventes</th>
          <th class="text-end">Achats</th>
          <th class="text-end">Net</th>
          <th class="text-end">Écart</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($monthly['closures'] as $c): ?>
          <tr>
            <td><?= date('d/m/Y', strtotime($c['closure_date'])) ?></td>
            <td>
              <a href="index.php?action=closurePanel&amp;date=<?= urlencode($c['closure_date']) ?>">
                <?= htmlspecialchars($c['closure_number']) ?>
              </a>
            </td>
            <td>
              <?php if (!empty($c['locked_at'])): ?>
                <span class="badge bg-success">Verrouillée</span>
              <?php else: ?>
                <span class="badge bg-warning text-dark">Brouillon</span>
              <?php endif; ?>
            </td>
            <td class="text-end"><?= number_format($c['total_sales'], 2, ',', ' ') ?> €</td>
            <td class="text-end"><?= number_format($c['total_purchases'], 2, ',', ' ') ?> €</td>
            <td class="text-end"><?= number_format($c['total_sales'] - $c['total_purchases'], 2, ',', ' ') ?> €</td>
            <td class="text-end"><?= number_format($c['cash_difference'] ?? 0, 2, ',', ' ') ?> €</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Navigation -->
  <div class="d-flex justify-content-between">
    <a class="btn btn-outline-secondary" href="index.php?action=monthBrocResult&amp;selectedDate=<?= urlencode($previousDate) ?>">&larr; Mois précédent</a>
    <a class="btn btn-outline-secondary" href="index.php?action=monthBrocResult&amp;selectedDate=<?= urlencode($nextDate) ?>">Mois suivant &rarr;</a>
  </div>

  <button class="btn btn-success mt-3" id="exportPdfButton">Exporter en PDF BROC</button>

</div>

<script>
  const closures = <?= $closuresJson ?>;
  const entityNameFromPHP = <?= json_encode($entityName) ?>;
</script>

<?php
$content = ob_get_clean();
$jsFile = 'result/monthBrocResult.js';
require('view/template/mainTemplate.php');
?>
