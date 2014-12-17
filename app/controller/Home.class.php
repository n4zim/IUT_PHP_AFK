<?php
class Home extends Controller {
    public function Home() {
        parent::__construct();
    }

    public function index() {
        $this->afk->view('index', array());
    }

    public function test() {
        $this->afk->view('index', array());
    }

    public function yolo() {
        $user = new UserModel();
        $users = print_r($user->getUser(1), true);
        
        $this->afk->view('yolo', array('users' => $users));
    }
}