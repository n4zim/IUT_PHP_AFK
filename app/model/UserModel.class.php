<?php
class UserModel extends Model {
    public function UserModel() {
        parent::__construct();
    }

    public function getUsers($page = 0) {
        $min = $page * Config::$listing['usersPerPage'];
        $max = $min + Config::$listing['usersPerPage'];

        $req = 'SELECT `Id`, `Username`, `Password`, `Salt`, `Mail`, `Gender`, `Avatar`, `Faction`
                FROM `User`
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
} 