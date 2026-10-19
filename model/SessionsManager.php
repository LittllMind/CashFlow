<?php

namespace OpenClassroom\Blog\Model;

require_once("model/BddManager.php");

class SessionsManager extends BddManager
{

    // ----------------------------------------- G E T

    public function getSessions()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT id, lieu_id, nombre_consultants, nombre_injecteurs, nombre_administrateurs, primo, secondaire, tertiaire, nombre_mineurs, cycles_termines, total_jour, dotation, 
            DATE_FORMAT(session_date, \'%d-%m-%Y\')
            AS session_date_fr,
            DATE_FORMAT(next_session_date, \'%d-%m-%Y\')
            AS next_session_date_fr,
            status
            FROM sessions
            ORDER BY session_date DESC'
        );

        return $req;
    }

    public function getPlannedSessions()
    {
        $bdd = $this->dbConnect();
        $req = $bdd->query(
            'SELECT id, lieu_id, nombre_consultants, nombre_injecteurs, nombre_administrateurs, primo, secondaire, tertiaire, nombre_mineurs, cycles_termines, total_jour, dotation, 
            DATE_FORMAT(session_date, \'%d-%m-%Y\')
            AS session_date_fr,
            DATE_FORMAT(next_session_date, \'%d-%m-%Y\')
            AS next_session_date_fr,
            status
            FROM sessions
            WHERE status = 0
            ORDER BY session_date ASC'
        );

        return $req;
    }

    public function getSessionsByStartDateAndEndDate($start_date, $end_date)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, lieu_id, nombre_consultants, nombre_injecteurs, nombre_administrateurs, primo, secondaire, tertiaire, nombre_mineurs, cycles_termines, total_jour, dotation,
            DATE_FORMAT(creation_date, \'%Y-%m-%d %H:%m:%s\')
            AS creation_date_fr, 
            DATE_FORMAT(session_date, \'%Y-%m-%d\')
            AS session_date_fr,
            DATE_FORMAT(next_session_date, \'%Y-%m-%d\')
            AS next_session_date_fr,
            DATE_FORMAT(creation_date, \'%Y-%m-%d %H:%m:%s\')
            AS creation_date_fr,
            status
            
            FROM sessions
            WHERE session_date > ? AND session_date < ?'
        );
        $req->execute(array($start_date, $end_date));
        

        return $req;
    }

    
    public function getPlannedSessionsByStartDateAndEndDate($start_date, $end_date)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, lieu_id, nombre_consultants, nombre_injecteurs, nombre_administrateurs, primo, secondaire, tertiaire, nombre_mineurs, cycles_termines, total_jour, dotation,
            DATE_FORMAT(creation_date, \'%Y-%m-%d %H:%m:%s\')
            AS creation_date_fr, 
            DATE_FORMAT(session_date, \'%Y-%m-%d\')
            AS session_date_fr,
            DATE_FORMAT(next_session_date, \'%Y-%m-%d\')
            AS next_session_date_fr,
            DATE_FORMAT(creation_date, \'%Y-%m-%d %H:%m:%s\')
            AS creation_date_fr,
            status
            
            FROM sessions
            
            WHERE session_date > ? AND session_date < ? AND status = 0'
        );
        $req->execute(array($start_date, $end_date));
        

        return $req;
    }


    public function getSession($session_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, lieu_id, nombre_consultants, nombre_injecteurs, nombre_administrateurs, primo, secondaire, tertiaire, nombre_mineurs, cycles_termines, total_jour, dotation,
            DATE_FORMAT(creation_date, \'%Y-%m-%d %H:%m:%s\')
            AS creation_date_fr, 
            DATE_FORMAT(session_date, \'%Y-%m-%d\')
            AS session_date_fr,
            DATE_FORMAT(next_session_date, \'%Y-%m-%d\')
            AS next_session_date_fr,
            DATE_FORMAT(creation_date, \'%Y-%m-%d %H:%m:%s\')
            AS creation_date_fr,
            status
            
            FROM sessions
            WHERE id = ?'
        );
        $req->execute(array($session_id));
        $session = $req->fetch();

        return $session;
    }

    public function getSessionsByLieuId($lieu_id)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, lieu_id, nombre_consultants, nombre_injecteurs, nombre_administrateurs, primo, secondaire, tertiaire, nombre_mineurs, cycles_termines, total_jour, dotation, 
            DATE_FORMAT(session_date, \'%d-%m-%Y\')
            AS session_date_fr,
            DATE_FORMAT(next_session_date, \'%Y-%m-%d\')
            AS next_session_date_fr,
            DATE_FORMAT(creation_date, \'%Y-%m-%d %H:%m:%s\')
            AS creation_date_fr,
            status
            
            FROM sessions
            WHERE lieu_id = ?'
        );
        $req->execute(array($lieu_id));

        return $req;
    }
    

        public function getSessionsByLieuIdAndDates($lieu_id, $start_date, $end_date)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, lieu_id, nombre_consultants, nombre_injecteurs, nombre_administrateurs, primo, secondaire, tertiaire, nombre_mineurs, cycles_termines, total_jour, dotation, 
            DATE_FORMAT(session_date, \'%d-%m-%Y\')
            AS session_date_fr,
            DATE_FORMAT(next_session_date, \'%Y-%m-%d\')
            AS next_session_date_fr,
            DATE_FORMAT(creation_date, \'%Y-%m-%d %H:%m:%s\')
            AS creation_date_fr,
            status
            
            FROM sessions
            WHERE lieu_id = ? AND session_date BETWEEN ? AND ?'
        );
        $req->execute(array($lieu_id, $start_date, $end_date));

        return $req;
    }


    

    
    public function getSessionByCreationDateAndSessionDate($creation_date, $session_date)
    {
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, lieu_id, nombre_consultants, nombre_injecteurs, nombre_administrateurs, primo, secondaire, tertiaire, nombre_mineurs, cycles_termines, total_jour, dotation, 
            DATE_FORMAT(session_date, \'%Y-%m-%d\')
            AS session_date_fr,
            DATE_FORMAT(next_session_date, \'%Y-%m-%d\')
            AS next_session_date_fr,
            DATE_FORMAT(creation_date, \'%Y-%m-%d %H:%m:%s\')
            AS creation_date_fr,
            status
            
            FROM sessions
            WHERE creation_date = ? AND session_date = ?'
        );
        $req->execute(array($creation_date, $session_date));
        $session = $req->fetch();

        return $session;
    }


    public function getSessionBySessionDateAndLieu($session_date, $lieu) {
    
        // Select event
        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, lieu_id, nombre_consultants, nombre_injecteurs, nombre_administrateurs, primo, secondaire, tertiaire, nombre_mineurs, cycles_termines, total_jour, dotation, 
            DATE_FORMAT(session_date, \'%Y-%m-%d\')
            AS session_date_fr,
            DATE_FORMAT(next_session_date, \'%Y-%m-%d\')
            AS next_session_date_fr,
            status
            FROM sessions
            WHERE session_date = ? AND lieu_id = ?'
        );
        $req->execute(array($session_date, $lieu));
        return $event = $req->fetch();
    }

    public function getSessionsByDay($sessionsDate)
    {

        $bdd = $this->dbConnect();
        $req = $bdd->prepare(
            'SELECT id, lieu_id, status,
            DATE_FORMAT(session_date, \'%d/%m/%Y\')
            AS session_date_fr
            FROM sessions
            WHERE session_date = ?
            ORDER BY id DESC'
        );

        $req->execute(array($sessionsDate));

        return $req;
    }

   
    public function postSession($lieu, $nombre_consultants, $nombre_injecteurs, $nombre_administrateurs, $primo, $secondaire, $tertiaire, $nombre_mineurs, $cycles_termines, $total_jour, $dotation, $previous_session_date, $session_date, $next_session_date, $creation_date, $session_terminee) {
        $bdd = $this->dbConnect();

        $session = $bdd->prepare(
            'INSERT INTO sessions(lieu_id, nombre_consultants, nombre_injecteurs, nombre_administrateurs, primo, secondaire, tertiaire, nombre_mineurs, cycles_termines, total_jour, dotation, previous_session_date, session_date, next_session_date, creation_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $new_session = $session->execute(array($lieu, $nombre_consultants, $nombre_injecteurs, $nombre_administrateurs, $primo, $secondaire, $tertiaire, $nombre_mineurs, $cycles_termines, $total_jour, $dotation, $previous_session_date, $session_date, $next_session_date, $creation_date, $session_terminee));

        return $new_session;
                    
    }

    public function updateSession($session_id, $lieu, $nombre_consultants, $nombre_injecteurs, $nombre_administrateurs, $primo, $secondaire, $tertiaire, $nombre_mineurs, $cycles_termines, $total_jour, $dotation, $previous_session_date,  $session_date, $next_session_date, $session_terminee)
    {

        $bdd = $this->dbConnect();

        $req = $bdd->prepare(
            'UPDATE sessions 
            SET     session_date = :session_date,
                    lieu_id = :lieu_id, 
                    nombre_consultants = :nombre_consultants, 
                    nombre_injecteurs = :nombre_injecteurs, 
                    nombre_administrateurs = :nombre_administrateurs, 
                    primo = :primo, 
                    secondaire = :secondaire, 
                    tertiaire = :tertiaire, 
                    nombre_mineurs = :nombre_mineurs,
                    cycles_termines = :cycles_termines, 
                    total_jour = :total_jour,
                    dotation = :dotation,
                    previous_session_date = :previous_session_date,
                    next_session_date = :next_session_date, 
                    status = :status 

            WHERE id = :id'
        );

        $updatedSession = $req->execute(array(
            'session_date' => $session_date,
            'lieu_id' => $lieu,
            'nombre_consultants' => $nombre_consultants,
            'nombre_injecteurs' => $nombre_injecteurs,
            'nombre_administrateurs' => $nombre_administrateurs,
            'primo' => $primo,
            'secondaire' => $secondaire,
            'tertiaire' => $tertiaire,
            'nombre_mineurs' => $nombre_mineurs,
            'cycles_termines' => $cycles_termines,
            'total_jour' => $total_jour,
            'dotation' => $dotation,
            'previous_session_date' => $previous_session_date,
            'next_session_date' => $next_session_date,
            'status' => $session_terminee,
            'id' => $session_id
        ));

        return $updatedSession;
    }

    public function deleteSession($session_id)
    {

        $bdd = $this->dbConnect();

        $req = $bdd->prepare(
            'DELETE FROM `sessions` WHERE `sessions`.`id` = ?'
        );

        $deletedSession = $req->execute(array($session_id));

        return $deletedSession;
    }

}