<?php ob_start(); ?>

<div class="control-panel">
    <div class="control-panel-head">
        <?php if (isset($message)) {
            ?>
            <h6 class="alert-message">
                <?= $message
                    ?>
            </h6>
            <?php
        }
        ?>

    </div>

    <div class="control-panel-form">
        <button class="control-panel-form-button"><a href="index.php?action=newSaleForm">Nouvelle Vente</a></button>
    </div>

    <div class="control-panel-form">
        <div class="date-selectors">
            <?php $selectedDate ?>

            <?php if ($selectedDate) {
                ?>

                <input type="date" id="selectedDate" name="day" value="<?= $selectedDate ?>">
                <?php
            } else {
                ?>

                <input type="date" id="selectedDate" name="day" value="<?= date('Y-m-d') ?>">

                <?php
            }
            ?>

            <!-- <input type="date" id="selectedDate" name="day" value="<?= date('Y-m-d') ?>"> -->
        </div>
    </div>

    <div class="control-panel-list">
        <h4 class="control-panel-list-title">Ventes</h4>
        <div class="control-panel-list-form">
            <table class="table table-striped col-12">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Total</th>
                        <th>Unit</th>
                        <th>-</th>
                        <th>Action</th> <!-- Nouvelle colonne pour le bouton de suppression -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list_of_sales as $sale): ?>
                        <tr id="saleRow<?= $sale['id'] ?>" class="saleRow">
                            <td>
                                <?= $sale['sale_date'] ?>
                            </td>
                            <td>
                                <a href="index.php?action=newSaleForm&sale_id=<?= $sale['id'] ?>" class="btn btn-success">
                                    <?= $sale['sale_time'] ?>
                                </a>
                            </td>
                            <td>
                                <?= $sale['total_amount'] ?> €
                            </td>
                            <td>

                                <?= $sale['article_count'] ?>

                            </td>
                            <td>
                                <?= $sale['payment_method'] ?>
                            </td>
                            <td>
                                <!-- Bouton de suppression avec alerte JavaScript -->
                                <button onclick="confirmDelete(<?= $sale['id'] ?>)"
                                    class="btn btn-danger delete-button"><img src="assets/images/delete-32.png"
                                        alt="DELETE"></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <table class="table striped-table">
                <tr>
                    <th style="text-align: center;">Jour</th>
                    <th style="text-align: center;">Ventes</th>
                    <th style="text-align: center">Articles</th>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <?= $total_amount ?> €
                    </td>
                    <td style="text-align: center;">
                        <?= $total_sales ?>
                    </td>
                    <td style="text-align: center;">
                        <?= $total_articles ?>
                    </td>
                </tr>
                <tr>
                    <th style="text-align: center;">
                        semaine
                    </th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <?= $total_amount_weekly ?> €
                    </td>
                    <td style="text-align: center;">
                        <?= $total_sales_weekly ?>
                    </td>
                    <td style="text-align: center;">
                        <?= $total_articles_weekly ?>
                    </td>
                </tr>
                <tr>
                    <th style="text-align: center;">Mois</th>
                    <th></th> 
                    <th></th>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <?= $total_amount_monthly ?> €
                    </td>
                    <td style="text-align: center;">
                        <?= $total_sales_monthly ?>
                    </td>
                    <td style="text-align: center;">
                        <?= $total_articles_monthly ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

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

            window.location.href = `index.php?action=controlPanel&date=${newDate}`;
        });


        // Fonction pour afficher une alerte de confirmation de suppression
        function confirmDelete(saleId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette vente ?')) {
                // Redirection vers la page de suppression avec l'identifiant de la vente
                window.location.href = `index.php?action=deleteSale&sale_id=${saleId}`;
            }
        }
    </script>
</div>

<!-- <script type="text/javascript" src="view/control-panel/sale-form.js"></script> -->

<?php $content = ob_get_clean(); ?>

<?php require ('view/template/mainTemplate.php'); ?>