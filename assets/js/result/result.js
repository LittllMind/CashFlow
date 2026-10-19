document.addEventListener("DOMContentLoaded", function () {
    const salesTableBody = document.querySelector("#sales-table tbody");
    const purchasesTableBody = document.querySelector("#purchases-table tbody");

    // S'assurer que filteredArticles est bien un tableau
    const articles = Array.isArray(filteredArticles)
        ? filteredArticles
        : Object.values(filteredArticles);

    let hasSales = false;
    let hasPurchases = false;

    articles.forEach((item) => {
        const row = document.createElement("tr");
        const heure = formatHeure(item.created_at);

        const articleLabel = `${item.category} - ${item.subcategory}`;
        const quantity = item.quantity;
        const unitPrice = parseFloat(item.unit_price).toFixed(2);
        const totalPrice = (item.unit_price * item.quantity).toFixed(2);

        if (item.sale_id) {
            hasSales = true;
            row.innerHTML = `
        <td>${heure}</td>
        <td>${escapeHTML(articleLabel)}</td>
        <td>${quantity}</td>
        <td>${unitPrice} €</td>
        <td>${totalPrice} €</td>
        <td>${escapeHTML(item.comment || '')}</td>
      `;
            salesTableBody.appendChild(row);
        }

        if (item.purchase_id) {
            hasPurchases = true;
            row.innerHTML = `
        <td>${heure}</td>
        <td>${escapeHTML(articleLabel)}</td>
        <td>${quantity}</td>
        <td>${unitPrice} €</td>
        <td>${totalPrice} €</td>
        <td></td>
      `;
            purchasesTableBody.appendChild(row);
        }
    });

    if (!hasSales) {
        salesTableBody.innerHTML = `<tr><td colspan="6"><p class="no-articles">Aucun article vendu ce jour.</p></td></tr>`;
    }

    if (!hasPurchases) {
        purchasesTableBody.innerHTML = `<tr><td colspan="6"><p class="no-articles">Aucun article acheté ce jour.</p></td></tr>`;
    }

    function formatHeure(dateStr) {
        const date = new Date(dateStr);
        if (monthViewCheckbox === 'true') {
            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
        }
        return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    }

    function escapeHTML(str) {
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
document.getElementById("export-pdf-btn").addEventListener("click", function () {
    const metaGrid = document.querySelector("#meta-grid");
    const tableColumns = document.querySelector(".table-columns");
    const selectedDateInput = document.getElementById('selectedDate');

    if (!metaGrid || !tableColumns || !selectedDateInput) {
        alert("Les éléments nécessaires ne sont pas trouvés dans la page.");
        return;
    }

    const selectedDate = new Date(selectedDateInput.value);
    const formattedDate = selectedDate.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }); // ex: 01/06/2025

    const safeDate = formattedDate.replace(/\//g, '-'); // ex: 01-06-2025
    const filename = `${safeDate} - ${entityNameFromPHP}.pdf`;

    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF("p", "mm", "a4");
    const margin = 10;
    const pageWidth = pdf.internal.pageSize.getWidth();
    const usableWidth = pageWidth - margin * 2;

    html2canvas(tableColumns).then((tableCanvas) => {
        const tableImg = tableCanvas.toDataURL("image/png");
        const tableRatio = tableCanvas.width / tableCanvas.height;
        const tableWidth = usableWidth;
        const tableHeight = tableWidth / tableRatio;

        let currentY = margin;
        pdf.addImage(tableImg, "PNG", margin, currentY, tableWidth, tableHeight);
        currentY += tableHeight + 5;

        html2canvas(metaGrid).then((metaCanvas) => {
            const metaImg = metaCanvas.toDataURL("image/png");
            const metaRatio = metaCanvas.width / metaCanvas.height;
            const metaWidth = usableWidth;
            const metaHeight = metaWidth / metaRatio;

            if (currentY + metaHeight > pdf.internal.pageSize.getHeight() - margin) {
                pdf.addPage();
                currentY = margin;
            }

            pdf.addImage(metaImg, "PNG", margin, currentY, metaWidth, metaHeight);

            pdf.save(filename);
        });
    });
});




document.addEventListener('DOMContentLoaded', function () {
    const selectedDateInput = document.getElementById('selectedDate');
    const previousDayButton = document.getElementById('previousDayButton');
    const nextDayButton = document.getElementById('nextDayButton');
    const entityNameInput = document.querySelector('input[name="entityName"]');
    const monthViewCheckboxInput = document.querySelector('input[name="monthViewCheckbox"]');

    // Sécurité : vérifier que tous les éléments existent
    if (!selectedDateInput || !entityNameInput || !monthViewCheckboxInput) {
        console.error("Certains éléments du formulaire sont manquants.");
        return;
    }

    nextDayButton.addEventListener('click', (e) => {
        e.preventDefault();

        const currentDate = new Date(selectedDateInput.value);
        currentDate.setDate(currentDate.getDate() + 1);

        const newDate = currentDate.toISOString().split('T')[0]; // format YYYY-MM-DD
        const entityName = entityNameInput.value;
        const monthView = monthViewCheckboxInput.checked;

        if (entityName === 'FRIPE') {
            window.location.href = `index.php?action=dayFripeResult&date=${newDate}&monthView=${monthView}`;
        } else if (entityName === 'BROC') {
            window.location.href = `index.php?action=dayBrocResult&date=${newDate}&monthView=${monthView}`;
        }
    });

    previousDayButton.addEventListener('click', (e) => {
        e.preventDefault();

        const currentDate = new Date(selectedDateInput.value);
        currentDate.setDate(currentDate.getDate() - 1);

        const newDate = currentDate.toISOString().split('T')[0];
        const entityName = entityNameInput.value;
        const monthView = monthViewCheckboxInput.checked;

        if (entityName === 'FRIPE') {
            window.location.href = `index.php?action=dayFripeResult&date=${newDate}&monthView=${monthView}`;
        } else if (entityName === 'BROC') {
            window.location.href = `index.php?action=dayBrocResult&date=${newDate}&monthView=${monthView}`;
        }
    });
});

