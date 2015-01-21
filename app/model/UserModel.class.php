<?php
class UserModel extends Model {
    private static $activeUsersAlreadyCleaned = false;

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

        $results = $statement->fetchAll();
        return $results;
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
        $req = 'SELECT `User`.`Id`, `Username`, `Password`, `Salt`, `Mail`, `Gender`,
                IFNULL(`Avatar`, `Faction`.`Logo`) AS `Avatar`, 
                `Faction`, `Faction`.`Name` AS `FactionName` 
                FROM `User`
                JOIN `Faction` ON `Faction`.`Id` = `User`.`Faction`
                WHERE `User`.`Id`=?';

        $statement = $this->db->prepare($req);
        $statement->execute(array($id));
        $result = $statement->fetch();

        return $result;
    }

    public function register($data) {
        $req = 'INSERT INTO `User` (`Username`, `Password`, `Salt`, `Gender`, `Mail`, `Faction`) VALUES (?, ?, ?, ?, ?, ?);';

        $salt = md5($data['username'].time());
        $pass = sha1($data['password'].$salt);
        $gender = (in_array($data['gender'], array('M', 'F')) ? $data['gender'] : null);

        $arr = array($data['username'], $pass, $salt, $gender, $data['mail'], $data['faction']);

        $stmt = $this->db->prepare($req);

        try {
            $stmt->execute($arr);   
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return array("success" => false, "message" => "Utilisateur ou adresse e-mail déjà existant.");
            } else {
                return array("success" => false, "message" => "Erreur inconnue.");
            }
        }

        return array("success" => true, "id" => $this->db->lastInsertId());
    }

    public function editUser($user, $data) {
        $req = 'UPDATE `User` SET `Mail` = ?, `Gender` = ?, `Avatar` = ? WHERE `Id` = ?';

        $gender = (in_array($data['gender'], array('M', 'F')) ? $data['gender'] : null);
        $arr = array($data['mail'], $gender, $data['avatar'], $user);

        $stmt = $this->db->prepare($req);

        try {
            $stmt->execute($arr);   
        } catch (PDOException $e) {
            if(!Config::$debug) $e = "";
            if ($e->errorInfo[1] == 1062) {
                return array("success" => false, "message" => "Erreur inconnue.<br/>".$e);
            }
        }

        return array("success" => true);
    }

    /**
     * Checks if an user exists with those credentials
     * @return User ID if login successful, false otherwise
     **/
    public function checkLogin($username, $password, $alreadySalted = false) {
        $req = 'SELECT `Id`, `Username`, `Password`, `Salt` FROM `User`
                WHERE `Username`=?';

        $statement = $this->db->prepare($req);
        $statement->execute(array($username));
        $result = $statement->fetch();

        $saltedPasswd = ($alreadySalted) ? $password : sha1($password.$result['Salt']);

        if($saltedPasswd == $result['Password']) {
            return array('Username' => $username, 'Password' => $saltedPasswd, 'Id' => $result['Id']);
        }

        return FALSE;
    }

    public function canUser($user, $action) {
        if($action == 'admin') $action = 'Admin';
        else return false;

        $req = 'SELECT `Can'.$action.'` AS `Permission` FROM `User` WHERE `Id` = ?';
        $st = $this->db->prepare($req);
        $st->execute(array($user));
        $r = $st->fetch();
        
        return ($r['Permission'] == 1) ? true : false;
    }

    public function updateActivity($user) {
        $this->cleanActiveUsers();
        
        $st = $this->db->prepare('SELECT EXISTS (SELECT `User` FROM `ActiveUsers` WHERE `User` = 1) AS `Result`');
        $st->execute();

        $timeout = Config::$app['activityTimeout'];

        if(intval($st->fetch()['Result']) == 0) { // user not listed in latest active members
            $st = $this->db->prepare('INSERT INTO `ActiveUsers` (`User`, `Expires`) VALUES (?, NOW()+'.$timeout.')');
        } else {
            $st = $this->db->prepare('UPDATE `ActiveUsers` SET `Expires` = NOW()+'.$timeout.'  WHERE `User` = ?');
        }

        $st->execute(array($user));
    }

    public function cleanActiveUsers() {
        if(self::$activeUsersAlreadyCleaned) return;

        $st = $this->db->prepare('DELETE FROM `ActiveUsers` WHERE `Expires` < NOW()');
        $st->execute();

        self::$activeUsersAlreadyCleaned = true;
    }

    public function countActiveUsers() {
        $this->cleanActiveUsers();

        $st = $this->db->prepare('SELECT COUNT(`User`) AS `Count` FROM `ActiveUsers`');
        $st->execute();
        return $st->fetch()['Count'];
    }
} 