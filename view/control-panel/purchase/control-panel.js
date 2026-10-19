document.addEventListener("DOMContentLoaded", function() {
    const dateTitle = document.querySelector('.sp-header-title');
    const hiddenDate = document.getElementById('hiddenSelectedDate');
    if (dateTitle && hiddenDate && hiddenDate.value) {
        dateTitle.textContent = ppFormatFrenchDate(hiddenDate.value);
    }

    const dateInput = document.getElementById("selectedDate");
    const prevBtn = document.getElementById("purchasePrevDay");
    const nextBtn = document.getElementById("purchaseNextDay");

    function fmt(d) { return d.toISOString().split("T")[0]; }

    if (prevBtn) prevBtn.addEventListener("click", function() {
        let d = new Date(dateInput.value); d.setDate(d.getDate() - 1);
        window.location.href = `index.php?action=purchasePanel&date=${fmt(d)}`;
    });
    if (nextBtn) nextBtn.addEventListener("click", function() {
        let d = new Date(dateInput.value); d.setDate(d.getDate() + 1);
        window.location.href = `index.php?action=purchasePanel&date=${fmt(d)}`;
    });

    /* Tri récap articles */
    const artTable = document.querySelector('.sp-table--articles');
    if (artTable) {
        const tbody = artTable.querySelector('tbody');
        const headers = artTable.querySelectorAll('.sp-sortable');
        let sortDir = { qty: -1, amount: -1 };
        headers.forEach(th => {
            th.addEventListener('click', function() {
                const key = this.dataset.sort;
                sortDir[key] *= -1;
                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.sort((a, b) => {
                    const av = parseFloat(a.dataset[key]) || 0;
                    const bv = parseFloat(b.dataset[key]) || 0;
                    return (av - bv) * sortDir[key];
                });
                rows.forEach(r => tbody.appendChild(r));
                headers.forEach(h => h.querySelector('.sp-sort-arrow').textContent = '⇅');
                this.querySelector('.sp-sort-arrow').textContent = sortDir[key] === 1 ? '↑' : '↓';
            });
        });
    }
});

function ppFormatFrenchDate(dateStr) {
    const d = new Date(dateStr + 'T12:00:00');
    const days = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
    const months = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    return days[d.getDay()] + ' ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}

function ppConfirmDelete(purchaseId, amount, time) {
    const msg = `Supprimer l'achat de ${time} (${amount.toFixed(2).replace('.', ',')} €) ?`;
    if (confirm(msg)) {
        window.location.href = `index.php?action=deletePurchase&purchase_id=${purchaseId}`;
    }
}
