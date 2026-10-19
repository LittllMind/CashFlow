
var selectedCategory;
var selectedSubcategory;

document.getElementById('total_amount').addEventListener('input', function () {
    var totalAmount = parseFloat(document.getElementById('total_amount').value);
    var amountPaidDiv = document.getElementById('amount_paid_div');
    var changeDiv = document.getElementById('change_div');

    // Si le total_amount est différent de zéro, afficher les champs amount_paid et change, sinon, les cacher
    if (totalAmount != 0) {
        amountPaidDiv.hidden = false;
        changeDiv.hidden = false;
    } else {
        amountPaidDiv.hidden = true;
        changeDiv.hidden = true;
    }
});

document.getElementById('amount_paid').addEventListener('input', function () {
    var amountPaid = parseFloat(document.getElementById('amount_paid').value);
    var totalAmount = parseFloat(document.getElementById('total_amount').value);
    var change = amountPaid - totalAmount;
    document.getElementById('change').value = change;
});

function toggleCategories() {
    const addArticleButton = document.getElementById("addArticleButton");
    var categoryDiv = document.getElementById("categoryButtons");
    var purchaserForm = document.getElementById("purchaseForm");
    if (categoryDiv.hidden) {
        categoryDiv.hidden = false;
        addArticleButton.hidden = true;
        purchaseForm.hidden = true;
    } else {
        categoryDiv.hidden = true;
        document.getElementById("subcategoryButtons").hidden = true;
        document.getElementById("articleTable").hidden = false;
        purchaseForm.hidden = false;
    }
}

function selectCategory(category) {

    var subcategoriesDiv = document.getElementById("subcategoryButtons");
    subcategoriesDiv.innerHTML = ""; // Vide le contenu précédent

    selectedCategory = category;

    var backButton = document.createElement("button");
    backButton.type = "button";
    backButton.className = "btn btn-warning";
    backButton.textContent = "Retour";
    backButton.onclick = goBackToCategories;
    subcategoriesDiv.appendChild(backButton);

    // if (category === 'Enfants') {
    //     // Définir les sous-catégories et les prix pour la catégorie "Enfants"
    //     const childSubCategories = [
    //         { name: 'Jouet', price: 0.5 },
    //         { name: 'Jouet', price: 1 },
    //         { name: 'Jouet', price: 2 },
    //         { name: 'Jouet', price: 5 }
    //     ];

    //     // Créer et ajouter les boutons pour les sous-catégories de "Enfants"
    //     childSubCategories.forEach(function (subCategory) {
    //         var button = document.createElement("button");
    //         button.type = "button";
    //         button.className = "btn btn-info";
    //         button.textContent = `${subCategory.name} - ${subCategory.price}€`;
    //         button.onclick = function () { selectToyQuantity(subCategory.name, subCategory.price); };
    //         subcategoriesDiv.appendChild(button);
    //     });
    // }

    // Ajouter les boutons pour les sous-catégories de la catégorie sélectionnée
    subcategories.forEach(function (subcategory) {
        if (subcategory.category_name === category) {
            var button = document.createElement("button");
            button.type = "button";
            button.className = "btn btn-info";
            button.textContent = subcategory.subcategory_name;
            button.onclick = function () { selectSubcategory(subcategory.subcategory_name); };
            subcategoriesDiv.appendChild(button);
        }
    });

    subcategoriesDiv.hidden = false;
    document.getElementById("categoryButtons").hidden = true;
}

// function selectToyQuantity(itemName, itemPrice) {
//     var quantityButtonsDiv = document.getElementById("quantityButtons");
//     quantityButtonsDiv.innerHTML = ""; // Vide le contenu précédent

//     var quantities = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // Quantités disponibles

//     var backButton = document.createElement("button");
//     backButton.type = "button";
//     backButton.className = "btn btn-warning";
//     backButton.textContent = "Retour";
//     backButton.onclick = goBackToSubcategories;
//     quantityButtonsDiv.appendChild(backButton);

//     quantities.forEach(function (quantity) {
//         var buttonContainer = document.createElement("div");
//         buttonContainer.className = "col-4";

//         var button = document.createElement("button");
//         button.type = "button";
//         button.className = "btn btn-info";
//         button.textContent = quantity;
//         button.addEventListener('click', () => validateToyItem(itemName, itemPrice, quantity));
//         quantityButtonsDiv.appendChild(button);
//     });

//     quantityButtonsDiv.hidden = false;
//     document.getElementById("subcategoryButtons").hidden = true;
// }

// function validateToyItem(itemName, itemPrice, quantity) {
//     var articleTable = document.getElementById("articleTable");
//     var tbody = articleTable.getElementsByTagName("tbody")[0];
//     var newRow = tbody.insertRow();

//     const quantityButtonsDiv = document.getElementById("quantityButtons");
//     quantityButtonsDiv.hidden = true;

//     // Création du formulaire d'ajout d'article
//     var form = document.createElement("form");
//     form.setAttribute("action", "index.php?action=saveArticle");
//     form.setAttribute("method", "post");

//     // Ajout de l'input purchase_id au formulaire
//     var purchaseIdInput = document.createElement("input");
//     purchaseIdInput.setAttribute("type", "hidden");
//     purchaseIdInput.setAttribute("name", "purchase_id");
//     purchaseIdInput.setAttribute("value", purchase_id);
//     form.appendChild(purchaseIdInput);

//     var categoryInput = document.createElement("input");
//     categoryInput.setAttribute("type", "hidden");
//     categoryInput.setAttribute("name", "category");
//     categoryInput.setAttribute("value", selectedCategory);
//     form.appendChild(categoryInput);

//     var subcategoryInput = document.createElement("input");
//     subcategoryInput.setAttribute("type", "hidden");
//     subcategoryInput.setAttribute("name", "subcategory");
//     subcategoryInput.setAttribute("value", itemName);
//     form.appendChild(subcategoryInput);

//     var quantityInput = document.createElement("input");
//     quantityInput.setAttribute("type", "hidden");
//     quantityInput.setAttribute("name", "quantity");
//     quantityInput.setAttribute("value", quantity);
//     form.appendChild(quantityInput);

//     var priceInput = document.createElement("input");
//     priceInput.setAttribute("type", "hidden");
//     priceInput.setAttribute("name", "unit_price");
//     priceInput.setAttribute("value", itemPrice);
//     form.appendChild(priceInput);

//     var commentInput = document.createElement("input");
//     commentInput.setAttribute("type", "hidden");
//     commentInput.setAttribute("name", "comment");
//     commentInput.setAttribute("value", document.getElementById("comment").value);
//     form.appendChild(commentInput);

//     // Ajout de la catégorie et de la sous-catégorie dans les cellules correspondantes
//     var categoryCell = newRow.insertCell(0);
//     categoryCell.textContent = selectedCategory;

//     var subcategoryCell = newRow.insertCell(1);
//     subcategoryCell.textContent = itemName;

//     // Ajout de la quantité dans une cellule
//     var quantityCell = newRow.insertCell(2);
//     quantityCell.textContent = quantity;

//     var priceCell = newRow.insertCell(3);
//     priceCell.textContent = itemPrice;

//     // Ajout du formulaire à la dernière cellule de la ligne
//     var validationCell = newRow.insertCell(4);
//     validationCell.appendChild(form);

//     // Soumission automatique du formulaire
//     form.submit();

//     const purchaseForm = document.getElementById("purchaseForm");
//     purchaseForm.hidden = false;
// }

function selectSubcategory(subcategory) {
    var subcategoriesDiv = document.getElementById("subcategoryButtons");
    subcategoriesDiv.hidden = true;
    selectedSubcategory = subcategory;
    var articleTable = document.getElementById("articleTable");
    articleTable.hidden = false;

    var quantityButtonsDiv = document.getElementById("quantityButtons");
    quantityButtonsDiv.innerHTML = ""; // Vide le contenu précédent

    var backButton = document.createElement("button");
    backButton.type = "button";
    backButton.className = "btn btn-warning";
    backButton.textContent = "Retour";
    backButton.onclick = goBackToSubcategories;
    quantityButtonsDiv.appendChild(backButton);


    for (var i = 1; i <= 40; i++) {
        var buttonContainer = document.createElement("div");
        buttonContainer.className = "col-4";

        var button = document.createElement("button");
        button.type = "button";
        button.className = "btn btn-info";
        button.textContent = i;
        button.onclick = function () { selectQuantity(this.textContent, document.getElementById("articleForm")); };
        buttonContainer.appendChild(button);

        quantityButtonsDiv.appendChild(buttonContainer);
    }


    quantityButtonsDiv.hidden = false;
}

function selectQuantity(quantity, form) {
    var priceButtonsDiv = document.getElementById("priceButton");
    var priceButtonContainer = document.getElementById("priceButtonContainer");
    priceButtonContainer.innerHTML = ""; // Vide le contenu précédent

    var quantityInput = document.getElementById("quantity");
    quantityInput.value = quantity;

    var backButton = document.createElement("button");
    backButton.type = "button";
    backButton.className = "btn btn-warning";
    backButton.textContent = "Retour";
    backButton.onclick = goBackToQuantities;
    priceButtonContainer.appendChild(backButton);

    for (let price = 0; price <= 20; price++) {
        var buttonContainer = document.createElement("div");
        buttonContainer.className = "col-4";

        var button = document.createElement("button");
        button.type = "button";
        button.className = "btn btn-info priceButton";
        button.textContent = price;
        button.addEventListener('click', () => selectPrice(price));
        buttonContainer.appendChild(button);

        priceButtonContainer.appendChild(buttonContainer);
    }

    priceButtonsDiv.hidden = false;
    document.getElementById("quantityButtons").hidden = true;
}



function setPrice() {
    var price = document.getElementById("price").value;
    selectPrice(price);
}

function selectPrice(price) {
    var articleTable = document.getElementById("articleTable");
    var tbody = articleTable.getElementsByTagName("tbody")[0];
    var newRow = tbody.insertRow();

    const priceButton = document.getElementById("priceButton");
    priceButton.hidden = true;

    // Création du formulaire d'ajout d'article
    var form = document.createElement("form");
    form.setAttribute("action", "index.php?action=saveArticle");
    form.setAttribute("method", "post");

    // Ajout de l'input purchase_id au formulaire
    var purchaseIdInput = document.createElement("input");
    purchaseIdInput.setAttribute("type", "hidden");
    purchaseIdInput.setAttribute("name", "purchase_id");
    purchaseIdInput.setAttribute("value", purchase_id);
    form.appendChild(purchaseIdInput);

    var categoryInput = document.createElement("input");
    categoryInput.setAttribute("type", "hidden");
    categoryInput.setAttribute("name", "category");
    categoryInput.setAttribute("value", selectedCategory);
    form.appendChild(categoryInput);

    var subcategoryInput = document.createElement("input");
    subcategoryInput.setAttribute("type", "hidden");
    subcategoryInput.setAttribute("name", "subcategory");
    subcategoryInput.setAttribute("value", selectedSubcategory);
    form.appendChild(subcategoryInput);

    var quantityInput = document.createElement("input");
    quantityInput.setAttribute("type", "hidden");
    quantityInput.setAttribute("name", "quantity");
    quantityInput.setAttribute("value", document.getElementById("quantity").value);
    form.appendChild(quantityInput);

    var priceInput = document.createElement("input");
    priceInput.setAttribute("type", "hidden");
    priceInput.setAttribute("name", "unit_price");
    priceInput.setAttribute("value", price);
    form.appendChild(priceInput);

    var commentInput = document.createElement("input");
    commentInput.setAttribute("type", "hidden");
    commentInput.setAttribute("name", "comment");
    commentInput.setAttribute("value", document.getElementById("comment").value);
    form.appendChild(commentInput);

    // Ajout de la catégorie et de la sous-catégorie dans les cellules correspondantes
    var categoryCell = newRow.insertCell(0);
    categoryCell.textContent = selectedCategory;

    var subcategoryCell = newRow.insertCell(1);
    subcategoryCell.textContent = selectedSubcategory;

    // Ajout de la quantité dans une cellule
    var quantityCell = newRow.insertCell(2);
    quantityCell.textContent = document.getElementById("quantity").value;

    var priceCell = newRow.insertCell(3);
    priceCell.textContent = price;

    // Ajout du formulaire à la dernière cellule de la ligne
    var validationCell = newRow.insertCell(4);
    validationCell.appendChild(form);

    // Soumission automatique du formulaire
    form.submit();

    const purchaseForm = document.getElementById("purchaseForm");
    purchaseForm.hidden = false;
}

function sendArticleData(category, subcategory, quantity, price, comment) {
    var xhr = new XMLHttpRequest();
    var url = 'index.php'; // Mettez l'URL de votre contrôleur ici
    var params = 'action=saveArticle&category=' + encodeURIComponent(category) +
        '&subcategory=' + encodeURIComponent(subcategory) +
        '&price=' + encodeURIComponent(price) +
        '&quantity=' + encodeURIComponent(quantity) +
        '&comment=' + encodeURIComponent(comment); // Ajoutez les autres paramètres ici

    xhr.open('POST', url, true); // Utilisation de la méthode asynchrone
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                console.log('Article enregistré avec succès !');
                window.location.reload(); // Recharge la page après l'envoi des données
            } else {
                console.error('Erreur lors de l\'enregistrement de l\'article');
            }
        }
    };

    xhr.send(params);
}


function deleteArticle(id) {
    // Confirmation de la suppression de l'article
    if (confirm("Êtes-vous sûr de vouloir supprimer cet article ?")) {
        // Envoi de la requête AJAX pour supprimer l'article
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "index.php?action=deleteArticle", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Rechargement de la page après la suppression
                    window.location.reload();
                } else {
                    // Gestion des erreurs
                    console.error("Erreur lors de la suppression de l'article");
                }
            }
        };
        // Envoi des données de l'article à supprimer
        xhr.send("purchase_id=" + encodeURIComponent(id));
    }
}

// ------------------------ GO BACK ------------------------ //

function goBackToAddArticle() {
    document.getElementById("categoryButtons").hidden = true;
    document.getElementById("addArticleButton").hidden = false;
}

function goBackToCategories() {
    document.getElementById("subcategoryButtons").hidden = true;
    document.getElementById("categoryButtons").hidden = false;
}

function goBackToSubcategories() {
    document.getElementById("quantityButtons").hidden = true;
    document.getElementById("subcategoryButtons").hidden = false;
}

function goBackToQuantities() {
    document.getElementById("priceButton").hidden = true;
    document.getElementById("quantityButtons").hidden = false;
}



// Ouvrir le pop-up
function openPopup(articleId, currentQuantity, currentPrice) {
    document.getElementById('popupOverlay').style.display = 'block';
    document.getElementById('popup').style.display = 'block';
    document.getElementById('articleId').value = articleId;
    document.getElementById('quantity').value = currentQuantity;
    document.getElementById('unit_price').value = currentPrice;
}

// Fermer le pop-up
function closePopup() {
    document.getElementById('popupOverlay').style.display = 'none';
    document.getElementById('popup').style.display = 'none';
}

// Soumettre le formulaire de mise à jour
function submitUpdateForm() {
    document.getElementById('updateForm').submit();
}

// Fermer le pop-up lorsque vous cliquez en dehors de celui-ci
window.onclick = function (event) {
    const popup = document.getElementById('popup');
    if (event.target == document.getElementById('popupOverlay')) {
        closePopup();
    }
}

