<?php
class EventModel extends Model {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get upcoming events
     * 
     * @param $id Event id, shows all upcoming event if null or unspecified
     * @param $allTime Show passed events if true. False by default.
     * @param $page Page number, do not paginate if null.
     * @param $currentUser If supplied, will also add a field to say if this User (id) is subscribed to the event or not
     * 
     * @return Associative Array containing fields from Event table
     **/
    public function getEvents($id = null, $allTime = false, $page = null, $checkForUser = null) {
        $clauseId = (isset($id)) ? ' AND `Id` = :id' : '';
        $clauseUser = (isset($checkForUser)) ? ', IF(`EventEntrant`.`User` IS NULL, 0, 1) AS `Subscribed`' : '';
        $clauseUser2 = (isset($checkForUser)) ? ' LEFT JOIN `EventEntrant` ON `EventEntrant`.`Event`= `Id` AND `EventEntrant`.`User` = :userId' : '';
        $req = 'SELECT `Id`,  `Organizer`,  `Titre`,  `Description`,  `Image`,  `Place`,  UNIX_TIMESTAMP(`PostDate`) AS `PostDate`,  UNIX_TIMESTAMP(`EventDate`) AS `EventDate`,  `Reward` '.$clauseUser.'
                FROM `Event`'.$clauseUser2.'
                WHERE `EventDate` > :eventDate '.$clauseId.'
                ORDER BY `EventDate` DESC';
        
        if(isset($page))
            $req .= ' LIMIT :min, :max';

        $statement = $this->db->prepare($req);

        if(isset($page)) {
            $min = $page * Config::$listing['usersPerPage'];
            $statement->bindValue('min', $page * Config::$listing['usersPerPage'], PDO::PARAM_INT);
            $statement->bindValue('max', $min + Config::$listing['usersPerPage'], PDO::PARAM_INT);
        }

        if(isset($id)) $statement->bindValue('id', $id);
        if($allTime || empty($id)) $statement->bindValue('eventDate', Helpers::formatSQLDate(0));
        else $statement->bindValue('eventDate', Helpers::formatSQLDate(time()));

        if(isset($checkForUser)) $statement->bindValue('userId', $checkForUser);

        $statement->execute();

        $result = $statement->fetchAll();
        if(isset($id)) $result = $result[0];
        return $result;
    }

    /**
     * Counts upcoming events
     * 
     * @param $allTime Count passed events if true. False by default.
     * @return Number
     **/
    public function countEvents($allTime = false) {
        $req = 'SELECT COUNT(`Id`) AS `Count`
                FROM `Event`
                WHERE `EventDate` > :eventDate';

        $statement = $this->db->prepare($req);
        $statement->bindValue('eventDate', Helpers::formatSQLDate( ($allTime) ? time() : 0 ));
        $statement->execute();

        $result = $statement->fetch();
        return $result['Count'];
    }

    /**
     * Subscribes an user to an event
     * 
     * @param $user User Id
     * @param $event Event Id
     * @return array('success' => true)
     * @return array('success' => false, 'message' => 'description de l\'erreur')
     **/
    public function subscribeUser($user, $event) {
        $req = 'INSERT INTO `EventEntrant` (`Event`, `User`, `JoinDate`) VALUES (?, ?, NOW())';
        $st = $this->db->prepare($req);

        try {
            $st->execute(array($event, $user));
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return array("success" => false, "message" => "Utilisateur déjà inscrit à cet évenement.");
            } else {
                return array("success" => false, "message" => "Erreur inconnue.");
            }
        }

        return array("success" => true);
    }

    /**
     * Unsubscribes an user frmm an event
     * 
     * @param $user User Id
     * @param $event Event Id
     * @return array('success' => true)
     * @return array('success' => false, 'message' => 'description de l\'erreur')
     **/
    public function unsubscribeUser($user, $event) {
        $req = 'DELETE FROM `EventEntrant` WHERE `Event` = ? AND `User` = ?';
        $st = $this->db->prepare($req);
        $st->execute(array($event, $user));

        if($st->rowCount() < 1)
            return array('success' => false, 'message' => 'Vous n\'étiez pas inscrit à cet évenement');
        return array('success' => true);
    }

    public function getSubscribed($user) {

    }
}