<?php
/**
 * CbBalanceManager.php — Suivi cumul CB à virer à Fripe
 */
namespace OpenClassroom\Blog\Model;

use PDO;
require_once("model/BddManager.php");

class CbBalanceManager extends BddManager
{
    const CB_FEE_RATE = 0.0146;
    const FRIPE_CATS = ['Femmes','Hommes','Enfants','Accessoires','Mercerie','Bougies'];

    public function getLastTransferDate(string $cat='FRIPE'): ?string
    {
        $sql='SELECT MAX(transfer_date) AS d FROM cb_balances WHERE category=?';
        $r=$this->dbConnect()->prepare($sql);
        $r->execute([$cat]);
        return $r->fetch(PDO::FETCH_ASSOC)['d'] ?: null;
    }

    public function getTransfers(string $cat='FRIPE'): array
    {
        $sql='SELECT *,DATE_FORMAT(transfer_date,"%d/%m/%Y") AS fd FROM cb_balances WHERE category=? AND transfer_date >= "2026-01-01" ORDER BY transfer_date DESC';
        $r=$this->dbConnect()->prepare($sql); $r->execute([$cat]); return $r->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Résultat : cumul FRIPE (brut,fee,net) + détail pur/mix + répartition cash/chèque/CB par pôle.
     */
    public function getFullReport(): array
    {
        $from=$this->getLastTransferDate('FRIPE') ?? '2026-01-01';
        $bdd=$this->dbConnect();

        $sql="SELECT
            s.id,
            s.total_amount,
            s.payment_method,
            SUM(CASE WHEN a.category IN ('Femmes','Hommes','Enfants','Accessoires','Mercerie','Bougies') THEN a.quantity*a.unit_price ELSE 0 END) fripe,
            SUM(CASE WHEN a.category='Brocante' THEN a.quantity*a.unit_price ELSE 0 END) broc,
            SUM(CASE WHEN a.category NOT IN ('Femmes','Hommes','Enfants','Accessoires','Mercerie','Bougies','Brocante') THEN a.quantity*a.unit_price ELSE 0 END) other
        FROM sales s JOIN articles a ON s.id=a.sale_id
        WHERE s.payment_method IN ('cash','check','card') AND s.sale_date>=?
        GROUP BY s.id";
        $stmt=$bdd->prepare($sql); $stmt->execute([$from]);
        $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

        $fripePure=0; $mixFripe=0; $mixCount=0;
        $fripe=['cash'=>0.0,'check'=>0.0,'card'=>0.0];
        $broc=['cash'=>0.0,'check'=>0.0,'card'=>0.0];

        foreach ($rows as $r) {
            $f=(float)$r['fripe']; $b=(float)$r['broc']; $o=(float)$r['other'];
            $pm=$r['payment_method'];
            $broc_total = $b + $o;  // TOUT non-FRIPE = BROC
            $types=($f>0?1:0)+($broc_total>0?1:0);
            if ($types>1 && $pm==='card') $mixCount++;

            if ($f>0) {
                $fripe[$pm]+=$f;
                if ($types===1 && $pm==='card') $fripePure+=$f; else $mixFripe+=$f;
            }
            if ($broc_total>0) { $broc[$pm]+=$broc_total; }
        }

        $cb=$fripe['card'];
        $fee=round($cb*self::CB_FEE_RATE,2);
        $net=round($cb-$fee,2);

        return [
            'from'=>$from,
            'fripe'=>['cash'=>round($fripe['cash'],2),'check'=>round($fripe['check'],2),'card'=>round($fripe['card'],2)],
            'broc'=>['cash'=>round($broc['cash'],2),'check'=>round($broc['check'],2),'card'=>round($broc['card'],2)],
            'fripe_pure'=>round($fripePure,2),'mix_fripe'=>round($mixFripe,2),'mix_count'=>$mixCount,
            'cb_amount'=>$cb,'fee_amount'=>$fee,'net_amount'=>$net,
        ];
    }

    public function recordTransfer(array $data): bool
    {
        $sql='INSERT INTO cb_balances (transfer_date,cb_amount,fee_rate,fee_amount,net_amount,category,created_at) VALUES (?,?,?,?,?,?,NOW())';
        $s=$this->dbConnect()->prepare($sql);
        return $s->execute([
            $data['transfer_date'],$data['cb_amount'],self::CB_FEE_RATE,$data['fee_amount'],$data['net_amount'],$data['category']
        ]);
    }

    /**
     * Génère un document de virement (HTML) stocké dans docs/cb/
     * Retourne le chemin absolu du fichier.
     */
    public function generateDocument(array $data): ?string
    {
        $docDir = __DIR__ . '/../docs/cb';
        if (!is_dir($docDir)) {
            if (!@mkdir($docDir, 0750, true)) return null;
        }

        $file = $docDir . '/cb_virement_' . date('Y-m-d_His') . '.html';
        $user = htmlspecialchars($data['user'] ?? 'Inconnu');
        $date = htmlspecialchars($data['date'] ?? date('Y-m-d'));

        ob_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Virement CB — <?= $date ?></title>
    <style>
        body{font-family:system-ui,-apple-system,sans-serif;max-width:480px;margin:2rem auto;padding:0 1rem;color:#3D3D3D}
        h1{font-size:1.25rem;border-bottom:2px solid #B8866B;padding-bottom:.5rem}
        .row{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid #F0EBE6}
        .label{color:#6B6B6B}
        .value{font-weight:600;font-variant-numeric:tabular-nums}
        .highlight{color:#B8866B;font-size:1.35rem;font-weight:700}
        .fee{color:#C97B7B}
        .net{color:#6B8E6B}
        .note{margin-top:1.5rem;padding:.75rem;background:#F5F0EB;border-radius:8px;font-size:.875rem;line-height:1.5}
        .footer{margin-top:2rem;text-align:right;font-size:.8rem;color:#A0A0A0}
    </style>
</head>
<body>
    <h1>💳 Virement CB FRIPE</h1>
    <p><strong>Date :</strong> <?= $date ?></p>
    <div class="row">
        <span class="label">CB brut (FRIPE)</span>
        <span class="value"><?= number_format($data['cb_amount'],2,',',' ') ?> €</span>
    </div>
    <div class="row">
        <span class="label">Frais lecteur (1,46 %)</span>
        <span class="value fee">−<?= number_format($data['fee_amount'],2,',',' ') ?> €</span>
    </div>
    <div class="row">
        <span class="label">Net viré à Fripe</span>
        <span class="value net highlight"><?= number_format($data['net_amount'],2,',',' ') ?> €</span>
    </div>
    <div class="row">
        <span class="label">Espèces FRIPE à déposer (Fripe)</span>
        <span class="value"><?= number_format($data['fripe_cash'],2,',',' ') ?> €</span>
    </div>
    <div class="row">
        <span class="label">Espèces BROC à déposer (Aurélien)</span>
        <span class="value"><?= number_format($data['broc_cash'],2,',',' ') ?> €</span>
    </div>
    <div class="note">
        Ce document atteste que le virement a été effectué, que les comptes sont à jour, et que les espèces correspondantes ont été déposées.
    </div>
    <div class="footer">
        Généré par CashFlow — <?= htmlspecialchars($user) ?> — <?= date('d/m/Y H:i') ?>
    </div>
</body>
</html>
<?php
        $html = ob_get_clean();
        if (file_put_contents($file, $html, LOCK_EX) === false) return null;
        return $file;
    }
}
