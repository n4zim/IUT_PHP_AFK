<?php
class PMModel extends Model {
    public function PMModel() {
        parent::__construct();
    }

    public function getPMs($user, $pmId = null) {
        $req = 'SELECT `PM`.`Id`, `Sender`, `Recipient`, `R`.`Id` AS `RecipientId`, `SendDate`, `Read`, `FileName`,
                    `S`.`UserName` AS `SenderName`, `R`.`UserName` AS `RecipientName`
                FROM `PM`
                JOIN `User` AS `S` ON `PM`.`Sender` = `S`.`Id`
                JOIN `User` AS `R` ON `PM`.`Recipient` = `R`.`Id`
                WHERE (`Recipient` = :user OR `Sender` = :user)'.(isset($pmId) ? ' AND `PM`.`Id` = :pmId' : '');
        
        $data = array(':user' => $user);
        if(isset($pmId)) $data[':pmId'] = $pmId;

        $st = $this->db->prepare($req);
        $st->execute($data);
        $rs = $st->fetchAll();

        foreach ($rs as &$message) {
            $message = $this->parseMessage($message);
        }

        return $rs;
    }

// todo : réunir les 2 fonctions
    public function getReceived($user) {
        $req = 'SELECT `PM`.`Id`, `Sender`, `Recipient`, `SendDate`, `Read`, `FileName`,
                    `S`.`UserName` AS `SenderName`, `R`.`UserName` AS `RecipientName`
                FROM `PM`
                JOIN `User` AS `S` ON `PM`.`Sender` = `S`.`Id`
                JOIN `User` AS `R` ON `PM`.`Recipient` = `R`.`Id`
                WHERE `Recipient` = :user
                ORDER BY `SendDate` DESC';

        $st = $this->db->prepare($req);
        $st->execute(array(':user' => $user));
        $rs = $st->fetchAll();

        $msgs = array();

        foreach ($rs as $message) {
            $m = $this->parseMessage($message);
            if(!empty($m)) $msgs[] = $m;
        }

        return $msgs;
    }

    public function getSent($user) {
        $req = 'SELECT `PM`.`Id`, `Sender`, `Recipient`, `SendDate`, `Read`, `FileName`,
                    `S`.`UserName` AS `SenderName`, `R`.`UserName` AS `RecipientName`
                FROM `PM`
                JOIN `User` AS `S` ON `PM`.`Sender` = `S`.`Id`
                JOIN `User` AS `R` ON `PM`.`Recipient` = `R`.`Id`
                WHERE `Sender` = :user
                ORDER BY `SendDate` DESC';
        $st = $this->db->prepare($req);
        $st->execute(array(':user' => $user));
        $rs = $st->fetchAll();

        $msgs = array();

        foreach ($rs as $message) {
            $m = $this->parseMessage($message);
            if(!empty($m)) $msgs[] = $m;
        }

        return $msgs;
    }

    private function parseMessage($data) {
        $fname = Config::$path['pm'].'/'.$data['FileName'];
        if(!file_exists($fname)) return null;
        $message = file_get_contents($fname);

        return array(
            'Id' => $data['Id'],
            'SenderId' => $data['Sender'],
            'Sender' => $data['SenderName'],
            'RecipientId' => $data['Recipient'],
            'Recipient' => $data['RecipientName'],
            'RecipientProfile' => Helpers::makeUrl('user', 'profile', array('id' => $data['Recipient'])),
            'SenderProfile' => Helpers::makeUrl('user', 'profile', array('id' => $data['Sender'])),
            'Read' => ($data['Read'] == 'Y') ? true : false,
            'Message' => $message,
            'Timestamp' => strtotime($data['SendDate']),
            'Url' => Helpers::makeUrl('pm', 'view', array('id' => $data['Id']))
        );
    }

    public function sendPM($from, $to, $message) {
        $req = 'INSERT INTO `PM` (`Sender`, `Recipient`, `SendDate`, `FileName`, `Read`) VALUES (?, ?, ?, ?, \'N\')';

        if($from == $to) return false;

        $filename = uniqid().'.pm';
        $filepath = Config::$path['pm'].'/'.$filename;

        // creates the file
        file_put_contents($filepath, $message);

        $st = $this->db->prepare($req);
        $st->execute(array($from, $to, Helpers::formatSQLDate(time()), $filename));
        return $this->db->lastInsertId();
    }

    public function countUnread($user) {
        $st = $this->db->prepare('SELECT COUNT(`Id`) AS `Count` FROM `PM` WHERE `Recipient` = ? AND `Read` = \'N\'');
        $st->execute(array($user));
        return $st->fetch()['Count'];
    }

    public function markRead($message, $read = true) {
        $status = ($read) ? 'Y' : 'N';
        $st = $this->db->prepare('UPDATE `PM` SET `Read` = ? WHERE `Id` = ?');
        $st->execute(array($status, $message));
    }

    public function deletePM($id) {
        $st = $this->db->prepare('DELETE FROM `PM` WHERE `Id` = ?');
        $st->execute(array($user));
    }
}