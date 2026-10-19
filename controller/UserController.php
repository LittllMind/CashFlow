<?php
require_once('controller/MainController.php');

require_once('model/UserManager.php');
require_once('model/SessionsManager.php');

use \OpenClassroom\Blog\Model\UserManager;
use \OpenClassroom\Blog\Model\SessionsManager;
use \OpenClassroom\Blog\Model\LieuxManager;

// ------------------------ M A N A G E   U S E R -----------------

function userForm($message)
{
    $userManager = new UserManager();

    $users = $userManager->getUsers();

    $authorisation = userValidation();

    $title = 'Ajouter un utilisateur';
    require('view/users/addUser.php');
}


function addUser($pseudo, $password, $confirmPassword, $user_status, $fonction)
{
    $userManager = new UserManager();

    
    if ($password == $confirmPassword && $_SESSION['user_status'] == 'admin') {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $newMember = $userManager->postUser($pseudo, $password_hash, $user_status, $fonction);
        if ($newMember === false) {
            throw new Exception('Impossible d\'ajouter l\'utilisateur !');
        } else {
            userValidation();
            $title = 'Ajout Utilisateur';
            header('Location: index.php?action=userForm');
        }
    } else {
        
    }
}

function manageUser($user_id, $message) {
    userValidation();

    $userManager = new UserManager();
    $sessionManager = new SessionsManager();

    $userToManage = $userManager->getUserById($user_id);

    $numberOfFinichedSessions = 0;
    $numberOfPlannedSessions = 0;

    require('view/users/manageUser.php');
}

function updateUser($user_id, $pseudo, $password, $user_status, $fonction, $new_password, $confirm_new_password) {
    
    userValidation();
    $userManager = new UserManager();

    $userToUpdate = $userManager->getUserByIdWithPassword($user_id);

    $isPasswordCorrect = password_verify($password, $userToUpdate['password']);

    $correctPassword = isset($isPasswordCorrect);

        if ($isPasswordCorrect == 1) {
            if ($new_password == $confirm_new_password) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $userToUpdate = $userManager->updateUser($user_id, $pseudo, $new_password_hash, $user_status, $fonction);
                $message = $pseudo . ' à bien été mis a jour';
                login();
            } else {
                $message = 'Les nouveaux mots de passes ne corresondent pas';
                manageUser($user_id, $message);
            }


        } else {
            $message = 'Mot de passe invalide';
            manageUser($user_id, $message);
        }
}


function deleteUser($user_id) {
    userValidation();

    $userManager = new UserManager();

    $deletedUser = $userManager->deleteUser($user_id);
    
    if ($deletedUser === false) {
        throw new Exception('Impossible de supprimer l\'utilisateur');
    } else {
        $title = 'Ajout d\'utilisateurs';
        $message = 'L\'utilisateur a bien été supprimé';
        header('Location: index.php?action=userForm');
    }
}

// -----------------------------------------------------------