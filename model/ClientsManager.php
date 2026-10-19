<?php

namespace OpenClassroom\Blog\Model;

use PDO;

require_once("model/BddManager.php");

class ClientsManager extends BddManager
{
    // ------------------------ G E T

    public function getClients()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT id, name, phone, email
             FROM clients
             ORDER BY name ASC'
        );
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClient($id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, name, phone, email
             FROM clients
             WHERE id = ?'
        );
        $req->execute([$id]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    // ------------------------ P O S T

    public function createClient($name, $phone = null, $email = null)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'INSERT INTO clients (name, phone, email)
             VALUES (?, ?, ?)'
        );
        $req->execute([$name, $phone, $email]);

        return $bdd->lastInsertId();
    }
}
