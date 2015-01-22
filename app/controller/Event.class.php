<?php
class Event extends Controller {
    public function __construct() {
        parent::__construct();
    }

    public function index($args) {
        $page = (isset($args['page'])) ? intval($args['page']) : 0;
        $eventModel = new EventModel();
        $events = $eventModel->getEvents(null, false, $page);

        foreach ($events as &$event) {
            $event['Url'] = Helpers::makeUrl('event', 'view', array('id' => $event['Id']));
        }

        // pagination
        $count = $eventModel->countEvents();
        $pageCount = ceil($count / Config::$listing['usersPerPage']);
        $pageNumber = (isset($args['p']) && intval($args['p']) <= $pageCount) ? intval($args['p']) : 1;

        $this->afk->view('event/list', array('events' => $events, 'pageCount' => $pageCount, 'pageNumber' => $pageNumber));
    }

    public function create() {
        Login::checkIfLogguedIn();

        $eventModel = new EventModel();
        $eventTypes = $eventModel->getTypes();

        $this->afk->view('event/create', array('eventTypes' => $eventTypes, 'formAction' => Helpers::makeUrl('event', 'post')));
    }

    public function post() {
        Login::checkIfLogguedIn();

        $mandatoryFields = array('title', 'type', 'description', 'place', 'date', 'heure');
        $protectFields = array('title', 'description', 'place');
        $mandatoryFieldsNames = array('Titre', 'Type', 'Description', 'Lieu', 'Date', 'Heure');

        // check if all fields are set
        $erreur = "";
        foreach ($mandatoryFields as $key => $field) {
            if(empty($_POST[$field])) 
                $erreur .= "Le champ ".$mandatoryFieldsNames[$key]." est vide.<br />";
        }
        if($erreur != "") {
            Helpers::notify('Erreur', $erreur, 'error');
            Helpers::redirect('event', 'create');
        }

        // protect fields
        foreach ($protectFields as &$field) {
            $_POST[$field] = htmlentities($_POST[$field]);
        }

        // insert in database
        $eventmodel = new EventModel();
        $r = $eventmodel->addEvent($_SESSION['u.id'], $_POST);
        
        if($r['success']) {
            //$r = $eventmodel->subscribeUser($_SESSION['u.id'], $r['id']);
            Helpers::notify('Event ajouté !', 'Vive le vent !<br />(le vent... l\'event... blague, drôle, tout ça)');
            Helpers::redirect('event', 'view', array('id' => $r['id']));
        } else {
            Helpers::notify('Erreur', $r['message'], 'error');
            Helpers::redirect('event', 'create');
        }
    }

    public function view($args) {
        $this->checkId($args);

        $eventModel = new EventModel();
        $event = $eventModel->getEvents($args['id'], true, null, (isset($_SESSION['u.id']) ? $_SESSION['u.id'] : null));

        $sub = Helpers::makeUrl('event', 'subscribe', array('id' => $args['id']));
        $unsub = Helpers::makeUrl('event', 'unsubscribe', array('id' => $args['id']));

        $this->afk->view('event/view', array('event' => $event, 'subLink' => $sub, 'unsubLink' => $unsub));
    }

    public function subscribe($args) {
        $this->checkId($args);
        Login::setGoto('event', 'view', array('id' => $args['id']));
        Login::checkIfLogguedIn();

        $eventModel = new EventModel();
        $r = $eventModel->subscribeUser($_SESSION['u.id'], $args['id']);

        if($r['success'] === false) {
            Helpers::notify('Erreur', $r['message'], 'error');
            Helpers::redirect('event', 'view', array('id' => $args['id']));
        }

        Helpers::notify('Ajouté !', 'Vous faites maintenant partie des participants à cet évenement !');
        Helpers::redirect('event', 'view', array('id' => $args['id']));
    }

    public function unsubscribe($args) {
        $this->checkId($args);
        Login::setGoto('event', 'view', array('id' => $args['id']));
        Login::checkIfLogguedIn();

        $eventModel = new EventModel();
        $r = $eventModel->unsubscribeUser($_SESSION['u.id'], $args['id']);

        if($r['success'] === false) {
            Helpers::notify('Erreur', $r['message'], 'error');
            Helpers::redirect('event', 'view', array('id' => $args['id']));
        }

        Helpers::notify('C\'est fait !', 'Vous n\'êtes plus inscrit à cet évenement.');
        Helpers::redirect('event', 'view', array('id' => $args['id']));
    }

    private function checkId($args) {
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Pas d\'identifiant d\'évenement spécifié', 'error');
            Helpers::redirect('event');
        }
    }
}