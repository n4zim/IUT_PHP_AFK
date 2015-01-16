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
                'page' => $pageNumber,
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

        if(empty($data['user']))
            exit('utilisateur introuvable');

        $this->afk->view('user/profile', $data);
    }
} 