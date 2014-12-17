<?php
class Model {
    protected $afk;
    protected $db;

    public function Model()
    {
        $this->afk = AFK::getInstance();
        $this->db = $this->afk->getDB();
    }
}