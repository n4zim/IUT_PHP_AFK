<?php
class Event extends Controller {
    public function Home() {
        parent::__construct();
    }

    public function index() {
        $eventModel = new EventModel();
        $events = $eventModel->getEvents();

        foreach ($events as &$event) {
            $event['Url'] = Helpers::makeUrl('event', 'view', array('id' => $event['Id']));
        }

        $this->afk->view('event/list', array('events' => $events));
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