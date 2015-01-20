<?php
class EventModel extends Model {
    public function FactionModel() {
        parent::__construct();
    }

    public function getEvents($id = null, $allTime = false) {
        $clauseId = (isset($id)) ? ' AND `Id` = :id' : '';
        $req = 'SELECT `Id`,  `Organizer`,  `Titre`,  `Description`,  `Image`,  `Place`,  UNIX_TIMESTAMP(`PostDate`) AS `PostDate`,  UNIX_TIMESTAMP(`EventDate`) AS `EventDate`,  `Reward`
                FROM `Event`
                WHERE `EventDate` > :eventDate '.$clauseId.'
                ORDER BY `EventDate` DESC';

        $data = array(':eventDate' => Helpers::formatSQLDate(time()));
        if($allTime) $data[':eventDate'] = Helpers::formatSQLDate(0);
        if(isset($id)) $data[':id'] = $id;

        $statement = $this->db->prepare($req);
        $statement->execute($data);

        $result = $statement->fetchAll();
        if(isset($id)) $result = $result[0];
        return $result;
    }
}