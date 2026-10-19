<?php ob_start(); ?>

<div class="control-panel">
    <div class="control-panel-head">
        <?php if (isset($message)): ?>
            <h6 class="alert-message">
                <?= $message ?>
            </h6>
        <?php endif; ?>
    </div>

    <div class="control-panel-form">
        <div class="date-selectors">
            <?php if ($selectedDate): ?>
                <input type="date" id="selectedDate" name="day" value="<?= $selectedDate ?>">
            <?php else: ?>
                <input type="date" id="selectedDate" name="day" value="<?= date('Y-m-d') ?>">
            <?php endif; ?>
        </div>
    </div>



    <div class="control-panel-list">

        <div class="control-panel-list-form">
            <!-- <table>
                <tr>
                    <th style="text-align: center;">Ventes.H</th>
                    <td style="text-align: center;"></td>
                    <th style="text-align: center;">Achats.H</th>
                </tr>
                <tr>
                    <td style="text-align: center;" id="totalSalesAmount"></td>
                    <td></td>
                    <td style="text-align: center;" id="totalPurchasesAmount"></td>
                </tr>
                <tr>
                    <th style="text-align: center;"></th>
                    <th style="text-align: center;">Tresor</th>
                    <th style="text-align: center;"></th>
                </tr>
                <tr>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center;" id="totalBalance"></td>
                    <td style="text-align: center;"></td>
                </tr>
                <tr>
                    <th style="text-align: center;">Extra Broc</th>
                    <td style="text-align: center;"></td>
                    <th style="text-align: center;">Extra Fripe</th>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <input class="col-6" type="text" id="extraBroc" name="extraBroc" oninput="calculate()" /> €
                    </td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center;">
                        <input class="col-6" type="text" id="extraFripe" name="extraFripe" oninput="calculate()" /> €
                    </td>
                </tr>
                <tr>
                    <th style="text-align: center;">Compensation</th>
                    <td style="text-align: center;"></td>
                    <th style="text-align: center;">Compensation</th>
                </tr>
                <tr>
                    <td style="text-align: center;" id="compensationBroc"></td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center;" id="compensationFripe"></td>
                </tr>
                <tr>
                    <th style="text-align: center;">Part</th>
                    <td style="text-align: center;"></td>
                    <th style="text-align: center;">Part</th>
                </tr>
                <tr>
                    <td style="text-align: center;" id="partBroc"></td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center;" id="partFripe"></td>
                </tr>
                <tr>
                    <th style="text-align: center;">A déclarer</th>
                    <td style="text-align: center;"></td>
                    <th style="text-align: center;">A déclarer</th>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <input class="col-6" type="text" id="montantADeclarerBroc" name="montantADeclarerBroc"
                            onchange="afficherMontants()" />
                    </td>
                    <td></td>
                    <td style="text-align: center;">

                        <input class="col-6" type="text" id="montantADeclarerFripe" name="montantADeclarerFripe"
                            onchange="afficherMontants()" />
                    </td>
                </tr>
                <tr>
                    <th style="text-align: center;">% URSAAF</th>
                    <td></td>
                    <th style="text-align: center;">% URSAAF</th>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <p style="text-align: center;" id="montantURSAFBroc"></p>
                    </td>
                    <td style="text-align: center;">
                        <input class="col-6" type="number" id="pourcentageADeclarer" name="pourcentageADeclarer"
                            value="25" onchange="afficherMontants()" /> %

                    </td>
                    <td style="text-align: center;">
                        <p style="text-align: center;" id="montantURSAFFripe"></p>
                    </td>
                </tr>
                <tr>
                    <th style="text-align: center;">Montant encaissé</th>
                    <th></th>
                    <th style="text-align: center;">Montant encaissé</th>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <input class="col-6" type="text" id="montantEncaisseBroc" name="montantEncaisseBroc"
                            onchange="afficherMontants()" />
                    </td>
                    <td>
                    </td>
                    <td style="text-align: center;">
                        <input class="col-6" type="text" id="montantEncaisseFripe" name="montantEncaisseFripe"
                            onchange="afficherMontants()" />
                    </td>
                </tr>
            </table> -->

            <!-- <script>
                const totalSalesAmount = <?= $totalHistoricSalesAmount['total_sales_amount'] ?>;
                const totalPurchasesAmount = <?= $totalHistoricPurchasesAmount['total_purchases_amount'] ?>;

                function calculate() {
                    const extraBroc = parseFloat(document.getElementById('extraBroc').value) || 0;
                    const extraFripe = parseFloat(document.getElementById('extraFripe').value) || 0;

                    const netProfit = totalSalesAmount - totalPurchasesAmount;
                    const totalBalance = netProfit;

                    const remainingProfitAfterExtras = netProfit - extraBroc - extraFripe;
                    const shareBroc = (remainingProfitAfterExtras / 2) + extraFripe;
                    const shareFripe = (remainingProfitAfterExtras / 2) + extraBroc;

                    document.getElementById('totalSalesAmount').textContent = totalSalesAmount + ' €';
                    document.getElementById('totalPurchasesAmount').textContent = totalPurchasesAmount + ' €';
                    document.getElementById('totalBalance').textContent = totalBalance + ' €';

                    document.getElementById('compensationBroc').textContent = extraFripe + ' €';
                    document.getElementById('compensationFripe').textContent = extraBroc + ' €';

                    document.getElementById('partBroc').textContent = shareBroc.toFixed(2) + ' €';
                    document.getElementById('partFripe').textContent = shareFripe.toFixed(2) + ' €';

                    afficherMontantADeclarerInitial();
                }

                function calculerMontantADeclarer(montantEncaisse, pourcentageADeclarer) {
                    return montantEncaisse + (montantEncaisse * (pourcentageADeclarer / 100));
                }

                function afficherMontantADeclarerInitial() {
                    const partBroc = parseFloat(document.getElementById('partBroc').textContent);
                    const partFripe = parseFloat(document.getElementById('partFripe').textContent);
                    const pourcentageADeclarer = parseFloat(document.getElementById('pourcentageADeclarer').value);

                    const montantADeclarerBroc = partBroc;
                    const montantADeclarerFripe = partFripe;

                    document.getElementById('montantADeclarerBroc').value = montantADeclarerBroc.toFixed(2);
                    document.getElementById('montantADeclarerFripe').value = montantADeclarerFripe.toFixed(2);

                    const montantURSAFBroc = partBroc * (pourcentageADeclarer / 100);
                    const montantURSAFFripe = partFripe * (pourcentageADeclarer / 100);

                    document.getElementById('montantURSAFBroc').textContent = montantURSAFBroc.toFixed(2) + ' €';
                    document.getElementById('montantURSAFFripe').textContent = montantURSAFFripe.toFixed(2) + ' €';

                    const montantEncaisseBroc = partBroc - montantURSAFBroc;
                    const montantEncaisseFripe = partFripe - montantURSAFFripe;

                    document.getElementById('montantEncaisseBroc').value = montantEncaisseBroc.toFixed(2);
                    document.getElementById('montantEncaisseFripe').value = montantEncaisseFripe.toFixed(2);
                }

                function afficherMontants() {
                    const montantADeclarerBroc = parseFloat(document.getElementById('montantADeclarerBroc').value);
                    const montantADeclarerFripe = parseFloat(document.getElementById('montantADeclarerFripe').value);
                    const pourcentageADeclarer = parseFloat(document.getElementById('pourcentageADeclarer').value);

                    if (isNaN(pourcentageADeclarer) || pourcentageADeclarer <= 0) {
                        alert('Veuillez saisir un pourcentage valide.');
                        return;
                    }

                    const montantURSAFBroc = montantADeclarerBroc * (pourcentageADeclarer / 100);
                    const montantURSAFFripe = montantADeclarerFripe * (pourcentageADeclarer / 100);

                    document.getElementById('montantURSAFBroc').textContent = montantURSAFBroc.toFixed(2) + ' €';
                    document.getElementById('montantURSAFFripe').textContent = montantURSAFFripe.toFixed(2) + ' €';

                    const montantEncaisseBroc = montantADeclarerBroc - montantURSAFBroc;
                    const montantEncaisseFripe = montantADeclarerFripe - montantURSAFFripe;

                    document.getElementById('montantEncaisseBroc').value = montantEncaisseBroc.toFixed(2);
                    document.getElementById('montantEncaisseFripe').value = montantEncaisseFripe.toFixed(2);

                    document.getElementById('montantADeclarerBroc').textContent = montantADeclarerBroc.toFixed(2) + ' €';
                    document.getElementById('montantADeclarerFripe').textContent = montantADeclarerFripe.toFixed(2) + ' €';
                }

                // Initial calculation
                document.addEventListener('DOMContentLoaded', calculate);
            </script> -->


        </div>

        <div class="control-panel-list-form">

            <table class="table striped-table">
                <tr>
                    <th style="text-align: center;">Jours d'ouverture</th>
                    <th></th>
                </tr>
                <tr>
                    <td style="text-align: center;"><?= $openedDays ?></td>
                    <td></td>

                </tr>

                <tr>
                    <th>Moyennes / jours</th>
                    <th></th>
                </tr>
                <tr>
                    <td style="text-align: center;"><?= $averageDay ?> €</td>
                    <th style="text-align: center;"><?= $averageSales ?> Ventes</th>
                </tr>
            </table>
        </div>

        <div class="control-panel-list-form">
            <table class="table striped-table">
                <tr>
                    <th style="text-align: center;">T Ventes</th>
                    <th style="text-align: center;">T Achats</th>
                    <th style="text-align: center;">Bilan</th>
                </tr>
                <tr>
                    <td style="text-align: center;"><?= $totalMonth['total_month'] ?> €</td>
                    <td style="text-align: center;"><?= $totalPurchasesAmount ?> €</td>
                    <th style="text-align: center;"><?= $totalMonth['total_month'] - $totalPurchasesAmount ?> €</th>
                </tr>
            </table>
        </div>


        <h4 class="control-panel-list-title">VENTES <?= date('m-Y', strtotime($selectedDate)) ?></h4>
        <div class="control-panel-list-form">
            <table class="table striped-table">
                <tr>
                    <th style="text-align: center;">Mois</th>
                    <th style="text-align: center;">Ventes</th>
                    <th style="text-align: center;">Articles</th>
                    <th style="text-align: center;">V.€</th>
                </tr>
                <tr>
                    <td style="text-align: center;"><?= date('M', strtotime($selectedDate)) ?></td>
                    <td style="text-align: center;"><?= $totalMonth['total_sales'] ?></td>
                    <td style="text-align: center;"><?= $totalMonth['total_articles'] ?></td>
                    <td style="text-align: center;"><?= $totalMonth['total_month'] ?> €</td>
                </tr>
                <tr>
                    <th style="text-align: center;">Jour</th>
                    <th style="text-align: center;">Ventes</th>
                    <th style="text-align: center;">Articles</th>
                    <th style="text-align: center;">Montant Jour</th>


                </tr>
                <?php foreach ($dailySales as $date => $salesInfo): ?>
                    <tr>
                        <td style="text-align: center;"><?= date('d', strtotime($date)) ?></td>
                        <td style="text-align: center;"><?= $salesInfo['total_sales'] ?></td>
                        <td style="text-align: center;"><?= $salesInfo['total_articles'] ?></td>
                        <td style="text-align: center;"><?= $salesInfo['total_amount'] ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <div class="control-panel-list">
        <h4 class="control-panel-list-title">ACHATS <?= date('m-Y', strtotime($selectedDate)) ?></h4>

        <div class="control-panel-list-form">
            <table class="table striped-table">
                <tr>
                    <th style="text-align: center;">Mois</th>
                    <th style="text-align: center;">Achats</th>
                    <th style="text-align: center;">A.€</th>
                </tr>
                <tr>
                    <td style="text-align: center;"><?= date('M', strtotime($selectedDate)) ?></td>
                    <td style="text-align: center;"><?= $totalPurchases ?></td>
                    <td style="text-align: center;"><?= $totalPurchasesAmount ?> €</td>
                </tr>
                <tr>
                    <th style="text-align: center;">Date</th>
                    <th style="text-align: center;">Client</th>
                    <th style="text-align: center;">€</th>
                </tr>
                <?php foreach ($monthlyPurchases as $purchaseDate => $purchaseInfo): ?>
                    <tr>
                        <td style="text-align: center;"><?= date('d-m-Y', strtotime($purchaseInfo['purchase_date'])) ?></td>
                        <td style="text-align: center;"><?= $purchaseInfo['provider_name'] ?></td>
                        <td style="text-align: center;"><?= $purchaseInfo['total_amount'] ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <canvas id="category_chart" width="400" height="400" style="background-color=#F2FAEF"></canvas>
        <canvas id="category_amount_chart" width="400" height="400"></canvas>
        <canvas id="men_subcategory_chart" width="400" height="400"></canvas>
        <canvas id="men_subcategory_amount_chart" width="400" height="400"></canvas>
        <canvas id="women_subcategory_chart" width="400" height="400"></canvas>
        <canvas id="women_subcategory_amount_chart" width="400" height="400"></canvas>
        <canvas id="flea_subcategory_chart" width="400" height="400"></canvas>
        <canvas id="flea_subcategory_amount_chart" width="400" height="400"></canvas>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ctx = document.getElementById('category_chart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($category_chart['labels']) ?>,
                datasets: [{
                    label: 'Nombre d\'articles par catégorie',
                    data: <?= json_encode($category_chart['data']) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });

    var ctx2 = document.getElementById('men_subcategory_chart').getContext('2d');
    var subcategoryChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: <?= json_encode($men_subcategory_chart['labels']) ?>,
            datasets: [{
                label: 'Nombre d\'articles par sous-catégorie (Hommes)',
                data: <?= json_encode($men_subcategory_chart['data']) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    var ctx3 = document.getElementById('women_subcategory_chart').getContext('2d');
    var subcategoryChart = new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: <?= json_encode($women_subcategory_chart['labels']) ?>,
            datasets: [{
                label: 'Nombre d\'articles par sous-catégorie (Femmes)',
                data: <?= json_encode($women_subcategory_chart['data']) ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    var ctx4 = document.getElementById('flea_subcategory_chart').getContext('2d');
    var subcategoryChart = new Chart(ctx4, {
        type: 'bar',
        data: {
            labels: <?= json_encode($flea_subcategory_chart['labels']) ?>,
            datasets: [{
                label: 'Nombre d\'articles par sous-catégorie (Brocante)',
                data: <?= json_encode($flea_subcategory_chart['data']) ?>,
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    var ctx5 = document.getElementById('men_subcategory_amount_chart').getContext('2d');
    var subcategoryChart = new Chart(ctx5, {
        type: 'bar',
        data: {
            labels: <?= json_encode($men_subcategory_amount_chart['labels']) ?>,
            datasets: [{
                label: 'Montant € par sous-catégorie (Hommes)',
                data: <?= json_encode($men_subcategory_amount_chart['data']) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    var ctx6 = document.getElementById('women_subcategory_amount_chart').getContext('2d');
    var subcategoryChart = new Chart(ctx6, {
        type: 'bar',
        data: {
            labels: <?= json_encode($women_subcategory_amount_chart['labels']) ?>,
            datasets: [{
                label: 'Montant € par sous-catégorie (Femmes)',
                data: <?= json_encode($women_subcategory_amount_chart['data']) ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    var ctx7 = document.getElementById('flea_subcategory_amount_chart').getContext('2d');
    var subcategoryChart = new Chart(ctx7, {
        type: 'bar',
        data: {
            labels: <?= json_encode($flea_subcategory_amount_chart['labels']) ?>,
            datasets: [{
                label: 'Montant € par sous-catégorie (Brocante)',
                data: <?= json_encode($flea_subcategory_amount_chart['data']) ?>,
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    var ctx8 = document.getElementById('category_amount_chart').getContext('2d');
    var myChart = new Chart(ctx8, {
        type: 'bar',
        data: {
            labels: <?= json_encode($category_amount_chart['labels']) ?>,
            datasets: [{
                label: 'Montant € par catégorie',
                data: <?= json_encode($category_amount_chart['data']) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

</script>

<script>
    // Sélection de l'élément de formulaire et du champ de date
    const dateForm = document.getElementById('dateForm');
    const selectedDateInput = document.getElementById('selectedDate');

    // Écouteur d'événement pour détecter les changements de sélection de date
    selectedDateInput.addEventListener('change', () => {
        // Mettre à jour la valeur du champ de date dans le formulaire avec la nouvelle date sélectionnée
        const newDate = selectedDateInput.value;

        // Soumettre automatiquement le formulaire
        // dateForm.submit();

        window.location.href = `index.php?action=statistic&date=${newDate}`;
    });

    // Fonction pour afficher une alerte de confirmation de suppression
    function confirmDelete(saleId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette vente ?')) {
            // Redirection vers la page de suppression avec l'identifiant de la vente
            window.location.href = `index.php?action=deleteSale&sale_id=${saleId}`;
        }
    }



</script>

<?php $content = ob_get_clean(); ?>
<?php require ('view/template/mainTemplate.php'); ?>