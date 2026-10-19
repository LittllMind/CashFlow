<?php ob_start(); ?>

<div class="closure-panel container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Clôtures journalières</h2>
        <span class="badge bg-info text-dark" id="envBadge">LOCAL</span>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Sélecteur de date -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="closurePanel">
                <div class="col-md-4">
                    <label class="form-label">Date de clôture</label>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($selectedDate) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Voir</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alertes : journées non clôturées -->
    <?php if (!empty($unclosedDays)): ?>
        <div class="alert alert-warning">
            <strong>Journées non clôturées (<?= count($unclosedDays) ?>) :</strong>
            <ul class="mb-0 mt-1">
                <?php foreach (array_slice($unclosedDays, 0, 10) as $u): ?>
                    <li>
                        <?= htmlspecialchars($u['day']) ?> — <?= $u['owner'] === 'broc' ? 'BROC' : 'FRIPE' ?>
                        <a href="index.php?action=closurePanel&date=<?= urlencode($u['day']) ?>"> clôturer</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Tableau des clôtures existantes pour la date -->
    <div class="row">
        <?php foreach (['broc' => 'BROC', 'fripe' => 'FRIPE'] as $ownerKey => $label): ?>
            <?php
                $ownerClosure = null;
                foreach ($closures as $c) {
                    if ($c['owner'] === $ownerKey) {
                        $ownerClosure = $c;
                        break;
                    }
                }
            ?>
            <div class="col-md-6 mb-3">
                <div class="card h-100 <?= $ownerClosure ? ($ownerClosure['locked_at'] ? 'border-success' : 'border-warning') : '' ?>">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong><?= $label ?> — <?= htmlspecialchars($selectedDate) ?></strong>
                        <?php if ($ownerClosure): ?>
                            <span class="badge <?= $ownerClosure['locked_at'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= $ownerClosure['locked_at'] ? 'Verrouillée' : 'Brouillon' ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Non générée</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($ownerClosure): ?>
                            <p><strong>N° </strong><code><?= htmlspecialchars($ownerClosure['closure_number']) ?></code></p>
                            <p><strong>Ventes :</strong> <?= number_format($ownerClosure['total_sales_count'], 0, ',', ' ') ?>
                            — <strong>Articles :</strong> <?= number_format($ownerClosure['total_items'], 0, ',', ' ') ?></p>
                            <p><strong>Total :</strong> <?= number_format($ownerClosure['total_sales'], 2, ',', ' ') ?> €
                            <br>
                            <span class="text-muted">Espèces <?= number_format($ownerClosure['total_cash'], 2, ',', ' ') ?> €
                            | Carte <?= number_format($ownerClosure['total_card'], 2, ',', ' ') ?> €
                            | Chèque <?= number_format($ownerClosure['total_cheque'], 2, ',', ' ') ?> €</span>
                            </p>

                            <?php if (($ownerClosure['total_purchases_count'] ?? 0) > 0): ?>
                            <p class="text-warning"><strong>Achats du jour :</strong> <?= number_format($ownerClosure['total_purchases'], 2, ',', ' ') ?> €
                            (<?= $ownerClosure['total_purchases_count'] ?> achat<?= $ownerClosure['total_purchases_count'] > 1 ? 's' : '' ?>)</p>
                            
                            <p class="small text-muted">Solde net caisse : <?= number_format($ownerClosure['total_sales'] - $ownerClosure['total_purchases'], 2, ',', ' ') ?> €</p>
                            <?php endif; ?>

                            <?php if ($ownerClosure['counted_cash'] !== null): ?>
                                <p class="<?= $ownerClosure['cash_difference'] == 0 ? 'text-success' : 'text-danger' ?>">
                                    <strong>Écart caisse :</strong> <?= number_format($ownerClosure['cash_difference'], 2, ',', ' ') ?> €
                                </p>
                            <?php endif; ?>

                            <p class="small text-muted">Hash : <?= substr(htmlspecialchars($ownerClosure['content_hash']), 0, 16) ?>…<br>
                            <?php if ($ownerClosure['previous_closure_hash']): ?>
                                Chaîne : <?= substr(htmlspecialchars($ownerClosure['previous_closure_hash']), 0, 16) ?>…
                            <?php else: ?>
                                Première clôture
                            <?php endif; ?>
                            </p>
                            <p class="small text-muted">
                                <a href="index.php?action=apiGetClosureAudit&owner=<?= urlencode($ownerKey) ?>&closure_date=<?= urlencode($selectedDate) ?>" target="_blank">Journal d'audit</a>
                                — IP dernière action : <?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'inconnue') ?>
                            </p>

                            <?php if (!$ownerClosure['locked_at']): ?>
                                <!-- Mise à jour fond / comptage / notes -->
                                <form class="closure-cash-form mt-3" data-owner="<?= $ownerKey ?>" data-date="<?= htmlspecialchars($selectedDate) ?>">
                                    <div class="mb-2">
                                        <label class="form-label small">Fond de caisse (€)</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm opening-cash" placeholder="0.00"
                                            value="<?= $ownerClosure['opening_cash_float'] !== null ? htmlspecialchars(number_format($ownerClosure['opening_cash_float'], 2, '.', '')) : '' ?>"
                                            <?= $ownerClosure['locked_at'] ? 'disabled' : '' ?>
                                        >
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Espèces comptées (€)</label>
                                        <input type="number" step="0.01" class="form-control form-control-sm counted-cash" placeholder="0.00"
                                            value="<?= $ownerClosure['counted_cash'] !== null ? htmlspecialchars(number_format($ownerClosure['counted_cash'], 2, '.', '')) : '' ?>"
                                            <?= $ownerClosure['locked_at'] ? 'disabled' : '' ?>
                                        >
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Notes</label>
                                        <textarea class="form-control form-control-sm notes"><?= htmlspecialchars($ownerClosure['notes'] ?? '') ?></textarea>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input signed-paper" type="checkbox" value="1"
                                            <?= !empty($ownerClosure['signed_paper_archived']) ? 'checked' : '' ?>
                                            <?= $ownerClosure['locked_at'] ? 'disabled' : '' ?>
                                        >
                                        <label class="form-check-label small">Papier imprimé et signé</label>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Enregistrer comptage</button>
                                </form>
                            <?php else: ?>
                                <?php if ($ownerClosure['notes']): ?>
                                    <p class="small"><strong>Notes :</strong> <?= nl2br(htmlspecialchars($ownerClosure['notes'])) ?></p>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <button class="btn btn-sm btn-success export-pdf"
                                    data-owner="<?= $ownerKey ?>"
                                    data-date="<?= htmlspecialchars($selectedDate) ?>"
                                    data-number="<?= htmlspecialchars($ownerClosure['closure_number']) ?>"
                                    data-total="<?= $ownerClosure['total_sales'] ?>"
                                    data-cash="<?= $ownerClosure['total_cash'] ?>"
                                    data-card="<?= $ownerClosure['total_card'] ?>"
                                    data-cheque="<?= $ownerClosure['total_cheque'] ?>"
                                    data-count="<?= $ownerClosure['total_sales_count'] ?>"
                                    data-items="<?= $ownerClosure['total_items'] ?>"
                                    data-purchases="<?= $ownerClosure['total_purchases'] ?? 0 ?>"
                                    data-purchases-count="<?= $ownerClosure['total_purchases_count'] ?? 0 ?>"
                                >
                                    PDF
                                </button>

                                <button class="btn btn-sm btn-outline-secondary export-csv"
                                    data-owner="<?= $ownerKey ?>"
                                    data-date="<?= htmlspecialchars($selectedDate) ?>"
                                    data-number="<?= htmlspecialchars($ownerClosure['closure_number']) ?>"
                                >
                                    CSV
                                </button>

                                <?php if (!$ownerClosure['locked_at']): ?>
                                    <button class="btn btn-sm btn-danger lock-closure"
                                        data-owner="<?= $ownerKey ?>"
                                        data-date="<?= htmlspecialchars($selectedDate) ?>"
                                    >
                                        Verrouiller
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Aucune clôture générée pour cette date.</p>
                            <form method="POST" action="index.php?action=generateDailyClosure">
                                <input type="hidden" name="owner" value="<?= $ownerKey ?>">
                                <input type="hidden" name="closure_date" value="<?= htmlspecialchars($selectedDate) ?>">
                                <button type="submit" class="btn btn-primary">Générer la clôture <?= $label ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$newJsFile = 'control-panel/closure/control-panel.js?v=2';
require('view/template/mainTemplate.php');
