<?php
class Controller {
    protected $afk;

    public function Controller()
    {
        $this->afk = AFK::getInstance();
    }
}