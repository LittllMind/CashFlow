<?php ob_start(); ?>
<div class="stock-panel">
    <h2>🗂️ Gestion des stocks</h2>

    <!-- Message d'alerte -->
    <?php if (!empty($message)): ?>
        <div class="alert-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Formulaire d'entrée rapide -->
    <div class="stock-actions">
        <form id="stockEntryForm" method="post" action="index.php?action=stockAddEntry">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">

            <div class="form-group">
                <label for="stockSubcategory">Modèle / Sous-catégorie</label>
                <select id="stockSubcategory" name="subcategory" required>
                    <?php
                    foreach ($subcategories as $sub):
                        if ($sub['category_name'] !== $category) continue;
                        $label = htmlspecialchars($sub['subcategory_name']);
                        $selected = '';
                        echo '<option value="' . $label . '" ' . $selected . '>' . $label . '</option>';
                    endforeach;
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="stockQty">Quantité</label>
                <input type="number" id="stockQty" name="quantity" value="1" min="1" required>
            </div>

            <div class="form-group">
                <label for="stockEntryType">Type d'entrée</label>
                <select id="stockEntryType" name="entry_type" required>
                    <option value="donation">Don / Récupération (0 €)</option>
                    <option value="purchase">Achat payant</option>
                    <option value="inventory">Inventaire</option>
                    <option value="adjustment">Ajustement</option>
                </select>
            </div>

            <div class="form-group" id="priceGroup">
                <label for="stockPrice">Prix unitaire (€)</label>
                <input type="number" id="stockPrice" name="unit_price" value="0.00" min="0" step="0.01">
            </div>

            <div class="form-group">
                <label for="stockComment">Commentaire</label>
                <input type="text" id="stockComment" name="comment" placeholder="Ex: don Mme Dupont, lot Emmaüs...">
            </div>

            <button type="submit" class="btn-submit">Enregistrer l'entrée</button>
        </form>
    </div>

    <!-- Sélecteur de catégorie -->
    <div class="form-row" style="margin-bottom: 1rem;">
        <form method="get" action="index.php" class="form-group">
            <input type="hidden" name="action" value="stockPanel">
            <label for="stockCategorySelect">Catégorie affichée</label>
            <select id="stockCategorySelect" name="category" onchange="this.form.submit()">
                <?php
                $seen = [];
                foreach ($subcategories as $sub) {
                    $cat = $sub['category_name'];
                    if (isset($seen[$cat])) continue;
                    $seen[$cat] = true;
                    $selected = ($cat === $category) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($cat) . '" ' . $selected . '>' . htmlspecialchars($cat) . '</option>';
                }
                ?>
            </select>
        </form>
    </div>

    <!-- Table des soldes -->
    <div class="stats-table-wrapper">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Sous-catégorie</th>
                    <th>Solde</th>
                    <th>Action rapide</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($balances as $row):
                    $balance = (int) $row['balance'];
                    $badgeClass = $balance > 0 ? 'positive' : ($balance < 0 ? 'negative' : 'zero');
                    $escSub = htmlspecialchars($row['subcategory'], ENT_QUOTES);
                ?>
                <tr data-subcategory="<?php echo htmlspecialchars($row['subcategory']); ?>">
                    <td><?php echo htmlspecialchars($row['subcategory']); ?></td>
                    <td>
                        <span class="stock-badge <?php echo $badgeClass; ?>">
                            <?php echo $balance; ?>
                        </span>
                    </td>
                    <td>
                        <button type="button" class="stock-btn donation" onclick="prepareQuickEntry('<?php echo $escSub; ?>', 'donation')">+ Don</button>
                        <button type="button" class="stock-btn purchase" onclick="prepareQuickEntry('<?php echo $escSub; ?>', 'purchase')">+ Achat</button>
                        <button type="button" class="stock-btn adjust" onclick="prepareQuickEntry('<?php echo $escSub; ?>', 'inventory')">Inventaire</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="stock-hint">
        <strong>Ressourcerie — entrées de stock :</strong>
        <ul style="margin: 0.4rem 0 0 1rem; padding: 0;">
            <li><strong>Don / Récupération</strong> : prix 0 €, pas de facture, pas de TVA (entrée gratuite).</li>
            <li><strong>Achat payant</strong> : lots ou collectes payantes, prix saisi.</li>
            <li><strong>Inventaire / Ajustement</strong> : correction du solde théorique.</li>
            <li>Conformité future : traçabilité donneur, date de réception, catégorisation légale de la seconde main.</li>
        </ul>
    </div>
</div>

<script>
    let currentCategory = <?php echo json_encode($category); ?>;
</script>

<?php
$content = ob_get_clean();
require('view/template/mainTemplate.php');
