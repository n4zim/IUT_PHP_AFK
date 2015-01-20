<?php
/**
 * Controller class
 * 
 * Extend this class in order to make a controller 
 **/
class Controller {
    /**
     * Handle to the main program
     * 
     * Can be used to access program methods
     * @var AFK
     */
    protected $afk;

    /**
     * Construtor for the controller
     * 
     * Will load in it's private member $this->afk the current running instance of the program.
     */
    public function Controller()
    {
        $this->afk = AFK::getInstance();
    }
}