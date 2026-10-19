<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Mon site') ?></title>

    <link rel="stylesheet" href="assets/css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">

    <?php
    // Charger le thème warm sur toutes les routes app + login
    $saleModernActions = [
        'login','memberConnexion',
        'salePanel','purchasePanel','newSaleForm','newPurchaseForm','updatePurchase','ardoisePanel','stockPanel','stockAddEntry',
        'resultPanel','dayBrocResult','dayFripeResult','monthBrocResult','monthFripeResult',
        'statistic','globalStatistic','articles','articlesFetch',
        'closurePanel','generateDailyClosure','apiGetClosureForDate','apiLockClosure','apiUpdateClosureNotesCash',
        'saveArticle','updateArticle','deleteArticle','deleteSale','deletePurchase',
        'addSale','addPurchase','apiSaveArticle','apiDeleteArticle','apiGetSaleArticles','apiGetSale','apiUpdateArticle','apiUpdateSale',
        'apiSavePurchaseArticle','apiDeletePurchaseArticle','apiUpdatePurchaseArticle','apiUpdatePurchase','apiAddProvider',
        'cbBalancePanel','urssafPanel'
    ];
    $currentAction = $_GET['action'] ?? '';
    if (in_array($currentAction, $saleModernActions, true)): ?>
    <link rel="stylesheet" href="assets/css/sale-modern.css?v=36" /><!-- purchasePanel theme salePanel -->
    <?php if ($currentAction === 'cbBalancePanel'): ?>
    <link rel="stylesheet" href="assets/css/cb-balance.css?v=1" />
    <?php endif; ?>
    <?php if ($currentAction === 'urssafPanel'): ?>
    <link rel="stylesheet" href="assets/css/urssaf-report.css?v=1" />
    <?php endif; ?>
    <?php if ($currentAction === 'articles'): ?>
    <link rel="stylesheet" href="assets/css/articles.css?v=1" />
    <?php endif; ?>
    <?php endif; ?>
    <?php
    // Détection environnement
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isLocal = (strpos($host, 'localhost') !== false || strpos($host, '10.157.248.24') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, ':8000') !== false);
    $envLabel = $isLocal ? 'LOCAL' : 'PROD';
    $envColor = $isLocal ? '#2980B9' : '#C0392B';
    if ($isLocal) {
        // Palette bleue/verte "dev" pour LOCAL
        $c1 = '#3498DB'; $c2 = '#2ECC71'; $c3 = '#5DADE2';
        $c4 = '#85C1E9'; $c5 = '#27AE60'; $c6 = '#58D68D';
        $c7 = '#2E86C1'; $c8 = '#82E0AA'; $c9 = '#1ABC9C';
    } else {
        // Palette rouge/orange "alerte" pour PROD
        $c1 = '#E74C3C'; $c2 = '#D35400'; $c3 = '#EC7063';
        $c4 = '#F1948A'; $c5 = '#C0392B'; $c6 = '#E67E22';
        $c7 = '#CB4335'; $c8 = '#F39C12'; $c9 = '#A93226';
    }
    // Favicon SVG matriciel 3x3, 32x32px
    $faviconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32">'
        . '<rect x="1"  y="1"  width="9"  height="9"  rx="2" fill="' . $c1 . '"/>'
        . '<rect x="12" y="1"  width="9"  height="9"  rx="2" fill="' . $c2 . '"/>'
        . '<rect x="23" y="1"  width="9"  height="9"  rx="2" fill="' . $c3 . '"/>'
        . '<rect x="1"  y="12" width="9"  height="9"  rx="2" fill="' . $c4 . '"/>'
        . '<rect x="12" y="12" width="9"  height="9"  rx="2" fill="' . $c5 . '"/>'
        . '<rect x="23" y="12" width="9"  height="9"  rx="2" fill="' . $c6 . '"/>'
        . '<rect x="1"  y="23" width="9"  height="9"  rx="2" fill="' . $c7 . '"/>'
        . '<rect x="12" y="23" width="9"  height="9"  rx="2" fill="' . $c8 . '"/>'
        . '<rect x="23" y="23" width="9"  height="9"  rx="2" fill="' . $c9 . '"/>'
        . '</svg>';
    $faviconHref = 'data:image/svg+xml;base64,' . base64_encode($faviconSvg);
    // Mini SVG matriciel pour le badge navbar (16x16 inline)
    $badgeSvg = '<svg width="16" height="16" viewBox="0 0 16 16" fill="transparent" xmlns="http://www.w3.org/2000/svg">'
        . '<rect x="1"  y="1"  width="4" height="4" rx="1" fill="' . $c1 . '"/>'
        . '<rect x="6"  y="1"  width="4" height="4" rx="1" fill="' . $c2 . '"/>'
        . '<rect x="11" y="1"  width="4" height="4" rx="1" fill="' . $c3 . '"/>'
        . '<rect x="1"  y="6"  width="4" height="4" rx="1" fill="' . $c4 . '"/>'
        . '<rect x="6"  y="6"  width="4" height="4" rx="1" fill="' . $c5 . '"/>'
        . '<rect x="11" y="6"  width="4" height="4" rx="1" fill="' . $c6 . '"/>'
        . '<rect x="1"  y="11" width="4" height="4" rx="1" fill="' . $c7 . '"/>'
        . '<rect x="6"  y="11" width="4" height="4" rx="1" fill="' . $c8 . '"/>'
        . '<rect x="11" y="11" width="4" height="4" rx="1" fill="' . $c9 . '"/>'
        . '</svg>';
    $badgeSvgB64 = 'data:image/svg+xml;base64,' . base64_encode($badgeSvg);
    ?>
    <link rel="icon" href="<?= $faviconHref ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <!-- Chart.js depuis un CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<body>

    <main>
        <div class="navbar-pill">
            <nav class="main-navbar">
                <div class="navbar-inner">
                    <div class="navbar-brand-wrap">
                        <svg class="navbar-logo" width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="2" y="2" width="7" height="7" rx="1.5" fill="#D4A574"/>
                            <rect x="11" y="2" width="7" height="7" rx="1.5" fill="#B8866B"/>
                            <rect x="20" y="2" width="7" height="7" rx="1.5" fill="#6B8E6B"/>
                            <rect x="2" y="11" width="7" height="7" rx="1.5" fill="#E8C9A8"/>
                            <rect x="11" y="11" width="7" height="7" rx="1.5" fill="#D4A574"/>
                            <rect x="20" y="11" width="7" height="7" rx="1.5" fill="#B8866B"/>
                            <rect x="2" y="20" width="7" height="7" rx="1.5" fill="#6B8E6B"/>
                            <rect x="11" y="20" width="7" height="7" rx="1.5" fill="#E8C9A8"/>
                            <rect x="20" y="20" width="7" height="7" rx="1.5" fill="#D4A574"/>
                        </svg>
                        <a href="index.php" class="navbar-brand">LA MATRICE</a>
                        <span class="env-badge" style="background-color: <?= $envColor ?>;" title="<?= $envLabel ?>">
                            <img src="<?= $badgeSvgB64 ?>" alt="" width="16" height="16" style="display:block;border-radius:2px;">
                        </span>
                    </div>

                    <div class="navbar-actions">
                        <a href="index.php?action=salePanel" class="nav-action nav-action--sale" title="Ventes">
                            <span class="nav-action-icon">📈</span>
                            <span class="nav-action-label">Ventes</span>
                        </a>
                        <a href="index.php?action=purchasePanel" class="nav-action nav-action--purchase" title="Achats">
                            <span class="nav-action-icon">📉</span>
                            <span class="nav-action-label">Achats</span>
                        </a>
                        <div class="burger" id="burger" role="button" aria-label="Menu">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>

                    <div class="navbar-links" id="nav-links">
                        <?php if (isset($_SESSION['user_status']) && $_SESSION['user_status'] != 'RGPD'): ?>
                            <div class="nav-group nav-group--ops">
                                <span class="nav-group-title">Opérations</span>
                                <a href="index.php?action=salePanel" class="<?= ($currentAction === 'salePanel' || $currentAction === 'newSaleForm') ? 'active' : '' ?>">Ventes</a>
                                <a href="index.php?action=purchasePanel" class="<?= ($currentAction === 'purchasePanel' || $currentAction === 'newPurchaseForm') ? 'active' : '' ?>">Achats</a>
                            </div>
                            <div class="nav-group">
                                <span class="nav-group-title">Résultats</span>
                                <a href="index.php?action=resultPanel" class="<?= ($currentAction === 'resultPanel') ? 'active' : '' ?>">Résultats</a>
                            </div>
                            <div class="nav-group">
                                <span class="nav-group-title">Contrôle</span>
                                <a href="index.php?action=closurePanel" class="<?= ($currentAction === 'closurePanel') ? 'active' : '' ?>">Clôtures</a>
                                <a href="index.php?action=cbBalancePanel" class="<?= ($currentAction === 'cbBalancePanel') ? 'active' : '' ?>">Balance CB</a>
                                <a href="index.php?action=urssafPanel" class="<?= ($currentAction === 'urssafPanel') ? 'active' : '' ?>">URSSAF</a>
                            </div>
                            <div class="nav-group">
                                <span class="nav-group-title">Données</span>
                                <a href="index.php?action=stockPanel" class="<?= ($currentAction === 'stockPanel') ? 'active' : '' ?>">Stock</a>
                                <a href="index.php?action=articles" class="<?= ($currentAction === 'articles') ? 'active' : '' ?>">Articles</a>
                                <a href="index.php?action=ardoisePanel" class="<?= ($currentAction === 'ardoisePanel') ? 'active' : '' ?>">Ardoises</a>
                            </div>
                            <div class="nav-group">
                                <span class="nav-group-title">Analyses</span>
                                <a href="index.php?action=statistic" class="<?= ($currentAction === 'statistic') ? 'active' : '' ?>">Statistiques</a>
                                <a href="index.php?action=globalStatistic" class="<?= ($currentAction === 'globalStatistic') ? 'active' : '' ?>">Stats Globales</a>
                            </div>
                            <?php if (isset($_SESSION['user_status']) && $_SESSION['user_status'] === 'admin'): ?>
                            <div class="nav-group nav-group--admin">
                                <span class="nav-group-title">Admin</span>
                                <a href="index.php?action=userForm" class="<?= ($currentAction === 'userForm' || $currentAction === 'manageUser') ? 'active' : '' ?>">Utilisateurs</a>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>

        <?= $content ?>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const burger = document.getElementById('burger');
            const navLinks = document.getElementById('nav-links');
            let lastScrollY = window.scrollY;

            burger.addEventListener('click', function(e) {
                e.stopPropagation();
                navLinks.classList.toggle('active');
                burger.classList.toggle('open');
            });

            // Fermer au clic en dehors
            document.addEventListener('click', function(e) {
                if (navLinks.classList.contains('active') && !burger.contains(e.target) && !navLinks.contains(e.target)) {
                    navLinks.classList.remove('active');
                    burger.classList.remove('open');
                }
            });

            // Fermer au scroll descendant sur mobile
            if ('ontouchstart' in window) {
                window.addEventListener('scroll', function() {
                    const y = window.scrollY;
                    if (y > lastScrollY + 10 && navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        burger.classList.remove('open');
                    }
                    lastScrollY = y;
                });
            }
        });
    </script>

    <?php if (isset($jsFile)): ?>
        <script type="text/javascript" src="assets/js/<?= $jsFile ?>?v=8"></script>
    <?php endif; ?>
    <?php if (isset($newJsFile)): ?>
        <script type="text/javascript" src="view/<?= $newJsFile ?>?v=16"></script>
    <?php endif; ?>
    <?php if (isset($purchaseJsFile)): ?>
        <script type="text/javascript" src="view/control-panel/purchase/update/update.js?v=3"></script>
    <?php endif; ?>
</body>
</html>
