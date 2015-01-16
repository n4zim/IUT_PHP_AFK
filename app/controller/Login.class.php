<?php
class Login extends Controller {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Main method, shows login form
     * @param args Argument array. Used arguments : 
     **/
    public function index($args) {
        Login::checkIfNotLogguedIn();

        $this->afk->view('login/form', array(
            'formAction' => Helpers::makeUrl('login', 'post'),
            'loginFailed' => isset($args['bad'])
        ));
    }

    /**
     * 
     *
     **/
    public function post($args) {
        Login::checkIfNotLogguedIn();

        if(empty($_POST['username']) || empty($_POST['password'])) {
            echo 'missing data';
        }

        $usermodel = new UserModel();
        $r = $usermodel->checkLogin($_POST['username'], $_POST['password']);

        if($r === FALSE)
            Helpers::redirect('login', null, 'bad');

        Login::loginUser($r);
        
        Helpers::notify('Connexion effectuée', 'Vous êtes dès à présent connecté à votre compte.');

        if(Login::getGoto() !== FALSE) {
            header('Location: '.Login::getGoto()); 
            Login::unsetGoto();
            exit();
        }

        Helpers::redirect('index');
    }

    public function out() {
        if(!isset($_SESSION['u.id'])) {
            Helpers::notify('Pas connecté', 'Impossible de vous déconnecter car vous êtes déjà deconnecté.', 'error');
            Helpers::redirect('index');
        }

        $this->logoutUser();
        Helpers::notify('Déconnecté', 'Votre session à bien été fermée.');
        Helpers::redirect('index');
    }

    public static function loginUser($data) {
        if(isset($data['routed'])) exit(); // prevent direct method calling

        $_SESSION['u.username'] = $data['Username'];
        $_SESSION['u.id'] = $data['Id'];
    }

    private function logoutUser() {
        unset($_SESSION['u.username']);
        unset($_SESSION['u.id']);
    }

    public static function checkIfLogguedIn() {
        if(empty($_SESSION['u.id'])) {
            Helpers::notify('Non connecté', 'Vous devez être connecté pour accéder à cette page', 'error');
            
            if(Login::getGoto() !== FALSE)
                Helpers::redirect('login');

            Helpers::redirect('index');
        }
    }

    private static function checkIfNotLogguedIn() {
        if(isset($_SESSION['u.id'])) {
            Helpers::notify('Déjà connecté', 'Vous êtes déjà connecté.', 'error');
            Helpers::redirect('index');
        }
    }

    public static function setGoto($action, $method = null, $args = null) {
        $_SESSION['l.goto'] = Helpers::makeUrl($action, $method, $args, false);
    }

    public static function unsetGoto() {
        $_SESSION['l.goto'] = null;
    }

    public static function getGoto() {
        if(isset($_SESSION['l.goto'])) return $_SESSION['l.goto'];
        return FALSE;
    }
} 