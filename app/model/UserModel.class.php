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
                LIMIT :min, :max
                ORDER BY `Username` ASC';

        $statement = $this->db->prepare($req);
        $statement->execute(array(':min' => $min, ':max' => $max));
        $result = $statement->fetchAll();

        return $result;
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