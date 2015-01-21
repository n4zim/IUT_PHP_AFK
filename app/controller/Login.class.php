<?php
class Login extends Controller {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Main method, shows login form
     **/
    public function index() {
        Login::checkIfNotLogguedIn();

        $this->afk->view('login/form', array(
            'formAction' => Helpers::makeUrl('login', 'post')
        ));
    }

    /**
     * When the login form gets validated
     **/
    public function post() {
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

    /**
     * Logout action
     */
    public function out() {
        if(!isset($_SESSION['u.id'])) {
            Helpers::notify('Pas connecté', 'Impossible de vous déconnecter car vous êtes déjà deconnecté (votre session a probablement expiré).', 'error');
            Helpers::redirect('index');
        }

        Login::logoutUser();
        Helpers::notify('Déconnecté', 'Votre session à bien été fermée.');
        Helpers::redirect('index');
    }

    /**
     * Checks if cookie is present and tries to login if so
     **/
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

    /**
     * Sets the session for an user
     * 
     * @param $data User info array (corresponding to the User table in DB)
     **/
    public static function loginUser($data) {
        $_SESSION['u.username'] = $data['Username'];
        $_SESSION['u.id'] = $data['Id'];
    }

    /**
     * Resets the session vars to logout an user
     **/
    public static function logoutUser() {
        // unset session vars
        unset($_SESSION['u.username']);
        unset($_SESSION['u.id']);
        unset($_SESSION['l.goto']);

        // remove cookies if any
        if(isset($_COOKIE['loginCookie']))
            setcookie('loginCookie', '', time() - 3600);
    }

    /**
     * Checks if an user is logged in.
     * If not, redirect him to login form, which will then redirect
     * to the Login::getGoto() page or the index
     **/
    public static function checkIfLogguedIn() {
        if(empty($_SESSION['u.id'])) {
            Helpers::notify('Non connecté', 'Vous devez être connecté pour accéder à cette page', 'error');
            
            if(Login::getGoto() !== FALSE)
                Helpers::redirect('login');

            Helpers::redirect('index');
        }
    }

    /**
     * Checks if an user is not loggued in
     * Redirects to the index
     */
    private static function checkIfNotLogguedIn() {
        if(isset($_SESSION['u.id'])) {
            Helpers::notify('Déjà connecté', 'Vous êtes déjà connecté.', 'error');
            Helpers::redirect('index');
        }
    }

    /**
     * Sets the destination after login, uses Helpers::makeUrl()
     * 
     * @param $action Controller route (see Helpers::makeUrl())
     * @param $method Method (see Helpers::makeUrl())
     * @param $args Arguments (see Helpers::makeUrl())
     */
    public static function setGoto($action, $method = null, $args = null) {
        $_SESSION['l.goto'] = Helpers::makeUrl($action, $method, $args, false);
    }

    /**
     * Unsets the login successful destination
     **/
    public static function unsetGoto() {
        unset($_SESSION['l.goto']);
    }

    /**
     * Gets the login sucessful destination
     * @return String : destination
     * @return FALSE if not set
     **/
    public static function getGoto() {
        if(isset($_SESSION['l.goto'])) return $_SESSION['l.goto'];
        return FALSE;
    }


    /**
     * Checks wether or not the current loggued in user can admin.
     * Redirects to index if it's not the case.
     **/
    public static function checkIfAdmin() {
        Login::checkIfLogguedIn();
        $um = new UserModel();
        if(!$um->canUser($_SESSION['u.id'], 'admin')) {
            Helpers::notify('Droits inssufisants', 'Vous ne pouvez pas accéder à cette page.', 'error');
            Helpers::redirect('index');
        }
    }

    public static function updateLastActivity() {
        $um = new UserModel();
        if(isset($_SESSION['u.id'])) $um->updateActivity($_SESSION['u.id']);
        else $um->cleanActiveUsers();
    }
} 