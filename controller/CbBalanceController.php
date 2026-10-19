<?php
/**
 * CbBalanceController.php — Panneau Balance CB + action "J'ai viré"
 */
require_once('model/CbBalanceManager.php');
use OpenClassroom\Blog\Model\CbBalanceManager;

function cbAuth(): void {
    if (empty($_SESSION['pseudo']) || userValidation()!=='Allowed') {
        header('Location: index.php?action=login'); exit;
    }
}

function cbBalancePanel($msg='') {
    cbAuth();
    $title='Balance CB — FRIPE';
    $newJsFile='control-panel/cb-balance/cbBalance.js';
    require('view/control-panel/cb-balance/cbBalancePanel.php');
}

function apiCbBalanceData() {
    try {
        if (empty($_SESSION['pseudo'])) { echo json_encode(['error'=>'Auth']); exit; }
        $m=new CbBalanceManager();
        $d=$m->getFullReport();
        $t=$m->getTransfers('FRIPE');
        $tot=0; foreach ($t as $row) $tot+=(float)$row['net_amount'];
        $lastTransferDate=$m->getLastTransferDate('FRIPE');
        echo json_encode(array_merge($d,[
            'ok'=>true,
            'transfers'=>$t,
            'total_transfere'=>$tot,
            'lastTransferDate'=>$lastTransferDate,
        ]),JSON_UNESCAPED_UNICODE); exit;
    } catch (\Throwable $e) {
        echo json_encode(['error'=>$e->getMessage()]); exit;
    }
}

function apiRecordTransfer() {
    try {
        if (empty($_SESSION['pseudo'])) { echo json_encode(['error'=>'Auth']); exit; }
        $d=$_POST;
        $cb=(float)($d['cb_amount']??0);
        if ($cb<=0) { echo json_encode(['error'=>'Montant']); exit; }
        $m=new CbBalanceManager();
        $today=date('Y-m-d');

        // 1. Enregistre le virement
        $ok=$m->recordTransfer([
            'transfer_date'=>$d['transfer_date']??$today,
            'cb_amount'=>$cb,
            'fee_amount'=>(float)($d['fee_amount']??0),
            'net_amount'=>(float)($d['net_amount']??0),
            'category'=>'FRIPE',
        ]);
        if (!$ok) { echo json_encode(['error'=>'Insert']); exit; }

        // 2. Génère le document (HTML)
        $docPath=$m->generateDocument([
            'date'=>$d['transfer_date']??$today,
            'cb_amount'=>$cb,
            'fee_amount'=>(float)($d['fee_amount']??0),
            'net_amount'=>(float)($d['net_amount']??0),
            'fripe_cash'=>(float)($d['fripe_cash']??0),
            'broc_cash'=>(float)($d['broc_cash']??0),
            'user'=>$_SESSION['pseudo'],
        ]);

        echo json_encode([
            'ok'=>true,
            'doc_url'=>$docPath ? str_replace(__DIR__.'/', '', $docPath) : null,
            'reset'=>true,
        ],JSON_UNESCAPED_UNICODE); exit;
    } catch (\Throwable $e) {
        echo json_encode(['error'=>$e->getMessage()]); exit;
    }
}

function apiCbDocument() {
    $file=$_GET['file']??'';
    $path=realpath(__DIR__.'/'.$file);
    $allowed=realpath(__DIR__.'/docs/cb/');
    if (!$path || !str_starts_with($path,$allowed) || !is_file($path)) {
        header('HTTP/1.1 404 Not Found'); exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.basename($path).'.html"');
    readfile($path);
    exit;
}
