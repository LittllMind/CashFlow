<?php
/** CbBalancePanel.php — Panneau Balance CB FRIPE */
ob_start();
?>

<section class="cb-panel">

    <header class="cb-header">
        <h2><?= htmlspecialchars($title) ?></h2>
        <p>Dernier virement : <span id="last-d">—</span></p>
    </header>

    <div class="cb-table-card">
        <h3 style="margin:1rem 1rem 0.5rem; font-size:1rem;">📊 Répartition dépôts caisse</h3>
        <table class="cb-table">
            <thead>
                <tr>
                    <th>Pôle</th>
                    <th class="cb-amount">Espèces</th>
                    <th class="cb-amount">Chèque</th>
                    <th class="cb-amount">CB</th>
                    <th class="cb-amount">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="cb-badge cb-badge-fripe">FRIPE</span></td>
                    <td class="cb-amount" id="td-fripe-cash">—</td>
                    <td class="cb-amount" id="td-fripe-check">—</td>
                    <td class="cb-amount" id="td-fripe-card">—</td>
                    <td class="cb-amount" id="td-fripe-total">—</td>
                </tr>
                <tr>
                    <td><span class="cb-badge cb-badge-broc">BROC</span></td>
                    <td class="cb-amount" id="td-broc-cash">—</td>
                    <td class="cb-amount" id="td-broc-check">—</td>
                    <td class="cb-amount" id="td-broc-card">—</td>
                    <td class="cb-amount" id="td-broc-total">—</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="cb-total-row">
                    <td>Total jour</td>
                    <td class="cb-amount" id="td-total-cash">—</td>
                    <td class="cb-amount" id="td-total-check">—</td>
                    <td class="cb-amount" id="td-total-card">—</td>
                    <td class="cb-amount" id="td-total-all">—</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="cb-card">
        <h3>💳 Ventes CB FRIPE à virer</h3>

        <div class="cb-row">
            <span class="cb-label">Montant CB brut</span>
            <span class="cb-value" id="cb-brut">—</span>
        </div>
        <div class="cb-row">
            <span class="cb-label">Frais lecteur (1,46 %)</span>
            <span class="cb-value cb-value-fee" id="cb-fee">—</span>
        </div>
        <hr class="cb-card-sep">
        <div class="cb-row">
            <span class="cb-label">Net à virer à Fripe</span>
            <span class="cb-value cb-value-lg cb-value-net" id="cb-net">—</span>
        </div>

        <div class="cb-detail">
            <div class="cb-detail-row">
                <span class="cb-detail-label">↳ FRIPE pur</span>
                <span id="cb-fripe-pure">—</span>
            </div>
            <div class="cb-detail-row">
                <span class="cb-detail-label">↳ MIX (ventes croisées)</span>
                <span id="cb-mix-fripe">—</span>
            </div>
            <div class="cb-detail-row">
                <span class="cb-detail-label">↳ Ventes croisées</span>
                <span id="cb-mix-count">—</span>
            </div>
        </div>

        <div class="cb-detail cb-cash">
            <div class="cb-detail-row">
                <span class="cb-detail-label">💰 Espèces à déposer Fripe (FRIPE)</span>
                <span id="cb-cash-fripe">—</span>
            </div>
            <div class="cb-detail-row">
                <span class="cb-detail-label">💰 Espèces à déposer Aurélien (BROC)</span>
                <span id="cb-cash-broc">—</span>
            </div>
        </div>

        <button type="button" class="btn-virer" id="btn-virer" title="Cliquer quand le virement est fait">
            💸 J'ai viré
        </button>
    </div>

    <div class="cb-table-card">
        <h3 style="margin:1rem 1rem 0.5rem; font-size:1rem;">📋 Historique virements</h3>
        <table class="cb-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="cb-amount">CB brut</th>
                    <th class="cb-amount">Frais</th>
                    <th class="cb-amount">Net viré</th>
                </tr>
            </thead>
            <tbody id="xfer-tbody">
            </tbody>
            <tfoot>
                <tr class="cb-total-row">
                    <td>Total viré</td>
                    <td class="cb-amount" id="total-xfer-brut">—</td>
                    <td class="cb-amount" id="total-xfer-fee">—</td>
                    <td class="cb-amount cb-value-net" id="total-xfer-net">—</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="cb-note">
        <p>Les ventes CB sont réparties proportionnellement : seule la part FRIPE (vêtements + accessoires) est comptabilisée. Frais CB 1,46 % s'appliquent uniquement sur le montant FRIPE.</p>
    </div>

</section>

<?php
$content = ob_get_clean();
require("view/template/mainTemplate.php");
?>
