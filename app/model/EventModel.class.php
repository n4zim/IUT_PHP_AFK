<?php
class EventModel extends Model {
    public function FactionModel() {
        parent::__construct();
    }

    /**
     * Get upcoming events
     * 
     * @param $id Event id, shows all upcoming event if null or unspecified
     * @param $allTime Show passed events if true. False by default.
     * @param $page Page number, do not paginate if null.
     * 
     * @return Associative Array containing fields from Event table
     **/
    public function getEvents($id = null, $allTime = false, $page = null) {
        $clauseId = (isset($id)) ? ' AND `Id` = :id' : '';
        $req = 'SELECT `Id`,  `Organizer`,  `Titre`,  `Description`,  `Image`,  `Place`,  UNIX_TIMESTAMP(`PostDate`) AS `PostDate`,  UNIX_TIMESTAMP(`EventDate`) AS `EventDate`,  `Reward`
                FROM `Event`
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
        if($allTime && empty($id)) $statement->bindValue('eventDate', Helpers::formatSQLDate(0));
        else $statement->bindValue('eventDate', Helpers::formatSQLDate(time()));

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
}