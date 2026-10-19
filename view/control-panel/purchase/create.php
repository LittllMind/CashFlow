<?php $title = 'Nouvel achat'; ?>

<?php ob_start(); ?>

<?php
$paymentLabels = ['cash' => 'Espèces', 'card' => 'Carte', 'check' => 'Chèque'];
$purchase_date = date('Y-m-d');
$paid_amount = 0.00;
$payment_method = '';
$comment = '';
?>

<div class="purchase-page">

<div class="sale-head">
    <?php if (isset($message)): ?>
        <h6 class="alert-message"><?= $message ?></h6>
    <?php endif; ?>
</div>

<div class="purchase-form-wrapper">
    <form id="purchaseForm" action="index.php?action=addPurchase" method="post" class="purchase-form purchase-create-form">

        <div class="purchase-info-row">
            <div class="purchase-info-tile purchase-tile-date">
                <span class="tile-icon">📅</span>
                <input type="date" id="purchase_date" name="purchase_date" value="<?= $purchase_date ?>" required>
            </div>

            <div class="purchase-info-tile purchase-tile-provider">
                <span class="tile-icon">🏭</span>
                <div class="purchase-provider-wrap">
                    <span class="tile-label">Fournisseur / Source</span>
                    <select name="provider_id" id="provider_id">
                        <option value="" selected>Particulier / Don / Vide-grenier</option>
                        <?php foreach ($providers as $provider): ?>
                            <option value="<?= $provider['id'] ?>">
                                <?= htmlspecialchars($provider['name']) ?> <?= ($provider['city'] ? '— ' . htmlspecialchars($provider['city']) : '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn-add-provider" onclick="openAddProviderPopup()">
                        ➕ Nouveau
                    </button>
                </div>
            </div>
        </div>

        <div class="purchase-info-row">
            <div class="purchase-info-tile purchase-tile-paid">
                <span class="tile-icon">💰</span>
                <span class="tile-label">Montant payé</span>
                <input type="number" step="0.01" min="0" id="paid_amount" name="paid_amount" value="<?= number_format($paid_amount, 2, '.', '') ?>"
                       class="purchase-paid-input" placeholder="0.00" required>
            </div>

            <div class="purchase-info-tile purchase-tile-payment">
                <span class="tile-label">Paiement</span>
                <div class="payment-buttons" id="paymentButtons">
                    <button type="button" class="payment-btn" data-method="cash" onclick="setPayment('cash')">
                        <span class="pay-icon">💵</span><span class="pay-label">Espèces</span>
                    </button>
                    <button type="button" class="payment-btn" data-method="card" onclick="setPayment('card')">
                        <span class="pay-icon">💳</span><span class="pay-label">Carte</span>
                    </button>
                    <button type="button" class="payment-btn" data-method="check" onclick="setPayment('check')">
                        <span class="pay-icon">🏦</span><span class="pay-label">Chèque</span>
                    </button>
                </div>
                <input type="hidden" id="payment_method" name="payment_method" value="">
            </div>

            <div class="purchase-info-tile purchase-tile-comment">
                <span class="tile-icon">📝</span>
                <span class="tile-label">Commentaire</span>
                <input type="text" id="comment" name="comment" value="<?= htmlspecialchars($comment) ?>" class="purchase-comment-input"
                       placeholder="Note...">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Créer l'achat &amp; ajouter des articles</button>
        </div>
    </form>
</div>

<!-- Modal ajout fournisseur -->
<div class="quick-provider-overlay" id="quickProviderOverlay" style="display:none;"></div>
<div class="quick-provider-popup" id="quickProviderPopup" style="display:none;">
    <h3>Nouveau fournisseur / source</h3>
    <input type="text" id="newProviderName" placeholder="Nom (prénom, nom, association...)" maxlength="100">
    <input type="text" id="newProviderCity" placeholder="Ville (facultatif)" maxlength="100">
    <input type="email" id="newProviderEmail" placeholder="Email (facultatif)" maxlength="100">
    <input type="tel" id="newProviderPhone" placeholder="Téléphone (facultatif)" maxlength="20">
    <div class="popup-actions">
        <button type="button" class="btn-secondary" onclick="closeAddProviderPopup()">Annuler</button>
        <button type="button" class="btn-submit" onclick="submitAddProvider()">Ajouter</button>
    </div>
</div>


<script>
function setPayment(method) {
    document.querySelectorAll('.payment-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    var targetBtn = document.querySelector('.payment-btn[data-method="' + method + '"]');
    if (targetBtn) targetBtn.classList.add('active');
    var input = document.getElementById('payment_method');
    if (input) input.value = method;
}

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
</script>

</div>

<?php $content = ob_get_clean(); ?>

<?php require('view/template/mainTemplate.php'); ?>
