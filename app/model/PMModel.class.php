<?php
class PMModel extends Model {
    public function PMModel() {
        parent::__construct();
    }

    public function getPMs($user) {
        $req = 'SELECT `PM`.`Id`, `Sender`, `Recipient`, `SendDate`, `Read`, `FileName`,
                    `S`.`UserName` AS `SenderName`, `R`.`UserName` AS `RecipientName`
                FROM `PM`
                JOIN `User` AS `S` ON `PM`.`Sender` = `S`.`Id`
                JOIN `User` AS `R` ON `PM`.`Recipient` = `R`.`Id`
                WHERE `Recipient` = :user OR `Sender` = :user';
                
        $st = $this->db->prepare($req);
        $st->execute(array(':user' => $user));
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
                WHERE `Recipient` = :user';

        $st = $this->db->prepare($req);
        $st->execute(array(':user' => $user));
        $rs = $st->fetchAll();

        foreach ($rs as &$message) {
            $message = $this->parseMessage($message);
        }

        return $rs;
    }

    public function getSent($user) {
        $req = 'SELECT `PM`.`Id`, `Sender`, `Recipient`, `SendDate`, `Read`, `FileName`,
                    `S`.`UserName` AS `SenderName`, `R`.`UserName` AS `RecipientName`
                FROM `PM`
                JOIN `User` AS `S` ON `PM`.`Sender` = `S`.`Id`
                JOIN `User` AS `R` ON `PM`.`Recipient` = `R`.`Id`
                WHERE `Sender` = :user';
        $st = $this->db->prepare($req);
        $st->execute(array(':user' => $user));
        $rs = $st->fetchAll();

        foreach ($rs as &$message) {
            $message = $this->parseMessage($message);
        }

        return $rs;
    }

    private function parseMessage($data) {
        $message = file_get_contents(Config::$path['pm'].'/'.$data['FileName']);

        return array(
            'Id' => $data['Id'],
            'SenderId' => $data['Sender'],
            'Sender' => $data['SenderName'],
            'RecipientId' => $data['Recipient'],
            'Recipient' => $data['RecipientName'],
            'Read' => ($data['Read'] == 'Y') ? true : false,
            'Message' => $message,
            'Timestamp' => strtotime($data['SendDate'])
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

    }
}