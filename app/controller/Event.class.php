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

    public function create($args) {
        Login::checkIfLogguedIn();

        $eventModel = new EventModel();
        $eventTypes = $eventModel->getTypes();

        $action = 'post';
        $editLink = null;
        $values = array('Titre' => '', 'Description' => '', 'Place' => '', 'Date' => '2015-06-30', 'Time' => '15:50:10', 'Image' => '');

        foreach ($eventTypes as &$type) {
            $type['Selected'] = '';
        }

        $actionData = null;

        if(isset($args['id'])) {
            $event = $eventModel->getEvents($args['id']);
            if($event['Organizer'] == $_SESSION['u.id']) {
                foreach ($eventTypes as &$type) {
                    $type['Selected'] = ($event['TypeEvent'] == $type['Id']) ? ' selected="selected" ' : '';
                }
                $event['Date'] = date('Y-m-d', $event['EventDate']);
                $event['Time'] = date('H:i:s', $event['EventDate']);
                $values = $event;
                $actionData = array('id' => $args['id']);
            } else {
                Helpers::notify('Erreur', 'Vous n\'avez pas la permission d\'éditer cet évenement', 'error');
                Helpers::redirect('event', 'view', array('id' => $args['id'])); 
            }
        }

        $this->afk->view('event/form', array('editLink' => $editLink, 'eventTypes' => $eventTypes, 'formAction' => Helpers::makeUrl('event', 'post', $actionData), 'v' => $values));
    }

    public function post($args) {
        Login::checkIfLogguedIn();

        $eventmodel = new EventModel();
        $mandatoryFields = array('title', 'type', 'description', 'place', 'date', 'heure');
        $protectFields = array('title', 'description', 'place');
        $mandatoryFieldsNames = array('Titre', 'Type', 'Description', 'Lieu', 'Date', 'Heure');
        $redirectArgs = null;
        $editMode = false;

        // edit mode
        if(isset($args['id'])) {
            $redirectArgs = array('id' => $args['id']);

            $eventModel = new EventModel();
            $event = $eventModel->getEvents($args['id']);
            if($event['Organizer'] == $_SESSION['u.id']) {
                $editMode = true;
            } else {
                Helpers::notify('Erreur', 'Vous n\'avez pas la permission d\'éditer cet évenement', 'error');
                Helpers::redirect('event', 'view', array('id' => $args['id'])); 
            }
        }

        // check if all fields are set
        $erreur = "";
        foreach ($mandatoryFields as $key => $field) {
            if(empty($_POST[$field])) 
                $erreur .= "Le champ ".$mandatoryFieldsNames[$key]." est vide.<br />";
        }

        if($erreur != "") {
            Helpers::notify('Erreur', $erreur, 'error');
            Helpers::redirect('event', 'create', $redirectArgs);
        }

        // protect fields
        foreach ($protectFields as &$field) {
            $_POST[$field] = htmlentities($_POST[$field]);
        }

        // insert in database
        if($editMode) $r = $eventmodel->editEvent($args['id'], $_POST);
        else $r = $eventmodel->addEvent($_SESSION['u.id'], $_POST);
        
        if($r['success']) {
            //$r = $eventmodel->subscribeUser($_SESSION['u.id'], $r['id']);
            if(!$editMode) Helpers::notify('Event ajouté !', 'Vive le vent !<br />(le vent... l\'event... blague, drôle, tout ça)');
            else Helpers::notify('Event modifié !', 'L\'évenement à été modifié avec succès');
            Helpers::redirect('event', 'view', array('id' => $r['id']));
        } else {
            Helpers::notify('Erreur', $r['message'], 'error');
            Helpers::redirect('event', 'create', $redirectArgs);
        }
    }

    public function view($args) {
        $this->checkId($args);

        $eventModel = new EventModel();
        $event = $eventModel->getEvents($args['id'], true, null, (isset($_SESSION['u.id']) ? $_SESSION['u.id'] : null));

        if(empty($event)) {
            Helpers::notify('Erreur', 'Cet évenement n\'existe pas ou à été supprimé.', 'error');
            Helpers::redirect('event');
        }

        $sub = Helpers::makeUrl('event', 'subscribe', array('id' => $args['id']));
        $unsub = Helpers::makeUrl('event', 'unsubscribe', array('id' => $args['id']));

        $users = $eventModel->getSubscribed($args['id']);
        foreach ($users as &$user) {
            $user['Url'] = Helpers::makeUrl('user', 'profile', array('id' => $user['Id']));
        }

        $data = array('event' => $event, 'subLink' => $sub, 'unsubLink' => $unsub, 'entrants' => $users);

        if($event['Organizer'] == $_SESSION['u.id']) {
            $data['editLink'] = Helpers::makeUrl('event', 'create', array('id' => $args['id']));
            if($event['RewardSent'] == 'N')
                $data['doneLink'] = Helpers::makeUrl('event', 'teamchoice', array('id' => $args['id']));
        }
        
        // todo : prevent subscribing if event date < now

        $data['userProfile'] = Helpers::makeUrl('user', 'profile', array('id' => $event['Organizer']));

        $this->afk->view('event/view', $data);
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

    public function upcoming() {
        Login::checkIfLogguedIn();
        
        $eventModel = new EventModel();
        $events = $eventModel->getUpcomingEventsFor($_SESSION['u.id']);

        foreach ($events as &$event) {
            $event['Url'] = Helpers::makeUrl('event', 'view', array('id' => $event['Id']));
            $event['unsub'] = Helpers::makeUrl('event', 'unsubscribe', array('id' => $event['Id']));
        }

        $this->afk->view('event/upcoming', array('events' => $events));
    }

    public function teamChoice($args) {
        Login::checkIfLogguedIn();
        $this->checkId($args);

        $factionsModel = new FactionModel();
        $factions = $factionsModel->getFactions();

        $this->afk->view('event/teamchoice', array(
            'formAction' => Helpers::makeUrl('event', 'teamchosen', array('id' => $args['id'])), 
            'factions' => $factions
        ));
    }

    public function teamChosen($args) {
        Login::checkIfLogguedIn();
        $this->checkId($args);

        $factionsModel = new FactionModel();
        $faction = $factionsModel->getFaction($_POST['faction']);
        if(empty($faction)) {
            Helpers::notify("Erreur", "Faction incorrecte", "error");
            Helpers::redirect('event', 'teamchoice', array('id' => $args['id']));
        }

        $em = new EventModel();
        $ev = $em->getEvents($args['id']);
        $r = $em->markEventDone($args['id'], array($_POST['faction']), "La team à remporté l'event ".$ev['Titre']);

        if($r['success'] == false) {
            Helpers::notify("Erreur", $r['message'], "error");
            Helpers::redirect('event', 'view', array('id' => $args['id']));
        } else {
            Helpers::notify("Ok", "C'est fait, les points ont été attribués !");
            Helpers::redirect('event', 'view', array('id' => $args['id']));
        }
    }

    private function checkId($args) {
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Pas d\'identifiant d\'évenement spécifié', 'error');
            Helpers::redirect('event');
        }
    }
}