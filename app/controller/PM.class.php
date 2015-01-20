<?php
class PM extends Controller {
    public function PM() {
        parent::__construct();
        Login::checkIfLogguedIn();
    }

    public function index($args) {
        $pmModel = new PMModel();

        $this->afk->view('pm/list', 
            array(
                'received' => $pmModel->getReceived($_SESSION['u.id']),
                'sent' => $pmModel->getSent($_SESSION['u.id'])
            )
        );
    }

    public function send($args) {
        $pmModel = new PMModel();
        $userModel = new UserModel();

        $mandatoryFields = array('to', 'message');
        $mandatoryFieldsNames = array('pseudo du destinataire', 'contenu du message');

        // check if all fields are set
        $error = "";
        foreach ($mandatoryFields as $key => $field) {
            if(empty($_POST[$field]))
                $error .= "Le champ ".$mandatoryFieldsNames[$key]." est vide.<br />";
        }
        if($error != "") $this->notifyError($error);

        // protect message
        $_POST['message'] = htmlspecialchars($_POST['message']);

        // send the message
        $pmId = $pmModel->sendPM(
            $_SESSION['u.id'],
            $userModel->getUserByName($_POST['to'])['Id'],
            $_POST['message'],
        );

        Helpers::notify('Effectué', 'Votre message à été envoyé');
        Helpers::redirect('pm', 'view', array('id' => $pmId));
    }

    private function notifyError($error) {
        Helpers::notify('Error', $error, 'error');
        Helpers::redirect('pm');
    }
}