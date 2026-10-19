document.addEventListener("DOMContentLoaded", function () {

  // === 1. Regroupement des données ===
  const groupedSales = {};
  const groupedPurchases = {};

  articles.forEach(item => {
    const dateKey = item.created_at.split(" ")[0];

    if (item.sale_id) {
      if (!groupedSales[dateKey]) groupedSales[dateKey] = [];
      groupedSales[dateKey].push(item);
    }

    if (item.purchase_id) {
      if (!groupedPurchases[dateKey]) groupedPurchases[dateKey] = [];
      groupedPurchases[dateKey].push(item);
    }
  });

  // === 2. Création tableau HTML résumé ===
  function createSummaryTable(dataByDate) {
    const rows = Object.entries(dataByDate).map(([date, items]) => {
      let total = 0, cash = 0, card = 0, check = 0, count = 0;

      items.forEach(item => {
        const amount = parseFloat(item.unit_price) * item.quantity;
        total += amount;
        count += item.quantity;

        switch (item.payment_method) {
          case 'cash': cash += amount; break;
          case 'card': card += amount; break;
          case 'check': check += amount; break;
        }
      });

      return { date, count, total, cash, card, check };
    });

    const totals = rows.reduce((acc, row) => {
      acc.count += row.count;
      acc.total += row.total;
      acc.cash += row.cash;
      acc.card += row.card;
      acc.check += row.check;
      return acc;
    }, { count: 0, total: 0, cash: 0, card: 0, check: 0 });

    const table = document.createElement("table");
    table.className = "compact-table";

    table.innerHTML = `
      <thead>
        <tr>
          <th>Date</th>
          <th>Articles</th>
          <th>Total (€)</th>
          <th>Espèces (€)</th>
          <th>Carte (€)</th>
          <th>Chèque (€)</th>
        </tr>
      </thead>
      <tbody>
        ${rows.map(row => `
          <tr>
            <td>${row.date}</td>
            <td>${row.count}</td>
            <td>${row.total.toFixed(2)}</td>
            <td>${row.cash.toFixed(2)}</td>
            <td>${row.card.toFixed(2)}</td>
            <td>${row.check.toFixed(2)}</td>
          </tr>
        `).join("")}
      </tbody>
      <tfoot>
        <tr style="font-weight:bold; background:#eee;">
          <td>Total</td>
          <td>${totals.count}</td>
          <td>${totals.total.toFixed(2)}</td>
          <td>${totals.cash.toFixed(2)}</td>
          <td>${totals.card.toFixed(2)}</td>
          <td>${totals.check.toFixed(2)}</td>
        </tr>
      </tfoot>
    `;

    return { table, totals };
  }

  // === 3. Insertion dans le DOM ===
  const container = document.getElementById("monthly-summary-container");
  const layout = document.createElement("div");
  layout.className = "table-columns";

  const salesResult = createSummaryTable(groupedSales);
  const purchasesResult = createSummaryTable(groupedPurchases);

  const createColumn = (title, tableElement) => {
    const column = document.createElement("div");
    column.className = "column";
    const titleEl = document.createElement("h3");
    titleEl.textContent = title;
    column.appendChild(titleEl);
    column.appendChild(tableElement);
    return column;
  };

  layout.appendChild(createColumn("Ventes du mois", salesResult.table));
  layout.appendChild(createColumn("Achats du mois", purchasesResult.table));
  container.appendChild(layout);

  // === 4. Récapitulatif URSSAF HTML ===
  const recapContainer = document.createElement("div");
  recapContainer.className = "summary";
  recapContainer.innerHTML = `
    <h3>Récapitulatif Déclaration URSSAF</h3>
    <table class="summary-table">
      <thead>
        <tr>
          <th>Total des ventes (€)</th>
          <th>Total des achats (€)</th>
          <th>Espèces (€)</th>
          <th>Carte (€)</th>
          <th>Chèque (€)</th>
          <th>À déclarer (€)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>${salesResult.totals.total.toFixed(2)}</td>
          <td>${purchasesResult.totals.total.toFixed(2)}</td>
          <td>${salesResult.totals.cash.toFixed(2)}</td>
          <td>${salesResult.totals.card.toFixed(2)}</td>
          <td>${salesResult.totals.check.toFixed(2)}</td>
          <td><strong>${salesResult.totals.total.toFixed(2)}</strong></td>
        </tr>
      </tbody>
    </table>
  `;
  container.appendChild(recapContainer);

  // === 5. Initialisation des selects mois / année ===
  const monthSelect = document.getElementById("monthSelect");
  const yearSelect = document.getElementById("yearSelect");

  const now = new Date();
  const currentMonth = now.getMonth() + 1;
  const currentYear = now.getFullYear();

  for (let m = 1; m <= 12; m++) {
    const option = document.createElement("option");
    option.value = m.toString().padStart(2, '0');
    option.textContent = m.toString().padStart(2, '0');
    if (m === currentMonth) option.selected = true;
    monthSelect.appendChild(option);
  }

  for (let y = 2020; y <= currentYear + 1; y++) {
    const option = document.createElement("option");
    option.value = y;
    option.textContent = y;
    if (y === currentYear) option.selected = true;
    yearSelect.appendChild(option);
  }

  // === 6. Export PDF ===
  document.getElementById("exportPdfButton").addEventListener("click", async () => {
    const doc = new window.jspdf.jsPDF({ unit: "mm", format: "a4" });
    const marginLeft = 15;
    const pageWidth = doc.internal.pageSize.getWidth();
    let y = 20;

    const selectedDateInput = document.getElementById('selectedDate');
    const selectedDate = new Date(selectedDateInput.value);

    const formattedDateDisplay = selectedDate.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
    const formattedDateFile = selectedDate.toLocaleDateString('fr-FR', { month: '2-digit', year: 'numeric' }).replace(/\//g, '-');

    // === Titre
    doc.setFont("helvetica", "bold");
    doc.setFontSize(14);
    doc.text(`Résumé mensuel - ${entityNameFromPHP}`, pageWidth / 2, y, { align: "center" });
    y += 8;
    doc.setFontSize(11);
    doc.setFont("helvetica", "normal");
    doc.text(`Période : ${formattedDateDisplay}`, pageWidth / 2, y, { align: "center" });
    y += 12;

    // === Fonction utilitaire : construire corps de tableau PDF
    function buildPdfBody(groupedData) {
      return Object.entries(groupedData).map(([date, items]) => {
        let total = 0, cash = 0, card = 0, check = 0, count = 0;
        items.forEach(item => {
          const amount = parseFloat(item.unit_price) * item.quantity;
          total += amount;
          count += item.quantity;
          switch (item.payment_method) {
            case 'cash': cash += amount; break;
            case 'card': card += amount; break;
            case 'check': check += amount; break;
          }
        });
        return [date, count, total.toFixed(2), cash.toFixed(2), card.toFixed(2), check.toFixed(2)];
      });
    }

    // === Ventes
    doc.setFont("helvetica", "bold");
    doc.text("Ventes du mois", marginLeft, y);
    y += 5;
    doc.autoTable({
      startY: y,
      head: [['Date', 'Articles', 'Total (€)', 'Espèces (€)', 'Carte (€)', 'Chèque (€)']],
      body: buildPdfBody(groupedSales),
      styles: { fontSize: 9, textColor: 33 },
      headStyles: { fillColor: [240, 240, 240], textColor: 50, halign: "center" },
      margin: { left: marginLeft, right: marginLeft },
      theme: 'grid'
    });
    y = doc.lastAutoTable.finalY + 10;

    // === Achats
    doc.setFont("helvetica", "bold");
    doc.text("Achats du mois", marginLeft, y);
    y += 5;
    doc.autoTable({
      startY: y,
      head: [['Date', 'Articles', 'Total (€)', 'Espèces (€)', 'Carte (€)', 'Chèque (€)']],
      body: buildPdfBody(groupedPurchases),
      styles: { fontSize: 9, textColor: 33 },
      headStyles: { fillColor: [240, 240, 240], textColor: 50, halign: "center" },
      margin: { left: marginLeft, right: marginLeft },
      theme: 'grid'
    });
    y = doc.lastAutoTable.finalY + 12;

    // === Récapitulatif URSSAF
    doc.setFont("helvetica", "bold");
    doc.setFontSize(12);
    doc.text("Récapitulatif URSSAF", pageWidth / 2, y, { align: "center" });
    y += 6;
    doc.autoTable({
      startY: y,
      body: [
        ['Total des ventes', `${salesResult.totals.total.toFixed(2)} €`],
        ['Total des achats', `${purchasesResult.totals.total.toFixed(2)} €`],
        ['Espèces', `${salesResult.totals.cash.toFixed(2)} €`],
        ['Carte', `${salesResult.totals.card.toFixed(2)} €`],
        ['Chèque', `${salesResult.totals.check.toFixed(2)} €`],
        ['Montant à déclarer URSSAF', `${salesResult.totals.total.toFixed(2)} €`]
      ],
      theme: 'plain',
      styles: { fontSize: 10, cellPadding: 2 },
      columnStyles: {
        0: { fontStyle: 'bold', halign: 'left' },
        1: { halign: 'right' }
      },
      margin: { left: marginLeft, right: marginLeft }
    });

    // === Sauvegarde
    const filename = `${formattedDateFile}-URSSAF-${entityNameFromPHP}.pdf`;
    doc.save(filename);
  });
});
