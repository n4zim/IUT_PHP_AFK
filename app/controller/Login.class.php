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

        print_r($_SESSION);
        $this->afk->view('login/form', array(
            'formAction' => Helpers::makeUrl('login', 'post')
        ));
    }

    public function post($args) {
        Login::checkIfNotLogguedIn();

        if(empty($_POST['username']) || empty($_POST['password'])) {
            echo 'missing data';
        }

        $usermodel = new UserModel();
        $r = $usermodel->checkLogin($_POST['username'], $_POST['password']);

        if($r === FALSE) {
            Helpers::notify('Erreur', 'Mauvais login ou mot de passe', 'error');
            Helpers::redirect('login');
        }

        Login::loginUser($r);

        if(isset($_POST['remember'])) {
            $cookieVal = array('Username' => $r['Username'], 'Password' => $r['Password']);
            setcookie("loginCookie", serialize($cookieVal), time()+3600);
        }
        
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
            Helpers::notify('Pas connecté', 'Impossible de vous déconnecter car vous êtes déjà deconnecté (votre session a probablement expiré).', 'error');
            Helpers::redirect('index');
        }

        Login::logoutUser();
        Helpers::notify('Déconnecté', 'Votre session à bien été fermée.');
        Helpers::redirect('index');
    }

    public static function checkCookie() {
        if(isset($_COOKIE['loginCookie']) && empty($_SESSION['u.id'])) {
            $c = unserialize($_COOKIE['loginCookie']);
            
            if(empty($c['Username']) || empty($c['Password'])) {
                unset($_COOKIE['loginCookie']);
                return;
            }
            
            $usermodel = new UserModel();
            $r = $usermodel->checkLogin($c['Username'], $c['Password'], true);

            if($r === FALSE) {
                unset($_COOKIE['loginCookie']);
            }

            self::loginUser($r);
        }

    }

    public static function loginUser($data) {
        $_SESSION['u.username'] = $data['Username'];
        $_SESSION['u.id'] = $data['Id'];
    }

    public static function logoutUser($args) {
        // unset session vars
        unset($_SESSION['u.username']);
        unset($_SESSION['u.id']);

        // remove cookies if any
        if(isset($_COOKIE['loginCookie']))
            setcookie('loginCookie', '', time() - 3600);
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
        unset($_SESSION['l.goto']);
    }

    public static function getGoto() {
        if(isset($_SESSION['l.goto'])) return $_SESSION['l.goto'];
        return FALSE;
    }
} 