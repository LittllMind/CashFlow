<?php
// tests/CbBalancePanelTest.php — Test statique feature CbBalance
$errors=[];

// --- Fichiers
foreach([
    'model/CbBalanceManager.php',
    'controller/CbBalanceController.php',
    'view/control-panel/cb-balance/cbBalancePanel.php',
    'view/control-panel/cb-balance/cbBalance.js',
    'assets/css/cb-balance.css',
    'migrations/2026-07-28_cb_balances.sql',
    'migrations/2026-07-28_cb_balances.rollback.sql',
] as $f){
    if(!file_exists(__DIR__.'/../'.$f)) $errors[]="$f manquant";
}

// --- Routes
$idx=file_get_contents(__DIR__.'/../index.php');
foreach(['cbBalancePanel','apiCbBalanceData','apiRecordTransfer'] as $r){
    if(strpos($idx,"case '$r'")===false) $errors[]="Route '$r' absente";
}

// --- mainTemplate whitelist + link + CSS
$tpl=file_get_contents(__DIR__.'/../view/template/mainTemplate.php');
if(strpos($tpl,"'cbBalancePanel'")===false) $errors[]="cbBalancePanel absent whitelist";
if(strpos($tpl,'Balance CB')===false) $errors[]="Lien Balance CB absent navbar";
if(strpos($tpl,'cb-balance.css')===false) $errors[]="CSS cb-balance.css non chargé";

// --- Vue marqueurs
$vue=file_get_contents(__DIR__.'/../view/control-panel/cb-balance/cbBalancePanel.php');
foreach([
    'cb-brut','cb-fee','cb-net','btn-virer',
    'cb-fripe-pure','cb-mix-fripe','cb-mix-count',
    'td-fripe-cash','td-fripe-check','td-fripe-card','td-fripe-total',
    'td-broc-cash','td-broc-check','td-broc-card','td-broc-total',
    'xfer-tbody','total-xfer-brut','total-xfer-net',
] as $m){
    if(strpos($vue,$m)===false) $errors[]="Vue marqueur '$m' absent";
}

// --- JS marqueurs
$js=file_get_contents(__DIR__.'/../view/control-panel/cb-balance/cbBalance.js');
foreach(['apiCbBalanceData','apiRecordTransfer','cb_amount','fee_amount','net_amount','fripe','broc','transfers'] as $m){
    if(strpos($js,$m)===false) $errors[]="JS marqueur '$m' absent";
}

// --- CSS classes
$css=file_get_contents(__DIR__.'/../assets/css/cb-balance.css');
foreach([
    '.cb-panel','.cb-header','.cb-card','.cb-row','.cb-detail',
    '.cb-table-card','.cb-table','.btn-virer','.cb-note','.cb-badge',
] as $c){
    if(strpos($css,$c)===false) $errors[]="CSS classe '$c' absente";
}

// --- Modèle calcul
require_once __DIR__.'/../model/BddManager.php';
require_once __DIR__.'/../model/CbBalanceManager.php';
$m=new OpenClassroom\Blog\Model\CbBalanceManager();
$r=$m->getFullReport();
$must=['cb_amount','fee_amount','net_amount','fripe_pure','mix_fripe','mix_count','fripe','broc'];
foreach($must as $k){ if(!array_key_exists($k,$r)) $errors[]="Model retourne pas '$k'"; }
if($r['cb_amount']<0) $errors[]="cb_amount négatif";
if($r['fee_amount']!=round($r['cb_amount']*0.0146,2)) $errors[]="Fee mal calculé";

// --- DB table
$pdo=new PDO('mysql:host=localhost;dbname=cashflow_db;charset=utf8mb4','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$st=$pdo->query("SHOW TABLES LIKE 'cb_balances'");
if($st->rowCount()===0) $errors[]="Table cb_balances absente";

// Report
if(empty($errors)){
    echo "OK — CbBalancePanel | cb=" . number_format($r['cb_amount'],2,',',' ')
         ."€ fee=".number_format($r['fee_amount'],2,',',' ')
         ."€ net=".number_format($r['net_amount'],2,',',' ')
         ."€ mix=".$r['mix_count']."\n";
    exit(0);
}
foreach($errors as $e) echo "KO: $e\n";
exit(1);
