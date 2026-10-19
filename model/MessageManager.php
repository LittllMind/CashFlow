<?php

namespace OpenClassroom\Blog\Model;

require_once ("model/BddManager.php");

class MessageManager extends BddManager
{
    public function postMessage($message_text, $message_date, $sender_id)
    {
        
        $bdd = $this->dbConnect();
        $message = $bdd->prepare(
            'INSERT INTO messages(sender_id, receiver_id, message_text, created_at)
           
            VALUES (?, ?, ?, ?)'
        
        );

        $createdMessage = $message->execute(array( $sender_id, $sender_id, $message_text, $message_date));

        return $createdMessage;
    
    }

    public function getMessages()
    {
        $bdd = $this->dbConnect();
        $messages = $bdd->query(
            'SELECT messages.id, sender_id, receiver_id, message_text, created_at, users.pseudo
            FROM messages
            JOIN users
            ON messages.sender_id = users.id
            ORDER BY created_at DESC'
        );

        $messages = $messages->fetchAll();

        return $messages;
    }

    public function deleteMessage($message_id)
    {
        $bdd = $this->dbConnect();
        $message = $bdd->prepare(
            'DELETE FROM messages WHERE id = ?'
        );

        $deletedMessage = $message->execute(array($message_id));

        return $deletedMessage;
    }


}
