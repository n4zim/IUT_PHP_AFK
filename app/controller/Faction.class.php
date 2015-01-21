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
        $factions = $factionModel->getFactions($args['id']);

        foreach ($factions as &$faction) {
            $factions['Url'] = Helpers::makeUrl('faction', 'view', array('id' => $factions['Id']));
            $factions['Score'] = $factionModel->getTotalScore($factions['Id']); // optimize me (use db directly in getFactions to get score)
        }

        $this->afk->view('faction/list', 
            array(
                'factions' => $factions
            )
        );
    }

} 