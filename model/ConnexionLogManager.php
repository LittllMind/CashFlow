<?php
namespace OpenClassroom\Blog\Model;

require_once("model/BddManager.php");

class ConnexionLogManager extends BddManager
{ 

    // ------------------------ P O S T 

    public function postConnexionLog($user_id, $connexion_date, $ip = null)
    {
        $bdd = $this->dbConnect();
        $connexionLog = $bdd->prepare(
            'INSERT INTO connexion_log (user_id, connexion_date, ip)
            VALUES (?, ?, ?)'
        );
        $connexionLogToPost = $connexionLog->execute(array($user_id, $connexion_date, $ip));
        return $connexionLogToPost;
    }

    public function postConnexionLogInteraction($connexion_log_id, $item_id, $item_name, $database_name, $item_action)
    {

        $bdd = $this->dbConnect();
        $connexionLog = $bdd->prepare(
            'INSERT INTO connexion_log_interaction (connexion_log_id, item_id, item_name, database_name, item_action)
            VALUES (?, ?, ?, ?, ?)'
        );

        $connexionLogInteractionToPost = $connexionLog->execute(array($connexion_log_id, $item_id, $item_name, $database_name, $item_action));

        return $connexionLogInteractionToPost;
    }

    // ------------------------ U P D A T E

    public function addDeconnexionDateToConnexionLog($connexion_log_id, $deconnexion_date) {
        
        $bdd = $this->dbConnect();

        $req = $bdd->prepare(
            'UPDATE connexion_log
            SET     deconnexion_date = :deconnexion_date

            WHERE id = :connexion_log_id'
        );

        $updatedConnexionLog = $req->execute(array(
            'deconnexion_date' => $deconnexion_date,
            'connexion_log_id' => $connexion_log_id
        ));

        return $updatedConnexionLog;
    }

    // ------------------------ G E T 

    public function getLastConnexionLog() {
        

        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT * FROM connexion_log ORDER BY ID DESC LIMIT 1'
        );
        
        $lastConnexionLog = $req->fetch();

        return $lastConnexionLog;
    }

    public function getConnexionLogs()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT id, user_id, 
            DATE_FORMAT(connexion_date, \'%d-%m-%Y\')
            AS connexion_date,
            DATE_FORMAT(deconnexion_date, \'%d-%m-%Y\')
            AS deconnexion_date
            FROM connexion_log
            ORDER BY id DESC'
        );

        return $req;
    }

    

    public function getConnexionLogInteractionByConnectionLogId($connexion_log_id) {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, connexion_log_id, item_id, item_name, database_name, item_action
            FROM connexion_log_interaction
            WHERE connexion_log_id = ?'
        );

        $req->execute(array($connexion_log_id));

        return $req;
    }
    

 
}