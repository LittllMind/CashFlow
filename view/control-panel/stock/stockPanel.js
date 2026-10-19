
function prepareQuickEntry(subcategory, type) {
    document.getElementById('stockSubcategory').value = subcategory;
    document.getElementById('stockEntryType').value = type;
    document.getElementById('stockQty').value = '1';
    document.getElementById('stockComment').value = '';
    if (type === 'donation') {
        document.getElementById('stockPrice').value = '0.00';
    } else if (type === 'purchase') {
        document.getElementById('stockPrice').value = '';
        document.getElementById('stockPrice').focus();
    } else {
        document.getElementById('stockPrice').value = '';
    }
    updatePriceVisibility();
    document.getElementById('stockQty').focus();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updatePriceVisibility() {
    const type = document.getElementById('stockEntryType').value;
    const priceGroup = document.getElementById('priceGroup');
    const priceInput = document.getElementById('stockPrice');
    if (type === 'donation') {
        priceInput.value = '0.00';
        priceGroup.style.opacity = '0.6';
    } else {
        priceGroup.style.opacity = '1';
    }
}

document.getElementById('stockEntryType')?.addEventListener('change', updatePriceVisibility);
document.addEventListener('DOMContentLoaded', updatePriceVisibility);
