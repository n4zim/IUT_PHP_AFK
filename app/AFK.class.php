<?php
/**
 * Main class for the website, initializes the system
 * Singleton
 **/
class AFK { 
    /**
     * Handle to the database
     *
     * This handle gets initialized whenever a models needs it
     * i.e. when you load a model "new MyModel();", it will call
     * (in the Model superclass constructor) this class @see AFK::getDb()
     * function which will initialize the connection when needed.
     * 
     * @var PDO
     */
	private $db = null;

	/**
	 * Array containing the site route
	 * 
	 * This array is configurable and gets initialized in the @see AFK::createRoutes()
	 * 
	 * @var array
	 */
	private $route;

	/**
	 * Stores the current app instance
	 * 
	 * Gets initialized by @see AFK::getInstance()
	 * 
	 * @var AFK
	 */
	private static $instance;

	// instance unique
	public static function getInstance() {
		if(is_null(self::$instance))
			return self::$instance = new AFK();
		return self::$instance;
	}

	private function AFK() {
		$this->route = Route::getRoutes();
		// Auto load des controlleurs et des modèles
		spl_autoload_register(function ($class) {
			$filename = $class.'.class.php';

			if(file_exists(Config::$path['controller'].$filename)) {
				include_once Config::$path['controller'].$filename;
			}

			else if(file_exists(Config::$path['model'].$filename))
				include_once Config::$path['model'].$filename;
		});

		// Modifiers personalisés pour le moteur de templates
		div::addCustomModifier('toGender:', 'Helpers::toFullGender');
		div::addCustomModifier('slugify:', 'Helpers::slugify');
		div::addCustomModifier('formatDateTime:', 'Helpers::formatDateTime');

		$this->init();
	}

	private function init() {
		Login::checkCookie();
	}

	public function router($request) {
		$queryArray = array();
		parse_str($request, $queryArray);

		// init route par défaut
		$class = $this->route[''];

		// recherche de la route
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

		// instantiate a new class using some PHP magic
		$controller = new $class();
		// check if the newly instanciated class extends a controller
		if($controller instanceOf Controller) {
			if($this->checkIfMethodRoutable($class, $method)) {
				$controller->$method($queryArray); // call Controller->method()
			}
			else
				$this->error404($queryArray, 'Method not found');
		}
	}

	/**
	 * Checks if we can route to a method of a class (the method must be public and non static)
	 * 
	 * This is used to prevent routing to static methods of class since PHP allows calling
	 * a static method on a non static object (e.g doing $classInstance->staticMethod()
	 * instead of Class::staticMethod())
	 * 
	 * Without this, the user would be able to call any method from a controller e.g
	 * ?action=Controller&method=staticMethod or ?action=Controller&method=private
	 * (which would have crashed the program because we can't call a private method)
	 * 
	 * @param $class String The class name
	 * @param $method String The method name
	 * @return true if callable
	 * @return false if not callable
	 **/
	private function checkIfMethodRoutable($class, $method) {
		$c = new ReflectionClass($class);
		$method = $c->getMethod($method);

		return !$method->isStatic() && $method->isPublic();
	}

	public function view($view, $data = NULL) {
		$view = Config::$path['views'].$view.'.tpl.html';
		if(empty($data)) $data = array();
		if(!file_exists($view)) exit('View '.$view.' not found.');

		$data['layout'] = Layout::prepareLayout();

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