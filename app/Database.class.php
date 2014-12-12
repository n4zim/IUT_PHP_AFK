<?php
/**
* Database manager
*/
class Database
{
    private $dbObject;

    function Database() {
        try {
            $dbObject = new PDO(Config->dbInfo['driver'], Config->dbInfo['username'], Config->dbInfo['password']);
        } catch(Exception $e) {
            exit('Erreur de connexion : ' . $e->getMessage());
        }
    }
}