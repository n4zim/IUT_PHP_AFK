<?php
header('Content-Type: text/html; charset=utf-8');

require_once('lib/div/div.php');
require_once('Config.class.php');
require_once('Layout.class.php');
//require_once('Database.class.php');
require_once('Model.class.php');
require_once('Controller.class.php');
require_once('Helpers.class.php');

/**
 * Main class for the website, initializes the system
 * Singleton
 **/
class AFK { 
	private $db = null;
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
		$this->createRoutes();
		// Auto load des controlleurs et des modèles
		spl_autoload_register(function ($class) {
			$filename = $class.'.class.php';

			if(file_exists(Config::$path['controller'].$filename)) {
				include Config::$path['controller'].$filename;
			}

			else if(file_exists(Config::$path['model'].$filename))
				include Config::$path['model'].$filename;
		});
	}

	private function createRoutes() {
		$this->route[''] = $this->route['home'] = $this->route['index'] = 'Home';
		$this->route['users'] = $this->route['user'] = 'User';
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

	public function view($view, $data = NULL) {
		$view = Config::$path['views'].$view.'.tpl.html';
		if(empty($data)) $data = array();
		if(!file_exists($view)) exit('View '.$view.' not found.');

		$content = file_get_contents($view);
		$page = new div($content, $data);
		echo $page;
	}

	private function initilizeDatabase() {
        try {
            $this->db = new PDO(Config::$dbInfo['driver'], Config::$dbInfo['username'], Config::$dbInfo['password']);
			$this->db->exec('SET CHARACTER SET utf8');
			
			// If we are in website debug mode, we display PDO errors
			if(Config::$debug) 
    			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        } catch(Exception $e) {
            exit('Erreur de connexion : ' . $e->getMessage());
        }
	}

	public function getDb() {
		if($this->db == null)
			$this->initilizeDatabase();

		return $this->db;
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

    function __destruct() {
    	if($this->db != null) $this->db = null;
    }
}