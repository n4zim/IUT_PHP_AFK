<?php
/**
 * Manages tables :
 *  Event
 *  EventEntrant
 **/
class EventModel extends Model {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get upcoming events
     * 
     * If I was to redo this function, I would use an array instead of this huuuge number of parameters.
     * It would be a little less confusing I think.
     * WHY CAN'T WE SURCHARGE FUNCTIONS IN PHP :(...
     * 
     * @param $id Event id, shows all upcoming event if null or unspecified
     * @param $allTime Show passed events if true. False by default.
     * @param $page Page number, do not paginate if null.
     * @param $checkForUser If supplied, will also add a field to say if this User (id) is subscribed to the event or not
     * @param $limit Number of events to get by page, defaults to config if unset
     * @param $order How to order events, possible values : ('eventdate', 'score', 'latest')
     * 
     * @return Associative Array containing fields from Event table
     **/
    public function getEvents($id = null, $allTime = false, $page = null, $checkForUser = null, $limit = null, $order = '') {
        $clauseId = (isset($id)) ? ' AND `Event`.`Id` = :id' : '';
        $clauseUser = (isset($checkForUser)) ? ', IF(`EventEntrant`.`User` IS NULL, 0, 1) AS `Subscribed`' : ', \'0\' AS `Subscribed`';
        $clauseUser2 = (isset($checkForUser)) ? ' LEFT JOIN `EventEntrant` ON `EventEntrant`.`Event`= `Id` AND `EventEntrant`.`User` = :userId' : '';
        $req = 'SELECT `Event`.`Id`,  `Organizer`, `User`.`Username`, `Titre`,  `Description`, `TypeEvent`, `EventType`.`TypeName`, `Image`,  `Place`,  UNIX_TIMESTAMP(`PostDate`) AS `PostDate`,  UNIX_TIMESTAMP(`EventDate`) AS `EventDate`,  `Reward` '.$clauseUser.'
                FROM `Event`'.$clauseUser2.'
                JOIN `EventType` ON `EventType`.`Id` = `TypeEvent`
                JOIN `User` ON `User`.`Id` = `Event`.`Organizer`
                WHERE `EventDate` >= :eventDate '.$clauseId.'
                ORDER BY ';

        switch ($order) {
            case 'latest':
                $req .= '`PostDate` DESC';
                break;

            case 'score':
                $req .= '`Score` DESC, `EventDate` DESC';
                break;
            
            default:
                $req .= '`EventDate` DESC';
                break;
        }
        
        if(isset($page))
            $req .= ' LIMIT :min, :max';

        $statement = $this->db->prepare($req);

        if(isset($page)) {
            $perPage = (empty($limit) ? Config::$listing['eventsPerPage'] : $limit);
            $min = $page * $perPage;
            $statement->bindValue('min', $page * $perPage, PDO::PARAM_INT);
            $statement->bindValue('max', $min + $perPage, PDO::PARAM_INT);
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

    /**
     * Returns events an user is subscribed to
     * 
     * @param $event Event Id
     **/
    public function getSubscribed($event) {
        $req = 'SELECT `User`.`Username`, `User`.`Id`, `EventEntrant`.`JoinDate`, IFNULL(`Avatar`, `Faction`.`Logo`) AS `Avatar`, `Faction`.`Logo` AS `FactionLogo`, `Faction`.`Name` AS `FactionName`
                FROM `EventEntrant`
                JOIN `User` ON `User`.`Id` = `EventEntrant`.`User`
                JOIN `Faction` ON `Faction`.`Id` = `User`.`Faction`
                WHERE `Event` = ?';
        $st = $this->db->prepare($req);
        $st->execute(array($event));
        return $st->fetchAll();
    }


    /**
     * Returns events an user is subscribed to
     * 
     * @param $event Event Id
     **/
    public function getUpcomingEventsFor($user) {
        $req = 'SELECT `Titre`, `Event`.`Id`, UNIX_TIMESTAMP(`EventDate`) AS `EventDate`
                FROM `EventEntrant`
                JOIN `User` ON `User`.`Id` = `EventEntrant`.`User`
                JOIN `Event` ON `Event`.`Id` = `EventEntrant`.`Event`
                WHERE `User` = ? AND `EventDate` > NOW()-6*60*60 ORDER BY `EventDate` ASC';
        $st = $this->db->prepare($req);
        $st->execute(array($user));
        return $st->fetchAll();
    }

    /**
     * Returns events an user is subscribed to
     * 
     * @param $event User Id
     **/
    public function getUserSubs($user) {
        $req = 'SELECT `Titre`, `Event`.`Id`, UNIX_TIMESTAMP(`EventDate`) AS `EventDate`
                FROM `EventEntrant`
                JOIN `User` ON `User`.`Id` = `EventEntrant`.`User`
                JOIN `Event` ON `Event`.`Id` = `EventEntrant`.`Event`
                WHERE `User` = ? ORDER BY `EventDate` DESC';
        $st = $this->db->prepare($req);
        $st->execute(array($user));
        return $st->fetchAll();
    }

    public function getUserEvents($user) {
        $req = 'SELECT `Titre`, `Id`, UNIX_TIMESTAMP(`EventDate`) AS `EventDate`
                FROM `Event`
                WHERE `Organizer` = ? ORDER BY `EventDate` DESC';
        $st = $this->db->prepare($req);
        $st->execute(array($user));
        return $st->fetchAll();
    }

    public function addEvent($user, $data) {
        $req = 'INSERT INTO `Event` (`Organizer`, `Titre`, `TypeEvent`, `Description`, `Image`, `Place`, `PostDate`, `EventDate`)
               VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)';

        $date = Helpers::formatSQLDate(strtotime($data['date'].' '.$data['heure']));

        $data = array(
            $user,
            $data['title'],
            $data['type'],
            $data['description'],
            (isset($data['image'])) ? $data['image'] : null,
            $data['place'],
            $date
        );
        
        $st = $this->db->prepare($req);
        $st->execute($data);

        return array("success" => true, 'id' => $this->db->lastInsertId());
    }

    public function editEvent($id, $data) {
        $req = 'UPDATE `Event` SET `Titre` = ?, `TypeEvent` = ?, `Description` = ?, `Image` = ?, `Place` = ?, `EventDate` = ? WHERE `Id` = ?';

        $date = Helpers::formatSQLDate(strtotime($data['date'].' '.$data['heure']));

        $data = array(
            $data['title'],
            $data['type'],
            $data['description'],
            (isset($data['image'])) ? $data['image'] : null,
            $data['place'],
            $date,
            $id
        );
        
        $st = $this->db->prepare($req);
        $st->execute($data);

        return array("success" => true, 'id' => $id);
    }

    public function getTypes() {
        $req = 'SELECT `Id`, `TypeName` FROM `EventType`';
        $st = $this->db->prepare($req);
        $st->execute();
        return $st->fetchAll();
    }

    public function deleteEvent($id) {
        $st = $this->db->prepare("DELETE FROM `EventEntrant` WHERE `Event` = ?");
        $st->execute(array($id));
        
        $st = $this->db->prepare("DELETE FROM `Event` WHERE `Id` = ?");
        $st->execute(array($id));
    }

    public function deleteEventsByUser($id) {
        $st = $this->db->prepare("DELETE FROM `EventEntrant` WHERE `User` = ?");
        $st->execute(array($id));
        
        $events = $this->getUserEvents($id);
        foreach ($events as $event) {
            $st = $this->db->prepare("DELETE FROM `EventEntrant` WHERE `Event` = ?");
            $st->execute(array($id));
            $st = $this->db->prepare("DELETE FROM `Event` WHERE `Id` = ?");
            $st->execute(array($event['Id']));
        }

    }
}