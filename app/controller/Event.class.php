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

    public function view($args) {
        $this->checkId($args);

        $eventModel = new EventModel();
        $event = $eventModel->getEvents($args['id'], true, null, $_SESSION['u.id']);

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