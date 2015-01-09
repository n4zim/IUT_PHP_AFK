<?php
class UserModel extends Model {
    public function UserModel() {
        parent::__construct();
    }

    public function getUsers($page = 0) {
        $min = $page * Config::$listing['usersPerPage'];
        $max = $min + Config::$listing['usersPerPage'];

        $req = 'SELECT `User`.`Id`, `Username`, `Password`, `Salt`, `Mail`, `Gender`, `Avatar`, `Faction`, `Faction`.`Name` AS `FactionName`, `Faction`.`Id` AS `FactionId`
                FROM `User`
                JOIN `Faction` ON `Faction`.`Id` = `User`.`Faction`
                ORDER BY `Username`
                LIMIT :min, :max';

        $statement = $this->db->prepare($req);
        $statement->bindValue('min', $min, PDO::PARAM_INT);
        $statement->bindValue('max', $max, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetchAll();
        return $result;
    }

    public function countUsers() {
        $req = 'SELECT COUNT(`Id`) AS `Count`
                FROM `User`';

        $statement = $this->db->prepare($req);
        $statement->execute();

        $result = $statement->fetch();
        return $result['Count'];
    }

    public function getUser($id = null) {
        $req = 'SELECT `Id`, `Username`, `Password`, `Salt`, `Mail`, `Gender`, `Avatar`, `Faction`
                FROM `User`
                WHERE `Id`=:id';

        $statement = $this->db->prepare($req);
        $statement->execute(array(':id' => $id));
        $result = $statement->fetchAll();

        return $result;
    }

    /**
     * Checks if an user exists with those credentials
     * @return  User ID if login successful, false otherwise
     **/
    public function checkLogin($username, $password) {
        $req = 'SELECT `Id`, `Username`, `Password`, `Salt` FROM `User`
                WHERE `Username`=?';

        $statement = $this->db->prepare($req);
        $statement->execute(array($username));
        $result = $statement->fetch();

        $saltedPasswd = sha1($password.$result['Salt']);

        if($saltedPasswd == $result['Password'])
            return $result;

        return FALSE;
    }
} 