<?php
class Faction extends Controller {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Main method, lists users
     * @param args Argument array. Used arguments : 
     **/
    public function index($args) {
        $factionModel = new FactionModel();
        $factions = $factionModel->getFactions();
        foreach ($factions as &$faction) {
            $faction['Url'] = Helpers::makeUrl('faction', 'view', array('id' => $faction['Id']));
            $faction['Score'] = $factionModel->getTotalScore($faction['Id']); // optimize me (use db directly in getFactions to get score)
        }

        $this->afk->view('faction/list', 
            array(
                'factions' => $factions
            )
        );
    }

    public function view($args) {
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Pas d\'identifiant de faction spécifié', 'error');
            Helpers::redirect('faction');
        }

        $factionModel = new FactionModel();
        $userModel = new UserModel();
        $faction = $factionModel->getFactions($args['id']);
        $members = $userModel->getUsers(null, $args['id']);

        $faction['Url'] = Helpers::makeUrl('faction', 'view', array('id' => $faction['Id']));
        $faction['Score'] = $factionModel->getTotalScore($faction['Id']); // optimize me (use db directly in getFactions to get score)

        foreach ($members as &$user) {
            $user['Url'] = Helpers::makeUrl('user', 'profile', array('id' => $user['Id']));
        }

        $this->afk->view('faction/view', 
            array(
                'faction' => $faction,
                'members' => $members
            )
        );
    }

} 