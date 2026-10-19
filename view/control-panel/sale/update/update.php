<?php $title = 'Inclusion'; ?>

<?php ob_start(); ?>

<?php

$categoryEmojis = [
    'Femmes' => '👚',
    'Brocante' => '🏺',
    'Hommes' => '👕',
    'Bijoux' => '💍',
    'Sticker' => '🏷️',
    'Accessoires' => '🎩',
    'Livres' => '📚',
    'Art de la Table' => '🍽️',
    'jouets' => '🧸',
    'Jouets' => '🧸',
    'Décoration' => '🖼️',
    'Enfants' => '👶',
    'Neuf' => '🆕',
    'Mercerie' => '🧵',
    'Collab' => '🤝',
    'Services' => '🔧',
    'Maroc' => '🇲🇦',
    'Bougies' => '🕯️',
    'Livre' => '📖',
];

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


<?php
// Sécuriser les variables sur une nouvelle vente
if (!isset($sale)) {
    $sale = ['formatted_sale_date' => date('Y-m-d')];
}
?>

<div class="sale-page">

<div class="sale-head">
    <?php if (isset($message)): ?>
        <h6 class="alert-message">
            <?= $message ?>
        </h6>
    <?php endif; ?>
</div>

<nav class="sale-nav">
    <!-- Vente précédente -->
    <?php if (!empty($previous_sale)): ?>
    <button type="button" class="sale-nav-btn nav-prev" onclick="navigateSale(<?= $previous_sale['id'] ?>)">
        <span class="nav-icon">◀</span>
        <span class="btn-text">Vente précédente</span>
    </button>
    <?php else: ?>
    <div></div>
    <?php endif; ?>
    <!-- Ajouter article -->
    <button id="addArticleButton" class="sale-nav-btn" type="button" onclick="toggleCategories()">
        <span class="nav-icon">➕</span>
        <span class="btn-text">Ajouter</span>
    </button>

    <!-- Vente suivante / Nouvelle vente -->
    <?php if (!empty($next_sale)): ?>
    <button type="button" class="sale-nav-btn nav-next" onclick="navigateSale(<?= $next_sale['id'] ?>)">
        <span class="btn-text">Vente suivante</span>
        <span class="nav-icon">▶</span>
    </button>
    <?php else: ?>
    <button type="button" class="sale-nav-btn" onclick="newSaleFormNav()">
        <span class="nav-icon">🛒</span>
        <span class="btn-text">Nouvelle vente</span>
    </button>
    <?php endif; ?>
    </nav>



    <form id="articleForm" action="index.php?action=saveArticle" method="post">


    <div id="categoryButtons" hidden="true">
        <!-- Bouton de retour à la page précédente -->
        <button type="button" class="btn btn-warning" onclick="goBackToAddArticle()">Retour</button>
        <!-- Boutons des catégories -->
        <?php foreach ($categories as $category): ?>
            <button type="button" class="btn btn-secondary"
                onclick="selectCategory('<?= $category['category_name'] ?>')"
            >
                <?php if ($category['category_name'] === 'Sticker'): ?>
                    <img src="assets/images/sticker-peyreleau.jpg" alt="" class="tile-sticker-img">
                    <span class="tile-label"><?= $category['category_name'] ?></span>
                <?php else: ?>
                    <span class="tile-emoji"><?= $categoryEmojis[$category['category_name']] ?? '?' ?></span>
                    <span class="tile-label"><?= $category['category_name'] ?></span>
                <?php endif; ?>
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

    <div id="stickerPacks" hidden="true">
        <button type="button" class="btn btn-warning" onclick="goBackToStickerPacks()">Retour</button>
        <!-- Les boutons des packs sticker seront ajoutés ici -->
    </div>

    <!-- Interface de pack variété (sélectionner chaque sticker) -->
    <div id="stickerSlots" hidden="true">
        <button type="button" class="btn btn-warning" onclick="cancelMixedPack()">Retour</button>
        <p id="stickerSlotsStatus" style="text-align:center;margin:0.5rem 0"></p>
        <div id="stickerSlotsButtons" style="display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;padding:0.5rem"></div>
        <div id="stickerSlotsChoices" style="display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;margin-top:0.75rem"></div>
    </div>

    <input type="hidden" id="quantity" value="0" name="quantity">

    <div id="priceButton" hidden="true"> 
        <div id="priceButtonContainer">
            <!-- Le clavier numérique sera injecté ici par JS -->
        </div> 
    </div>

    <!-- Ancre pour insertion JS des articles -->
    <div id="articlePreview" hidden="true"></div>
</form>
<?php if (empty($articles_list[0])): ?>
    <p class="no-articles" id="noArticlesMsg">Aucun article pour le moment.</p>
    <script>
        window.onload = function() {
            toggleCategories();
        };
    </script>
<?php else: ?>
    <div class="receipt-list" id="receiptList">
        <?php foreach ($articles_list as $article):
            $qty = floatval($article['quantity']);
            $up = floatval($article['unit_price']);
            $lineTotal = number_format($qty * $up, 2);
            $hasComment = !empty($article['comment']);
            $comm = htmlspecialchars($article['comment'] ?? '');
            $upStr = number_format($up, 2);
            $catEmoji = $categoryEmojis[$article['category']] ?? '📦';
        ?>
        <div class="receipt-line" id="row<?= $article['id'] ?>" data-id="<?= $article['id'] ?>">
            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
            <input type="hidden" name="sale_id" value="<?= $sale_id ?>">

            <div class="receipt-head" onclick="toggleReceiptDetail(<?= $article['id'] ?>)">
                <div class="receipt-left">
                    <span class="receipt-emoji"><?= $catEmoji ?></span>
                    <span class="receipt-name"><?= htmlspecialchars($article['subcategory']) ?></span>
                    <span class="receipt-cat"><?= htmlspecialchars($article['category']) ?></span>
                    <?php if ($hasComment): ?><span class="receipt-comment">💬 <?= $comm ?></span><?php endif; ?>
                </div>
                <div class="receipt-right">
                    <span class="receipt-math"><?= $qty ?> × <?= $upStr ?>€</span>
                    <span class="receipt-total"><?= $lineTotal ?>€</span>
                    <span class="receipt-chevron">▼</span>
                </div>
            </div>

            <div class="receipt-detail" id="detail<?= $article['id'] ?>" hidden>
                <div class="detail-grid">
                    <div class="detail-group">
                        <span class="detail-label">Quantité</span>
                        <div class="card-stepper" data-field="quantity">
                            <button type="button" class="stepper-btn dec">
                                <img src="assets/images/signe-moins.png" alt="−" width="14" height="14">
                            </button>
                            <span class="stepper-val"><?= $qty ?></span>
                            <button type="button" class="stepper-btn inc">
                                <img src="assets/images/ajouter.png" alt="+" width="14" height="14">
                            </button>
                            <input type="hidden" name="quantity" value="<?= $qty ?>">
                        </div>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Prix unitaire</span>
                        <div class="card-stepper" data-field="unit_price" data-step="0.5">
                            <button type="button" class="stepper-btn dec">
                                <img src="assets/images/signe-moins.png" alt="−" width="14" height="14">
                            </button>
                            <span class="stepper-val"><?= $upStr ?></span>
                            <button type="button" class="stepper-btn inc">
                                <img src="assets/images/ajouter.png" alt="+" width="14" height="14">
                            </button>
                            <input type="hidden" name="unit_price" value="<?= $up ?>">
                        </div>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Total</span>
                        <span class="detail-total-val" id="detailTotal<?= $article['id'] ?>"><?= $lineTotal ?>€</span>
                    </div>
                </div>
                <div class="detail-extra">
                    <div class="detail-comment">
                        <input type="text" name="comment" value="<?= $comm ?>" class="comment-input" placeholder="Ajouter un commentaire…">
                    </div>
                    <button type="button" class="btn-detail-delete" onclick="deleteArticle(<?= $article['id'] ?>, <?= $sale_id ?>)">
                        🗑 Supprimer
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<div class="sale">
    <!-- Tableau pour saisir les détails de l'article -->
    <table id="articleTable" class="article-table">
        <tbody></tbody>


    </table>


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
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input class="form-control" type="text" name="comment" placeholder="Commentaire">
                        </td>
                    </tr>
                </table>
            </div>

            <div class="row">
                <button class="btn btn-success" type="button" onclick="submitUpdateForm()">Valider</button>
                <button class="btn btn-primary" type="button" onclick="closePopup()">Annuler</button>
            </div>

        </form>
    </div>

    <!-- Modale SAISIE RAPIDE (long-press tuile sous-catégorie) -->
    <div id="fastEntryOverlay" class="popup-overlay" onclick="closeFastEntry()"></div>
    <div id="fastEntryPopup" class="popup fast-popup">
        <form id="fastEntryForm" action="index.php?action=saveArticle" method="post" onsubmit="return false;">
            <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($sale_id); ?>">
            <input type="hidden" name="category" id="fastCategory">
            <input type="hidden" name="subcategory" id="fastSubcategory">

            <h3 id="fastEntryTitle"></h3>
            <div class="fast-fields">
                <div class="fast-field">
                    <label for="fastQuantity">Quantité</label>
                    <input type="tel" inputmode="numeric" pattern="[0-9]*" id="fastQuantity" value="1" min="1" max="99" class="form-control">
                </div>
                <div class="fast-field">
                    <label for="fastPrice">Prix (€)</label>
                    <input type="tel" inputmode="decimal" pattern="[0-9,.]*" id="fastPrice" value="" class="form-control" placeholder="0.00">
                </div>
            </div>
            <div class="fast-buttons">
                <button type="button" class="btn btn-success" onclick="submitFastEntry()">Valider</button>
                <button type="button" class="btn btn-primary" onclick="closeFastEntry()">Annuler</button>
            </div>
        </form>
    </div>

    <div class="sale-form-wrapper">
        <form id="saleForm" action="index.php?action=addSale" method="post" class="sale-form">
            <input id="sale_id" type="hidden" name="sale_id" value="<?= $sale_id ?>">
            <input type="hidden" id="payment_method" name="payment_method" value="<?= $payment_method ?>">
            <input type="hidden" id="total_amount" name="total_amount" value="<?= $total_amount ?>">

            <div class="sale-info-row">
                <div class="sale-info-tile sale-tile-total" onclick="toggleCategories()" style="cursor:pointer;">
                    <span class="tile-icon">💰</span>
                    <span class="tile-value" id="saleTotalDisplay"><?= number_format($total_amount, 2) ?>€</span>
                    <span class="tile-label">Total</span>
                </div>

                <div class="sale-info-tile sale-tile-date">
                    <span class="tile-icon">📅</span>
                    <input type="date" id="sale_date" name="sale_date" value="<?= $sale['formatted_sale_date'] ?>" required onchange="updateSaleDate(this.value)">
                </div>

                <div class="sale-info-tile sale-tile-payment">
                    <span class="tile-label">Paiement</span>
                    <div class="payment-buttons" id="paymentButtons">
                        <button type="button" class="payment-btn <?= ($payment_method == 'cash') ? 'active' : '' ?>" data-method="cash" onclick="setPayment('cash')">
                            <span class="pay-icon">💵</span>
                            <span class="pay-label">Espèces</span>
                        </button>
                        <button type="button" class="payment-btn <?= ($payment_method == 'card') ? 'active' : '' ?>" data-method="card" onclick="setPayment('card')">
                            <span class="pay-icon">💳</span>
                            <span class="pay-label">Carte</span>
                        </button>
                        <button type="button" class="payment-btn <?= ($payment_method == 'check') ? 'active' : '' ?>" data-method="check" onclick="setPayment('check')">
                            <span class="pay-icon">🏦</span>
                            <span class="pay-label">Chèque</span>
                        </button>
                    </div>
                </div>

                <div class="sale-info-tile sale-tile-client">
                    <span class="tile-icon">👤</span>
                    <div class="client-select-wrap">
                        <select name="client_id" id="client_id" onchange="updateSaleClient(this.value)">
                            <option value="">Aucun client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>">
                                    <?= htmlspecialchars($client['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn-add-client" onclick="openClientPopup()" title="Nouveau client">+</button>
                    </div>
                    <!-- Micro-popup nouveau client -->
                    <div id="clientPopup" class="client-popup" hidden>
                        <input type="text" id="newClientName" placeholder="Nom du client" class="form-control">
                        <input type="tel" id="newClientPhone" placeholder="Téléphone (optionnel)" class="form-control">
                        <div class="client-popup-actions">
                            <button type="button" class="btn btn-success" onclick="createClient()">Créer</button>
                            <button type="button" class="btn btn-primary" onclick="closeClientPopup()">Annuler</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-submit" onclick="submitSaleForm()">Valider la vente</button>
            </div>
            <div class="sale-actions" style="margin: 0.5rem 0; justify-content: center; gap: 1rem; display: flex;">
                <a href="#" data-action="print" data-url="index.php?action=exportSale&sale_id=<?= $sale_id ?>" onclick="openBuyerPopup(this); return false;" title="Imprimer ticket">
                    <span class="action-icon">🖨️</span>
                </a>
                <a href="#" data-action="copy" data-url="index.php?action=exportSale&sale_id=<?= $sale_id ?>&format=text" onclick="openBuyerPopup(this); return false;" title="Copier texte">
                    <span class="action-icon">📋</span>
                </a>
                <a href="#" data-action="mail" data-url="https://demo.example.com/index.php?action=exportSale&sale_id=<?= $sale_id ?>&format=text" data-subject="Vente+<?= urlencode('#' . $sale_id) ?>+<?= urlencode(date('d/m/Y', strtotime($sale['sale_date']))) ?>" onclick="openBuyerPopup(this); return false;" title="Envoyer par mail">
                    <span class="action-icon">✉️</span>
                </a>
            </div>
            <!-- Popup nom client (avant export facture) -->
            <div id="buyerPopup" class="client-popup" hidden>
                <input type="text" id="buyerNameInput" placeholder="Nom du client ou organisme" class="form-control">
                <div class="client-popup-actions">
                    <button type="button" class="btn btn-success" onclick="submitBuyerPopup()">Valider</button>
                    <button type="button" class="btn btn-primary" onclick="closeBuyerPopup()">Annuler</button>
                </div>
            </div>
        </form>
    </div>
    <!-- <div class="sale-form-wrapper">
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
    </div> -->
</div>

</div> <!-- /sale-page -->

<script>
    // ─── popup nom client (avant export ticket) ───
    let _buyerAnchor = null;

    function openBuyerPopup(anchor) {
        _buyerAnchor = anchor;
        document.getElementById('buyerPopup').removeAttribute('hidden');
        document.getElementById('buyerNameInput').focus();
    }

    function closeBuyerPopup() {
        document.getElementById('buyerPopup').setAttribute('hidden', 'true');
        _buyerAnchor = null;
    }

    function submitBuyerPopup() {
        const name = document.getElementById('buyerNameInput').value.trim();
        const action = _buyerAnchor.dataset.action;
        const baseUrl = _buyerAnchor.dataset.url;

        let url;
        if (action === 'mail') {
            const subject = encodeURIComponent('Vente #' + sale_id + ' - ' + (new Date().toLocaleDateString('fr-FR')));
            url = 'mailto:?subject=' + subject + '&body=' + encodeURIComponent(baseUrl + '&buyer=' + encodeURIComponent(name));
            window.location.href = url;
        } else {
            url = baseUrl + '&buyer=' + encodeURIComponent(name);
            window.open(url, '_blank');
        }
        closeBuyerPopup();
    }

    const subcategories = <?= json_encode($subcategories) ?>;
    const sale_id = <?= json_encode($sale_id) ?>;
    const subcategoryScores = <?= json_encode($subcategoryScores ?? []) ?>;
</script>

<?php $content = ob_get_clean(); ?>

<?php require('view/template/mainTemplate.php'); ?>