<?php
class Admin extends Controller {
    public function __construct() {
        parent::__construct();
        Login::checkIfAdmin();
    }

    public function index($args) {
        echo 'hello admin';
    }
}