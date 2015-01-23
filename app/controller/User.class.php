<?php
class User extends Controller {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Main method, lists users
     * @param args Argument array. Used arguments : 
     **/
    public function index($args) {
        $usermodel = new UserModel();
        $count = $usermodel->countUsers();
        
        $pages = ceil($count / Config::$listing['usersPerPage']);
        $pageNumber = (isset($args['p']) && intval($args['p']) <= $pages) ? intval($args['p']) : 1;

        $users = $usermodel->getUsers($pageNumber - 1);

        foreach ($users as &$user) {
            $user['Url'] = Helpers::makeUrl('user', 'profile', array('id' => $user['Id']));
            if(isset($_SESSION['u.id']) && $user['Id'] != $_SESSION['u.id'])
                $user['AddFriendUrl'] = Helpers::makeUrl('user', 'addfriend', array('id' => $user['Id']));
        }

        self::setRedirectAfterFriendOperation();

        $this->afk->view('user/directory', 
            array(
                'count' => $count,
                'users' => $users,
                'pageNumber' => $pageNumber,
                'pageCount' => $pages
            )
        );
    }

    public function profile($args) {
        Login::setGoto('user', 'profile');
        Login::checkIfLogguedIn();

        // if no id is supplied, we show the profile of the loggued in user
        if(empty($args['id'])) {
            $args['id'] = $_SESSION['u.id'];
        } else {
            $args['id'] = intval($args['id']);
        }

        $usermodel = new UserModel();
        $data = array();
        $data['user'] = $usermodel->getUser(intval($args['id']));

        if(empty($data['user'])) {
            Helpers::notify('Erreur', 'Cet utilisateur n\'existe pas', 'error');
            Helpers::redirect('');
        }

        $data['user']['FactionLink'] = Helpers::makeUrl('faction', 'view', array('id' => $data['user']['Faction']));
        $data['isMine'] = ($_SESSION['u.id'] == intval($args['id']));
        if($_SESSION['u.id'] == intval($args['id'])) $data['lienEdit'] = Helpers::makeUrl('user', 'edit');
        $data['MPUrl'] = Helpers::makeUrl('pm', 'write', array('id' => $data['user']['Id']));

        // todo : check if not friend
        if($_SESSION['u.id'] != intval($args['id']))
            $data['FriendUrl'] = Helpers::makeUrl('user', 'addfriend', array('id' => $data['user']['Id']));

        // get events created by the user and events where the user is subscribed
        $eventModel = new EventModel();
        $eventsOrg = $eventModel->getUserEvents($data['user']['Id']);
        $eventsSubs = $eventModel->getUserSubs($data['user']['Id']);

        // todo : do this operation in model
        foreach ($eventsOrg as &$event) {
            $event['Url'] = Helpers::makeUrl('event', 'view', array('id' => $event['Id']));
        }
        foreach ($eventsSubs as &$event) {
            $event['Url'] = Helpers::makeUrl('event', 'view', array('id' => $event['Id']));
        }

        $data['eventOrg'] = $eventsOrg;
        $data['eventSubs'] = $eventsSubs;

        $this->afk->view('user/profile', $data);
    }

    public function editform() {

    }

    public function edit($args) {
        Login::setGoto('user', 'profile');
        Login::checkIfLogguedIn();

        $mandatoryFields = array('mail', 'gender', 'avatar');
        $protectFields = array('mail', 'avatar');
        $mandatoryFieldsNames = array('adresse email', 'sexe', 'URL de l\'avatar');

        // check if all fields are set
        $error = "";
        foreach ($mandatoryFields as $key => $field) {
            if(empty($_POST[$field]))
                $error .= "Le champ ".$mandatoryFieldsNames[$key]." est vide.<br />";
        }
        if($error != "") $this->notifyError($error);

        // protect fields
        foreach ($protectFields as &$field) {
            $_POST[$field] = htmlentities($_POST[$field]);
        }

        if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL))
            $this->notifyError("L'adresse email n'est pas valide");

        $usermodel = new UserModel();
        $r = $usermodel->edit($_SESSION['u.id'], $_POST);

        if($r['success']) {
            Helpers::notify('Effectué', 'Modifications enregistrées');
            Helpers::redirect('user', 'profile');
        } else $this->notifyError('Erreur : '.$r['message']);
    }

    public function friendlist($args) {
        Login::checkIfLogguedIn();

        $um = new UserModel();
        $users = $um->getFriendsOf($_SESSION['u.id']);

        foreach ($users as &$user) {
            $user['Url'] = Helpers::makeUrl('user', 'profile', array('id' => $user['Id']));
            $user['DelFriendUrl'] = Helpers::makeUrl('user', 'removefriend', array('id' => $user['Id']));
            $user['MPUrl'] = Helpers::makeUrl('pm', 'write', array('id' => $user['Id']));
        }

        self::setRedirectAfterFriendOperation('user', 'friendlist');

        $this->afk->view('user/friendlist', 
            array(
                'users' => $users
            )
        );
    }

    public function addfriend($args) {
        Login::checkIfLogguedIn();
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Pas d\'identifiant utilisateur spécifié', 'error');
            Helpers::redirect('index');
        }
        if($_SESSION['u.id'] == $args['id']) {
            Helpers::notify('Erreur', 'Je sais que tu es désespéré, mais tu ne peux pas être ami avec toi même.', 'error');
            Helpers::redirect('index');
        }

        $um = new UserModel();
        $r = $um->addFriend($_SESSION['u.id'], $args['id']);

        if($r['success']) {
            Helpers::notify('Ami ajouté !', 'Ça fait toujours plaisir d\'avoir des amis !');
        } else {
            Helpers::notify('Erreur', 'Impossible de rajouter un ami :<br />'.$r['message'], 'error');
        }
        self::doRedirect();
    }

    public function removefriend($args) {
        Login::checkIfLogguedIn();
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Pas d\'identifiant utilisateur spécifié', 'error');
            Helpers::redirect('index');
        }

        $um = new UserModel();
        $r = $um->removeFriend($_SESSION['u.id'], $args['id']);

        if($r['success']) {
            Helpers::notify('Et pof !', 'Un ami en moins dans votre liste. Un peu triste tout de même.');
        } else {
            Helpers::notify('Erreur', 'Impossible de supprimmer un ami (malheureusement pour vous) :<br />'.$r['message'], 'error');
        }
        self::doRedirect();
    }

    public static function setRedirectAfterFriendOperation($action = 'user', $method = null, $args = null) {
        $_SESSION['u.redirectTo'] = Helpers::makeUrl('user', $method, $args, false);
    }

    private static function doRedirect() {
        if(empty($_SESSION['u.redirectTo'])) {
            header('Location: '.Helpers::makeUrl('index', null, null, false));
            exit;
        }
        $r = $_SESSION['u.redirectTo'];
        unset($_SESSION['u.redirectTo']);
        header('Location: '.$r);
        exit;
    }
} 