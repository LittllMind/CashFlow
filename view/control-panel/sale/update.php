<?php $title = 'Inclusion'; ?>

<?php ob_start(); ?>

<?php

if ($sale_id == '') {
    $sale_id = 'add';
    $sale_date = date("Y-m-d");
    $total_amount = 0;
    $payment_method = '';
} else {
    $sale_date = $sale['sale_date'];
    $total_amount = $sale['total_amount'];
    $payment_method = $sale['payment_method'];
}
?>

<div class="sale">
    <!-- Tableau pour saisir les détails de l'article -->
    <table id="articleTable" class="col-12 table table-striped sale-table">

        <tbody>
            <?php if (empty($articles_list[0])): ?>
                <script>
                    window.onload = function() {
                        toggleCategories(); // Appel de la fonction si le tableau interne est vide
                    };
                </script>
            <?php else: ?>
                <?php foreach ($articles_list as $article): ?>
                    <tr onclick="openPopup(<?= $article['id'] ?>, <?= $article['quantity'] ?>, <?= $article['unit_price'] ?>)">
                        <td><?= htmlspecialchars($article['category']) ?></td>
                        <td><?= htmlspecialchars($article['subcategory']) ?></td>
                        <td></td>
                        <td></td>
                        <td><?= htmlspecialchars($article['comment']) ?></td>
                    </tr>
                    <tr onclick="openPopup(<?= $article['id'] ?>, <?= $article['quantity'] ?>, <?= $article['unit_price'] ?>)">
                        <td>
                            <strong><?= htmlspecialchars($article['quantity']) ?></strong>
                        </td>
                        <td>
                            <strong> x <?= htmlspecialchars($article['unit_price']) ?> €</strong>
                        </td>
                        <td><strong> = <?= htmlspecialchars($article['unit_price'] * $article['quantity']) ?> €</strong></td>
                        <td></td>
                        <td>
                            <form id="deleteForm<?= $article['id'] ?>" action="index.php?action=deleteArticle" method="post">
                                <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                <input type="hidden" name="sale_id" value="<?= $sale_id ?>">
                                <button class="btn btn-danger" type="submit"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">
                                    <img src="assets/images/delete-32.png" alt="DELETE">
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>


    </table>

    <div class="sale-head">
        <?php if (isset($message)): ?>
            <h6 class="alert-message">
                <?= $message ?>
            </h6>
        <?php endif; ?>
    </div>

    <div id="popupOverlay" class="popup-overlay"></div>

    <div id="popup" class="popup">
        <form id="updateForm" action="index.php?action=updateArticle" method="post">
            <div class="col-12">
                <input type="hidden" name="article_id" id="articleId">
                <input type="hidden" name="sale_id" value="<?= $sale_id ?>">
                <table>
                    <tr>
                        <th>Unités </th>
                        <th> Prix</th>
                    </tr>
                    <tr>
                        <td><input class="form-control" type="number" name="quantity" id="quantity"></td>
                        <td><input class="form-control" type="number" name="unit_price" id="unit_price"></td>
                        comment

                    </tr>
                </table>
            </div>

            <div class="row">
                <button class="btn btn-success" type="button" onclick="submitUpdateForm()">Valider</button>
                <button class="btn btn-primary" type="button" onclick="closePopup()">Annuler</button>
            </div>

        </form>
    </div>


    <form id="articleForm" action="index.php?action=saveArticle" method="post">

        <div id="articleButtonContainer">
            <button id="addArticleButton" class="btn btn-primary" type="button" onclick="toggleCategories()">Ajouter un
                article</button>
        </div>

        <div id="categoryButtons" hidden="true">
            <!-- Bouton de retour à la page précédente -->
            <button type="button" class="btn btn-warning" onclick="goBackToAddArticle()">Retour</button>
            <!-- Boutons des catégories -->
            <?php foreach ($categories as $category): ?>
                <button type="button" class="btn btn-secondary"
                    onclick="selectCategory('<?= $category['category_name'] ?>')">
                    <?= $category['category_name'] ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Div pour afficher les boutons des sous-catégories -->
        <div id="subcategoryButtons" hidden="true">
            <button type="button" class="btn btn-warning" onclick="goBackToCategories()">Retour</button>

            <!-- Les boutons des sous-catégories seront affichés ici -->
        </div>

        <div id="quantityButtons" hidden="true">
            <button type="button" class="btn btn-warning" onclick="goBackToSubcategories()">Retour</button>

            <!-- Les boutons de sélection de quantité seront affichés ici -->
        </div>

        <input type="hidden" id="quantity" value="0" name="quantity">

        <div id="priceButton" hidden="true">
            <div id="priceButtonContainer" class="row">

            </div>
            <!-- Le bouton de sélection de prix sera affiché ici -->
            <div id="priceSelector">
                <input type="number" class="form-control" name="unit_price" value="0" id="price" min="0">

                <button id="submitPrice" class="btn btn-success" type="button" onclick="setPrice()">Valider</button>
            </div>

            <div id="commentSelector">
                <input type="texatrea" name="comment" id="comment" class="form-control" placeholder="Commentaire">
            </div>

        </div>

        <!-- Formulaire pour saisir les détails de l'article -->
        <div id="articleTable" hidden="true">
            <table class="table">
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Sous-catégorie</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="articleTableBody">
                    <!-- Les lignes d'articles seront ajoutées dynamiquement ici -->
                </tbody>
            </table>
        </div>
    </form>
    <div class="sale-form-wrapper">
        <form id="saleForm" action="index.php?action=addSale" method="post" class="sale-form">
            <input id="sale_id" type="hidden" name="sale_id" value="<?= $sale_id ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="total_amount">Montant total</label>
                    <input type="number" class="form-control" id="total_amount" name="total_amount"
                        value="<?= $total_amount ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="sale_date">Date de vente</label>
                    <input type="date" id="sale_date" name="sale_date" value="<?= $sale['formatted_sale_date'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="payment_method">Méthode de paiement</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="cash" <?= ($payment_method == 'cash') ? 'selected' : '' ?>>Espèces</option>
                        <option value="card" <?= ($payment_method == 'card') ? 'selected' : '' ?>>Carte de crédit</option>
                        <option value="check" <?= ($payment_method == 'check') ? 'selected' : '' ?>>Chèque</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Valider</button>
            </div>
        </form>
    </div>
    <div class="sale-form-wrapper">
        <form class="sale-form" action="index.php?action=newSaleForm" method="post">
            <?php if ($sale['formatted_sale_date']) {
            ?>
                <input type="hidden" name="selectedDate" value="<?= $sale['formatted_sale_date'] ?>">
            <?php
            } else {
            ?>
                <input type="hidden" name="selectedDate" value="<?= date('Y-m-d') ?>">
            <?php
            }
            ?>

            <div class="form-actions">

                <button type="submit" class="btn-submit">NOUVELLE VENTE</button>
            </div>
        </form>
    </div>
</div>

<script>
    const subcategories = <?= json_encode($subcategories) ?>;
    const sale_id = <?= json_encode($sale_id) ?>;
</script>

<?php $content = ob_get_clean(); ?>

<?php require('view/template/mainTemplate.php'); ?>