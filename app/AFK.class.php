<?php
header('Content-Type: text/html; charset=utf-8');

require_once('lib/div/div.php');
require_once('Config.class.php');
require_once('Layout.class.php');
//require_once('Database.class.php');
require_once('Controller.class.php');
require_once('Helpers.class.php');

/**
 * Main class for the website, initializes the system
 * Singleton
 **/
class AFK { 
	private $db;
	private $route;
	private static $instance;
	private $models = array();

	// instance unique
	public static function getInstance() {
		if(is_null(self::$instance))
			return self::$instance = new AFK();
		return self::$instance;
	}

	private function AFK() {
		//$this->db = $db;
		$this->route[''] = $this->route['home'] = $this->route['index'] = 'Home';
		$this->route['about'] = 'about';

		// Auto load des controlleurs
		spl_autoload_register(function ($class) {
			include 'controller/'.$class.'.class.php';
		});
	}

	public function router($request) {
		$queryArray = array();
		parse_str($request, $queryArray);

		$class = $this->route[''];
		if(isset($queryArray['action'])) {
			if(isset($this->route[$queryArray['action']])) {
				$class = $this->route[$queryArray['action']];
			} else {
				$this->error404($queryArray, 'Controller not found');
			}
		}

		$method = 'index';
		if(isset($queryArray['method']))
			$method = $queryArray['method'];

		$controller = new $class();
		if($controller instanceOf Controller) {
			if(method_exists($controller, $method))
				$controller->$method($queryArray);
			else
				$this->error404($queryArray, 'Method not found');
		}
	}

	public function loadModel($name) {
		//include 'controller/'.$name.'.class.php';
		$this->models[$name] = $name;
	}

	public function view($view, $data = NULL) {
		$view = Config::$paths['views'].$view.'.tpl.html';
		if(empty($data)) $data = array();
		if(!file_exists($view)) exit('View '.$view.' not found.');

		$content = file_get_contents($view);
		$page = new div($content, $data);
		echo $page;
	}

	public function error404($query, $message = '') {
		header('HTTP/1.0 404 Not Found'); 

		$details = '';
		if(isset($message)) $details .= $message.PHP_EOL;
		$details .= 'Query:'.PHP_EOL;
		$details .= print_r($query, true);

		$this->view('404', array('details' => $details));

		exit;
	}
}