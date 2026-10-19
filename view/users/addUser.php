<?php ob_start(); ?>

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
        <h4 class="user-form-title">Ajouter un utilisateur</h4>
        <form action="index.php?action=addUser" method="post">

            <div class="user-form-infos">
                <div class="user-form-infos-group">
                    <label for="pseudo">Nom d'utilisateur</label><br />
                    <input class="form-control" type="text" id="pseudo" name="pseudo" />
                </div>
                <div class="user-form-infos-group">
                    <label for="password">Mot de passe</label><br />
                    <input class="form-control" type="password" id="password" name="password"></input>
                </div>
                <div class="user-form-infos-group">
                    <label for="confirmPassword">Confirmation du mot de passe</label><br />
                    <input class="form-control" type="password" id="confirmPassword" name="confirmPassword"></input>
                </div>
            </div>

            <div class="user-form-place">

                <select name="user_status" id="user_status" style="max-width: 80%">
                    <option value="" selected>Status de l'utilisateur :</option>

                    <option value="user">Utilisateur</option>
                    <option value="admin">Responsable</option>

                </select>
                <select name="fonction" id="fonction" style="max-width: 80%">
                    <option value="" selected>Fonction de l'utilisateur :</option>

                    <option value="addictologist">Addictologue</option>
                    <option value="cisp">Conseiller en insertion sociale & professionelle</option>
                    <option value="psychologist">Psychologue</option>
                    <option value="RGPD">Control RGPD</option>
                    <option value="maraude">Maraude</option>

                </select>
            </div>
            <div class="user-form-manage">
                <button class="user-form-manage-submit" type="submit">Valider</button>

            </div>
        </form>
    </div>
    <div class="user-list">
        <h4 class="user-list-title">Utilisateurs enregistrés</h4>



        <div class="user-list-form">

            <?php
        while ($user = $users->fetch()) {
            ?>
            <a href="index.php?action=manageUser&amp;user_id=<?= $user['id'] ?>" class="user-list-form-content">
                <div class="user-list-form-content-info">
                    <table>
                        <thead class="user-list-info-thead">
                            <tr>
                                <th><?= htmlspecialchars($user['pseudo']); ?></th>

                                <td><strong><?= $user['fonction'] ?></strong></td>
                                <td colspan="2"><em>Ajouté le <?= $user['registration_date']; ?></em></td>
                            </tr>
                        </thead>
                        <tbody class="user-list-info-tbody">
                            <tr>

                                <td><?= $user['user_status']; ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="user-list-info-tbody">
                            <tr>

                            </tr>
                        </tfoot>
                    </table>
                </div>

            </a>

            <?php
        }
        $users->closeCursor();
        ?>
        </div>
    </div>

</div>
<?php $content = ob_get_clean(); ?>

<?php require('view/template/mainTemplate.php');
