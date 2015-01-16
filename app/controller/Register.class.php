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
        if($erreur != "") $this->notifyError($erreur);

        // protect fields
        foreach ($protectFields as &$field) {
            $_POST[$field] = htmlentities($_POST[$field]);
        }

        // check if passwords are the same
        if($_POST['password'] != $_POST['password2'])
            $this->notifyError("Les mots de passe ne correspondent pas.");

        // validate the mail
        if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL))
            $this->notifyError("L'adresse email n'est pas valide");

        // protect faction id
        $_POST['faction'] = intval($_POST['faction']);

        // init faction model
        $factionsModel = new FactionModel();

        // if we want a random faction OR if we supplied a specific faction id
        if($_POST['faction'] == -1) {
            $faction = $factionsModel->getRandomFaction();
        } else {
            // check if the faction actually exists
            $faction = $factionsModel->getFaction($_POST['faction']);
            if(empty($faction))
                $this->notifyError("Faction incorrecte");
        }

        // insert in database
        $usermodel = new UserModel();
        $r = $usermodel->register($_POST);
        
        if($r['success']) {
            Login::loginUser($usermodel->getUser($r['id'])[0]);
            Helpers::notify('Inscription effectuée', 'Vous êtes dès à présent connecté à votre compte.');
            Helpers::redirect('index');
        } else {
            $this->notifyError("Erreur : ".$r['message']);
        }
    }

    private function notifyError($error) {
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