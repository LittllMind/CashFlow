<?php
require_once('model/UserManager.php');
require_once('model/SessionsManager.php');
require_once('model/ConnexionLogManager.php');

use \OpenClassroom\Blog\Model\UserManager;
use \OpenClassroom\Blog\Model\SessionsManager;
use \OpenClassroom\Blog\Model\ConnexionLogManager;

function index()
{
    $title = 'Datatheque';
    header('Location: index.php?action=login');
}

// ------------------------ L O G I N -----------------------------

function login()
{
    if (isset($_SESSION['id']) && isset($_SESSION['pseudo'])) {
        $selectedDate = date('Y-m-d');
        header('Location: index.php?action=salePanel&date=' . $selectedDate);
    } else {
        $title = 'Connexion';
        require('view/users/connexion.php');
    }
}


function userConnection($pseudo, $password, $stayConnect)
{
    $userManager = new UserManager();
    $user = $userManager->getUserByPseudo($pseudo);

    $isPasswordCorrect = password_verify($password, $user['password']);

    if ($isPasswordCorrect) {
        $connexionLogManager = new ConnexionLogManager();
        $dateNow = new DateTime();
        $connexion_date = $dateNow->format('Y-m-d H:i:s');
        $clientIp = getClientIp();
        $connexionLogToAdd = $connexionLogManager->postConnexionLog($user['id'], $connexion_date, $clientIp);
        $lastConnexionLog = $connexionLogManager->getLastConnexionLog();

        $_SESSION['id'] = $user['id'];
        $_SESSION['pseudo'] = $user['pseudo'];
        $_SESSION['user_status'] = $user['user_status'];
        $_SESSION['fonction'] = $user['fonction'];
        $_SESSION['mail'] = $user['mail'];
        $_SESSION['connexion_log_id'] = $lastConnexionLog['id'] ?? null;

        $selectedDate = date('Y-m-d');
        header('Location: index.php?action=salePanel&date=' . $selectedDate);
    } else {
        $errorMessage = 'Mauvais identifiant ou mot de passe';
        header('Location: index.php?action=login');
    }
}

function userValidation()
{
    if (!isset($_SESSION['pseudo']) || !isset($_SESSION['id'])) {
        header('Location: index.php?action=login');
        exit;
    }

    $userManager = new UserManager();
    $user = $userManager->getUserByPseudo($_SESSION['pseudo']);

    if (!$user || $user['id'] != $_SESSION['id']) {
        return 'Not allowed';
    }

    return 'Allowed';
}

/**
 * Retourne l'adresse IP du client avec fallback sur headers de proxy.
 */
function getClientIp(): string
{
    $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $list = explode(',', (string)$_SERVER[$header]);
            $ip = trim($list[0] ?? '');
            if ($ip !== '' && $ip !== '127.0.0.1') {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function deconnexion()
{
    $connexionLogManager = new ConnexionLogManager();
    $dateNow = new DateTime();
    $deconnexion_date = $dateNow->format('Y-m-d H:i:s');

    $connexionLogToUpdate = $connexionLogManager->addDeconnexionDateToConnexionLog($_SESSION['connexion_log_id'] ?? '', $deconnexion_date);

    $_SESSION = array();
    session_destroy();
    setcookie('pseudo', '');
    setcookie('password', '');
    setcookie('user_status', '');
    setcookie('fonction', '');
    setcookie('connexion_log_id', '');

    header('Location: index.php?action=index');
}
