<?php
if (isset($_SESSION['id']) && isset($_SESSION['pseudo'])) {
    header('Location: index.php?action=salePanel&date=' . date('Y-m-d'));
    exit;
}
ob_start();
?>

<div class="connexion">
    <div class="connexion-head">
        <h3 class="connexion-head-title">
            LA MATRICE
        </h3>
        <h5 class="connexion-head-title">Gestion des ventes</h5>
        <h2 class="connexion-head-title">CashFlow</h2>
        <?php if (!isset($message)) {
        ?>
            <h6 class="alert-message">
            </h6>
        <?php
        } else {
        ?>
            <h6 class="alert-message">

                <?php
                echo $message

                ?>
            </h6>
        <?php
        }
        ?>
    </div>


    <div class="connexion-form">
        <form action="index.php?action=memberConnexion" method="post">
            <div class="connexion-form-group">
                <label for="mail">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="pseudo" name="pseudo" aria-describedby="mailHelp" placeholder="Entrez votre nom d'utilisateur" required="true">
            </div>

            <div class="connexion-form-group">
                <label for="password">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" aria-describedby="paswordAlert" placeholder="Entrez votre mot de passe" required="true">
            </div>
            <!--<div class="form-check">
              <input
              class="form-check-input"
              type="checkbox"
              value="stayConnect"
              name="stayConnectOn"
              id="stayConnect"
              checked
              />
              <label class="form-check-label" for="stayConnect">
                Stay connect
              </label>
            </div>-->
            <div class="connexion-form-manage">
                <button class="connexion-form-manage-submit" type="submit"> Connexion </button>
            </div>
        </form>
    </div>

</div>


<?php $content = ob_get_clean(); ?>

<?php require('view/template/mainTemplate.php'); ?>