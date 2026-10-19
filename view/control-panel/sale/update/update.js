
var selectedCategory;
var selectedSubcategory;
document.querySelectorAll('.editable input[type="number"]').forEach(input => {
    const resize = () => {
        input.style.width = ((input.value.length + 1) * 0.6) + 'em';
    }
    input.addEventListener('input', resize);
    resize(); // taille initiale
});

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
        return;
    }
    const valueSpanClicked = e.target.closest('.card-stepper .stepper-val');
    if (valueSpanClicked) {
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
        const save = () => {
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
        input.addEventListener('keydown', (ev) => { if (ev.key === 'Enter') save(); });
    }
});

/* Recalcule le total d'une card et met à jour le header */
function syncCardTotal(form) {
    if (!form) return;
    const qty  = parseFloat(form.querySelector('input[name="quantity"]').value) || 0;
    const up   = parseFloat(form.querySelector('input[name="unit_price"]').value) || 0;
    const lineTotal = (qty * up).toFixed(2);
    const headTotal = form.querySelector('.receipt-total');
    if (headTotal) headTotal.textContent = lineTotal + '€';
    const detailTotal = form.querySelector('.detail-total-val');
    if (detailTotal) detailTotal.textContent = lineTotal + '€';
    const mathSpan = form.querySelector('.receipt-math');
    if (mathSpan) mathSpan.textContent = qty + ' × ' + up.toFixed(2) + '€';
}

/* Auto-save article card via AJAX (debounce 600ms) */
const _autoSaveTimers = {};
function autoSaveCard(card) {
    if (!card) return;
    const articleId = card.querySelector('input[name="article_id"]').value;
    if (!articleId) return;
    // Debounce par article_id
    if (_autoSaveTimers[articleId]) clearTimeout(_autoSaveTimers[articleId]);
    _autoSaveTimers[articleId] = setTimeout(function() {
        const qty  = parseFloat(card.querySelector('input[name="quantity"]').value) || 0;
        const up   = parseFloat(card.querySelector('input[name="unit_price"]').value) || 0;
        const comment = card.querySelector('input[name="comment"]')?.value || '';
        const fd = new FormData();
        fd.append('article_id', articleId);
        fd.append('quantity', qty);
        fd.append('unit_price', up.toFixed(2));
        fd.append('comment', comment);
        fetch('index.php?action=apiUpdateArticle', {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(json) {
            if (json.success) {
                updateTotalAmount(json.sale_total);
                flashMessage('Article mis à jour');
            }
        })
        .catch(function(err) { console.error('autoSaveCard error', err); });
    }, 600);
}



function toggleCategories() {
    const addArticleButton = document.getElementById("addArticleButton");
    var categoryDiv = document.getElementById("categoryButtons");
    var saleForm = document.getElementById("saleForm");
    if (categoryDiv.hidden) {
        categoryDiv.hidden = false;
        addArticleButton.hidden = true;
        if (addArticleButton.classList) {
            addArticleButton.classList.remove('sale-nav-btn-add');
            addArticleButton.classList.add('nav-collapsed');
        }
        saleForm.hidden = true;
        document.getElementById("stickerPacks").hidden = true;
        window.scrollTo(0, 0);
    } else {
        categoryDiv.hidden = true;
        document.getElementById("subcategoryButtons").hidden = true;
        document.getElementById("quantityButtons").hidden = true;
        document.getElementById("priceButton").hidden = true;
        document.getElementById("stickerPacks").hidden = true;
        document.getElementById("stickerSlots").hidden = true;
        addArticleButton.hidden = false;
        if (addArticleButton.classList) {
            addArticleButton.classList.remove('nav-collapsed');
            addArticleButton.classList.add('sale-nav-btn-add');
        }
        saleForm.hidden = false;
    }
}

// Mapping emoji categorie
const categoryEmojis = {
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
};

// 🕯️ Whitelist des modèles Bougies avec prix indicatifs
const BOUGIES_TARGET = new Set([
    'Arbre (3€)', 'Chandelle (3€)', 'Chat (3€)', 'Crapaud (3€)',
    'Boîte métal (6€)', 'Cylindre (6€)', 'Ganesh (6€)', 'Lune-Soleil (6€)', 'Mère-Fille (6€)',
    'Tortue (9€)', 'Cobra (9€)', 'Cylindre abeille (9€)', 'Fleur Lotus (9€)', 'Groot (9€)', 'Gros chat (9€)',
    'Sirène (9€)', 'Vierge Marie (9€)', 'Moto (9€)', 'Sorcier (9€)', 'Ours (9€)',
    'Loup (9€)', 'Indien (9€)',
    'Clown (13€)', 'Cylindre Renard (13€)'
]);

// 🕯️ Dégradé couleur par tranche de prix (chaud bougie)
function getPriceGradient(subcategoryName) {
    const m = subcategoryName.match(/\((\d+)[€$]?\)/);
    if (!m) return '';
    const p = parseInt(m[1], 10);
    // Pas de degre si ce n'est pas Bougies
    if (selectedCategory !== 'Bougies') return '';
    if (p <= 3) return 'price-3';
    if (p <= 6) return 'price-6';
    if (p <= 9) return 'price-9';
    if (p <= 13) return 'price-13';
    return 'price-15';
}

// Extrait le prix du libellé de sous-catégorie
function parsePrice(subcategoryName) {
    const m = subcategoryName.match(/\((\d+(?:\.\d+)?)[€$]?\)/);
    return m ? parseFloat(m[1]) : 9999;
}


const stickerImages = {
    'Tarn': 'assets/images/sticker-tarn.png',
    'Jonte': 'assets/images/sticker-jonte.webp',
    'Jonte new': 'assets/images/sticker-peyreleau.jpg',
    'Peyreleau': 'assets/images/sticker-peyreleau.jpg'
};

const stickerDisplayNames = {
    'Jonte new': 'Peyreleau'
};

function getStickerImg(name, className='tile-sticker-img') {
    var src = stickerImages[name] || '';
    var display = stickerDisplayNames[name] || name;
    if (src) {
        return '<img src="' + src + '" alt="' + display + '" class="' + className + '"\u003e';
    }
    return '<span class="tile-emoji">' + (subcategoryEmojis[name] || '📦') + '</span>';
}

function getStickerLabel(name) {
    return stickerDisplayNames[name] || name;
}

const subcategoryEmojis = {
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
};

// ============================================================
// AJAX HELPERS — ajout/suppression/modification sans rechargement
// ============================================================

// Helper qui met à jour le total affiché
function updateTotalAmount(newTotal) {
    const display = document.getElementById('total_amount');
    if (display) display.value = parseFloat(newTotal).toFixed(2);
    const displaySpan = document.getElementById('saleTotalDisplay');
    if (displaySpan) displaySpan.textContent = parseFloat(newTotal).toFixed(2) + '€';
}

// Message de feedback fugitif (sans alert disruptive)
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

// Créer le HTML d'une ligne d'article identique à PHP
function buildReceiptLineHTML(art) {
    const qty  = parseFloat(art.quantity || art.qty);
    const up   = parseFloat(art.unit_price || art.price);
    const lineTotal = (qty * up).toFixed(2);
    const comm = escapeHtml(art.comment || '');
    const upStr = up.toFixed(2);
    const catEmoji = categoryEmojis[art.category] || '📦';
    const id = art.article_id || art.id;
    const sid = art.sale_id;

    return `<div class="receipt-line" id="row${id}" data-id="${id}">
  <input type="hidden" name="article_id" value="${id}">
  <input type="hidden" name="sale_id" value="${sid}">

  <div class="receipt-head" onclick="toggleReceiptDetail(${id})">
    <div class="receipt-left">
      <span class="receipt-emoji">${catEmoji}</span>
      <span class="receipt-name">${escapeHtml(art.subcategory)}</span>
      <span class="receipt-cat">${escapeHtml(art.category)}</span>
      ${comm ? '<span class="receipt-comment">💬 ' + comm + '</span>' : ''}
    </div>
    <div class="receipt-right">
      <span class="receipt-math">${qty} × ${upStr}€</span>
      <span class="receipt-total">${lineTotal}€</span>
      <span class="receipt-chevron">▼</span>
    </div>
  </div>

  <div class="receipt-detail" id="detail${id}" hidden>
    <div class="detail-grid">
      <div class="detail-group">
        <span class="detail-label">Quantité</span>
        <div class="card-stepper" data-field="quantity">
          <button type="button" class="stepper-btn dec">
            <img src="assets/images/signe-moins.png" alt="−" width="14" height="14">
          </button>
          <span class="stepper-val">${qty}</span>
          <button type="button" class="stepper-btn inc">
            <img src="assets/images/ajouter.png" alt="+" width="14" height="14">
          </button>
          <input type="hidden" name="quantity" value="${qty}">
        </div>
      </div>
      <div class="detail-group">
        <span class="detail-label">Prix unitaire</span>
        <div class="card-stepper" data-field="unit_price" data-step="0.5">
          <button type="button" class="stepper-btn dec">
            <img src="assets/images/signe-moins.png" alt="−" width="14" height="14">
          </button>
          <span class="stepper-val">${upStr}</span>
          <button type="button" class="stepper-btn inc">
            <img src="assets/images/ajouter.png" alt="+" width="14" height="14">
          </button>
          <input type="hidden" name="unit_price" value="${up}">
        </div>
      </div>
      <div class="detail-group">
        <span class="detail-label">Total</span>
        <span class="detail-total-val" id="detailTotal${id}">${lineTotal}€</span>
      </div>
    </div>
    <div class="detail-extra">
      <div class="detail-comment">
        <input type="text" name="comment" value="${comm}" class="comment-input" placeholder="Ajouter un commentaire…">
      </div>
      <button type="button" class="btn-detail-delete" onclick="deleteArticle(${id}, ${sid})">
        🗑 Supprimer
      </button>
    </div>
  </div>
</div>`;
}

function prependArticleToList(art) {
    var container = document.getElementById('receiptList');
    if (!container) {
        var oldP = document.getElementById('noArticlesMsg') || document.querySelector('.no-articles');
        if (oldP) oldP.remove();
        container = document.createElement('div');
        container.id = 'receiptList';
        container.className = 'receipt-list';
        var articleTableDiv = document.getElementById('articleTable');
        if (articleTableDiv && articleTableDiv.parentNode) {
            articleTableDiv.parentNode.insertBefore(container, articleTableDiv);
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

function escapeHtml(text) {
    if (text == null) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/* Toggle receipt detail expand/collapse */
function toggleReceiptDetail(articleId) {
    var detail = document.getElementById('detail' + articleId);
    var line = document.getElementById('row' + articleId);
    if (!detail || !line) return;
    var open = detail.hidden;
    detail.hidden = !open;
    line.classList.toggle('expanded', open);
}

// API — ajouter un article (POST json/x-www-form-urlencoded)
function apiAddArticle(params, onSuccess, onError) {
    const formData = new FormData();
    formData.append('sale_id', params.sale_id);
    formData.append('category', params.category);
    formData.append('subcategory', params.subcategory);
    formData.append('quantity', params.quantity);
    formData.append('unit_price', params.price);
    formData.append('comment', params.comment || '');

    fetch('index.php?action=apiSaveArticle', {
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
        console.error('apiAddArticle error', err);
        if (onError) onError(err.message);
        else alert('Erreur réseau lors de l\'ajout');
    });
}

// API — supprimer un article
function apiDeleteArticle(saleId, articleId, onSuccess, onError) {
    const formData = new FormData();
    formData.append('sale_id', saleId);
    formData.append('article_id', articleId);

    fetch('index.php?action=apiDeleteArticle', {
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
        console.error('apiDeleteArticle error', err);
        if (onError) onError(err.message);
        else alert('Erreur réseau lors de la suppression');
    });
}

// ============================================================


// ── Tri stable par popularité du mois (bucket) ──
function getSubcategoryScore(category, subcategoryName) {
    const key = category + '|' + subcategoryName;
    return subcategoryScores[key] || 0;
}

function sortByMonthPopularityStable(list, category) {
    return list.slice().sort(function(a, b) {
        const sa = getSubcategoryScore(category, a.subcategory_name);
        const sb = getSubcategoryScore(category, b.subcategory_name);
        if (sa !== sb) return sb - sa; // score décroissant
        return a.subcategory_name.localeCompare(b.subcategory_name);
    });
}

const RECENT_KEY = 'matrico_recent_subcats';
const RECENT_MAX = 6;

function getRecentSubcats(category) {
    try {
        const raw = localStorage.getItem(RECENT_KEY + '_' + category);
        if (!raw) return [];
        return JSON.parse(raw);
    } catch (e) { return []; }
}

function pushRecentSubcat(category, subcategory) {
    const key = RECENT_KEY + '_' + category;
    let arr = getRecentSubcats(category);
    // Supprimer si déjà présent (pour remonter en tête)
    arr = arr.filter(s => s !== subcategory);
    arr.unshift(subcategory);
    if (arr.length > RECENT_MAX) arr = arr.slice(0, RECENT_MAX);
    try { localStorage.setItem(key, JSON.stringify(arr)); } catch (e) {}
}

/* Ancienne section "Récents" par localStorage — remplacée par tri global par popularité du mois.
   On conserve les helpers pour compatibilité éventuelle, mais ils ne sont plus utilisés. */
function renderRecentPills(container, category, availableSubcats) {
    return;
}

function selectCategory(category) {
    var subcategoriesDiv = document.getElementById("subcategoryButtons");
    subcategoriesDiv.innerHTML = ""; // Vide le contenu précédent

    selectedCategory = category;

    var backButton = document.createElement("button");
    backButton.type = "button";
    backButton.className = "btn btn-warning mb-3";
    backButton.textContent = "Retour";
    backButton.onclick = goBackToCategories;
    subcategoriesDiv.appendChild(backButton);

    if (category === 'Sticker') {
        renderRecentPills(subcategoriesDiv, category, subcategories.filter(s => s.category_name === 'Sticker'));

        // Tri stable par popularité du mois (bucket) : très vendu → vendu → peu → jamais
        const stickerSubcats = sortByMonthPopularityStable(
            subcategories.filter(subcat => subcat.category_name === 'Sticker'),
            'Sticker'
        );

        stickerSubcats.forEach(subcat => {
            var name = subcat.subcategory_name;
            var button = document.createElement("button");
            button.type = "button";
            button.className = "btn btn-outline-warning";
            button.innerHTML = getStickerImg(name, 'tile-sticker-img') + '<span class="tile-label">' + getStickerLabel(name) + '</span>';
            button.onclick = function () { selectStickerCategory(name); };
            attachLongPress(button, selectedCategory, name);
            subcategoriesDiv.appendChild(button);
        });

        // Pas de bouton packs sticker séparé — les packs sont accessibles via chaque sous-catégorie

        subcategoriesDiv.hidden = false;
        document.getElementById("categoryButtons").hidden = true;
        document.getElementById("quantityButtons").hidden = true;
        document.getElementById("priceButton").hidden = true;
        document.getElementById("stickerPacks").hidden = true;
        document.getElementById("stickerSlots").hidden = true;
        window.scrollTo(0, 0);
        return;
    }

    // Filtrer sous-catégories peu utilisées (moins de 5 articles)
    let catSubcategories = subcategories.filter(function(subcat) {
        return subcat.category_name === category;
    });

    // Tri stable par popularité du mois (bucket) : très vendu → vendu → peu → jamais
    catSubcategories = sortByMonthPopularityStable(catSubcategories, category);

    // Pour Bougies : n'afficher que les modèles cibles, triés par prix croissant
    if (category === 'Bougies') {
        catSubcategories = catSubcategories
            .filter(function(subcat) { return BOUGIES_TARGET.has(subcat.subcategory_name); })
            .sort(function(a, b) {
                const pa = parsePrice(a.subcategory_name);
                const pb = parsePrice(b.subcategory_name);
                if (pa !== pb) return pa - pb;
                return a.subcategory_name.localeCompare(b.subcategory_name);
            });
    }

    renderRecentPills(subcategoriesDiv, category, catSubcategories);

    catSubcategories.forEach(function (subcategory) {
        var button = document.createElement("button");
        button.type = "button";
        var priceCls = getPriceGradient(subcategory.subcategory_name);
        button.className = "btn btn-outline-primary" + (priceCls ? " " + priceCls : "");
        // Score affiché pour Bougies : "Nom (Prix€ | Score)"
        var displayName = subcategory.subcategory_name;
        if (category === 'Bougies' && typeof subcategory.stock_score !== 'undefined') {
            displayName = displayName.replace(/\((\d+(?:\.\d+)?)[€$]?\)/, '($1€ | ' + subcategory.stock_score + ')');
        }
        button.innerHTML = `<span class="tile-emoji">${subcategoryEmojis[subcategory.subcategory_name] || '🕯️'}</span><span class="tile-label">${displayName}</span>`;
        button.onclick = function () { selectSubcategory(subcategory.subcategory_name); };
        attachLongPress(button, selectedCategory, subcategory.subcategory_name);
        subcategoriesDiv.appendChild(button);
    });

    // Si aucune sous-categorie → "Autres"
    if (catSubcategories.length === 0) {
        var button = document.createElement("button");
        button.type = "button";
        button.className = "btn btn-secondary";
        button.innerHTML = `<span class="tile-emoji">📦</span><span class="tile-label">Autres</span>`;
        button.onclick = function () { selectSubcategory("Autres"); };
        subcategoriesDiv.appendChild(button);
    }

    subcategoriesDiv.hidden = false;
    document.getElementById("categoryButtons").hidden = true;
    document.getElementById("quantityButtons").hidden = true;
    document.getElementById("priceButton").hidden = true;
    document.getElementById("stickerPacks").hidden = true;
    document.getElementById("stickerSlots").hidden = true;
    window.scrollTo(0, 0);
}

function selectStickerQuantity(itemName, itemPrice) {
    var quantityButtonsDiv = document.getElementById("quantityButtons");
    quantityButtonsDiv.innerHTML = ""; // Vide le contenu précédent

    var quantities = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30]; // Quantités disponibles

    var backButton = document.createElement("button");
    backButton.type = "button";
    backButton.className = "btn btn-warning";
    backButton.textContent = "Retour";
    backButton.onclick = goBackToSubcategories;
    quantityButtonsDiv.appendChild(backButton);

    quantities.forEach(function (quantity) {
        var button = document.createElement("button");
        button.type = "button";
        button.className = "btn btn-info";
        button.textContent = quantity;
        button.addEventListener('click', () => validateStickerItem(itemName, itemPrice, quantity));
        quantityButtonsDiv.appendChild(button);
    });

    quantityButtonsDiv.hidden = false;
    document.getElementById("subcategoryButtons").hidden = true;
}

function validateStickerItem(itemName, itemPrice, quantity) {
    const quantityButtonsDiv = document.getElementById("quantityButtons");
    quantityButtonsDiv.hidden = true;

    var saleId = (typeof sale_id !== 'undefined') ? sale_id : document.getElementById("sale_id").value;

    apiAddArticle({
        sale_id: saleId,
        category: selectedCategory,
        subcategory: itemName,
        quantity: quantity,
        price: itemPrice,
        comment: ''
    }, function(res) {
        // Succès — insérer le HTML
        prependArticleToList({
            article_id: res.article_id,
            sale_id: saleId,
            category: selectedCategory,
            subcategory: itemName,
            quantity: quantity,
            unit_price: itemPrice,
            comment: ''
        });
        updateTotalAmount(res.sale_total);
        flashMessage(itemName + ' × ' + quantity + ' ajouté');
    });

    const saleForm = document.getElementById("saleForm");
    saleForm.hidden = false;
    document.getElementById("addArticleButton").hidden = false;
}

function selectSubcategory(subcategory) {
    var subcategoriesDiv = document.getElementById("subcategoryButtons");
    subcategoriesDiv.hidden = true;
    selectedSubcategory = subcategory;

    // Mémoriser cette sous-catégorie comme récente
    pushRecentSubcat(selectedCategory, subcategory);

    var articleTable = document.getElementById("articlePreview");
    if (articleTable) articleTable.hidden = true;

    document.getElementById("priceButton").hidden = true;
    document.getElementById("stickerPacks").hidden = true;
    document.getElementById("stickerSlots").hidden = true;

    var quantityButtonsDiv = document.getElementById("quantityButtons");
    quantityButtonsDiv.innerHTML = ""; // Vide le contenu précédent

    var backButton = document.createElement("button");
    backButton.type = "button";
    backButton.className = "btn btn-warning";
    backButton.textContent = "Retour";
    backButton.onclick = goBackToSubcategories;
    quantityButtonsDiv.appendChild(backButton);


    for (var i = 1; i <= 10; i++) {
        var button = document.createElement("button");
        button.type = "button";
        button.className = "btn btn-info";
        button.textContent = i;
        button.onclick = function () { selectQuantity(this.textContent, document.getElementById("articleForm")); };
        quantityButtonsDiv.appendChild(button);
    }
    quantityButtonsDiv.hidden = false;
    window.scrollTo(0, 0);
}

function selectQuantity(quantity, form) {
    const priceButtonsDiv = document.getElementById("priceButton");
    const priceButtonContainer = document.getElementById("priceButtonContainer");
    priceButtonContainer.innerHTML = "";

    const quantityInput = document.getElementById("quantity");
    quantityInput.value = quantity;

    // ── Retour en haut ──
    const backBtn = document.createElement("button");
    backBtn.type = "button";
    backBtn.className = "btn btn-warning numpad-back";
    backBtn.textContent = "← Retour";
    backBtn.onclick = goBackToQuantities;
    priceButtonContainer.appendChild(backBtn);

    // ── Affichage prix ──
    const display = document.createElement("div");
    display.id = "numpadDisplay";
    display.className = "numpad-display";
    display.textContent = "0,00 €";
    priceButtonContainer.appendChild(display);

    // ── Grille 4×4 ──
    const numpad = document.createElement("div");
    numpad.className = "numpad-grid";

    const keyDefs = [
        {k:'7', act:'7'}, {k:'8', act:'8'}, {k:'9', act:'9'}, {k:'←', act:'backspace'},
        {k:'4', act:'4'}, {k:'5', act:'5'}, {k:'6', act:'6'}, {k:'C', act:'clear'},
        {k:'1', act:'1'}, {k:'2', act:'2'}, {k:'3', act:'3'}, {k:'.', act:'dot'},
        {k:'0', act:'0', cls:'numpad-zero'}, {k:'OK', act:'ok', cls:'numpad-ok'}
    ];

    keyDefs.forEach(function(def) {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "numpad-btn" + (def.cls ? " " + def.cls : "");
        btn.textContent = def.k;
        btn.addEventListener('click', function() { numpadHandle(def.act); });
        numpad.appendChild(btn);
    });

    priceButtonContainer.appendChild(numpad);

    // ── Commentaire ──
    const commentWrap = document.createElement("div");
    commentWrap.className = "numpad-comment-wrap";
    commentWrap.innerHTML = '<label for="numpadComment" class="numpad-comment-label">Note :</label>' +
        '<input type="text" id="numpadComment" class="numpad-comment-input" placeholder="Neuf, promo, état…" maxlength="80">';
    priceButtonContainer.appendChild(commentWrap);

    priceButtonsDiv.hidden = false;
    document.getElementById("quantityButtons").hidden = true;
    window.scrollTo(0, 0);

    window._numpadValue = '';
    updateNumpadDisplay();
}

/* ── Logique numpad ── */
function numpadHandle(action) {
    let val = window._numpadValue || '';

    if (action === 'clear') {
        val = '';
    } else if (action === 'backspace') {
        val = val.slice(0, -1);
    } else if (action === 'ok') {
        const price = parseFloat(val.replace(',', '.') || '0');
        if (price > 0) selectPrice(price);
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
    const disp = document.getElementById("numpadDisplay");
    if (!disp) return;
    const val = window._numpadValue || '';
    const num = parseFloat(val || '0');
    disp.textContent = num.toFixed(2).replace('.', ',') + " €";
    disp.classList.remove('pulse');
    void disp.offsetWidth;
    disp.classList.add('pulse');
}

function setPrice() {
    var price = document.getElementById("price").value;
    selectPrice(price);
}

function selectPrice(price) {
    const priceButton = document.getElementById("priceButton");
    priceButton.hidden = true;

    // Anti-double-clic : retourner si déjà en cours de validation
    if (window._isSubmittingPrice) return;
    window._isSubmittingPrice = true;

    var saleId = (typeof sale_id !== 'undefined') ? sale_id : document.getElementById("sale_id").value;
    var qty = document.getElementById("quantity").value;

    apiAddArticle({
        sale_id: saleId,
        category: selectedCategory,
        subcategory: selectedSubcategory,
        quantity: qty,
        price: price,
        comment: (document.getElementById("numpadComment")?.value || '')
    }, function(res) {
        window._isSubmittingPrice = false;
        prependArticleToList({
            article_id: res.article_id,
            sale_id: saleId,
            category: selectedCategory,
            subcategory: selectedSubcategory,
            quantity: qty,
            unit_price: price,
            comment: (document.getElementById("numpadComment")?.value || '')
        });
        updateTotalAmount(res.sale_total);
        flashMessage(selectedSubcategory + ' × ' + qty + ' ajouté');
    }, function(err) {
        window._isSubmittingPrice = false;
        console.error('Erreur ajout article', err);
        alert('Erreur lors de l\'ajout : ' + err);
    });

    const saleForm = document.getElementById("saleForm");
    saleForm.hidden = false;
    document.getElementById("addArticleButton").hidden = false;
}

function sendArticleData(category, subcategory, quantity, price, comment) {
    var saleId = (typeof sale_id !== 'undefined') ? sale_id : document.getElementById("sale_id").value;
    apiAddArticle({
        sale_id: saleId,
        category: category,
        subcategory: subcategory,
        quantity: quantity,
        price: price,
        comment: comment
    }, function(res) {
        prependArticleToList({
            article_id: res.article_id,
            sale_id: saleId,
            category: category,
            subcategory: subcategory,
            quantity: quantity,
            unit_price: price,
            comment: comment
        });
        updateTotalAmount(res.sale_total);
        flashMessage(subcategory + ' × ' + quantity + ' ajouté');
    });
}

function deleteArticle(articleId, saleId) {
    if (confirm("Êtes-vous sûr de vouloir supprimer cet article ?")) {
        apiDeleteArticle(saleId, articleId, function(res) {
            var row = document.getElementById('row' + articleId);
            if (row) row.remove();
            updateTotalAmount(res.sale_total);
            flashMessage('Article supprimé');
        });
    }
}



// ------------------------ GO BACK ------------------------ //

function goBackToAddArticle() {
    document.getElementById("categoryButtons").hidden = true;
    document.getElementById("subcategoryButtons").hidden = true;
    document.getElementById("quantityButtons").hidden = true;
    document.getElementById("priceButton").hidden = true;
    document.getElementById("stickerPacks").hidden = true;
    document.getElementById("stickerSlots").hidden = true;
    document.getElementById("addArticleButton").hidden = false;
    document.getElementById("saleForm").hidden = false;
    window.scrollTo(0, 0);
}

function goBackToCategories() {
    document.getElementById("subcategoryButtons").hidden = true;
    document.getElementById("quantityButtons").hidden = true;
    document.getElementById("priceButton").hidden = true;
    document.getElementById("stickerPacks").hidden = true;
    document.getElementById("stickerSlots").hidden = true;
    document.getElementById("categoryButtons").hidden = false;
    window.scrollTo(0, 0);
}

function goBackToSubcategories() {
    document.getElementById("quantityButtons").hidden = true;
    document.getElementById("priceButton").hidden = true;
    document.getElementById("stickerPacks").hidden = true;
    document.getElementById("stickerSlots").hidden = true;
    document.getElementById("subcategoryButtons").hidden = false;
    window.scrollTo(0, 0);
}

function goBackToQuantities() {
    document.getElementById("priceButton").hidden = true;
    document.getElementById("stickerPacks").hidden = true;
    document.getElementById("stickerSlots").hidden = true;
    document.getElementById("quantityButtons").hidden = false;
    window.scrollTo(0, 0);
}



// Ouvrir le pop-up
function openPopup(articleId, currentQuantity, currentPrice) {
    document.getElementById('popupOverlay').style.display = 'block';
    document.getElementById('popup').style.display = 'block';
    document.getElementById('articleId').value = articleId;
    document.getElementById('quantity').value = currentQuantity;
    document.getElementById('unit_price').value = currentPrice;
}

// Fermer le pop-up
function closePopup() {
    document.getElementById('popupOverlay').style.display = 'none';
    document.getElementById('popup').style.display = 'none';
}

//---- STICKER PACKS
function selectStickerCategory(subcategoryName) {
    // Hardcoded prices as before
    const stickerPrices = {
        'Jonte': 2,
        'Tarn': 2, // Prix unitaire (ancien 2€)
        'Fantaisie': 0.5,
        'Autres': 0.5,
        'Jonte new': 2,
        'Anciens': 2
    };
    const unitPrice = stickerPrices[subcategoryName] || 0.5;
    selectedCategory = 'Sticker';  // ← définir la catégorie
    selectedSubcategory = subcategoryName;
    document.getElementById("subcategoryButtons").hidden = true;
    const stickerPacksDiv = document.getElementById("stickerPacks");
    stickerPacksDiv.innerHTML = ''; // vider

    // Bouton Retour
    var backButton = document.createElement("button");
    backButton.type = "button";
    backButton.className = "btn btn-warning mb-3";
    backButton.textContent = "Retour";
    backButton.onclick = goBackToStickerPacks;
    stickerPacksDiv.appendChild(backButton);

    // Bouton "Unitaires (prix unitaire)"
    var unitButton = document.createElement("button");
    unitButton.type = "button";
    unitButton.className = "btn btn-info";
    unitButton.innerHTML = getStickerImg(subcategoryName, 'sticker-pack-img') + `<span class="tile-label">Unitaires (${unitPrice}€/pc)</span>`;
    unitButton.onclick = function() {
        selectStickerQuantity(subcategoryName, unitPrice);
        document.getElementById("stickerPacks").hidden = true;
        document.getElementById("quantityButtons").hidden = false;
    };
    stickerPacksDiv.appendChild(unitButton);

    // PACKS selon sous-catégorie
    if (subcategoryName === 'Fantaisie') {
        // Fantaisie : 10 pc = 4€, 20 pc = 6€
        var pack10Button = document.createElement("button");
        pack10Button.type = "button";
 pack10Button.className = "btn btn-success";
        pack10Button.innerHTML = `<span class="tile-emoji">📦</span><span class="tile-label">Pack 10 (4€)</span>`;
        pack10Button.onclick = function() { selectStickerPack('Sticker', 'Fantaisie', 10, 4); };
        stickerPacksDiv.appendChild(pack10Button);

        var pack20Button = document.createElement("button");
        pack20Button.type = "button";
        pack20Button.className = "btn btn-success";
        pack20Button.innerHTML = `<span class="tile-emoji">📦</span><span class="tile-label">Pack 20 (6€)</span>`;
        pack20Button.onclick = function() { selectStickerPack('Sticker', 'Fantaisie', 20, 6); };
        stickerPacksDiv.appendChild(pack20Button);
    } else if (['Tarn', 'Jonte', 'Jonte new'].includes(subcategoryName)) {
        // Tarn/Jonte/Jonte new : 2 pc = 3€, 4 pc = 4€
        var pack2Button = document.createElement("button");
        pack2Button.type = "button";
        pack2Button.className = "btn btn-success";
        pack2Button.innerHTML = getStickerImg(subcategoryName, 'sticker-pack-img') + `<span class="tile-label">Pack 2 (3€)</span>`;
        pack2Button.onclick = function() { selectStickerPack('Sticker', subcategoryName, 2, 3); };
        stickerPacksDiv.appendChild(pack2Button);

        var pack4Button = document.createElement("button");
        pack4Button.type = "button";
     pack4Button.className = "btn btn-success";
        pack4Button.innerHTML = getStickerImg(subcategoryName, 'sticker-pack-img') + `<span class="tile-label">Pack 4 (4€)</span>`;
        pack4Button.onclick = function() { selectStickerPack('Sticker', subcategoryName, 4, 4); };
        stickerPacksDiv.appendChild(pack4Button);

        // Bouton Pack Variété
        var mixed2Button = document.createElement("button");
        mixed2Button.type = "button";
        mixed2Button.className = "btn btn-info";
        mixed2Button.innerHTML = `<span class="tile-emoji">🎨</span><span class="tile-label">Panaché 2 (3€)</span>`;
        mixed2Button.onclick = function() { openMixedPack(2, 3); };
        stickerPacksDiv.appendChild(mixed2Button);

        var mixed3Button = document.createElement("button");
mixed3Button.type = "button";
        mixed3Button.className = "btn btn-info";
        mixed3Button.innerHTML = `<span class="tile-emoji">🎨</span><span class="tile-label">Panaché 3 (4€)</span>`;
        mixed3Button.onclick = function() { openMixedPack(3, 4); };
        stickerPacksDiv.appendChild(mixed3Button);

        var mixed4Button = document.createElement("button");
        mixed4Button.type = "button";
        mixed4Button.className = "btn btn-info";
        mixed4Button.innerHTML = `<span class="tile-emoji">🎨</span><span class="tile-label">Panaché 4 (5€)</span>`;
        mixed4Button.onclick = function() { openMixedPack(4, 5); };
        stickerPacksDiv.appendChild(mixed4Button);
    }

    stickerPacksDiv.hidden = false;
}

function selectStickerPack(category, subcategory, quantity, packPrice) {
    selectedCategory = category;
    selectedSubcategory = subcategory;

    var saleId = (typeof sale_id !== 'undefined') ? sale_id : document.getElementById("sale_id").value;
    var unitPrice = parseFloat((packPrice / quantity).toFixed(2));

    apiAddArticle({
        sale_id: saleId,
        category: category,
        subcategory: subcategory,
        quantity: quantity,
        price: unitPrice.toFixed(2),
        comment: ''
    }, function(res) {
        prependArticleToList({
            article_id: res.article_id,
            sale_id: saleId,
            category: category,
            subcategory: subcategory,
            quantity: quantity,
            unit_price: unitPrice.toFixed(2),
            comment: ''
        });
        updateTotalAmount(res.sale_total);
        flashMessage(subcategory + ' pack × ' + quantity + ' ajouté');
        document.getElementById("stickerPacks").hidden = true;
        document.getElementById("addArticleButton").hidden = false;
        document.getElementById("articleTable").hidden = false;
        document.getElementById("saleForm").hidden = false;
    });
}

function goBackToStickerPacks() {
    document.getElementById("stickerPacks").hidden = true;
    document.getElementById("subcategoryButtons").hidden = false;
}

// ========================== PACK VARIÉTÉ (panaché) ==========================
// Variables globales pour le pack panaché
let mixedPackSlots = [];
let mixedPackSize = 0;
let mixedPackPrice = 0;
let mixedPackCounts = { 'Tarn': 0, 'Jonte': 0, 'Jonte new': 0 };

function openMixedPack(size, price) {
    mixedPackSize = size;
    mixedPackPrice = price;
    mixedPackSlots = [];
    mixedPackCounts = { 'Tarn': 0, 'Jonte': 0, 'Jonte new': 0 };
    selectedCategory = 'Sticker';

    // Masquer stickerPacks, afficher stickerSlots
    document.getElementById("stickerPacks").hidden = true;
    document.getElementById("stickerSlots").hidden = false;

    // Mettre à jour le statut
    updateMixedPackStatus();

    // Afficher les boutons de choix de sticker
    var choicesDiv = document.getElementById("stickerSlotsChoices");
    choicesDiv.innerHTML = '';

    var stickers = ['Tarn', 'Jonte', 'Jonte new'];
    for (var i = 0; i < stickers.length; i++) {
        var btn = document.createElement("button");
        btn.type = "button";
        btn.className = "btn btn-secondary";
        btn.style.fontSize = "1.1rem";
        btn.style.padding = "0.6rem 1.2rem";
        btn.innerHTML = getStickerImg(stickers[i], 'sticker-choice-img') + ' <span style="font-size:0.9rem;color:#aaa" id="count_' + stickers[i] + '"\u003e(0)</span>';
        btn.id = 'mixedBtn_' + stickers[i];
        btn.onclick = (function(s) {
            return function() { pushStickerSlot(s); };
        })(stickers[i]);
        choicesDiv.appendChild(btn);
    }

    // Afficher les emplacements vides
    renderMixedPackSlots();
}

function updateMixedPackStatus() {
    var status = document.getElementById("stickerSlotsStatus");
    status.textContent = 'Sélectionne ' + mixedPackSize + ' stickers'
        + ' — ' + mixedPackSlots.length + '/' + mixedPackSize + ' choisis';
}

function renderMixedPackSlots() {
    var slotsDiv = document.getElementById("stickerSlotsButtons");
    slotsDiv.innerHTML = '';

    for (var i = 0; i < mixedPackSize; i++) {
        var slot = document.createElement("div");
        slot.className = "sticker-slot";

        if (i < mixedPackSlots.length) {
            slot.classList.add("filled");
            slot.innerHTML = getStickerImg(mixedPackSlots[i], 'sticker-choice-img');
            slot.onclick = (function(idx) {
                return function() { popStickerSlot(idx); };
            })(i);
        } else {
            slot.textContent = '?';
     }

        slotsDiv.appendChild(slot);
    }

    // Si tout est rempli, bouton Valider
    if (mixedPackSlots.length === mixedPackSize) {
        var validateBtn = document.createElement("button");
        validateBtn.type = "button";
        validateBtn.className = "btn btn-success";
     validateBtn.style.width = "100%";
        validateBtn.style.marginTop = "0.75rem";
        validateBtn.style.padding = "0.75rem";
        validateBtn.style.fontSize = "1.1rem";
        validateBtn.textContent = '✅ Valider le pack (' + mixedPackPrice.toFixed(2) + '€)';
        validateBtn.onclick = submitMixedPack;
  slotsDiv.appendChild(validateBtn);
    }

    updateMixedPackStatus();
}

function pushStickerSlot(stickerName) {
    if (mixedPackSlots.length >= mixedPackSize) {
        return; // déjà complet
    }
    mixedPackSlots.push(stickerName);
    updateMixedCounts();
    renderMixedPackSlots();
}

function popStickerSlot(index) {
    if (index >= 0 && index < mixedPackSlots.length) {
        mixedPackSlots.splice(index, 1);
        updateMixedCounts();
        renderMixedPackSlots();
    }
}

function updateMixedCounts() {
    // Compter combien de chaque sticker a été sélectionné
    var counts = { 'Tarn': 0, 'Jonte': 0, 'Jonte new': 0 };
    for (var i = 0; i < mixedPackSlots.length; i++) {
        if (counts.hasOwnProperty(mixedPackSlots[i])) {
            counts[mixedPackSlots[i]]++;
        }
    }
    mixedPackCounts = counts;

    // Mettre à jour l'affichage des compteurs dans les boutons
    var stickers = ['Tarn', 'Jonte', 'Jonte new'];
    for (var i = 0; i < stickers.length; i++) {
        var countSpan = document.getElementById('count_' + stickers[i]);
        if (countSpan) {
            countSpan.textContent = '(' + counts[stickers[i]] + ')';
        }
    }
}

function submitMixedPack() {
    if (mixedPackSlots.length !== mixedPackSize) {
        return;
    }

    var validateBtn = document.querySelector('#stickerSlotsButtons button.btn-success');
    if (validateBtn) {
        validateBtn.disabled = true;
        validateBtn.textContent = 'Envoi en cours…';
    }

    var saleId = (typeof sale_id !== 'undefined') ? sale_id : document.getElementById("sale_id").value;
    var unitPriceBase = parseFloat((mixedPackPrice / mixedPackSize).toFixed(2));
    var completed = 0;
    var errors = [];
    var totalAdded = 0;

    for (var i = 0; i < mixedPackSlots.length; i++) {
        var itemPrice = (i === mixedPackSlots.length - 1)
            ? (mixedPackPrice - (mixedPackSlots.length - 1) * unitPriceBase).toFixed(2)
            : unitPriceBase.toFixed(2);

        (function(idx, itemPriceLocal) {
            apiAddArticle({
                sale_id: saleId,
                category: 'Sticker',
                subcategory: mixedPackSlots[idx],
                quantity: 1,
                price: itemPriceLocal,
                comment: ''
            }, function(res) {
                completed++;
                totalAdded += parseFloat(res.line_total);
                prependArticleToList({
                    article_id: res.article_id,
                    sale_id: saleId,
                    category: 'Sticker',
                    subcategory: mixedPackSlots[idx],
                    quantity: 1,
                    unit_price: itemPriceLocal,
                    comment: ''
                });
                if (completed === mixedPackSlots.length) {
                    updateTotalAmount(res.sale_total);
                    flashMessage('Pack panaché ' + mixedPackSize + ' ajouté');
                    document.getElementById("stickerSlots").hidden = true;
                    document.getElementById("addArticleButton").hidden = false;
                    document.getElementById("articleTable").hidden = false;
                    document.getElementById("saleForm").hidden = false;
                }
            }, function(err) {
                completed++;
                errors.push(err);
                if (completed === mixedPackSlots.length) {
                    alert('Certaines requêtes ont échoué. Rechargez la page pour vérifier : ' + errors.join(', '));
                }
            });
        })(i, itemPrice);
    }
}

function cancelMixedPack() {
    mixedPackSlots = [];
    mixedPackSize = 0;
    mixedPackPrice = 0;
    document.getElementById("stickerSlots").hidden = true;
    document.getElementById("stickerPacks").hidden = false;
}

// ====== UPDATE SALE METADATA (payment, date, client) ======
var _saleSavePromise = null;

function _navigateWhenIdle(url) {
    if (_saleSavePromise) {
        _saleSavePromise.then(function() { window.location.href = url; })
                       .catch(function() { window.location.href = url; });
    } else {
        window.location.href = url;
    }
}

function navigateSale(saleId) {
    _navigateWhenIdle('index.php?action=newSaleForm&sale_id=' + encodeURIComponent(saleId));
}

function newSaleFormNav() {
    var dateInput = document.getElementById('selectedDate') || document.getElementById('sale_date');
    var selectedDate = dateInput ? dateInput.value : '';
    var url = 'index.php?action=newSaleForm';
    if (selectedDate) {
        url += '&selectedDate=' + encodeURIComponent(selectedDate);
    }
    _navigateWhenIdle(url);
}

function setPayment(method) {
    // Mise à jour visuelle (sans attendre l'API)
    document.querySelectorAll('.payment-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    var targetBtn = document.querySelector('.payment-btn[data-method="' + method + '"]');
    if (targetBtn) targetBtn.classList.add('active');
    document.getElementById('payment_method').value = method;

    var saleId = (typeof sale_id !== 'undefined') ? sale_id : document.getElementById("sale_id").value;

    // NOUVELLE VENTE -> on ne submit pas automatiquement
    if (!saleId || saleId === 'add') {
        return;
    }

    // VENTE EXISTANTE -> AJAX silencieux
    var fd = new FormData();
    fd.append('sale_id', saleId);
    fd.append('payment_method', method);

    _saleSavePromise = fetch('index.php?action=apiUpdateSale', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    }).then(function(r) {
        if (r.redirected) {
            throw new Error('Session expirée — rechargement...');
        }
        if (!r.ok) {
            throw new Error('Erreur HTTP ' + r.status);
        }
        return r.text().then(function(txt) {
            try { return JSON.parse(txt); }
            catch(e) { throw new Error('Réponse non-JSON: ' + txt.substring(0,200)); }
        });
    })
    .then(function(json) {
        if (json.success) {
            flashMessage('Paiement mis à jour');
        } else {
            flashMessage((json.error || 'Échec'));
        }
    }).catch(function(err) {
        console.error('setPayment error', err);
        flashMessage('⚠️ ' + err.message);
    }).finally(function() {
        _saleSavePromise = null;
    });
}

function updateSaleDate(date) {
    var saleId = (typeof sale_id !== 'undefined') ? sale_id : document.getElementById("sale_id").value;
    if (!saleId || saleId === 'add') return;

    var fd = new FormData();
    fd.append('sale_id', saleId);
    fd.append('sale_date', date);

    fetch('index.php?action=apiUpdateSale', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    }).then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) {
            flashMessage('Date mise à jour');
        }
    }).catch(function(err) {
        console.error('updateSaleDate error', err);
    });
}

function updateSaleClient(clientId) {
    var saleId = (typeof sale_id !== 'undefined') ? sale_id : document.getElementById("sale_id").value;
    if (!saleId || saleId === 'add') return;

    var fd = new FormData();
    fd.append('sale_id', saleId);
    fd.append('client_id', clientId);

    fetch('index.php?action=apiUpdateSale', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    }).then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) {
            flashMessage('Client mis à jour');
        }
    }).catch(function(err) {
        console.error('updateSaleClient error', err);
    });
}

// Soumettre le formulaire de mise a jour
function submitUpdateForm() {
    var form = document.getElementById('updateForm');
    var fd = new FormData(form);
    fetch('index.php?action=apiUpdateArticle', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) {
            var row = document.getElementById('row' + json.article_id);
            if (row) {
                row.querySelector('[data-field="quantity"] .value').textContent = json.quantity;
                row.querySelector('input[name="quantity"]').value = json.quantity;
                row.querySelector('[data-field="unit_price"] .value').textContent = parseFloat(json.unit_price).toFixed(2);
                row.querySelector('input[name="unit_price"]').value = parseFloat(json.unit_price).toFixed(2);
                row.querySelector('[data-field="comment"] span').textContent = json.comment || '';
                row.querySelector('[data-field="comment"] input').value = json.comment || '';
                row.querySelector('.total strong').textContent = json.line_total.toFixed(2) + '€';
            }
            updateTotalAmount(json.sale_total);
            closePopup();
            flashMessage('Article modifié');
        } else {
            alert('Erreur : ' + json.error);
        }
    })
    .catch(function(err) {
        console.error(err);
        alert('Erreur réseau');
    });
}

// ========================== SAISIE RAPIDE (long-press) ==========================
let longPressTimer = null;
let longPressTarget = null;
const LONG_PRESS_MS = 600;

function attachLongPress(button, categoryName, subcategoryName) {
    function start(e) {
        if (e.button === 2) return; // ignore clic droit
        longPressTarget = { category: categoryName, subcategory: subcategoryName };
        longPressTimer = setTimeout(() => {
            longPressTimer = null;
            openFastEntry(categoryName, subcategoryName);
        }, LONG_PRESS_MS);
    }
    function cancel() {
        if (longPressTimer) { clearTimeout(longPressTimer); longPressTimer = null; }
        longPressTarget = null;
    }
    button.addEventListener('mousedown', start);
    button.addEventListener('touchstart', start, { passive: true });
    button.addEventListener('mouseup', cancel);
    button.addEventListener('mouseleave', cancel);
    button.addEventListener('touchend', cancel);
    button.addEventListener('touchmove', cancel);
    // Prevent context menu on mobile
    button.addEventListener('contextmenu', e => e.preventDefault());
}

function openFastEntry(category, subcategory) {
    document.getElementById('fastCategory').value = category;
    document.getElementById('fastSubcategory').value = subcategory;
    document.getElementById('fastEntryTitle').textContent = subcategory + ' — ' + category;
    document.getElementById('fastQuantity').value = '1';
    document.getElementById('fastPrice').value = '';
    document.getElementById('fastEntryPopup').style.display = 'block';
    document.getElementById('fastEntryOverlay').style.display = 'block';
    document.getElementById('fastPrice').focus();
}

function closeFastEntry() {
    document.getElementById('fastEntryPopup').style.display = 'none';
    document.getElementById('fastEntryOverlay').style.display = 'none';
}

function submitFastEntry() {
    const qty = parseFloat(document.getElementById('fastQuantity').value);
    const priceRaw = document.getElementById('fastPrice').value.replace(',', '.');
    const price = parseFloat(priceRaw);
    const category = document.getElementById('fastCategory').value;
    const subcategory = document.getElementById('fastSubcategory').value;

    if (isNaN(price) || price <= 0) {
        document.getElementById('fastPrice').style.borderColor = '#dc3545';
        document.getElementById('fastPrice').focus();
        return;
    }
    if (isNaN(qty) || qty < 1) {
        document.getElementById('fastQuantity').style.borderColor = '#dc3545';
        document.getElementById('fastQuantity').focus();
        return;
    }

    document.getElementById('fastPrice').style.borderColor = '#ddd';
    document.getElementById('fastQuantity').style.borderColor = '#ddd';

    var saleId = (typeof sale_id !== 'undefined') ? sale_id : document.getElementById("sale_id").value;

    apiAddArticle({
        sale_id: saleId,
        category: category,
        subcategory: subcategory,
        quantity: qty,
        price: price,
        comment: ''
    }, function(res) {
        prependArticleToList({
            article_id: res.article_id,
            sale_id: saleId,
            category: category,
            subcategory: subcategory,
            quantity: qty,
            unit_price: price,
            comment: ''
        });
        updateTotalAmount(res.sale_total);
        closeFastEntry();
        flashMessage(subcategory + ' × ' + qty + ' ajouté');
    });
}

// Fermer le pop-up lorsque vous cliquez en dehors de celui-ci
window.onclick = function (event) {
    const popup = document.getElementById('popup');
    if (event.target == document.getElementById('popupOverlay')) {
        closePopup();
    }
}
// champs de modigfications

document.addEventListener('DOMContentLoaded', function () {
    let activeInput = null;
    let activeForm = null;

    document.querySelectorAll('.editable').forEach(cell => {
        cell.addEventListener('click', function (e) {
            e.stopPropagation();

            // Fermer l'autre champ si nécessaire
            if (activeInput && activeInput !== this) {
                toggleToSpan(activeInput);
            }

            const span = this.querySelector('span');
            const input = this.querySelector('input');

            if (span && !span.classList.contains('d-none')) {
                span.classList.add('d-none');
                input.classList.remove('d-none');
                input.focus();

                activeInput = this;
                activeForm = this.closest('.receipt-line');
            }
        });
    });

    // Auto-save comment on blur
    document.querySelectorAll('.editable input').forEach(input => {
        input.addEventListener('blur', function() {
            const card = this.closest('.receipt-line');
            const span = this.previousElementSibling;
            if (span && span.classList.contains('comment-display')) {
                span.textContent = this.value || 'Ajouter un commentaire…';
                span.classList.toggle('empty', !this.value);
                span.classList.remove('d-none');
                this.classList.add('d-none');
            }
            autoSaveCard(card);
        });
        input.addEventListener('keydown', function(ev) {
            if (ev.key === 'Enter') this.blur();
        });
    });

    // Auto-save comment-input (receipt-line) on blur/input (delegation for dynamic rows)
    document.addEventListener('focusout', function(e) {
        const input = e.target.closest('.comment-input');
        if (!input) return;
        autoSaveCard(input.closest('.receipt-line'));
    });
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Enter') return;
        const input = e.target.closest('.comment-input');
        if (!input) return;
        input.blur();
    });

    // Cliquer ailleurs → referme le champ
    document.addEventListener('click', function () {
        if (activeInput) {
            toggleToSpan(activeInput);
            activeInput = null;
        }
    });

    function toggleToSpan(cell) {
        const span = cell.querySelector('span');
        const input = cell.querySelector('input');
        if (span) {
            span.textContent = input.value || 'Ajouter un commentaire…';
            span.classList.toggle('empty', !input.value);
            span.classList.remove('d-none');
        }
        if (input) input.classList.add('d-none');
    }
});

/* ======================== CLIENT POPUP ======================== */
function openClientPopup() {
    document.getElementById('clientPopup').hidden = false;
    document.getElementById('newClientName').focus();
}

function closeClientPopup() {
    document.getElementById('clientPopup').hidden = true;
    document.getElementById('newClientName').value = '';
    document.getElementById('newClientPhone').value = '';
}

function createClient() {
    const name = document.getElementById('newClientName').value.trim();
    const phone = document.getElementById('newClientPhone').value.trim();
    if (!name) {
        document.getElementById('newClientName').style.borderColor = '#dc3545';
        return;
    }
    document.getElementById('newClientName').style.borderColor = '';

    const fd = new FormData();
    fd.append('name', name);
    fd.append('phone', phone);

    fetch('index.php?action=apiCreateClient', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        if (json.success) {
            // Ajouter au select et sélectionner
            const select = document.getElementById('client_id');
            const opt = document.createElement('option');
            opt.value = json.client_id;
            opt.textContent = json.name;
            select.appendChild(opt);
            select.value = json.client_id;
            // Mettre à jour la vente si existante
            updateSaleClient(json.client_id);
            closeClientPopup();
            flashMessage('Client créé : ' + json.name);
        } else {
            alert('Erreur : ' + (json.error || 'Création échouée'));
        }
    })
    .catch(function(err) {
        console.error('createClient error', err);
        alert('Erreur réseau');
    });
}

// Vibe — soumettre le formulaire vente (création ou update silencieux)
function submitSaleForm() {
    var saleId = (typeof sale_id !== 'undefined') ? sale_id : document.getElementById('sale_id').value;
    
    // NOUVELLE VENTE : submit classique
    if (!saleId || saleId === 'add') {
        document.getElementById('saleForm').submit();
        return;
    }
    
    // VENTE EXISTANTE : AJAX silencieux
    var dateVal = document.getElementById('sale_date').value;
    var paymentVal = document.getElementById('payment_method').value;
    var fd = new FormData();
    fd.append('sale_id', saleId);
    fd.append('sale_date', dateVal);
    fd.append('payment_method', paymentVal);
    
    fetch('index.php?action=apiUpdateSale', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
    }).then(function(r) {
        if (r.redirected) {
            throw new Error('Session expirée — rechargement...');
        }
        if (!r.ok) {
            throw new Error('Erreur HTTP ' + r.status);
        }
        return r.text().then(function(txt) {
            try { return JSON.parse(txt); }
            catch(e) { throw new Error('Réponse non-JSON: ' + txt.substring(0,200)); }
        });
    })
    .then(function(json) {
        if (json.success) {
            flashMessage('Vente enregistrée');
        } else {
            flashMessage('Erreur : ' + (json.error || 'Échec'));
        }
    }).catch(function(err) {
        console.error('submitSaleForm error', err);
        flashMessage('⚠️ ' + err.message);
    });
}
