<?php
class Home extends Controller {
    public function Home() {
        parent::__construct();
    }

    public function index() {
        $eventModel = new EventModel();
        $userModel = new UserModel();
        $factionModel = new FactionModel();

        $factions = $factionModel->getFactions();

        // yep I know, this is not optimal and the model /should/ automatically do that but HEY! IT WORKS! ...
        foreach ($factions as &$faction) {
            $faction['Score'] = $factionModel->getTotalScore($faction['Id']);
            $faction['Url'] = Helpers::makeUrl('faction', 'view', array('id' => $event['Id']));
        }

        $latest = $eventModel->getEvents(null, false, 0, null, Config::$listing['eventsOnHomePage'], 'latest');
        
        foreach ($latest as &$event) {
            $event['Url'] = Helpers::makeUrl('event', 'view', array('id' => $event['Id']));
        }
        
        $this->afk->view('home', array(
            'eventCount' => $eventModel->countEvents(true),
            'userCount' => $userModel->countUsers(),
            'events' => $latest,
            'activeCount' => $userModel->countActiveUsers(),
            'loginAction' => Helpers::makeUrl('login', 'post'),
            'factions' => $factions
        ));
    }
}