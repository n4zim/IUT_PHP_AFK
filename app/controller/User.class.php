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

        $this->afk->view('user/list', 
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
        $data['user']['FactionLink'] = Helpers::makeUrl('faction', 'view', array('id' => $data['user']['Faction']));
        $data['isMine'] = ($_SESSION['u.id'] == intval($args['id']));
        $data['lienEdit'] = Helpers::makeUrl('user', 'edit');

        if(empty($data['user'])) {
            Helpers::notify('Erreur', 'Cet utilisateur n\'existe pas', 'error');
            Helpers::redirect('');
        }

        $this->afk->view('user/profile', $data);
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
} 