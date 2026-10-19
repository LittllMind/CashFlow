/**
 * cbBalance.js — Panneau Balance CB v3
 */
(function(){
    'use strict';
    const FEE=0.0146;
    const fmt=n=>new Intl.NumberFormat('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(Number(n))+' €';
    const fmtD=d=>{const p=(d||'').split('-');return p.length===3?`${p[2]}/${p[1]}/${p[0]}`:d;};

    async function load(){
        try{
            const r=await fetch('index.php?action=apiCbBalanceData',{credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'}});
            if(!r.ok)throw new Error('HTTP '+r.status);
            let d;try{d=await r.json();}catch(e){throw new Error('JSON invalide');}
            if(d.error)throw new Error(d.error);

            document.getElementById('last-d').textContent=d.from==='2026-01-01'?'Jamais':fmtD(d.from);
            document.getElementById('cb-brut').textContent=fmt(d.cb_amount);
            document.getElementById('cb-fee').textContent='-'+fmt(d.fee_amount);
            document.getElementById('cb-net').textContent=fmt(d.net_amount);
            document.getElementById('cb-fripe-pure').textContent=fmt(d.fripe_pure);
            document.getElementById('cb-mix-fripe').textContent=fmt(d.mix_fripe);
            document.getElementById('cb-mix-count').textContent=(d.mix_count||0)+' vente'+(d.mix_count>1?'s':'');

            // Espèces à déposer par pôle
            document.getElementById('cb-cash-fripe').textContent=fmt(d.fripe?.cash||0);
            document.getElementById('cb-cash-broc').textContent =fmt(d.broc?.cash ||0);

            document.getElementById('td-fripe-cash').textContent=fmt(d.fripe?.cash||0);
            document.getElementById('td-fripe-check').textContent=fmt(d.fripe?.check||0);
            document.getElementById('td-fripe-card').textContent=fmt(d.fripe?.card||0);
            document.getElementById('td-fripe-total').textContent=fmt((d.fripe?.cash||0)+(d.fripe?.check||0)+(d.fripe?.card||0));

            document.getElementById('td-broc-cash').textContent=fmt(d.broc?.cash||0);
            document.getElementById('td-broc-check').textContent=fmt(d.broc?.check||0);
            document.getElementById('td-broc-card').textContent=fmt(d.broc?.card||0);
            document.getElementById('td-broc-total').textContent=fmt((d.broc?.cash||0)+(d.broc?.check||0)+(d.broc?.card||0));

            const tx=document.getElementById('xfer-tbody');
            if(tx){
                tx.innerHTML='';
                if(!d.transfers||!d.transfers.length){
                    const tr=document.createElement('tr');
                    tr.innerHTML='<td colspan="4" class="cb-empty">Aucun virement.</td>'; tx.appendChild(tr);
                }else{
                    let tb=0,tf=0,tn=0;
                    d.transfers.forEach(t=>{
                        tb+=parseFloat(t.cb_amount); tf+=parseFloat(t.fee_amount); tn+=parseFloat(t.net_amount);
                        const tr=document.createElement('tr');
                        tr.innerHTML=`<td>${t.fd||fmtD(t.transfer_date)}</td>
                            <td class="cb-amount">${fmt(t.cb_amount)}</td>
                            <td class="cb-amount">${fmt(t.fee_amount)}</td>
                            <td class="cb-amount cb-value-net">${fmt(t.net_amount)}</td>`;
                        tx.appendChild(tr);
                    });
                    document.getElementById('total-xfer-brut').textContent=fmt(tb);
                    document.getElementById('total-xfer-fee').textContent=fmt(tf);
                    document.getElementById('total-xfer-net').textContent=fmt(tn);
                }
            }

            // Totaux globaux tableau dépôts
            const fc=d.fripe?.cash||0, fch=d.fripe?.check||0, fca=d.fripe?.card||0;
            const bc=d.broc?.cash||0, bch=d.broc?.check||0, bca=d.broc?.card||0;
            document.getElementById('td-total-cash').textContent=fmt(fc+bc);
            document.getElementById('td-total-check').textContent=fmt(fch+bch);
            document.getElementById('td-total-card').textContent=fmt(fca+bca);
            document.getElementById('td-total-all').textContent=fmt(fc+fch+fca+bc+bch+bca);

            window.__cb=d;
        }catch(e){console.error(e);alert(e.message);}
    }

    async function virer(){
        const d=window.__cb;
        if(!d||d.cb_amount<=0){alert('Aucun montant');return;}
        if(!confirm(`Virement de ${fmt(d.net_amount)} à Fripe ?\n(CB brut ${fmt(d.cb_amount)} − frais ${fmt(d.fee_amount)})`))return;
        const fd=new FormData();
        fd.append('cb_amount',d.cb_amount);
        fd.append('fee_amount',d.fee_amount);
        fd.append('net_amount',d.net_amount);
        fd.append('fripe_cash',d.fripe?.cash||0);
        fd.append('broc_cash',d.broc?.cash||0);
        fd.append('transfer_date',new Date().toISOString().split('T')[0]);
        try{
            const r=await fetch('index.php?action=apiRecordTransfer',{method:'POST',credentials:'include',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd});
            const j=await r.json();
            if(j.ok) {
                let msg='Comptes mis à jour. Virement enregistré. Espèces déposées.';
                if(j.doc_url) msg+='\nDocument : '+j.doc_url;
                alert(msg);
                window.__cb=null; await load();
            }
            else alert(j.error||'Erreur');
        }catch(e){alert(e.message);}
    }

    document.getElementById('btn-virer')?.addEventListener('click',virer);
    load();
})();
