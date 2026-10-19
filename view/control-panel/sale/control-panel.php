<?php ob_start(); ?>
<div class="sale-form-wrapper sale-panel">
    <script>
        const salesStats = <?= $json_totals ?>;
    </script>

    <!-- Bouton Nouvelle Vente -->
    <div class="form-actions">
        <form method="post" action="index.php?action=newSaleForm">
            <input type="hidden" name="selectedDate" value="<?= $selectedDate ?? date('Y-m-d') ?>">
            <button class="btn-submit">NOUVELLE VENTE</button>
        </form>
    </div>

    <!-- Message d’alerte -->
    <?php if (!empty($message)): ?>
        <div class="alert-message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Sélection de date + Liste des ventes -->
    <div class="sale-table-wrapper">
        <div class="form-row">
            <div class="form-group">
                <input type="date" id="selectedDate" name="day" value="<?= $selectedDate ?? date('Y-m-d') ?>">
            </div>
        </div>

        <table class="sale-table">
            <thead>
                <tr>
                    <th>Heure</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list_of_sales as $sale): ?>
                    <tr>
                        <td><a href="index.php?action=newSaleForm&sale_id=<?= $sale['id'] ?>" class="btn btn-success"><?= $sale['sale_time'] ?></a></td>
                        <td><?= $sale['total_amount'] ?> €</td>
                        <td>
                            <button onclick="confirmDelete(<?= $sale['id'] ?>)" class="btn btn-danger">
                                <img src="assets/images/delete-32.png" alt="DELETE">
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Statistiques par période -->
    <div class="sale-stats stats-table-wrapper">
        <table class="stats-table-custom">
            <thead class="stats-table-head">
                <tr>
                    <th class="stats-th">Période</th>
                    <th class="stats-th">Total</th>
                    <th class="stats-th">Ventes</th>
                    <th class="stats-th">Articles</th>
                    <th class="stats-th">Prix moyen/article</th>
                </tr>
            </thead>
            <tbody class="stats-table-body">
                <!-- Rempli dynamiquement -->
            </tbody>
        </table>
    </div>

    <!-- Résumé par catégorie/sous-catégorie -->
    <div class="sale-summary-category stats-table-wrapper">
        <div id="monthly-summary-container"></div>
    </div>

    <!-- Détail des articles -->
    <div class="sale-articles stats-table-wrapper">
        <table class="table striped-table">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Sous-catégorie</th>
                    <th>Quantité</th>
                    <th>Prix</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list_of_articles as $article): ?>
                    <tr>
                        <td><?= htmlspecialchars($article['category']) ?></td>
                        <td><?= htmlspecialchars($article['subcategory']) ?></td>
                        <td><?= $article['quantity'] ?> <?= htmlspecialchars($article['comment']) ?></td>
                        <td><?= $article['unit_price'] ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('selectedDate').addEventListener('change', function(e) {
        window.location.href = `index.php?action=salePanel&date=${e.target.value}`;
    });

    function confirmDelete(saleId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette vente ?')) {
            window.location.href = `index.php?action=deleteSale&sale_id=${saleId}`;
        }
    }
</script>
<?php $content = ob_get_clean(); ?>
<?php require('view/template/mainTemplate.php'); ?>
