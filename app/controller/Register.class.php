<?php
class Register extends Controller {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Main method, shows register form
     * @param args Argument array. Used arguments : 
     **/
    public function index($args) {
        $this->checkIfNotLogguedIn();

        $factionsModel = new FactionModel();
        $factions = $factionsModel->getFactions();

        $this->afk->view('register/form', array(
            'formAction' => Helpers::makeUrl('register', 'post'),
            'factions' => $factions
        ));
    }

    /**
     * 
     *
     **/
    public function post($args) {
        $this->checkIfNotLogguedIn();

        $mandatoryFields = array('username', 'mail', 'password', 'password2', 'gender', 'faction');
        $protectFields = array('username', 'mail');
        $mandatoryFieldsNames = array('nom d\'utilisateur', 'adresse email', 'mot de passe', 'confirmation du mot de passe', 'sexe', 'faction');

        // check if all fields are set
        $erreur = "";
        foreach ($mandatoryFields as $key => $field) {
            if(empty($_POST[$field]))
                $erreur .= "Le champ ".$mandatoryFieldsNames[$key]." est vide.<br />";
        }
        if($erreur != "") self::notifyError($erreur);

        // protect fields
        foreach ($protectFields as &$field) {
            $_POST[$field] = htmlentities($_POST[$field]);
        }

        // check if passwords are the same
        if($_POST['password'] != $_POST['password2'])
            self::notifyError("Les mots de passe ne correspondent pas.");

        // validate the mail
        if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL))
            self::notifyError("L'adresse email n'est pas valide");

        // protect faction id
        $_POST['faction'] = intval($_POST['faction']);

        // init faction model
        $factionsModel = new FactionModel();

        // if we want a random faction OR if we supplied a specific faction id
        if($_POST['faction'] == -1) {
            $_POST['faction'] = $factionsModel->getRandomFaction()['Id'];
        } else {
            // check if the faction actually exists
            $faction = $factionsModel->getFaction($_POST['faction']);
            if(empty($faction))
                self::notifyError("Faction incorrecte");
        }

        // insert in database
        $usermodel = new UserModel();
        $r = $usermodel->register($_POST);
        
        if($r['success']) {
            self::sendActivationMail($r['id']);
            Helpers::notify('Inscription effectuée', 'Un e-mail vous à été envoyé pour confirmer votre compte.');
            Helpers::redirect('index');
        } else {
            self::notifyError("Erreur : ".$r['message']);
        }
    }

    public function activate($args) {
        if(empty($args['token']))
            self::notifyError('Le jeton d\'activation est invalide');

        $usermodel = new UserModel();
        if($usermodel->activateAccount($args['token'])) {
            Helpers::notify('Compte activé !', 'Vous pouvez dès à présent vous connecter.');
            Helpers::redirect('login');
        }
        
        self::notifyError('Le jeton d\'activation est invalide');
    }


    public static function sendActivationMail($uid) {
        if(empty($uid)) return;

        $usermodel = new UserModel();
        $r = $usermodel->getUser($uid);
        $token = $r['ActivationToken'];
        $link = 'http://'.Config::$app['hostname'].'/'.Helpers::makeUrl('register', 'activate', array('token' => $token));

        $msg  = "<h1>Bienvenue à CookieCatch</h1>\n\n";
        $msg .= "<p>Votre inscription s'est déroulée avec succès.<br />\n";
        $msg .= "Votre identifiant de connexion est : <strong>".$r['Username']."</strong></p>\n";
        $msg .= "<p>Avant de vous connecter, vous devez activer votre compte en vous rendant sur ce lien :<br />\n";
        $msg .= '<a href="'.$link.'">'.$link.'</a></p>';

        Helpers::sendMail($r['Mail'], 'Bienvenue à CookieCatch !', $msg);
    }


    private static function notifyError($error) {
        Helpers::notify('Erreur', $error, 'error');
        Helpers::redirect('register');
    }

    public static function isLogguedIn() {
        return isset($_SESSION['u.id']);
    }

    private function checkIfNotLogguedIn() {
        if(isset($_SESSION['u.id'])) {
            Helpers::notify('Déjà connecté', 'Vous êtes déjà connecté.', 'error');
            Helpers::redirect('index');
        }
    }
} 