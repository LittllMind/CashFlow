var selectedCategory;
var selectedSubcategory;

// Autres modes
var lotMode = false;
var globalLot = (typeof lotModeFlag !== 'undefined') ? lotModeFlag : false;
var globalLotTotal = (typeof lotTotal !== 'undefined') ? parseFloat(lotTotal) : 0.00;

var categoryEmojis = {
    'Sticker': '🏷️',
    'Hommes': '👕',
    'Femmes': '👚',
    'Enfants': '👶',
    'Accessoires': '🧥',
    'Bijoux': '💍',
    'Decoration': '🖼️',
    'Brocalogue': '🏺',
    'Brocante': '🏺',
    'Livres': '📚',
    'Livre': '📖',
    'Jouets': '🧸',
    'Maroc': '🇲🇦',
    'Art de la Table': '🍽️',
    'Mercerie': '🧵',
    'Services': '🔧',
    'Neuf': '🆕',
    'Collab': '🤝',
    'Bougies': '🕯️'
}

var subcategoryEmojis = {
    // ── VÊTEMENTS ──
    'Top': '👕', 'T-shirt': '👕', 'T-shirts': '👕', 'Tops et chemisiers': '👕',
    'Pull': '🧥', 'Veste': '🧥', 'Manteau': '🧥', 'Gilet': '🦺',
    'Polaire': '🧥', 'Imperméable': '🧥', 'Combi/salopette': '👷', 'Combi/salopettes': '👷',
    'Robe': '👗', 'Robes': '👗', 'Jupe': '👗', 'Jupes': '👗',
    'Pantalon': '👖', 'Jean': '👖', 'Short': '🩳', 'Jogging': '🏃',
    'Legging': '🩱', 'Leggings': '🩱', 'Maillots': '🩱',
    'Chemise': '👔', 'Chemises': '👔', 'Polo': '👕',
    'Enfant': '👶', 'Enfants': '👶', 'Ado': '🧑‍🎤', 'Adulte': '👤',
    'Chaussettes': '🧦', 'Sous vêtements': '🩲', 'Gants': '🧤', 'Gant Gommage': '🧤',
    'Costume': '🤵', 'Lingerie': '💋', 'Ensemble': '👕',
    'Vêtement': '👕', 'Vêtements': '👕',
    // ── CHAUSSURES / ACCESSOIRES ──
    'Chaussures': '👟', 'Sac': '👜', 'Sacs': '👜', 'Poche': '👜',
    'Ceinture': '🎀', 'Ceintures': '🎀', 'Foulards': '🧣', 'Chouchou': '🎀',
    'Couvre chef': '🎩', 'Chapeau': '🎩', 'Lunettes': '🕶️',
    'Porte-Clef': '🔑', 'Porte-Clefs': '🔑', 'Porte clefs': '🔑',
    'Portefeuilles': '👛', 'Porte Savon': '🧼',
    // ── BIJOUX ──
    'Bague': '💍', 'Bagues': '💍', 'Collier': '📿', 'Bracelet': '📿',
    'Pendentif': '📿', 'Broche': '📌', 'Ambre': '💎', 'Bijoux': '💍',
    'BO': '👂', 'BO Maison': '🏠', "Boucle d'O": '👂', "Boucles d'O": '👂',
    "Pin's": '🧷', 'Pin s Portes clefs': '🧷',
    // ── LIVRES / MUSIQUE / MÉDIA ──
    'Livre': '📖', 'Livres': '📚', 'BD': '📚', 'Magazines': '📰',
    'Vinyle': '🎵', 'Vinyl': '🎵', 'vynile': '🎵', 'CDF': '💿', 'DVD': '📀',
    'Musique': '🎵', 'Photographie': '📷',
    // ── DÉCORATION / ART DE LA TABLE ──
    'Deco': '🖼️', 'Décoration': '🖼️', 'Tableau Miroir': '🖼️',
    'Lampe': '💡', 'Plante': '🪴', 'Mobilier': '🪑',
    'Art de la table': '🍽️', 'Art de la Table': '🍽️',
    'Vaisselle': '🍽️', 'Assiette': '🍽️', 'Verre': '🥃', 'Bol': '🥣',
    'Tasse': '☕', 'Théière': '🫖', 'Cafetière': '☕', 'Carafe': '🍶',
    'Pot': '🏺', 'Bocal': '🫙', 'Bouteille': '🍾', 'Couvert': '🍽️',
    'Nappe': '🧺', 'Torchon': '🧹', 'Linge de lit': '🛏️',
    'Serviette': '🧻', 'Mouchoir': '🤧', 'Mouchoirs': '🤧',
    // ── BROCHANTE / ANCIEN ──
    'Bricabrac': '🏺', 'Bibelot': '🏺', 'Ancien': '🏺', 'Anciens': '📜',
    'Souvenirs': '🎁', 'Figurines': '🗿', 'Véhicules miniatures': '🚗',
    'Playmobil': '🏰', 'Lego': '🧱', 'Peluches': '🧸', 'Puzzle': '🧩',
    'Jouet': '🧸', 'Jouets': '🧸', 'DINO': '🦕', 'Barbies et AM': '👸',
    // ── CUISINE / ALIMENTAIRE ──
    'Miel': '🍯', 'Café': '☕', 'Thé': '🍵', 'Eau de Rose': '🌹',
    'Huile Argan': '🧴', 'Creme Argan': '🧴', 'Savon': '🧼', 'Savon Noir': '🧼',
    'Gourde': '💧', 'Moulin': '🏭', 'Éplucheur': '🔪',
    // ── MERCERIE / LOISIRS CRÉATIFS ──
    'Mercerie': '🧵', 'Tissus': '🧵', 'Tissu': '🧵', 'Fil': '🧵',
    'Broderie': '🧵', 'Boutton': '🔘', 'Tampon': '🟩', 'Tampons': '🧴',
    'Papeterie': '📝', 'papeterie': '📝',
    // ── PIERRES / BIEN-ÊTRE ──
    'Pierre Gommage': '🪨', 'Arnica': '🌿',
    // ── ÉLECTROMÉNAGER / OUTILS ──
    'Éléctroménager': '🔌', 'Metallerie': '⚙️', 'Service': '🔧', 'Couteau': '🔪',
    'Couteaux': '🔪', 'Allumettes': '🦟',
    // ── STICKERS ──
    'Sticker': '🏷️', 'Tarn': '🏷️', 'Jonte': '🏷️', 'Jonte new': '🏷️',
    'Fantaisie': '🎭', 'Anciens (Sticker)': '📜', 'Autres': '📦', 'Autre': '📦',
    // ── CARTES ──
    'Carte Postale': '🎑',
    // ── BOUGIES ──
    'Chat (3€)': '🐱', 'Crapaud (3€)': '🐸', 'Arbre (3€)': '🌳', 'Chandelle (3€)': '🕯️',
    'Lune-Soleil (6€)': '🌙', 'Ganesh (6€)': '🐘', 'Mère-Fille (6€)': '👩‍👧',
    'Vénus (6€)': '♀️', 'Fille (6€)': '👧', 'Cylindre (6€)': '🔋', 'Boîte métal (6€)': '📦',
    'Fleur de Lotus (9€)': '🪷', 'Groot (9€)': '🌱', 'Tortue (9€)': '🐢', 'Sirène (9€)': '🧜',
    'Vierge Marie (9€)': '🙏', 'Moto (9€)': '🏍️', 'Sorcier (9€)': '🧙', 'Ours sur puf (9€)': '🧸',
    'Indien (9€)': '🪶', 'Gros chat (9€)': '🐱', 'Cobra (9€)': '🐍', 'Cylindre abeille (9€)': '🐝',
    'Forêt (10€)': '🌲', 'Abeille (10€)': '🐝', 'Aigle (10€)': '🦅', 'Serpent (10€)': '🐍',
    'Clown (13€)': '🤡', 'Cylindre Renard (13€)': '🦊',
    'Renaud (15€)': '🦊',
    'Visage rond (6€)': '☺️',
    'Vierge (9€)': '🙏',
    'Chat (5€)': '🐱',
    'Crapaud (5€)': '🐸',
    'Arbre (4€)': '🌳',
    'Double visage (7€)': '🎭',
    'Fleur (9€)': '🌸',
    'Moto (7€)': '🏍️',
    'Aigle (9€)': '🦅',
    'Tortue (10€)': '🐢',
    'Sorcière (10€)': '🧙',
    'Argile': '🏺',
    'Huile Barbarie': '🌵',
    'Creme Barbarie': '🧴',
    'Cuir': '👜',
    'Boite': '📦',
    'Dino': '🦕',
    'Cheville': '📿',
    'Étiquettes': '🏷️',
    'Montre': '⌚',
    'Boucles d\'O': '👂',
    'Pin\'s': '🧷',
    'Jeux Société': '🎲',
    'Jeux de construction': '🧱',
    'Jeux de société': '🎲',
    'Indien (8€)': '🪶',
    'Sirène (8€)': '🧜',
    'Journaux': '📰',
    // ── DIVERS ──
    'Divers': '📦', '': '❓'
}


// Gestion clics externes pour stepper inline, recalc, auto-save
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.stepper-btn');
    if (!btn) return;
    const isInc = btn.classList.contains('inc');
    const isDec = btn.classList.contains('dec');
    if (isInc || isDec) {
        const wrapper = btn.closest('.card-stepper');
        if (!wrapper) return;
        const valueSpan = wrapper.querySelector('.stepper-val');
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const step = parseFloat(wrapper.dataset.step) || 1;
        let current = parseFloat(hiddenInput.value) || 0;
        let newValue = isInc
            ? parseFloat((current + step).toFixed(2))
            : parseFloat((current - step).toFixed(2));
        if (newValue < 0) newValue = 0;
        valueSpan.textContent = newValue;
        hiddenInput.value = newValue;
        syncCardTotal(wrapper.closest('.receipt-line'));
        autoSaveCard(wrapper.closest('.receipt-line'));
        e.stopPropagation();
    }
});

document.addEventListener('click', function(e) {
    const valueSpanClicked = e.target.closest('.card-stepper .stepper-val');
    if (!valueSpanClicked) return;
    e.stopPropagation();
    const wrapper = valueSpanClicked.closest('.card-stepper');
    if (!wrapper || wrapper.querySelector('input[type="number"]')) return;
    const field = wrapper.dataset.field;
    const input = document.createElement('input');
    input.type = 'number';
    input.value = parseFloat(wrapper.querySelector('input[type="hidden"]').value) || 0;
    input.style.width = '5ch';
    input.style.textAlign = 'center';
    if (field === 'unit_price') {
        input.step = '0.01';
        input.min = '0';
    } else {
        input.step = '1';
        input.min = '0';
    }
    valueSpanClicked.replaceWith(input);
    input.focus();
    const save = function() {
        let newValue = parseFloat(input.value);
        const hidden = wrapper.querySelector('input[type="hidden"]');
        if (isNaN(newValue) || newValue < 0) newValue = parseFloat(hidden.value) || 0;
        const newSpan = document.createElement('span');
        newSpan.className = 'stepper-val';
        newSpan.textContent = newValue;
        input.replaceWith(newSpan);
        hidden.value = newValue;
        syncCardTotal(wrapper.closest('.receipt-line'));
        autoSaveCard(wrapper.closest('.receipt-line'));
    };
    input.addEventListener('blur', save);
    input.addEventListener('keydown', function(ev) { if (ev.key === 'Enter') save(); });
});

function syncCardTotal(form) {
    if (!form) return;
    const qty = parseFloat(form.querySelector('input[name="quantity"]').value) || 0;
    const up = parseFloat(form.querySelector('input[name="unit_price"]').value) || 0;
    const lineTotal = (qty * up).toFixed(2);
    const headTotal = form.querySelector('.receipt-total');
    if (headTotal) headTotal.textContent = lineTotal + '€';
    const detailTotal = form.querySelector('.detail-total-val');
    if (detailTotal) detailTotal.textContent = lineTotal + '€';
    const mathSpan = form.querySelector('.receipt-math');
    if (mathSpan) mathSpan.textContent = qty + ' × ' + up.toFixed(2) + '€';
}

const _autoSaveTimers = {};
function autoSaveCard(card) {
    if (!card) return;
    const articleId = card.querySelector('input[name="article_id"]').value;
    if (!articleId) return;
    if (_autoSaveTimers[articleId]) clearTimeout(_autoSaveTimers[articleId]);
    _autoSaveTimers[articleId] = setTimeout(function() {
        const qty = parseFloat(card.querySelector('input[name="quantity"]').value) || 0;
        const upInput = card.querySelector('input[name="unit_price"]');
        const up = upInput ? parseFloat(upInput.value) || 0 : 0;
        const comment = card.querySelector('input[name="comment"]')?.value || '';
        const fd = new FormData();
        fd.append('article_id', articleId);
        fd.append('quantity', qty);
        if (!globalLot) {
            fd.append('unit_price', up.toFixed(2));
        }
        fd.append('comment', comment);
        fetch('index.php?action=apiUpdatePurchaseArticle', {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(json) {
            if (json.success) {
                if (json.normalized) {
                    refreshLotLines(json.articles, json.unit_price, json.total_quantity, json.purchase_total);
                } else {
                    updateTotalAmount(json.purchase_total);
                }
                flashMessage('Article mis à jour');
            }
        })
        .catch(function(err) { console.error('autoSaveCard error', err); });
    }, 600);
}

function toggleCategories() {
    const addArticleButton = document.getElementById('addArticleButton');
    const categoryDiv = document.getElementById('categoryButtons');
    const purchaseForm = document.getElementById('purchaseForm');
    if (categoryDiv.hidden) {
        categoryDiv.hidden = false;
        if (addArticleButton) addArticleButton.hidden = true;
        if (purchaseForm) purchaseForm.hidden = true;
        document.getElementById('subcategoryButtons').hidden = true;
        document.getElementById('quantityButtons').hidden = true;
        document.getElementById('priceButton').hidden = true;
        window.scrollTo(0, 0);
    } else {
        categoryDiv.hidden = true;
        document.getElementById('subcategoryButtons').hidden = true;
        document.getElementById('quantityButtons').hidden = true;
        document.getElementById('priceButton').hidden = true;
        if (addArticleButton) addArticleButton.hidden = false;
        if (purchaseForm) purchaseForm.hidden = false;
    }
}

function selectCategory(category) {
    var subcategoriesDiv = document.getElementById('subcategoryButtons');
    subcategoriesDiv.innerHTML = '';

    selectedCategory = category;

    var backButton = document.createElement('button');
    backButton.type = 'button';
    backButton.className = 'btn btn-warning mb-3';
    backButton.textContent = 'Retour';
    backButton.onclick = goBackToCategories;
    subcategoriesDiv.appendChild(backButton);

    let catSubcategories = subcategories.filter(function(subcat) {
        return subcat.category_name === category;
    });

    catSubcategories.forEach(function(subcategory) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-primary';
        var displayName = subcategory.subcategory_name;
        if (category === 'Bougies' && typeof subcategory.stock_score !== 'undefined') {
            displayName = displayName.replace(/\((\d+(?:\.\d+)?)[€$]?\)/, '($1€ | ' + subcategory.stock_score + ')');
        }
        button.innerHTML = '<span class="tile-emoji">' + (subcategoryEmojis[subcategory.subcategory_name] || '📦') + '</span><span class="tile-label">' + displayName + '</span>';
        button.onclick = function() { selectSubcategory(subcategory.subcategory_name); };
        subcategoriesDiv.appendChild(button);
    });

    if (catSubcategories.length === 0) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-secondary';
        button.innerHTML = '<span class="tile-emoji">📦</span><span class="tile-label">Autres</span>';
        button.onclick = function() { selectSubcategory('Autres'); };
        subcategoriesDiv.appendChild(button);
    }

    subcategoriesDiv.hidden = false;
    document.getElementById('categoryButtons').hidden = true;
    document.getElementById('quantityButtons').hidden = true;
    document.getElementById('priceButton').hidden = true;
    window.scrollTo(0, 0);
}

function selectSubcategory(subcategory) {
    var subcategoriesDiv = document.getElementById('subcategoryButtons');
    subcategoriesDiv.hidden = true;
    selectedSubcategory = subcategory;

    var quantityButtonsDiv = document.getElementById('quantityButtons');
    quantityButtonsDiv.innerHTML = '';

    var backButton = document.createElement('button');
    backButton.type = 'button';
    backButton.className = 'btn btn-warning';
    backButton.textContent = 'Retour';
    backButton.onclick = goBackToSubcategories;
    quantityButtonsDiv.appendChild(backButton);

    for (var i = 1; i <= 30; i++) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-info';
        button.textContent = i;
        button.onclick = function() { selectQuantity(this.textContent); };
        quantityButtonsDiv.appendChild(button);
    }

    quantityButtonsDiv.hidden = false;
    window.scrollTo(0, 0);
}

function selectQuantity(quantity) {
    document.getElementById('quantity').value = quantity;

    if (globalLot && globalLotTotal > 0) {
        // Mode lot global : pas de prix à saisir, on ajoute directement
        const comment = (document.getElementById('numpadComment')?.value || '');
        addPurchaseLine(quantity, 0.00, comment, true);
        return;
    }

    const priceButtonsDiv = document.getElementById('priceButton');
    const priceButtonContainer = document.getElementById('priceButtonContainer');
    priceButtonContainer.innerHTML = '';

    const backBtn = document.createElement('button');
    backBtn.type = 'button';
    backBtn.className = 'btn btn-warning numpad-back';
    backBtn.textContent = '← Retour';
    backBtn.onclick = goBackToQuantities;
    priceButtonContainer.appendChild(backBtn);

    const display = document.createElement('div');
    display.id = 'numpadDisplay';
    display.className = 'numpad-display';
    display.textContent = '0,00 €';
    priceButtonContainer.appendChild(display);

    const numpad = document.createElement('div');
    numpad.className = 'numpad-grid';
    const keyDefs = [
        {k:'7', act:'7'}, {k:'8', act:'8'}, {k:'9', act:'9'}, {k:'←', act:'backspace'},
        {k:'4', act:'4'}, {k:'5', act:'5'}, {k:'6', act:'6'}, {k:'C', act:'clear'},
        {k:'1', act:'1'}, {k:'2', act:'2'}, {k:'3', act:'3'}, {k:'.', act:'dot'},
        {k:'0', act:'0', cls:'numpad-zero'}, {k:'OK', act:'ok', cls:'numpad-ok'}
    ];

    keyDefs.forEach(function(def) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'numpad-btn' + (def.cls ? ' ' + def.cls : '');
        btn.textContent = def.k;
        btn.addEventListener('click', function() { numpadHandle(def.act); });
        numpad.appendChild(btn);
    });

    priceButtonContainer.appendChild(numpad);

    const commentWrap = document.createElement('div');
    commentWrap.className = 'numpad-comment-wrap';
    commentWrap.innerHTML = '<label for="numpadComment" class="numpad-comment-label">Note :</label>' +
        '<input type="text" id="numpadComment" class="numpad-comment-input" placeholder="Neuf, promo, état…" maxlength="80">';
    priceButtonContainer.appendChild(commentWrap);

    // Mode lot classique masqué quand on est en lot global
    if (!globalLot) {
        const lotWrap = document.createElement('div');
        lotWrap.className = 'lot-toggle';
        lotWrap.innerHTML = '<label class="lot-checkbox"><input type="checkbox" id="lotMode" onchange="toggleLotMode()">' +
            '<span>🧺 Mode lot : saisir total du lot puis quantité</span></label>';
        priceButtonContainer.appendChild(lotWrap);
    }

    priceButtonsDiv.hidden = false;
    document.getElementById('quantityButtons').hidden = true;
    window.scrollTo(0, 0);

    window._numpadValue = '';
    window._lotTotal = 0;
    lotMode = false;
    updateNumpadDisplay();
}

function numpadHandle(action) {
    let val = window._numpadValue || '';

    if (action === 'clear') {
        val = '';
    } else if (action === 'backspace') {
        val = val.slice(0, -1);
    } else if (action === 'ok') {
        if (lotMode) {
            const total = parseFloat(val.replace(',', '.') || '0');
            if (total >= 0) {
                window._lotTotal = total;
                applyLotPrice();
            }
            return;
        }
        const price = parseFloat(val.replace(',', '.') || '0');
        if (price >= 0) selectPrice(price);
        return;
    } else if (action === 'dot') {
        if (!val.includes('.')) val += '.';
    } else if (/^[0-9]$/.test(action)) {
        val += action;
    }

    if (val.includes('.')) {
        const parts = val.split('.');
        if (parts[1] && parts[1].length > 2) val = parts[0] + '.' + parts[1].slice(0, 2);
    }
    const num = parseFloat(val || '0');
    if (num > 999.99) val = '999.99';

    window._numpadValue = val;
    updateNumpadDisplay();
}

function updateNumpadDisplay() {
    const disp = document.getElementById('numpadDisplay');
    if (!disp) return;
    const val = window._numpadValue || '';
    const num = parseFloat(val || '0');
    const label = lotMode ? 'Total lot ' : '';
    disp.textContent = label + num.toFixed(2).replace('.', ',') + ' €';
    disp.classList.remove('pulse');
    void disp.offsetWidth;
    disp.classList.add('pulse');
}

function toggleLotMode() {
    lotMode = document.getElementById('lotMode').checked;
    window._numpadValue = '';
    updateNumpadDisplay();
}

function applyLotPrice() {
    const qty = parseFloat(document.getElementById('quantity').value) || 1;
    const total = window._lotTotal || 0;
    if (qty <= 0) return;
    const unitPrice = parseFloat((total / qty).toFixed(2));
    selectPrice(unitPrice);
}


function addPurchaseLine(quantity, price, comment, normalize) {
    var purchaseId = (typeof purchase_id !== 'undefined') ? purchase_id : document.getElementById('purchase_id').value;
    if (purchaseId === 'add') {
        alert('Créez d\'abord l\'achat avec le formulaire ci-dessous, puis ajoutez les articles.');
        goBackToAddArticle();
        return;
    }

    var payload = {
        purchase_id: purchaseId,
        category: selectedCategory,
        subcategory: selectedSubcategory,
        quantity: quantity,
        price: price,
        comment: comment || ''
    };

    apiSavePurchaseArticle(payload, function(res) {
        if (res.normalized) {
            refreshLotLines(res.articles, res.unit_price, res.total_quantity, res.purchase_total);
            flashMessage(selectedSubcategory + ' × ' + quantity + ' ajouté au lot');
        } else {
            prependArticleToList({
                article_id: res.article_id,
                purchase_id: purchaseId,
                category: selectedCategory,
                subcategory: selectedSubcategory,
                quantity: quantity,
                unit_price: price,
                comment: comment || ''
            });
            updateTotalAmount(res.purchase_total);
            updateLotBanner(quantity);
            flashMessage(selectedSubcategory + ' × ' + quantity + ' ajouté');
        }
    });

    goBackToAddArticle();
}

function updateLotBanner(addedQty) {
    if (!globalLot) return;
    var qtyDisplay = document.getElementById('lotQtyDisplay');
    var priceDisplay = document.getElementById('lotUnitPriceDisplay');
    if (!qtyDisplay || !priceDisplay) return;
    var currentQty = parseFloat(qtyDisplay.textContent) || 0;
    var newQty = currentQty + (parseFloat(addedQty) || 0);
    qtyDisplay.textContent = newQty;
    if (newQty > 0 && globalLotTotal > 0) {
        priceDisplay.textContent = (globalLotTotal / newQty).toFixed(2).replace('.', ',');
    }
}

function refreshLotLines(articles, unitPrice, totalQuantity, purchaseTotal) {
    var noArt = document.getElementById('noArticlesMsg') || document.querySelector('.no-articles');
    if (noArt) noArt.remove();

    var container = document.getElementById('receiptList');
    if (!container) {
        container = document.createElement('div');
        container.id = 'receiptList';
        container.className = 'receipt-list';
        var form = document.getElementById('articleForm');
        if (form && form.parentNode) {
            form.parentNode.insertBefore(container, form.nextSibling);
        }
    }
    container.innerHTML = '';

    articles.forEach(function(art) {
        art.unit_price = unitPrice;
        var html = buildReceiptLineHTML(art);
        var temp = document.createElement('div');
        temp.innerHTML = html.trim();
        container.appendChild(temp.firstElementChild);
    });

    updateTotalAmount(purchaseTotal);
    var qtyDisplay = document.getElementById('lotQtyDisplay');
    var priceDisplay = document.getElementById('lotUnitPriceDisplay');
    if (qtyDisplay) qtyDisplay.textContent = totalQuantity;
    if (priceDisplay && totalQuantity > 0) {
        priceDisplay.textContent = (globalLotTotal / totalQuantity).toFixed(2).replace('.', ',');
    }
}

function selectPrice(price) {
    document.getElementById('priceButton').hidden = true;
    lotMode = false;
    var qty = document.getElementById('quantity').value;
    var comment = (document.getElementById('numpadComment')?.value || '');
    addPurchaseLine(qty, price, comment, false);
}

function apiSavePurchaseArticle(params, onSuccess, onError) {
    const formData = new FormData();
    formData.append('purchase_id', params.purchase_id);
    formData.append('category', params.category);
    formData.append('subcategory', params.subcategory);
    formData.append('quantity', params.quantity);
    formData.append('unit_price', params.price);
    formData.append('comment', params.comment || '');

    fetch('index.php?action=apiSavePurchaseArticle', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(json) {
        if (json.success) {
            if (onSuccess) onSuccess(json);
        } else {
            if (onError) onError(json.error || 'Erreur serveur');
            else console.error(json.error);
        }
    })
    .catch(function(err) {
        console.error('apiSavePurchaseArticle error', err);
        if (onError) onError(err.message);
        else alert('Erreur réseau lors de l\'ajout');
    });
}

function apiDeletePurchaseArticle(articleId, onSuccess, onError) {
    const formData = new FormData();
    formData.append('article_id', articleId);

    fetch('index.php?action=apiDeletePurchaseArticle', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(json) {
        if (json.success) {
            if (onSuccess) onSuccess(json);
        } else {
            if (onError) onError(json.error || 'Erreur serveur');
            else console.error(json.error);
        }
    })
    .catch(function(err) {
        console.error('apiDeletePurchaseArticle error', err);
        if (onError) onError(err.message);
        else alert('Erreur réseau lors de la suppression');
    });
}

function deleteArticle(articleId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
        apiDeletePurchaseArticle(articleId, function(res) {
            if (res.normalized) {
                refreshLotLines(res.articles, res.unit_price, res.total_quantity, res.purchase_total);
            } else {
                var row = document.getElementById('row' + articleId);
                if (row) row.remove();
                updateTotalAmount(res.purchase_total);
            }
            flashMessage('Article supprimé');
        });
    }
}

function prependArticleToList(art) {
    var container = document.getElementById('receiptList');
    if (!container) {
        var oldP = document.getElementById('noArticlesMsg') || document.querySelector('.no-articles');
        if (oldP) oldP.remove();
        container = document.createElement('div');
        container.id = 'receiptList';
        container.className = 'receipt-list';
        var form = document.getElementById('articleForm');
        if (form && form.parentNode) {
            form.parentNode.insertBefore(container, form.nextSibling);
        } else {
            document.body.appendChild(container);
        }
    }
    var html = buildReceiptLineHTML(art);
    var temp = document.createElement('div');
    temp.innerHTML = html.trim();
    var newLine = temp.firstElementChild;
    container.insertBefore(newLine, container.firstChild);
}

function buildReceiptLineHTML(art) {
    const qty = parseFloat(art.quantity || art.qty);
    const up = parseFloat(art.unit_price || art.price);
    const lineTotal = (qty * up).toFixed(2);
    const comm = escapeHtml(art.comment || '');
    const upStr = up.toFixed(2);
    const catEmoji = categoryEmojis[art.category] || '📦';
    const id = art.article_id || art.id;

    return '<div class="receipt-line" id="row' + id + '" data-id="' + id + '">' +
      '<input type="hidden" name="article_id" value="' + id + '">' +

      '<div class="receipt-head" onclick="toggleReceiptDetail(' + id + ')">' +
        '<div class="receipt-left">' +
          '<span class="receipt-emoji">' + catEmoji + '</span>' +
          '<span class="receipt-name">' + escapeHtml(art.subcategory) + '</span>' +
          '<span class="receipt-cat">' + escapeHtml(art.category) + '</span>' +
          (comm ? '<span class="receipt-comment">💬 ' + comm + '</span>' : '') +
        '</div>' +
        '<div class="receipt-right">' +
          '<span class="receipt-math">' + qty + ' × ' + upStr + '€</span>' +
          '<span class="receipt-total">' + lineTotal + '€</span>' +
          '<span class="receipt-chevron">▼</span>' +
        '</div>' +
      '</div>' +

      '<div class="receipt-detail" id="detail' + id + '" hidden>' +
        '<div class="detail-grid">' +
          '<div class="detail-group">' +
            '<span class="detail-label">Quantité</span>' +
            '<div class="card-stepper" data-field="quantity">' +
              '<button type="button" class="stepper-btn dec"><img src="assets/images/signe-moins.png" alt="−" width="14" height="14"></button>' +
              '<span class="stepper-val">' + qty + '</span>' +
              '<button type="button" class="stepper-btn inc"><img src="assets/images/ajouter.png" alt="+" width="14" height="14"></button>' +
              '<input type="hidden" name="quantity" value="' + qty + '">' +
            '</div>' +
          '</div>' +
          '<div class="detail-group">' +
            '<span class="detail-label">Prix unitaire</span>' +
            '<div class="card-stepper" data-field="unit_price" data-step="0.5">' +
              '<button type="button" class="stepper-btn dec"><img src="assets/images/signe-moins.png" alt="−" width="14" height="14"></button>' +
              '<span class="stepper-val">' + upStr + '</span>' +
              '<button type="button" class="stepper-btn inc"><img src="assets/images/ajouter.png" alt="+" width="14" height="14"></button>' +
              '<input type="hidden" name="unit_price" value="' + up + '">' +
            '</div>' +
          '</div>' +
          '<div class="detail-group">' +
            '<span class="detail-label">Total</span>' +
            '<span class="detail-total-val" id="detailTotal' + id + '">' + lineTotal + '€</span>' +
          '</div>' +
        '</div>' +
        '<div class="detail-extra">' +
          '<div class="detail-comment">' +
            '<input type="text" name="comment" value="' + comm + '" class="comment-input" placeholder="Ajouter un commentaire…">' +
          '</div>' +
          '<button type="button" class="btn-detail-delete" onclick="deleteArticle(' + id + ')">🗑 Supprimer</button>' +
        '</div>' +
      '</div>' +
    '</div>';
}

function escapeHtml(text) {
    if (text == null) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleReceiptDetail(articleId) {
    var detail = document.getElementById('detail' + articleId);
    var line = document.getElementById('row' + articleId);
    if (!detail || !line) return;
    var open = detail.hidden;
    detail.hidden = !open;
    line.classList.toggle('expanded', open);
}

function updateTotalAmount(newTotal) {
    const display = document.getElementById('total_amount');
    if (display) display.value = parseFloat(newTotal).toFixed(2);
    const displaySpan = document.getElementById('purchaseTotalDisplay');
    if (displaySpan) displaySpan.textContent = parseFloat(newTotal).toFixed(2) + '€';
}

function flashMessage(msg) {
    let flash = document.getElementById('ajaxFlashMsg');
    if (!flash) {
        flash = document.createElement('div');
        flash.id = 'ajaxFlashMsg';
        flash.style.cssText = 'position:fixed;top:10px;left:50%;transform:translateX(-50%);background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;font-size:1rem;z-index:9999;display:none;';
        document.body.appendChild(flash);
    }
    flash.textContent = msg;
    flash.style.display = 'block';
    setTimeout(function() { flash.style.display = 'none'; }, 2000);
}

function goBackToAddArticle() {
    document.getElementById('categoryButtons').hidden = true;
    document.getElementById('subcategoryButtons').hidden = true;
    document.getElementById('quantityButtons').hidden = true;
    document.getElementById('priceButton').hidden = true;
    var addArticleButton = document.getElementById('addArticleButton');
    if (addArticleButton) addArticleButton.hidden = false;
    var purchaseForm = document.getElementById('purchaseForm');
    if (purchaseForm) purchaseForm.hidden = false;
    window.scrollTo(0, 0);
}

function goBackToCategories() {
    document.getElementById('subcategoryButtons').hidden = true;
    document.getElementById('quantityButtons').hidden = true;
    document.getElementById('priceButton').hidden = true;
    document.getElementById('categoryButtons').hidden = false;
    window.scrollTo(0, 0);
}

function goBackToSubcategories() {
    document.getElementById('quantityButtons').hidden = true;
    document.getElementById('priceButton').hidden = true;
    document.getElementById('subcategoryButtons').hidden = false;
    window.scrollTo(0, 0);
}

function goBackToQuantities() {
    document.getElementById('priceButton').hidden = true;
    document.getElementById('quantityButtons').hidden = false;
    window.scrollTo(0, 0);
}

function setPayment(method) {
    document.querySelectorAll('.payment-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    var targetBtn = document.querySelector('.payment-btn[data-method="' + method + '"]');
    if (targetBtn) targetBtn.classList.add('active');
    var input = document.getElementById('payment_method');
    if (input) input.value = method;

    var purchaseId = (typeof purchase_id !== 'undefined') ? purchase_id : document.getElementById('purchase_id').value;
    if (!purchaseId || purchaseId === 'add') {
        return;
    }

    var fd = new FormData();
    fd.append('purchase_id', purchaseId);
    fd.append('payment_method', method);
    fetch('index.php?action=apiUpdatePurchase', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) {
            flashMessage('Paiement mis à jour');
        } else {
            flashMessage(json.error || 'Échec');
        }
    })
    .catch(function(err) {
        console.error('setPayment error', err);
        flashMessage('⚠️ ' + err.message);
    });
}

function updatePurchaseDate(date) {
    var purchaseId = (typeof purchase_id !== 'undefined') ? purchase_id : document.getElementById('purchase_id').value;
    if (!purchaseId || purchaseId === 'add') return;

    var fd = new FormData();
    fd.append('purchase_id', purchaseId);
    fd.append('purchase_date', date);
    fetch('index.php?action=apiUpdatePurchase', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) flashMessage('Date mise à jour');
    })
    .catch(function(err) { console.error('updatePurchaseDate error', err); });
}

function updatePurchaseProvider(providerId) {
    var purchaseId = (typeof purchase_id !== 'undefined') ? purchase_id : document.getElementById('purchase_id').value;
    if (!purchaseId || purchaseId === 'add') return;

    var fd = new FormData();
    fd.append('purchase_id', purchaseId);
    fd.append('provider_id', providerId);
    fetch('index.php?action=apiUpdatePurchase', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) flashMessage('Fournisseur mis à jour');
    })
    .catch(function(err) { console.error('updatePurchaseProvider error', err); });
}

function updatePurchasePaidAmount(amount) {
    var purchaseId = (typeof purchase_id !== 'undefined') ? purchase_id : document.getElementById('purchase_id').value;
    if (!purchaseId || purchaseId === 'add') return;

    var fd = new FormData();
    fd.append('purchase_id', purchaseId);
    fd.append('paid_amount', parseFloat(amount || '0').toFixed(2));
    fetch('index.php?action=apiUpdatePurchase', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) flashMessage('Montant payé mis à jour');
    })
    .catch(function(err) { console.error('updatePurchasePaidAmount error', err); });
}

// Soumission du formulaire achat : validation minimum
document.addEventListener('DOMContentLoaded', function() {
    var purchaseForm = document.getElementById('purchaseForm');
    if (purchaseForm) {
        purchaseForm.addEventListener('submit', function(e) {
            var method = document.getElementById('payment_method').value;
            if (!method) {
                e.preventDefault();
                flashMessage('Veuillez choisir un mode de paiement');
                return false;
            }
        });
    }
});


/* ---------- Ajout rapide fournisseur ---------- */
var quickProviderSavePromise = null;
function openAddProviderPopup() {
    var popup = document.getElementById('quickProviderPopup');
    var overlay = document.getElementById('quickProviderOverlay');
    if (popup) popup.style.display = 'block';
    if (overlay) overlay.style.display = 'block';
    document.getElementById('newProviderName').value = '';
    document.getElementById('newProviderCity').value = '';
    document.getElementById('newProviderEmail').value = '';
    document.getElementById('newProviderPhone').value = '';
    document.getElementById('newProviderName').focus();
}

function closeAddProviderPopup() {
    var popup = document.getElementById('quickProviderPopup');
    var overlay = document.getElementById('quickProviderOverlay');
    if (popup) popup.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
}

function submitAddProvider() {
    var name = document.getElementById('newProviderName').value.trim();
    if (!name) {
        document.getElementById('newProviderName').style.borderColor = '#dc3545';
        document.getElementById('newProviderName').focus();
        return;
    }
    document.getElementById('newProviderName').style.borderColor = '#ddd';

    var fd = new FormData();
    fd.append('name', name);
    fd.append('city', document.getElementById('newProviderCity').value.trim());
    fd.append('contact_email', document.getElementById('newProviderEmail').value.trim());
    fd.append('contact_phone', document.getElementById('newProviderPhone').value.trim());

    if (quickProviderSavePromise) return;
    quickProviderSavePromise = fetch('index.php?action=apiAddProvider', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    })
    .then(function(r) {
        if (r.redirected) throw new Error('Session expirée');
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(json) {
        if (json.success) {
            var select = document.getElementById('provider_id');
            var option = document.createElement('option');
            option.value = json.provider_id;
            option.textContent = json.name + (json.city ? ' — ' + json.city : '');
            select.appendChild(option);
            select.value = json.provider_id;
            closeAddProviderPopup();
            if (typeof updatePurchaseProvider === 'function' && purchase_id && purchase_id !== 'add') {
                updatePurchaseProvider(json.provider_id);
            }
            flashMessage('Fournisseur ajouté');
        } else {
            flashMessage(json.error || 'Échec ajout');
        }
    })
    .catch(function(err) {
        console.error(err);
        flashMessage('⚠️ ' + err.message);
    })
    .finally(function() {
        quickProviderSavePromise = null;
    });
}
