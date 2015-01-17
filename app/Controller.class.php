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

    /**
     * Prevents a direct call of any method via the router by stopping the script
     * 
     * Used to prevent public method calling by the user, e.g. ?action=Controller&method=dontCallMe
     * In this case, assuming "public function dontCallMe($args)" uses this function, the script will be stopped.
     * 
     * It is useful for public staic controller methods (because php allows for calling static methods on a non static object)
     * 
     * @param $args Argument array passed from the router. Here we check if $args['routed'] is set or not.
     **/
    public function preventRouter($args) {
        if(isset($args['routed']))
            exit();
    }
}