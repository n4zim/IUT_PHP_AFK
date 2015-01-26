<?php
class FactionModel extends Model {
    public function FactionModel() {
        parent::__construct();
    }

    public function getFactions($id = null) {
        $req = 'SELECT `Id`, `Name`, `Description`, `Logo`, `CSSId`
                FROM `Faction`'.
                ((isset($id)) ? ' WHERE `Id` = ? ' : '')
                .' ORDER BY `Id`';

        $data = isset($id) ? array($id) : array();

        $statement = $this->db->prepare($req);
        $statement->execute($data);

        $result = isset($id) ? $statement->fetch() : $statement->fetchAll();
        return $result;
    }

    // to remove
    public function getFaction($id) { 
        $req = 'SELECT `Id`, `Name`, `Description`
                FROM `Faction`
                WHERE `Id` = ?
                ORDER BY `Id`';

        $statement = $this->db->prepare($req);
        $statement->execute(array($id));

        $result = $statement->fetch();
        return $result;
    }

    public function getRandomFaction() {
        $req = 'SELECT `r1`.`Id`, `r1`.`Name`, `r1`.`Description`
                FROM Faction AS r1 JOIN
                   (SELECT CEIL(RAND() *
                                 (SELECT MAX(Id)
                                    FROM Faction)) AS Id)
                    AS r2
                WHERE r1.Id >= r2.Id
                ORDER BY r1.Id ASC
                LIMIT 1';

        $statement = $this->db->prepare($req);
        $statement->execute();

        $result = $statement->fetch();
        return $result;
    }

    public function getScoreRecords($faction) {
        $req = 'SELECT `Id`, `Faction`, UNIX_TIMESTAMP(`Date`), `Info`, `Score` FROM `Score` WHERE `Faction` = ?';
        $st = $this->db->prepare($req);
        $st->execute(array($faction));
        return $st->fetch()['Score'];
    }

    public function getTotalScore($faction) {
        $req = 'SELECT IFNULL(SUM(`Points`), 0) AS `Score` FROM `Score` WHERE `Faction` = ?';
        $st = $this->db->prepare($req);
        $st->execute(array($faction));
        return $st->fetch()['Score'];
    }

    public function insertNewScore($faction, $score, $message = null) {
        $req = 'INSERT INTO `Score` (`Faction`, `Date`, `Score`, `Info`) VALUES (?, NOW(), ?, ?)';
        $st = $this->db->prepare($req);

        if($score < 0) return false;
        $st->execute(array($faction, $score, $message));
    }
} 