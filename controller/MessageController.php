<?php
require_once ('model/UserManager.php');
require_once ('model/SessionsManager.php');
require_once ('model/ConnexionLogManager.php');
require_once ('model/MessageManager.php');

use \OpenClassroom\Blog\Model\UserManager;
use \OpenClassroom\Blog\Model\SessionsManager;
use \OpenClassroom\Blog\Model\ConnexionLogManager;
use \OpenClassroom\Blog\Model\MessageManager;

require_once ('controller/SalesController.php');


function postMessage($message_text, $message_date, $selectedDate)
{

    // send datas to the model
    $messageManager = new MessageManager();
    $userManager = new UserManager();

    $sender_id = $_SESSION['id'];

    $messageManager->postMessage($message_text, $message_date, $sender_id);

    $message = 'Message envoyé';

    salePanel($selectedDate, $message);
}

function deleteMessage($message_id, $selectedDate)
{
    $messageManager = new MessageManager();

    $messageManager->deleteMessage($message_id);

    $message = 'Message supprimé';

    salePanel($selectedDate, $message);
}

