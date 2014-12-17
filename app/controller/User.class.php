<?php
class User extends Controller {
    public function Home() {
        parent::__construct();
    }

    /**
     * Main method, lists users
     * @param args Argument array. Used arguments : 
     **/
    public function index($args) {
        $usermodel = new UserModel();
        $count = $usermodel->countUsers();
        
        $pages = ceil($count / Config::$listing['usersPerPage']);
        $pageNumber = (isset($args['p']) && intval($args['p']) <= $pages) ? intval($args['p']) : 1;

        $users = $usermodel->getUsers($pageNumber - 1);

        $this->afk->view('user/list', 
            array(
                'count' => $count,
                'users' => $users,
                'page' => $pageNumber,
                'pageCount' => $pages
            )
        );
    }
} 