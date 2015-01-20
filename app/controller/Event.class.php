<?php
class Event extends Controller {
    public function Home() {
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
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Pas d\'identifiant d\'évenement spécifié', 'error');
            Helpers::redirect('event');
        }

        $eventModel = new EventModel();
        $event = $eventModel->getEvents($args['id']);

        $this->afk->view('event/view', array('event' => $event));
    }
}