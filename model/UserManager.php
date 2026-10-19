<?php
namespace OpenClassroom\Blog\Model;

require_once("model/BddManager.php");

class UserManager extends BddManager
{

    // ------------------------ POST


    public function postUser($pseudo, $password, $user_status, $fonction)
    {
        $email = $pseudo . '@' . $user_status;
        $bdd = $this->dbConnect();
        $user = $bdd->prepare(
            'INSERT INTO users(pseudo, mail, password, user_status, fonction)
            VALUES (?, ?, ?, ?, ?)'
        );

        $affectedLines = $user->execute(array($pseudo, $email, $password, $user_status, $fonction));

        return $affectedLines;
    }

    // ---------------------- U P D A TE

    public function updateUser($user_id, $pseudo, $new_password_hash, $user_status, $fonction) {
        $mail = $pseudo . '@' . $fonction;    
        
        $bdd = $this->dbConnect();
    
            $req = $bdd->prepare(
                'UPDATE users
                SET     pseudo = :pseudo,
                        password = :new_password_hash,
                        mail = :mail,
                        fonction = :fonction,
                        user_status = :user_status
                WHERE id = :user_id'
            );
    
            $updatedUser = $req->execute(array(
                'pseudo' => $pseudo,
                'new_password_hash' => $new_password_hash,
                'mail' => $mail,
                'fonction' => $fonction,
                'user_status' => $user_status,
                'user_id' => $user_id
            ));
    
            return $updatedUser;
    }

  // ------------------------ GET

    public function getUserByPseudo($pseudo)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, password, pseudo, mail, fonction, user_status, registration_date
            FROM users
            WHERE pseudo = ?'
        );

        $req->execute(array($pseudo));
        $user = $req->fetch();


        return $user;
    }

    public function getUserById($user_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, pseudo, user_status, fonction
            FROM users
            WHERE id = ?'
        );

        $req->execute(array($user_id));
        $user = $req->fetch();


        return $user;
    }

    public function getUserByIdWithPassword($user_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, password, pseudo, user_status, fonction
            FROM users
            WHERE id = ?'
        );

        $req->execute(array($user_id));
        $user = $req->fetch();


        return $user;
    }


    public function getUsers()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT id, pseudo, mail, DATE_FORMAT(registration_date, \'%d/%m/%Y\')
            AS registration_date,
            user_status,
            fonction
            FROM users
            ORDER BY id ASC'
        );

        return $req;
    }

    public function getUsersByFonction($fonction)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, pseudo, user_status, registration_date, fonction
            FROM users
            WHERE fonction = ?'
        );

        $req->execute(array($fonction));

        return $req;
    }

  

    // ---------------------- D E L E T E

    public function deleteUser($user_id)
    {

        $bdd = $this->dbConnect();

        $req = $bdd->prepare(
            'DELETE FROM `users` WHERE `users`.`id` = ?'
        );

        $deletedUser = $req->execute(array($user_id));

        return $deletedUser;
    }
}
