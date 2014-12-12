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
        $this->afk->view('yolo', array('test' => 'swag'));
    }
}