<?php
class Admin extends Controller {
    public function __construct() {
        parent::__construct();
        Login::checkIfAdmin();
    }

    public function index($args) {
        $userModel  = new UserModel();
        $eventModel = new EventModel();

        $count = $userModel->countUsers();
        
        $pages = ceil($count / Config::$listing['usersPerPage']);
        $pageNumber = (isset($args['p']) && intval($args['p']) <= $pages) ? intval($args['p']) : 1;
        $users = $userModel->getUsers($pageNumber - 1);

        foreach ($users as &$user) {
            $user['CanAdmin'] = ($user['CanAdmin']) ? 'Admin' : 'Utilisateur';
            $user['IsActivated'] = (empty($user['ActivationToken'])) ? 'Oui' : 'Non';
            $user['Url'] = Helpers::makeUrl('user', 'profile', array('id' => $user['Id']));
            $user['DeleteLink'] = Helpers::makeUrl('admin', 'deluser', array('id' => $user['Id']));
        }

        $this->afk->view('admin/index', array(
                'count' => $count,
                'users' => $users,
                'pageNumber' => $pageNumber,
                'pageCount' => $pages
        ));
    }

    public function deluser($args) {
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Identifiant introuvable', 'error');
            Helpers::redirect('admin');
        }

        $um = new UserModel();
        $um->DeleteUser($args['id']);

        if(empty($args['p'])) $args['p'] = 1;
        
        Helpers::notify('Info', 'Utilisateur supprimmé !');
        Helpers::redirect('admin', '', array($args['id'], $args['p']));
    }
}