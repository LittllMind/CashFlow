<?php $title = 'Achat'; ?>

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

$paymentLabels = ['cash' => 'Espèces', 'card' => 'Carte', 'check' => 'Chèque'];
$isNew = empty($purchase_id) || $purchase_id === 'add';

if ($isNew) {
    $purchase_id = 'add';
    $purchase = [
        'id' => 'add',
        'formatted_purchase_date' => date('Y-m-d'),
        'provider_id' => '',
        'paid_amount' => 0.00,
        'payment_method' => '',
        'comment' => '',
    ];
    $articles_list = [];
    $total_amount = 0.00;
} else {
    $purchase_id = $purchase['id'];
    $total_amount = floatval($purchase['total_amount']);
}
?>

<div class="purchase-page">

<?php $lotMode = floatval($purchase['paid_amount'] ?? 0) > 0; ?>


<div class="sale-head">
    <?php if (isset($message)): ?>
        <h6 class="alert-message">
            <?= $message ?>
        </h6>
    <?php endif; ?>
</div>


<?php if ($lotMode): ?>
<div class="lot-banner" id="lotBanner">
    🧺 Mode lot : <strong id="lotAmountDisplay"><?= number_format(floatval($purchase['paid_amount']), 2) ?>€</strong> répartis sur <strong id="lotQtyDisplay">0</strong> articles
    → <span id="lotUnitPriceDisplay">—</span> € / pièce
</div>
<?php endif; ?>

<nav class="sale-nav">
    <button id="addArticleButton" class="sale-nav-btn" type="button" onclick="toggleCategories()">
        <span class="nav-icon">➕</span>
        <span class="btn-text">Ajouter</span>
    </button>

    <?php if (!$isNew): ?>
    <button type="button" class="sale-nav-btn nav-danger" onclick="if(confirm('Supprimer cet achat ?')) { window.location.href='index.php?action=deletePurchase&amp;purchase_id=<?= $purchase_id ?>'; }">
        <span class="nav-icon">🗑</span>
        <span class="btn-text">Supprimer</span>
    </button>
    <?php endif; ?>
</nav>


<form id="articleForm" action="index.php?action=saveArticle" method="post">

    <div id="categoryButtons" hidden="true">
        <button type="button" class="btn btn-warning" onclick="goBackToAddArticle()">Retour</button>
        <?php foreach ($categories as $category): ?>
            <button type="button" class="btn btn-secondary" onclick="selectCategory('<?= addslashes($category['category_name']) ?>')">
                <span class="tile-emoji"><?= $categoryEmojis[$category['category_name']] ?? '📦' ?></span>
                <span class="tile-label"><?= htmlspecialchars($category['category_name']) ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <div id="subcategoryButtons" hidden="true">
        <button type="button" class="btn btn-warning" onclick="goBackToCategories()">Retour</button>
    </div>

    <div id="quantityButtons" hidden="true">
        <button type="button" class="btn btn-warning" onclick="goBackToSubcategories()">Retour</button>
    </div>

    <input type="hidden" id="quantity" value="0" name="quantity">

    <div id="priceButton" hidden="true">
        <div id="priceButtonContainer"></div>
        <div id="lotToggleWrap" class="lot-toggle">
            <label class="lot-checkbox">
                <input type="checkbox" id="lotMode" onchange="toggleLotMode()">
                <span>🧺 Mode lot : saisir total du lot puis quantité</span>
            </label>
        </div>
    </div>

    <div id="articlePreview" hidden="true"></div>
</form>

<?php if (empty($articles_list)): ?>
    <p class="no-articles" id="noArticlesMsg">Aucun article pour le moment.</p>
    <script>
        window.addEventListener('load', function() {
            <?php if (!$isNew): ?> toggleCategories(); <?php endif; ?>
        });
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
                    <div class="detail-group <?= $lotMode ? 'lot-unit-price' : '' ?>">
                        <span class="detail-label">Prix unitaire</span>
                        <?php if ($lotMode): ?>
                            <span class="lot-price-readonly"><?= $upStr ?>€</span>
                        <?php else: ?>
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
                        <?php endif; ?>
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
                    <button type="button" class="btn-detail-delete" onclick="deleteArticle(<?= $article['id'] ?>)">
                        🗑 Supprimer
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<div class="purchase-form-wrapper">
    <form id="purchaseForm" action="index.php?action=<?= $isNew ? 'addPurchase' : 'updatePurchase' ?>" method="post" class="purchase-form">
        <input id="purchase_id" type="hidden" name="purchase_id" value="<?= $purchase_id ?>">
        <input type="hidden" id="payment_method" name="payment_method" value="<?= htmlspecialchars($purchase['payment_method'] ?? '') ?>">
        <input type="hidden" id="total_amount" name="total_amount" value="<?= number_format($total_amount, 2, '.', '') ?>">

        <div class="purchase-info-row">
            <div class="purchase-info-tile purchase-tile-total" onclick="toggleCategories()" style="cursor:pointer;">
                <span class="tile-icon">📊</span>
                <span class="tile-value" id="purchaseTotalDisplay"><?= number_format($total_amount, 2) ?>€</span>
                <span class="tile-label">Total articles</span>
            </div>

            <div class="purchase-info-tile purchase-tile-date">
                <span class="tile-icon">📅</span>
                <input type="date" id="purchase_date" name="purchase_date" value="<?= $purchase['formatted_purchase_date'] ?>" required onchange="updatePurchaseDate(this.value)">
            </div>

            <div class="purchase-info-tile purchase-tile-provider">
                <span class="tile-icon">🏭</span>
                <div class="purchase-provider-wrap">
                    <span class="tile-label">Fournisseur / Source</span>
                    <select name="provider_id" id="provider_id"
                            <?php if (!$isNew): ?> onchange="updatePurchaseProvider(this.value)"<?php endif; ?>
                    >
                        <option value="">Particulier / Don / Vide-grenier</option>
                            <?php foreach ($providers as $provider): ?>
                            <option value="<?= $provider['id'] ?>" <?= ($provider['id'] == ($purchase['provider_id'] ?? '')) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($provider['name']) ?> <?= ($provider['city'] ? '— ' . htmlspecialchars($provider['city']) : '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn-add-provider" onclick="openAddProviderPopup()">➕ Nouveau</button>
                </div>
            </div>
        </div>

        <div class="purchase-info-row">
            <div class="purchase-info-tile purchase-tile-paid">
                <span class="tile-icon">💰</span>
                <span class="tile-label">Montant payé</span>
                <input type="number" step="0.01" min="0" id="paid_amount" name="paid_amount" value="<?= number_format(floatval($purchase['paid_amount'] ?? 0), 2, '.', '') ?>"
                       onchange="updatePurchasePaidAmount(this.value)"
                       class="purchase-paid-input" placeholder="0.00">
            </div>

            <div class="purchase-info-tile purchase-tile-payment">
                <span class="tile-label">Paiement</span>
                <div class="payment-buttons" id="paymentButtons">
                    <button type="button" class="payment-btn <?= ($purchase['payment_method'] ?? '') === 'cash' ? 'active' : '' ?>" data-method="cash" onclick="setPayment('cash')">
                        <span class="pay-icon">💵</span>
                        <span class="pay-label">Espèces</span>
                    </button>
                    <button type="button" class="payment-btn <?= ($purchase['payment_method'] ?? '') === 'card' ? 'active' : '' ?>" data-method="card" onclick="setPayment('card')">
                        <span class="pay-icon">💳</span>
                        <span class="pay-label">Carte</span>
                    </button>
                    <button type="button" class="payment-btn <?= ($purchase['payment_method'] ?? '') === 'check' ? 'active' : '' ?>" data-method="check" onclick="setPayment('check')">
                        <span class="pay-icon">🏦</span>
                        <span class="pay-label">Chèque</span>
                    </button>
                </div>
            </div>

            <div class="purchase-info-tile purchase-tile-comment">
                <span class="tile-icon">📝</span>
                <span class="tile-label">Commentaire</span>
                <input type="text" id="comment" name="comment" value="<?= htmlspecialchars($purchase['comment'] ?? '') ?>" class="purchase-comment-input"
                       placeholder="Note...">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit"><?= $isNew ? 'Créer l\'achat' : 'Valider l\'achat' ?></button>
        </div>
    </form>
</div>

</div> <!-- /purchase-page -->

<script>
    const subcategories = <?= json_encode($subcategories) ?>;
    const purchase_id = <?= json_encode($purchase_id) ?>;
    const lotModeFlag = <?= json_encode($lotMode) ?>;
    const lotTotal = <?= json_encode(floatval($purchase['paid_amount'] ?? 0)) ?>;
</script>

<?php $content = ob_get_clean(); ?>

<?php $purchaseJsFile = 'control-panel/purchase/update/update.js'; ?>

<?php require('view/template/mainTemplate.php'); ?>
