<?php

namespace OpenClassroom\Blog\Model;

require_once ("model/BddManager.php");

class ProvidersManager extends BddManager
{

    // --------------------- G E T 
    public function getProviders()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query('SELECT * FROM providers');
        $providers = $req->fetchAll();
        return $providers;
    }

    public function getProviderById($provider_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare('SELECT * FROM providers WHERE id = ?');
        $req->execute(array($provider_id));
        $provider = $req->fetch();
        return $provider;
    }



    public function postProvider($name, $city = null, $contact_email = null, $contact_phone = null)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'INSERT INTO providers (name, city, contact_email, contact_phone) VALUES (?, ?, ?, ?)'
        );
        $created = $req->execute(array($name, $city, $contact_email, $contact_phone));
        if (!$created) {
            return false;
        }
        return $bdd->lastInsertId();
    }

}