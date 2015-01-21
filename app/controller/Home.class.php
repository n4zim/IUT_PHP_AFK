<?php
class Home extends Controller {
    public function Home() {
        parent::__construct();
    }

    public function index() {
        $eventModel = new EventModel();
        $userModel = new UserModel();

        $latest = $eventModel->getEvents(null, false, 0, null, Config::$listing['eventsOnHomePage'], 'latest');
        
        foreach ($latest as &$event) {
            $event['Url'] = Helpers::makeUrl('event', 'view', array('id' => $event['Id']));
        }
        
        $this->afk->view('index', array(
            'eventCount' => $eventModel->countEvents(true),
            'userCount' => $userModel->countUsers(),
            'events' => $latest,
            'activeCount' => $userModel->countActiveUsers(),
            'loginAction' => Helpers::makeUrl('login', 'post')
        ));
    }
}