<?php
class Home extends Controller {
    public function Home() {
        parent::__construct();
    }

    public function index() {
        $this->afk->view('index', array());
    }
}