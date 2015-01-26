<?php
class UserModel extends Model {
    private static $activeUsersAlreadyCleaned = false;

    public function UserModel() {
        parent::__construct();
    }

    public function getUserIdByName($name) {
        $st = $this->db->prepare('SELECT `Id` FROM `User` WHERE `Username` = ?');
        $st->execute(array($name));
        $return = $st->fetch();
        return $return['Id'];
    }

    public function getUsers($page = 0, $faction = null) {
        $min = $page * Config::$listing['usersPerPage'];
        $max = $min + Config::$listing['usersPerPage'];

        $req = 'SELECT `User`.`Id`, `Username`, `Password`, `Salt`, UNIX_TIMESTAMP(`RegisterDate`) AS `RegisterDate`, `Mail`, `Gender`, IFNULL(`Avatar`, `Faction`.`Logo`) AS `Avatar`, `CanAdmin`,
                       `Faction`, `Faction`.`Name` AS `FactionName`, `Faction`.`Id` AS `FactionId`, `Faction`.`Logo` AS `FactionLogo`
                FROM `User`
                JOIN `Faction` ON `Faction`.`Id` = `User`.`Faction`
                WHERE `ActivationToken` IS NULL'.
                (isset($faction) ? ' AND `User`.`Faction` = :facId ' : ' ').'
                ORDER BY `Username`
                LIMIT :min, :max';

        $statement = $this->db->prepare($req);
        $statement->bindValue('min', $min, PDO::PARAM_INT);
        $statement->bindValue('max', $max, PDO::PARAM_INT);
        if(isset($faction)) $statement->bindValue('facId', $faction, PDO::PARAM_INT);
        $statement->execute();

        $results = $statement->fetchAll();
        return $results;
    }

    public function countUsers() {
        $req = 'SELECT COUNT(`Id`) AS `Count`
                FROM `User` WHERE `ActivationToken` IS NULL';

        $statement = $this->db->prepare($req);
        $statement->execute();

        $result = $statement->fetch();
        return $result['Count'];
    }

    public function getUser($id = null) {
        $req = 'SELECT `User`.`Id`, `Username`, `Password`, `Salt`, `Mail`, `Gender`,
                IFNULL(`Avatar`, `Faction`.`Logo`) AS `Avatar`,
                `ActivationToken`,
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
        $req = 'INSERT INTO `User` (`Username`, `Password`, `Salt`, `Gender`, `Mail`, `Faction`, `ActivationToken`, `RegisterDate`) VALUES (?, ?, ?, ?, ?, ?, ?, NOW());';

        $salt = md5($data['username'].time());
        $pass = sha1($data['password'].$salt);
        $gender = (in_array($data['gender'], array('M', 'F')) ? $data['gender'] : null);

        $token = md5(uniqid(rand(), true));

        $arr = array($data['username'], $pass, $salt, $gender, $data['mail'], $data['faction'], $token);

        $stmt = $this->db->prepare($req);

        try {
            $stmt->execute($arr);   
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return array("success" => false, "message" => "Utilisateur ou adresse e-mail déjà existant.");
            } else {
                return array("success" => false, "message" => "Erreur inconnue.<br/>".$e);
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
        $req = 'SELECT `Id`, `Username`, `Password`, `ActivationToken`, `CanAdmin`, `Salt` FROM `User`
                WHERE `Username`=?';

        $statement = $this->db->prepare($req);
        $statement->execute(array($username));
        $result = $statement->fetch();

        $saltedPasswd = ($alreadySalted) ? $password : sha1($password.$result['Salt']);

        if($saltedPasswd == $result['Password']) {
            return array('Username' => $username, 'Password' => $saltedPasswd, 'CanAdmin' => $result['CanAdmin'], 'Id' => $result['Id'], 'ActivationToken' => $result['ActivationToken']);
        }

        return FALSE;
    }

    /**
     * Checks if an user has the right to..
     **/
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
        
        $timeout = Config::$app['activityTimeout'];

        $st = $this->db->prepare('INSERT INTO `ActiveUsers` (`User`, `Expires`) VALUES (?, NOW()+'.$timeout.') ON DUPLICATE KEY UPDATE `User`=VALUES(`User`)');

        $st->execute(array($user));
    }

    public function cleanActiveUsers() {
        if(self::$activeUsersAlreadyCleaned) return;

        $st = $this->db->prepare('DELETE FROM `ActiveUsers` WHERE `Expires` > NOW()');
        $st->execute();

        self::$activeUsersAlreadyCleaned = true;
    }

    public function countActiveUsers() {
        $this->cleanActiveUsers();

        $st = $this->db->prepare('SELECT COUNT(`User`) AS `Count` FROM `ActiveUsers`');
        $st->execute();
        $return = $st->fetch();
        return $return['Count'];
    }

    public function getFriendsOf($user) {
        $st = $this->db->prepare('SELECT `B`.`Id`, `B`.`Username`, IFNULL(`B`.`Avatar`, `Faction`.`Logo`) AS `Avatar`
            FROM `Friend`
            JOIN `User` `B` ON `B`.`Id` = `Friend`.`UserB`
            JOIN `Faction` ON `B`.`Faction` = `Faction`.`Id`
            WHERE `UserA` = ?');
        $st->execute(array($user));

        return $st->fetchAll();
    }

    public function addFriend($a, $b) {
        $st = $this->db->prepare('INSERT INTO `Friend` (`UserA`, `UserB`) VALUES (?, ?)');
        try {
            $st->execute(array($a, $b));
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return array("success" => false, "message" => "Utilisateur déjà présent dans la liste d'amis.");
            } else {
                return array("success" => false, "message" => "Erreur inconnue.");
            }
        }
        return array('success' => true);
    }

    public function removeFriend($a, $b) {
        $st = $this->db->prepare('DELETE FROM `Friend` WHERE `UserA` = ? AND `UserB` = ?');
        try {
            $st->execute(array($a, $b));
        } catch (PDOException $e) {
            return array("success" => false, "message" => "Impossible de trouver de l'eau sur Mars afin satisfaire votre requête et lancer un seau d'eau sur la tête de votre 'ami'.");
        }
        return array('success' => true);
    }

    public function activateAccount($token) {
        $st = $this->db->prepare('UPDATE `User` SET `ActivationToken` = NULL WHERE `ActivationToken` = ?');
        $st->execute(array($token));
        return $st->rowCount();
    }

    public function deleteFriends($id) {
        $st = $this->db->prepare("DELETE FROM `Friend` WHERE `UserA` = ? OR `UserB` = ?");
        $st->execute(array($id, $id));
    }

    public function deleteUser($id) {
        $em = new EventModel();
        $pm = new PMModel();

        $em->deleteEventsByUser($id);
        $pm->deletePMsOf($id);
        $this->deleteFriends($id);
        
        $req = "DELETE FROM `User` WHERE `Id` = ?";
        
        $st = $this->db->prepare($req);
        $st->execute(array($id));
    }
} 