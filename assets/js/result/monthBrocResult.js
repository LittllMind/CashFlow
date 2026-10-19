document.addEventListener("DOMContentLoaded", function () {
  const exportBtn = document.getElementById("exportPdfButton");
  if (!exportBtn) return;

  exportBtn.addEventListener("click", function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: "mm", format: "a4" });
    const marginLeft = 15;
    const pageWidth = doc.internal.pageSize.getWidth();
    let y = 15;

    const monthLabel = typeof entityNameFromPHP !== "undefined" ? entityNameFromPHP : "BROC";
    doc.setFont("helvetica", "bold");
    doc.setFontSize(14);
    doc.text(`Résumé mensuel - ${monthLabel}`, pageWidth / 2, y, { align: "center" });
    y += 8;

    // Totaux (calculés depuis closures)
    let totalSales = 0, totalPurchases = 0, totalCash = 0, totalCard = 0, totalCheque = 0, totalItems = 0;
    closures.forEach(c => {
      totalSales += parseFloat(c.total_sales || 0);
      totalPurchases += parseFloat(c.total_purchases || 0);
      totalCash += parseFloat(c.total_cash || 0);
      totalCard += parseFloat(c.total_card || 0);
      totalCheque += parseFloat(c.total_cheque || 0);
      totalItems += parseInt(c.total_items || 0, 10);
    });

    doc.setFontSize(11);
    doc.setFont("helvetica", "normal");
    doc.text(`Période : ${document.querySelector("h2").textContent.replace("Brocante — mois de ", "")}`, pageWidth / 2, y, { align: "center" });
    y += 8;

    doc.setFontSize(10);
    doc.text(`Total ventes : ${totalSales.toFixed(2)} €`, marginLeft, y);
    y += 5;
    doc.text(`Total achats : ${totalPurchases.toFixed(2)} €`, marginLeft, y);
    y += 5;
    doc.text(`Solde net : ${(totalSales - totalPurchases).toFixed(2)} €`, marginLeft, y);
    y += 5;
    doc.text(`Articles vendus : ${totalItems}`, marginLeft, y);
    y += 10;

    // Tableau détaillé
    doc.setFont("helvetica", "bold");
    doc.text("Clôtures journalières", marginLeft, y);
    y += 5;

    const body = closures.map(c => [
      c.closure_date,
      c.closure_number,
      c.locked_at ? "Verrouillée" : "Brouillon",
      parseFloat(c.total_sales || 0).toFixed(2),
      parseFloat(c.total_purchases || 0).toFixed(2),
      (parseFloat(c.total_sales || 0) - parseFloat(c.total_purchases || 0)).toFixed(2)
    ]);

    doc.autoTable({
      startY: y,
      head: [["Date", "N° clôture", "État", "Ventes", "Achats", "Net"]],
      body: body,
      styles: { fontSize: 9, textColor: 33 },
      headStyles: { fillColor: [240, 240, 240], textColor: 50, halign: "center" },
      margin: { left: marginLeft, right: marginLeft },
      theme: "grid"
    });

    doc.save(`${monthLabel.toLowerCase()}-resume-mensuel.pdf`);
  });
});
