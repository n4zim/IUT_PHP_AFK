<?php
class Home extends Controller {
    public function Home() {
        parent::__construct();
    }

    public function index() {
        $eventModel = new EventModel();
        $userModel = new UserModel();

        $latest = $eventModel->getEvents(null, false, 0, null, Config::$listing['eventsOnHomePage'], 'latest');
        
        $this->afk->view('index', array(
            'eventCount' => $eventModel->countEvents(true),
            'userCount' => $userModel->countUsers(),
            'events' => $latest,
            'activeCount' => $userModel->countActiveUsers()
        ));
    }
}