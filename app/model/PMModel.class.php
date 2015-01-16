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

    public function getPaste($slug) {
        $req = 'SELECT `Slug`, `IP`, `Posted`, `Expires`, `Code`, `Paste`, `DeleteLink`
                FROM `paste`
                WHERE `Slug` = ?';

        $st = $this->db->prepare($req);
        $st->execute(array($slug));
        $rs = $st->fetch();

        return $rs;
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

    public function addPaste($data) {
        $req = 'INSERT INTO `paste` (`Slug`, `IP`, `Posted`, `Code`, `Paste`, `DeleteLink`) VALUES (?, ?, ?, ?, ?, ?)';
        $slug = '';

        $slug = 'vitjar';


        $st = $this->db->prepare($req);

        while(true) {
            try {
                $slug = $this->genSlug();

                $d = array(
                    $slug,
                    $_SERVER['REMOTE_ADDR'],
                    Helpers::formatSQLDate(time()),
                    null,
                    $data['paste'],
                    ''
                );

                $st->execute($d);
                break;
            } catch (PDOException $e) {
                if ($e->errorInfo[1] != 1062) {
                    return false;
                }
            }
        }

        return $slug;
    }

    private function genSlug() {
        $pw = '';
        $c = 'bcdfghjklmnprstvwz'; //consonants except hard to speak ones
        $v = 'aeiou'; //vowels
        $a = $c.$v; //both
         
        //use two syllables...
        for($i=0;$i < 2; $i++){
        $pw .= $c[rand(0, strlen($c)-1)];
        $pw .= $v[rand(0, strlen($v)-1)];
        $pw .= $a[rand(0, strlen($a)-1)];
        }
        //... and add a nice number
        //$pw .= rand(10,99);
         
        return $pw;
    }
}