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
        $pageNumber = (isset($args['p']) && intval($args['p']) <= $pages && intval($args['p']) > 0) ? intval($args['p']) : 1;
        $users = $userModel->getUsers($pageNumber - 1);

        foreach ($users as &$user) {
            $user['CanAdmin'] = ($user['CanAdmin']) ? 'Admin' : 'Utilisateur';
            $user['IsActivated'] = (empty($user['ActivationToken'])) ? 'Oui' : 'Non';
            $user['Url'] = Helpers::makeUrl('user', 'profile', array('id' => $user['Id']));
            $user['DeleteLink'] = Helpers::makeUrl('admin', 'deluser', array('id' => $user['Id'], 'p' => $pageNumber));
        }

        $countEv = $eventModel->countEvents();
        $pageCountEv = ceil($countEv / Config::$listing['eventsPerPage']);

        $pageNumberEv = (isset($args['p2']) && intval($args['p2']) <= $pageCountEv && intval($args['p2']) > 0) ? intval($args['p2']) : 1;
        $events = $eventModel->getEvents(null, false, $pageNumberEv - 1);

        foreach ($events as &$event) {
            $event['Url'] = Helpers::makeUrl('event', 'view', array('id' => $event['Id']));
            $event['DeleteLink'] = Helpers::makeUrl('admin', 'delevent', array('id' => $event['Id'], 'p' => $pageNumberEv));
        }

        $this->afk->view('admin/index', array(
                'users' => $users,
                'events' => $events,
                'count' => $count,
                'pageNumberUsr' => $pageNumber,
                'pageCountUsr' => $pages, 
                'countEv' => $countEv,
                'pageNumberEv' => $pageNumberEv,
                'pageCountEv' => $pageCountEv
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

    public function delevent($args) {
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Identifiant introuvable', 'error');
            Helpers::redirect('admin');
        }

        $em = new EventModel();
        $em->deleteEvent($args['id']);
        
        if(empty($args['p'])) $args['p'] = 1;
        Helpers::notify('Info', 'Event supprimmé !');
        Helpers::redirect('admin', '', array($args['id'], $args['p']));
    }
}