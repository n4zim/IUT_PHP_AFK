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
                'sent' => $pmModel->getSent($_SESSION['u.id']),
                'writePMLink' => Helpers::makeUrl('pm', 'write')
            )
        );
    }

    public function delete($args) {
        return;
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Pas d\'identifiant de message spécifié', 'error');
            Helpers::redirect('pm');
        }

        $pmModel = new PMModel();
        $message = $pmModel->getPMs($_SESSION['u.id'], $args['id'])[0];

        if(empty($message)) {
            Helpers::notify('Erreur', 'Message introuvable ou permission non accordée', 'error');
            Helpers::redirect('pm');
        }

        $message = $pmModel->deletePM($args['id'])[0];
        Helpers::notify('Effectué', 'Vous n\'entendrez plus parler de ce message.');
        Helpers::redirect('pm');
    }

    public function write($args) {
        $data = array('formAction' => Helpers::makeUrl('pm', 'send'), 'to' => '', 'disableChange' => '');
        if(isset($args['id'])) {
            $um = new UserModel(); 
            $data['to'] = $um->getUser($args['id'])['Username'];
            $data['disableChange'] = 'disabled="disabled"';
        }

        $this->afk->view('pm/write', $data);
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
            $userModel->getUserIdByName($_POST['to']),
            $_POST['message']
        );

        if($pmId === false) {
            Helpers::notify('Erreur', 'Impossible d\'envoyer ce message, avez vous essayé de vous parler à l\'homme invisible ou encore mieux, à vous même ?', 'error');
            Helpers::redirect('pm');
        }

        Helpers::notify('Effectué', 'Votre message à été envoyé');
        Helpers::redirect('pm', 'view', array('id' => $pmId));
    }

    public function view($args) {
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Pas d\'identifiant de message spécifié', 'error');
            Helpers::redirect('pm');
        }

        $pmModel = new PMModel();
        $message = $pmModel->getPMs($_SESSION['u.id'], $args['id'])[0];

        if(empty($message)) {
            Helpers::notify('Erreur', 'Message introuvable ou permission non accordée', 'error');
            Helpers::redirect('pm');
        }

        $data = array('message' => $message);

        if($message['RecipientId'] == $_SESSION['u.id']) {
            $pmModel->markRead($args['id']);
            //$data['deleteLink'] = Helpers::makeUrl('pm', 'delete', array('id' => $args['id']));
        }

        $this->afk->view('pm/view', $data);
    }

    public function markread($message) {
        if(empty($args['id'])) {
            Helpers::notify('Erreur', 'Pas d\'identifiant de message spécifié', 'error');
            Helpers::redirect('pm');
        }

        $pmModel = new PMModel();
        $message = $pmModel->getPMs($_SESSION['u.id'], $args['id'])[0];

        if(empty($message)) {
            Helpers::notify('Erreur', 'Message introuvable ou permission non accordée', 'error');
            Helpers::redirect('pm');
        }

        if($message['RecipientId'] == $_SESSION['u.id'])
            $pmModel->markRead($args['id']);
    }

    public static function countUnread() {
        if(empty($_SESSION['u.id']))
            return 0;

        $pmModel = new PMModel();
        $user = $_SESSION['u.id'];
        return $pmModel->countUnread($user);
    }

    private function notifyError($error) {
        Helpers::notify('Error', $error, 'error');
        Helpers::redirect('pm');
    }
}