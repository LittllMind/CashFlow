
document.addEventListener('DOMContentLoaded', () => {
  // Remplir les cartes de statistiques évoluées
  const periods = {
    daily: { label: 'Jour', cardId: 'stats-card-daily' },
    weekly: { label: 'Semaine', cardId: 'stats-card-weekly' },
    monthly: { label: 'Mois', cardId: 'stats-card-monthly' }
  };

  // Calculer les tendances (simplifié : comparaison avec la période précédente)
  const amounts = {
    daily: salesStats.daily?.total_amount || 0,
    weekly: salesStats.weekly?.total_amount || 0,
    monthly: salesStats.monthly?.total_amount || 0
  };

  // Déterminer la tendance (up, down, neutral)
  function computeTrend(current, previous) {
    if (previous === 0) return 'neutral';
    const change = ((current - previous) / previous) * 100;
    if (change > 5) return 'up';
    if (change < -5) return 'down';
    return 'neutral';
  }

  const trends = {
    daily: computeTrend(amounts.daily, amounts.weekly / 7), // Jour vs moyenne quotidienne de la semaine
    weekly: computeTrend(amounts.weekly, amounts.monthly / 4), // Semaine vs moyenne hebdo du mois
    monthly: 'neutral' // Mois : pas de référence
  };

  // Ratio articles / vente
  function computeRatio(articles, sales) {
    if (sales === 0) return '–';
    const ratio = (articles / sales).toFixed(1);
    return `${ratio} art/v.`;
  }

  // Sparkline : largeur proportionnelle au montant relatif (max = mensuel)
  const maxAmount = Math.max(amounts.daily, amounts.weekly, amounts.monthly);

  for (const [key, config] of Object.entries(periods)) {
    const data = salesStats[key];
    if (!data) continue;

    const card = document.getElementById(config.cardId);
    if (!card) continue;

    // Total
    const valueEl = card.querySelector('.stats-card-value');
    if (valueEl) {
      valueEl.textContent = data.total_amount > 0 ? data.total_amount.toFixed(2) + ' €' : '–';
    }

    // Tendance badge
    const trendBadge = card.querySelector('.stats-trend-badge');
    if (trendBadge) {
      const trend = trends[key];
      trendBadge.className = `stats-trend-badge trend-${trend}`;
      // Le texte sera généré via CSS content (▲/▼/–)
    }

    // Sparkline bar
    const sparklineBar = card.querySelector('.sparkline-bar');
    if (sparklineBar && maxAmount > 0) {
      const widthPercent = (data.total_amount / maxAmount) * 100;
      sparklineBar.style.width = `${widthPercent}%`;
    }

    // Ventes et articles
    const ventesEl = card.querySelector('.stats-detail-item:nth-child(1) .stats-detail-number');
    if (ventesEl) {
      ventesEl.textContent = data.total_sales > 0 ? data.total_sales : '–';
    }
    const articlesEl = card.querySelector('.stats-detail-item:nth-child(2) .stats-detail-number');
    if (articlesEl) {
      articlesEl.textContent = data.total_articles > 0 ? data.total_articles : '–';
    }

    // Ratio articles/vente
    const ratioEl = card.querySelector('.stats-ratio');
    if (ratioEl) {
      ratioEl.textContent = computeRatio(data.total_articles, data.total_sales);
    }

    // Moyenne prix/article
    const avgEl = card.querySelector('.stats-average');
    if (avgEl) {
      avgEl.textContent = data.total_articles > 0 ? data.avg_price_per_article.toFixed(2) + ' €/art.' : '– €/art.';
    }
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const rows = document.querySelectorAll(".striped-table tr");
  const data = {};
  let totalGlobalQuantity = 0;
  let totalGlobalAmount = 0;

  for (let i = 1; i < rows.length; i++) {
    const cells = rows[i].querySelectorAll("td");
    if (cells.length < 4) continue;

    const category = cells[0].innerText.trim();
    const subcategory = cells[1].innerText.trim();
    const quantityStr = cells[2].innerText.trim().split(" ")[0];
    const unitPriceStr = cells[3].innerText.trim().replace("€", "").replace(",", ".");

    const quantity = parseFloat(quantityStr);
    const unitPrice = parseFloat(unitPriceStr);

    if (!category || !subcategory || isNaN(quantity) || isNaN(unitPrice)) continue;

    const key = `${category}||${subcategory}`;

    if (!data[key]) {
      data[key] = {
        category,
        subcategory,
        totalQuantity: 0,
        totalWeightedPrice: 0
      };
    }

    data[key].totalQuantity += quantity;
    data[key].totalWeightedPrice += unitPrice * quantity;
  }

  const table = document.createElement("table");
  table.className = "monthly-summary sortable";

  const thead = document.createElement("thead");
  thead.innerHTML = `
    <tr>
      <th data-sort="text">Catégorie</th>
      <th data-sort="text">Sous-catégorie</th>
      <th data-sort="number">Nb articles</th>
      <th data-sort="number">Prix unitaire moyen</th>
      <th data-sort="number">Montant total</th>
    </tr>
  `;

  const tbody = document.createElement("tbody");

  Object.values(data).forEach(entry => {
    const avg = entry.totalWeightedPrice / entry.totalQuantity;
    const totalAmount = entry.totalQuantity * avg;

    totalGlobalQuantity += entry.totalQuantity;
    totalGlobalAmount += totalAmount;

    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${entry.category}</td>
      <td>${entry.subcategory}</td>
      <td>${entry.totalQuantity}</td>
      <td>${avg.toFixed(2).replace('.', ',')} €</td>
      <td>${totalAmount.toFixed(2).replace('.', ',')} €</td>
    `;
    tbody.appendChild(row);
  });

  // ➕ Ajout ligne de total global
  const avgGlobalUnitPrice = totalGlobalAmount / totalGlobalQuantity;
  const totalRow = document.createElement("tr");
  totalRow.style.fontWeight = "bold";
  totalRow.innerHTML = `
    <td>Total général :</td>
    <td></td>
    <td>${totalGlobalQuantity}</td>
    <td>${avgGlobalUnitPrice.toFixed(2).replace('.', ',')} €</td>
    <td>${totalGlobalAmount.toFixed(2).replace('.', ',')} €</td>
  `;
  tbody.appendChild(totalRow);

  table.appendChild(thead);
  table.appendChild(tbody);
  document.getElementById("monthly-summary-container").appendChild(table);

  makeTableSortable(table);
});

function makeTableSortable(table) {
  const headers = table.querySelectorAll("th");
  headers.forEach((header, index) => {
    header.style.cursor = "pointer";
    header.addEventListener("click", () => {
      const isNumber = header.dataset.sort === "number";
      const direction = header.dataset.direction === "asc" ? "desc" : "asc";
      header.dataset.direction = direction;

      const rows = Array.from(table.querySelectorAll("tbody tr")).filter(
        row => !row.innerText.includes("Total général")
      );

      rows.sort((a, b) => {
        const cellA = a.children[index].innerText.replace("€", "").replace(",", ".");
        const cellB = b.children[index].innerText.replace("€", "").replace(",", ".");

        const valA = isNumber ? parseFloat(cellA) : cellA.toLowerCase();
        const valB = isNumber ? parseFloat(cellB) : cellB.toLowerCase();

        return direction === "asc" ? valA - valB : valB - valA;
      });

      const tbody = table.querySelector("tbody");
      tbody.innerHTML = "";
      rows.forEach(row => tbody.appendChild(row));
      tbody.appendChild(table.querySelector("tr[style*='font-weight']"));
    });
  });
}


async function exportTableToPDF() {
  // Accès à jsPDF depuis le module UMD
  const { jsPDF } = window.jspdf;

  const doc = new jsPDF();

  doc.setFontSize(14);
  doc.text("Résumé mensuel des ventes/achats", 14, 15);

  const table = document.querySelector(".monthly-summary");

  if (!table) {
    alert("Tableau introuvable !");
    return;
  }

  doc.autoTable({
    html: table,
    startY: 25,
    theme: "striped",
    styles: {
      fontSize: 9,
      halign: "center"
    },
    headStyles: {
      fillColor: [50, 50, 50],
      textColor: [255, 255, 255],
      fontStyle: "bold"
    },
    footStyles: {
      fillColor: [230, 230, 230],
      textColor: [0, 0, 0],
      fontStyle: "bold"
    }
  });

  doc.save("bilan-mensuel.pdf");
}

// jour suivant et précédent

document.addEventListener("DOMContentLoaded", () => {
  const dateField = document.getElementById("selectedDate");
  const prevBtn = document.getElementById("prevDay");
  const nextBtn = document.getElementById("nextDay");

  function getDate() {
    return new Date(dateField.value);
  }
  function formatDate(d) {
    return d.toISOString().split("T")[0];
  }

  prevBtn.addEventListener("click", () => {
    let d = getDate();
    d.setDate(d.getDate() - 1);
    window.location.href = `index.php?action=salePanel&date=${formatDate(d)}`;
  });

  nextBtn.addEventListener("click", () => {
    let d = getDate();
    d.setDate(d.getDate() + 1);
    window.location.href = `index.php?action=salePanel&date=${formatDate(d)}`;
  });
});
