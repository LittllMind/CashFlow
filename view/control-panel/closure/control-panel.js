document.addEventListener('DOMContentLoaded', function () {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')
    ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    : '';

  function postJson(url, data) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-CSRF-Token': csrfToken },
      body: data
    }).then(r => r.json()).catch(err => ({ success: false, error: err.message }));
  }

  // --- Mise à jour comptage / notes / papier signé ---
  document.querySelectorAll('.closure-cash-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const owner = this.dataset.owner;
      const date = this.dataset.date;
      const fd = new FormData();
      fd.append('owner', owner);
      fd.append('closure_date', date);
      fd.append('opening_cash_float', this.querySelector('.opening-cash').value);
      fd.append('counted_cash', this.querySelector('.counted-cash').value);
      fd.append('notes', this.querySelector('.notes').value);
      fd.append('signed_paper_archived', this.querySelector('.signed-paper').checked ? '1' : '0');

      postJson('index.php?action=apiUpdateClosureNotesCash', fd).then(json => {
        if (json.success) {
          window.location.reload();
        } else {
          alert('Erreur : ' + (json.error || 'Échec de la mise à jour'));
        }
      });
    });
  });

  // --- Verrouillage ---
  document.querySelectorAll('.lock-closure').forEach(btn => {
    btn.addEventListener('click', function () {
      if (!confirm('Verrouiller définitivement cette clôture ?')) return;
      const fd = new FormData();
      fd.append('owner', this.dataset.owner);
      fd.append('closure_date', this.dataset.date);
      postJson('index.php?action=apiLockClosure', fd).then(json => {
        if (json.success) {
          window.location.reload();
        } else {
          alert('Erreur : ' + (json.error || 'Échec du verrouillage'));
        }
      });
    });
  });

  // --- Export PDF ---
  document.querySelectorAll('.export-pdf').forEach(btn => {
    btn.addEventListener('click', function () {
      const owner = this.dataset.owner;
      const entity = owner === 'broc' ? 'BROC' : 'FRIPE';
      const date = this.dataset.date;
      const number = this.dataset.number;
      const total = parseFloat(this.dataset.total || 0).toFixed(2);
      const cash = parseFloat(this.dataset.cash || 0).toFixed(2);
      const card = parseFloat(this.dataset.card || 0).toFixed(2);
      const cheque = parseFloat(this.dataset.cheque || 0).toFixed(2);
      const count = this.dataset.count;
      const items = this.dataset.items;

      const { jsPDF } = window.jspdf;
      const doc = new jsPDF('p', 'mm', 'a4');
      const pageWidth = doc.internal.pageSize.getWidth();
      let y = 12;

      doc.setFont('helvetica', 'bold');
      doc.setFontSize(14);
      doc.text(`Clôture journalière — ${entity}`, pageWidth / 2, y, { align: 'center' });
      y += 7;
      doc.setFontSize(10);
      doc.setFont('helvetica', 'normal');
      doc.text(`Date : ${date}`, pageWidth / 2, y, { align: 'center' });
      y += 5;
      doc.text(`N° ${number}`, pageWidth / 2, y, { align: 'center' });
      y += 10;

      const purchases = parseFloat(this.dataset.purchases || 0).toFixed(2);
      const purchasesCount = this.dataset.purchasesCount || 0;

      doc.autoTable({
        startY: y,
        head: [['Élément', 'Valeur']],
        body: [
          ['Ventes', `${count} ventes / ${items} articles`],
          ['Total caisse', `${total} €`],
          ['Espèces', `${cash} €`],
          ['Carte', `${card} €`],
          ['Chèque', `${cheque} €`],
          ...(parseFloat(purchases) > 0 ? [['Achats du jour', `${purchases} € (${purchasesCount} achat${purchasesCount > 1 ? 's' : ''})`]] : []),
          ...(parseFloat(purchases) > 0 ? [['Solde net caisse', `${(parseFloat(total) - parseFloat(purchases)).toFixed(2)} €`]] : [])
        ],
        theme: 'grid',
        styles: { fontSize: 10 },
        columnStyles: { 0: { fontStyle: 'bold' } }
      });

      y = doc.lastAutoTable.finalY + 15;
      doc.setFontSize(10);
      doc.text('Certification :', 15, y);
      y += 6;
      doc.setFontSize(9);
      const text = "Je soussigné(e), certifie que cet état correspond aux écritures issues de l'application pour la période indiquée, sous réserve des corrections et rapprochements comptables.";
      const split = doc.splitTextToSize(text, pageWidth - 30);
      doc.text(split, 15, y);
      y += split.length * 4 + 10;

      doc.text('Signature exploitant : _______________________', 15, y);
      y += 10;
      doc.text(`Document généré le ${new Date().toLocaleString('fr-FR')} — CashFlow`, 15, y);

      doc.save(`${number}-cloture-journaliere.pdf`);
      logExport(owner, date, 'pdf', `${number}-cloture-journaliere.pdf`);
    });
  });

  // --- Export CSV ---
  document.querySelectorAll('.export-csv').forEach(btn => {
    btn.addEventListener('click', function () {
      const number = this.dataset.number;
      const date = this.dataset.date;
      const entity = this.dataset.owner === 'broc' ? 'BROC' : 'FRIPE';
      const rows = [
        ['Clôture journalière', entity, date, number],
        [''],
        ['Type', 'Nombre', 'Espèces', 'Carte', 'Chèque', 'Total'],
        [
          'Ventes',
          `${this.dataset.count || ''} / ${this.dataset.items || ''}`,
          this.dataset.cash,
          this.dataset.card,
          this.dataset.cheque,
          this.dataset.total
        ]
      ];
      const purchases = parseFloat(this.dataset.purchases || 0);
      if (purchases > 0) {
        rows.push(['Achats du jour', this.dataset.purchasesCount || '', '', '', '', this.dataset.purchases]);
        rows.push(['Solde net caisse', '', '', '', '', (parseFloat(this.dataset.total) - purchases).toFixed(2)]);
      }
      const csv = rows.map(r => r.map(c => `"${String(c).replace(/"/g, '"""')}"`).join(';')).join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `${number}-cloture.csv`;
      document.body.appendChild(link);
      link.click();
      link.remove();
      logExport(btn.dataset.owner, date, 'csv', `${number}-cloture.csv`);
    });
  });

  // --- Log export côté serveur ---
  function logExport(owner, closureDate, type, file) {
    const fd = new FormData();
    fd.append('owner', owner);
    fd.append('closure_date', closureDate);
    fd.append('type', type);
    fd.append('file', file);
    postJson('index.php?action=apiLogClosureExport', fd).then(() => {});
  }
});
