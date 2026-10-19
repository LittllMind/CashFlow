<?php ob_start(); ?>
<div class="purchase-panel">
    <input type="hidden" id="hiddenSelectedDate" value="<?= $selectedDate ?? date('Y-m-d') ?>">

    <!-- ===== HEADER : Date + Navigation + Nouvel achat ===== -->
    <header class="sp-header">
        <div class="sp-header-nav">
            <button type="button" class="sp-btn-icon" id="purchasePrevDay" aria-label="Jour précédent">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <div class="sp-header-center">
                <h1 class="sp-header-title" onclick="document.getElementById('datePickerInput').showPicker()"><?= date('l j F Y', strtotime($selectedDate)) ?></h1>
                <input type="date" id="datePickerInput" value="<?= $selectedDate ?>" hidden onchange="window.location.href='index.php?action=purchasePanel&date='+this.value">
                <span class="sp-header-subtitle"><?= $total_purchases ?> achat<?= $total_purchases > 1 ? 's' : '' ?> — <?= $dailyTotals['total_articles'] ?? 0 ?> article<?= ($dailyTotals['total_articles'] ?? 0) > 1 ? 's' : '' ?></span>
            </div>
            <button type="button" class="sp-btn-icon" id="purchaseNextDay" aria-label="Jour suivant">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>
        <div class="sp-header-actions">
            <button type="button" class="sp-btn-today" onclick="window.location.href='index.php?action=purchasePanel&date=<?= date('Y-m-d') ?>'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Aujourd'hui
            </button>
            <button type="button" class="sp-btn-new" onclick="window.location.href='index.php?action=newPurchaseForm&selectedDate=<?= $selectedDate ?>'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                + Achat
            </button>
        </div>
    </header>

    <!-- ===== ALERTE ===== -->
    <?php if (!empty($message)): ?>
        <div class="alert-message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- ===== TABLEAU DES ACHATS ===== -->
    <div class="sp-table-wrapper">
        <div class="sp-table-header">
            <h2>Achats du jour</h2>
            <span class="sp-table-count"><?= $total_purchases ?> transaction<?= $total_purchases > 1 ? 's' : '' ?></span>
        </div>
        <?php if ($total_purchases > 0): ?>
        <table class="sp-table">
            <thead>
                <tr>
                    <th class="sp-th-time">Heure</th>
                    <th class="sp-th-method">Paiement</th>
                    <th class="sp-th-provider">Fournisseur</th>
                    <th class="sp-th-amount">Montant</th>
                    <th class="sp-th-action"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list_of_purchases as $purchase):
                    $paymentMethod = $purchase['payment_method'] ?? 'cash';
                ?>
                    <tr>
                        <td class="sp-td-time">
                            <a href="index.php?action=newPurchaseForm&purchase_id=<?= $purchase['id'] ?>" class="sp-time-link">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <?= $purchase['purchase_time'] ?? '--:--' ?>
                            </a>
                        </td>
                        <td class="sp-td-method">
                            <span class="sp-badge sp-badge--<?= htmlspecialchars($paymentMethod) ?>">
                                <?= ppPaymentLabel($paymentMethod) ?>
                            </span>
                        </td>
                        <td class="sp-td-provider"><?= htmlspecialchars($purchase['provider_name'] ?? '-') ?></td>
                        <td class="sp-td-amount"><?= number_format($purchase['total_amount'], 2, ',', ' ') ?> €</td>
                        <td class="sp-td-action">
                            <button type="button" class="sp-btn-delete" onclick="event.stopPropagation(); ppConfirmDelete(<?= $purchase['id'] ?>, <?= $purchase['total_amount'] ?>, '<?= $purchase['purchase_time'] ?? '--:--' ?>')" title="Supprimer">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="sp-empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <p>Aucun achat ce jour</p>
            <button type="button" class="sp-btn-new" onclick="window.location.href='index.php?action=newPurchaseForm&selectedDate=<?= $selectedDate ?>'">Créer un achat</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== STATS CARDS ===== -->
    <div class="sp-stats-row">
        <div class="sp-stat-card sp-stat-card--daily">
            <div class="sp-stat-card__top">
                <span class="sp-stat-card__label">Jour</span>
                <?php if (isset($trend_j1)): ?>
                    <span class="sp-stat-card__trend <?= $trend_j1['class'] ?>"><?= $trend_j1['symbol'] ?> <?= $trend_j1['pct'] ?>%</span>
                <?php endif; ?>
            </div>
            <div class="sp-stat-card__value"><?= number_format($dailyTotals['total_amount'], 2, ',', ' ') ?> €</div>
            <div class="sp-stat-card__meta">
                <span><?= $dailyTotals['total_purchases'] ?> achats</span>
                <span><?= $dailyTotals['total_articles'] ?> articles</span>
            </div>
        </div>
        <div class="sp-stat-card">
            <div class="sp-stat-card__top">
                <span class="sp-stat-card__label">Semaine</span>
            </div>
            <div class="sp-stat-card__value"><?= number_format($weeklyTotals['total_amount'], 2, ',', ' ') ?> €</div>
            <div class="sp-stat-card__meta">
                <span><?= $weeklyTotals['total_purchases'] ?> achats</span>
            </div>
        </div>
        <div class="sp-stat-card">
            <div class="sp-stat-card__top">
                <span class="sp-stat-card__label">Mois</span>
            </div>
            <div class="sp-stat-card__value"><?= number_format($monthlyTotals['total_amount'], 2, ',', ' ') ?> €</div>
            <div class="sp-stat-card__meta">
                <span><?= $monthlyTotals['total_purchases'] ?> achats</span>
            </div>
        </div>
    </div>

    <!-- ===== RÉCAP ARTICLES DU JOUR ===== -->
    <div class="sp-table-wrapper sp-articles-recap">
        <div class="sp-table-header">
            <h2>Récap articles</h2>
            <span class="sp-table-count"><?= count($aggregatedArticles) ?> type<?= count($aggregatedArticles) > 1 ? 's' : '' ?></span>
        </div>
        <?php if (!empty($aggregatedArticles)): ?>
        <table class="sp-table sp-table--articles">
            <thead>
                <tr>
                    <th class="sp-th-icon"></th>
                    <th class="sp-th-item">Article</th>
                    <th class="sp-th-qty sp-sortable" data-sort="qty" title="Trier par quantité">Qté <span class="sp-sort-arrow">⇅</span></th>
                    <th class="sp-th-amt sp-sortable" data-sort="amount" title="Trier par montant">Montant <span class="sp-sort-arrow">⇅</span></th>
                    <th class="sp-th-avg">Prix moy.</th>
                </tr>
            </thead>
            <tbody id="articlesRecapBody">
                <?php foreach ($aggregatedArticles as $row):
                    $hasPriceWarning = (float)$row['avg_unit_price'] <= 0;
                ?>
                <tr class="<?= $hasPriceWarning ? 'sp-row-warning' : '' ?>" data-qty="<?= $row['total_quantity'] ?>" data-amount="<?= $row['total_amount'] ?>">
                    <td class="sp-td-icon"><?= ppArticleIcon($row['category'], $row['subcategory']) ?></td>
                    <td class="sp-td-item">
                        <div class="sp-item-name"><?= htmlspecialchars($row['subcategory']) ?></div>
                        <div class="sp-item-cat"><?= htmlspecialchars($row['category']) ?></div>
                        <?php if ($hasPriceWarning): ?>
                        <span class="sp-price-warning">prix 0 détecté</span>
                        <?php endif; ?>
                    </td>
                    <td class="sp-td-qty"><?= $row['total_quantity'] ?></td>
                    <td class="sp-td-amt"><?= number_format($row['total_amount'], 2, ',', ' ') ?> €</td>
                    <td class="sp-td-avg"><?= number_format($row['avg_unit_price'], 2, ',', ' ') ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="sp-tf-label">Total</td>
                    <td class="sp-tf-qty"><?= array_sum(array_column($aggregatedArticles, 'total_quantity')) ?></td>
                    <td class="sp-tf-amt"><?= number_format(array_sum(array_column($aggregatedArticles, 'total_amount')), 2, ',', ' ') ?> €</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div class="sp-empty sp-empty--small">
            <p>Aucun article acheté ce jour</p>
        </div>
        <?php endif; ?>
    </div>

    <input type="date" id="selectedDate" value="<?= $selectedDate ?>" hidden>
</div>

<script type="text/javascript" src="view/control-panel/purchase/control-panel.js?v=1"></script>

<?php
/**
 * Helper : icône d'article selon sous-catégorie (fallback catégorie)
 */
function ppArticleIcon($category, $subcategory = '') {
    $subMap = [
        'Top' => '👕', 'T-shirt' => '👕',
        'Chemise' => '👔', 'Chemises' => '👔',
        'Pull' => '🧥', 'Veste' => '🧥', 'Manteau' => '🧥', 'Gilet' => '🦺', 'Polaire' => '🧥', 'Imperméable' => '🧥',
        'Combi/salopette' => '👷', 'Robe' => '👗', 'Jupe' => '👗',
        'Pantalon' => '👖', 'Jean' => '👖', 'Short' => '🩳', 'Jogging' => '👖', 'Legging' => '🩱',
        'Maillots' => '🩱', 'Sous vêtements' => '🩲', 'Vêtement' => '👕',
        'Chaussures' => '👟', 'Sac' => '👜', 'Ceinture' => '🎀', 'Couvre chef' => '🧢', 'Chapeau' => '🎩',
        'Lunettes' => '🕶️', 'Porte-Clef' => '🔑', 'Portefeuilles' => '👛', 'Montre' => '⌚', 'Foulards' => '🧣',
        'Gants' => '🧤', 'Chaussettes' => '🧦', 'Gourde' => '💧', 'Mouchoir' => '🤧', 'Pin s Portes clefs' => '🔑',
        'Serviette' => '🧻',
        'Bague' => '💍', 'Bagues' => '💍', 'Boucles d\'O' => '👂', 'Pendentif' => '📿', 'Bracelet' => '📿',
        'Collier' => '📿', 'Broche' => '📌', 'Ambre' => '💎', 'Cheville' => '📿',
        'Livre' => '📖', 'Livres' => '📚', 'BD' => '📚', 'Magazines' => '📰', 'Vinyle' => '🎵', 'Vinyl' => '🎵',
        'CDF' => '💿', 'DVD' => '📀', 'Musique' => '🎵', 'Photographie' => '📷', 'Enfant' => '👶', 'Ado' => '🧑‍🎤',
        'Adulte' => '👤', 'Poche' => '👜', 'Ancien' => '🏺', 'Journaux' => '📰', 'Jeux Société' => '🎲',
        'Deco' => '🖼️', 'Décoration' => '🖼️', 'Tableau Miroir' => '🖼️', 'Lampe' => '💡', 'Plante' => '🪴',
        'Mobilier' => '🪑', 'Art de la table' => '🍽️', 'Vaisselle' => '🍽️', 'Assiette' => '🍽️', 'Verre' => '🥃',
        'Bol' => '🥣', 'Tasse' => '☕', 'Théière' => '🫖', 'Cafetière' => '☕', 'Carafe' => '🍶', 'Pot' => '🏺',
        'Bocal' => '🫙', 'Bouteille' => '🍾', 'Couvert' => '🍽️', 'Nappe' => '🧺', 'Torchon' => '🧹',
        'Linge de lit' => '🛏️', 'Moulin' => '🏭', 'Service' => '🔧', 'Divers' => '📦', 'Couteau' => '🔪',
        'Bricabrac' => '🏺', 'Bibelot' => '🏺', 'Anciens' => '📜', 'Souvenirs' => '🎁', 'Figurines' => '🗿',
        'Véhicules miniatures' => '🚗', 'Playmobil' => '🏰', 'Lego' => '🧱', 'Peluches' => '🧸', 'Puzzle' => '🧩',
        'Jouet' => '🧸', 'Jouets' => '🧸', 'Dino' => '🦕', 'Barbies et AM' => '👸', 'Carte Postale' => '🎑',
        'Metallerie' => '⚙️', 'Allumettes' => '🦟', 'Pin\'s' => '🧷', 'Papeterie' => '📝', 'Tampon' => '🟩',
        'Éléctroménager' => '🔌',
        'Miel' => '🍯', 'Café' => '☕', 'Thé' => '🍵', 'Eau de Rose' => '🌹', 'Huile Argan' => '🧴',
        'Creme Argan' => '🧴', 'Huile Barbarie' => '🌵', 'Creme Barbarie' => '🧴', 'Savon Noir' => '🧼',
        'Savon' => '🧼', 'Arnica' => '🌿', 'Argile' => '🏺', 'Pierre Gommage' => '🪨', 'Gant Gommage' => '🧤',
        'Porte Savon' => '🧼',
        'Sticker' => '🏷️', 'Tarn' => '🏷️', 'Jonte' => '🏷️', 'Jonte new' => '🏷️', 'Fantaisie' => '🎭', 'Anciens (Sticker)' => '📜',
        'Mercerie' => '🧵', 'Tissus' => '🧵', 'Tissu' => '🧵', 'Fil' => '🧵', 'Broderie' => '🧵', 'Boutton' => '🔘', 'Cuir' => '👜',
        'Éplucheur' => '🔪',
        'Chat (3€)' => '🐱', 'Crapaud (3€)' => '🐸', 'Arbre (3€)' => '🌳', 'Chandelle (3€)' => '🕯️',
        'Lune-Soleil (6€)' => '🌙', 'Ganesh (6€)' => '🐘', 'Mère-Fille (6€)' => '👩‍👧',
        'Boîte métal (6€)' => '📦', 'Cylindre (6€)' => '⭕', 'Cobra (9€)' => '🐍',
        'Cylindre abeille (9€)' => '🐝', 'Fleur Lotus (9€)' => '🪷', 'Gros chat (9€)' => '🐱',
        'Groot (9€)' => '🌱', 'Indien (9€)' => '🪶', 'Loup (9€)' => '🐺', 'Moto (9€)' => '🏍️',
        'Ours (9€)' => '🐻', 'Sirène (9€)' => '🧜', 'Sorcier (9€)' => '🧙', 'Tortue (9€)' => '🐢',
        'Vierge Marie (9€)' => '🙏', 'Clown (13€)' => '🤡', 'Cylindre Renard (13€)' => '🦊',
        'Autres' => '📦', 'Autre' => '📦',
    ];

    if ($subcategory && isset($subMap[$subcategory])) {
        return $subMap[$subcategory];
    }

    $catMap = [
        'Hommes' => '👕', 'Femmes' => '👚', 'Enfants' => '👶', 'Accessoires' => '🎩', 'Livres' => '📚',
        'Brocante' => '🏺', 'Collab' => '🤝', 'Services' => '🔧', 'Bijoux' => '💍', 'Décoration' => '🖼️',
        'Art de la Table' => '🍽️', 'Maroc' => '🇲🇦', 'Sticker' => '🏷️', 'Mercerie' => '🧵', 'Jouets' => '🧸',
        'Matériel' => '🔧', 'Bougies' => '🕯️',
    ];

    return $catMap[$category] ?? '📦';
}

/**
 * Helper : label de méthode de paiement
 */
function ppPaymentLabel($method) {
    $map = ['cash' => 'Espèces', 'card' => 'Carte', 'check' => 'Chèque'];
    return $map[$method] ?? ucfirst($method);
}
?>

<?php $content = ob_get_clean(); ?>
<?php require('view/template/mainTemplate.php'); ?>
