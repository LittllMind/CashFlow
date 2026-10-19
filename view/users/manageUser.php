<?php ob_start(); ?>


<?php
require_once('model/SessionsManager.php');

use \OpenClassroom\Blog\Model\SessionsManager;

function getSessionToDisplayById($sessionId)
{
    $sessionsManager = new SessionsManager();
    $sessionToDisplay = $sessionsManager->getSession($sessionId);

    return $sessionToDisplay;
}


?>


<div class="user">
    <div class="user-head">
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

    <div class="user-form">
        <h4 class="user-form-title">Modifier un utilisateur</h4>
        <form action="index.php?action=updateUser" method="post">

            <div class="user-form-infos">
                <div class="user-form-infos-actual">
                    <div class="user-form-infos-actual-group">
                        <input class="form-control" type="hidden" value="<?= $userToManage['id'] ?>" id="user_id" name="user_id" />
                    </div>
                    <div class="user-form-infos-actual-group">
                        <label for="pseudo">Nom</label><br />
                        <input class="form-control" type="text" value="<?= $userToManage['pseudo'] ?>" id="pseudo" name="pseudo" readonly />
                    </div>
                    <div class="user-form-infos-actual-group">
                        <label for="password">Mot de passe Actuel <br> (Obligatoire pour toute
                            modifications)</label><br />
                        <input class="form-control" type="password" id="password" name="password"></input>
                    </div>
                </div>

                <div class="user-form-infos-news">
                    <div class="user-form-infos-actual-group">
                        <label for="confirmPassword">Nouveau mot de passe</label><br />
                        <input class="form-control" type="password" id="newPassword" name="newPassword"></input>
                    </div>
                    <div class="user-form-infos-actual-group">
                        <label for="confirmPassword">Confirmer le nouveau mot de passe</label><br />
                        <input class="form-control" type="password" id="confirmNewPassword" name="confirmNewPassword"></input>
                    </div>


                </div>
                <div class="user-form-place">

                    <select name="user_status" id="user_status" style="max-width: 80%">
                        <option value="<?= $userToManage['user_status'] ?>" selected><?= $userToManage['user_status'] ?>
                        </option>

                        <option value="vacataire">Vacataire</option>
                        <option value="admin">Responsable</option>

                    </select>
                    <select name="fonction" id="fonction" style="max-width: 80%">
                        <option value="<?= $userToManage['fonction'] ?>" selected><?= $userToManage['fonction'] ?>
                        </option>

                        <option value="addictologist">Addictologue</option>
                        <option value="cisp">Conseiller en insertion sociale & professionelle</option>
                        <option value="psychologist">Psychologue</option>
                        <option value="RGPD">Control RGPD</option>
                        <option value="maraude">Maraude</option>

                    </select>
                </div>

            </div>


            <div class="user-form-manage">
                <button type="submit" class="user-form-manage-submit">Valider</button>
                <button class="user-form-manage-delete"><a href="index.php?action=deleteUser&amp;user_id=<?= $userToManage['id'] ?>">Supprimer</a></button>

            </div>
        </form>
    </div>
    <!--<div class="user-datas">
        <table class="user-datas-table">
            <thead class="user-datas-table-head">
                <tr>
                    <th>Nombre de sessions terminées</th>
                    <th>Nombre de sessions programmées</th>
                </tr>
            </thead>
            <tbody class="user-datas-table-body">
                <tr>
                    <td><?= $numberOfFinichedSessions ?></td>
                    <td><?= $numberOfPlannedSessions ?></td>
                </tr>
            </tbody>
        </table>
    </div>-->
    <div class="user-list">
        <h4 class="user-list-title">Sessions associées à l'utilisateur</h4>
        <div class="user-list-form">



        </div>
    </div>



</div>
<?php $content = ob_get_clean(); ?>

<?php require('view/template/mainTemplate.php');
