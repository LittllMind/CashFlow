<?php

namespace OpenClassroom\Blog\Model;

require_once ("model/BddManager.php");

class ModelManager extends BddManager
{
    protected $bdd;
    protected $table;

    public function __construct($table)
    {
        $this->bdd = $this->dbConnect();
        $this->table = $table;
    }

    public function getAll()
    {
        $req = $this->bdd->query('SELECT * FROM ' . $this->table);
        return $req;
    }

    public function get($id)
    {
        $req = $this->bdd->prepare('SELECT * FROM ' . $this->table . ' WHERE id = ?');
        $req->execute(array($id));
        return $req;
    }

    public function post($datas)
    {
        $req = $this->bdd->prepare('INSERT INTO ' . $this->table . ' (title, content, creation_date) VALUES (?, ?, NOW())');
        $req->execute($datas);
    }

    public function update($id, $datas)
    {
        $req = $this->bdd->prepare('UPDATE ' . $this->table . ' SET title = ?, content = ? WHERE id = ?');
        $req->execute(array($datas['title'], $datas['content'], $id));
    }

    public function delete($id)
    {
        $req = $this->bdd->prepare('DELETE FROM ' . $this->table . ' WHERE id = ?');
        $req->execute(array($id));
    }
}
