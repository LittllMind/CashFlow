<?php
/**
 * Export ticket de vente — ticket thermique compact, mentions discrètes
 * Usage : index.php?action=exportSale&sale_id=123&format=html|text
 */

require_once('model/SalesManager.php');
require_once('model/ArticlesManager.php');
require_once('model/EntityProfile.php');

use \OpenClassroom\Blog\Model\SalesManager;
use \OpenClassroom\Blog\Model\ArticlesManager;

function getSiretsForTicket(array $lines): array
{
    $fripeCats = EntityProfile::fripeCategories();
    $hasFripe = false; $hasBroc = false;

    foreach ($lines as $l) {
        if (in_array($l['category'], $fripeCats, true)) $hasFripe = true;
        else $hasBroc = true;
    }

    $sirets = [];
    if ($hasFripe) {
        $sirets[] = ['nom' => EntityProfile::NOM_FRIPE,     'value' => EntityProfile::SIRET_FRIPE];
    }
    if ($hasBroc) {
        $sirets[] = ['nom' => EntityProfile::NOM_BROC, 'value' => EntityProfile::SIRET_BROC];
    }
    return $sirets;
}

function exportSale($sale_id, $message = '')
{
    $saleManager     = new SalesManager();
    $articlesManager = new ArticlesManager();

    $sale = $saleManager->getSaleById($sale_id);
    if (!$sale) die('Vente introuvable.');

    $articles = $articlesManager->getArticlesBySaleId($sale_id);

    $lines = [];
    $total = 0;
    foreach ($articles as $a) {
        $qty = floatval($a['quantity']);
        $up  = floatval($a['unit_price']);
        $sub = $qty * $up;
        $total += $sub;
        $lines[] = [
            'category'    => $a['category']    ?? '',
            'subcategory' => $a['subcategory'] ?? '',
            'quantity'    => $qty,
            'unit_price'  => $up,
            'comment'     => $a['comment']     ?? '',
            'subtotal'    => $sub,
        ];
    }

    $sirets = getSiretsForTicket($lines);

    $fmtDate  = date('d/m/Y', strtotime($sale['sale_date']));
    $fmtTime  = date('H:i',   strtotime($sale['sale_date']));
    $fmtTotal = number_format($total, 2, ',', ' ');

    $fmtMethod = [
        'cash'  => 'Espèces',
        'card'  => 'Carte',
        'check' => 'Chèque',
    ][$sale['payment_method']] ?? ucfirst($sale['payment_method']);

    $buyer = $_GET['buyer'] ?? '';
    $buyerSafe = htmlspecialchars($buyer);

    $format = $_GET['format'] ?? 'html';

    /* ════════════ TEXTE PLAIN (email / SMS / copier) ════════════ */
    if ($format === 'text') {
        header('Content-Type: text/plain; charset=utf-8');
        $w = 36; $sep = str_repeat('-', $w); $rul = str_repeat('=', $w);

        $out  = $rul . "\n";
        $out .= " " . str_pad(EntityProfile::MAGASIN_NOM, $w - 2, ' ', STR_PAD_BOTH) . "\n";
        $out .= $sep . "\n";
        $out .= " Vente #{$sale_id}   {$fmtDate} {$fmtTime}\n";
        $out .= " Paiement : {$fmtMethod}\n";
        $out .= $sep . "\n";

        foreach ($lines as $l) {
            $label = $l['quantity'] . '× ' . ($l['subcategory'] ?: $l['category']);
            $subStr = number_format($l['subtotal'], 2, ',', ' ');
            $out .= ' ' . str_pad($label, $w - 10, ' ', STR_PAD_RIGHT)
                  . str_pad($subStr, 8, ' ', STR_PAD_LEFT) . "\n";
            if ($l['comment']) {
                $out .= '    (' . $l['comment'] . ")\n";
            }
        }

        $out .= $sep . "\n";
        $out .= ' ' . str_pad('TOTAL', $w - 10, ' ', STR_PAD_RIGHT) . str_pad($fmtTotal, 8, ' ', STR_PAD_LEFT) . "\n";
        $out .= $rul . "\n";

        if ($sirets) {
            foreach ($sirets as $s) {
                $out .= ' ' . $s['nom'] . "\n";
                $out .= '   SIRET : ' . $s['value'] . "\n";
            }
            $out .= ' ' . EntityProfile::MENTION_TVA . "\n";
            $out .= $rul . "\n";
        }
        echo $out;
        return;
    }

    /* ══════════════ HTML — ticket thermique ══════════════ */
    $baseUrl   = 'https://demo.example.com';
    $exportUrl = $baseUrl . '/index.php?action=exportSale&sale_id=' . $sale_id;
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>#<?= htmlspecialchars($sale_id) ?></title>
        <style>
            * { box-sizing:border-box; }
            body { background:#eee; display:flex; justify-content:center; padding:16px; margin:0; font-family: 'Courier New', Courier, monospace; }
            .ticket { background:#fff; width:280px; padding:12px; box-shadow:0 2px 8px rgba(0,0,0,.1); }
            .t-center { text-align:center; }
            .t-right  { text-align:right; }
            .t-small  { font-size:.7rem; color:#555; }
            .t-tiny   { font-size:.62rem; color:#777; line-height:1.4; }
            .sep { border-top:1px dashed #bbb; margin:6px 0; padding-top:4px; }
            .sep-bold { border-top:2px dashed #333; margin:8px 0; }
            .line { display:flex; justify-content:space-between; margin:3px 0; font-size:.8rem; }
            .line-total { font-weight:700; font-size:.95rem; }
            .pay-method { font-size:.7rem; color:#555; margin-top:2px; }
            .legal { margin-top:8px; padding-top:6px; border-top:1px dashed #ccc; }
            .legal-row { display:flex; justify-content:space-between; margin:1px 0; }
            .actions { display:flex; justify-content:center; gap:8px; margin-top:16px; }
            .btn { background:#222; color:#fff; border:none; padding:6px 12px; border-radius:3px; cursor:pointer; font-size:.75rem; text-decoration:none; }
            .btn-print { background:none; color:#222; border:1px solid #222; }
            @media print {
                body { background:#fff; padding:0; }
                .ticket { box-shadow:none; width:72mm; padding:2mm; }
                .actions { display:none !important; }
            }
        </style>
    </head>
    <body>
        <div class="ticket">
            <div class="t-center t-small">
                <strong><?= htmlspecialchars(EntityProfile::MAGASIN_NOM) ?></strong><br>
                <?= htmlspecialchars(EntityProfile::MAGASIN_ADR) ?>
            </div>
            <div class="sep t-center t-tiny">
                #<?= htmlspecialchars($sale_id) ?> — <?= $fmtDate ?> <?= $fmtTime ?>
            </div>
            <?php if ($buyer): ?>
            <div class="t-center t-small" style="margin-top:4px;"><?= $buyerSafe ?></div>
            <?php endif; ?>

            <?php foreach ($lines as $l): ?>
            <div class="line">
                <span><?= htmlspecialchars($l['quantity']) ?>×&nbsp;<?= htmlspecialchars($l['subcategory'] ?: $l['category']) ?></span>
                <span><?= number_format($l['subtotal'], 2, ',', ' ') ?></span>
            </div>
            <?php if ($l['comment']): ?>
            <div class="t-tiny" style="margin-left:14px;">(<?= htmlspecialchars($l['comment']) ?>)</div>
            <?php endif; ?>
            <?php endforeach; ?>

            <div class="sep-bold line line-total">
                <span>TOTAL</span>
                <span><?= $fmtTotal ?> €</span>
            </div>
            <div class="pay-method t-center"><?= htmlspecialchars($fmtMethod) ?></div>

            <?php if ($sirets): ?>
            <div class="legal t-tiny">
                <?php foreach ($sirets as $s): ?>
                <div class="legal-row">
                    <span><?= htmlspecialchars($s['nom']) ?></span>
                    <span>SIRET <?= htmlspecialchars($s['value']) ?></span>
                </div>
                <?php endforeach; ?>
                <div style="margin-top:3px;"><?= htmlspecialchars(EntityProfile::MENTION_TVA) ?></div>
            </div>
            <?php endif; ?>

            <div class="actions no-print">
                <button class="btn btn-print" onclick="window.print()">🖨️</button>
                <a class="btn btn-print" href="<?= $exportUrl ?>&format=text" target="_blank">📋</a>
                <button class="btn btn-print" onclick="window.history.back()">←</button>
            </div>
        </div>
    </body>
    </html>
    <?php
}
