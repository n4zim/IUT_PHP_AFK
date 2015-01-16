<?php
class PM extends Controller {
    public function PM() {
        parent::__construct();
    }

    public function index($args) {
        $pmModel = new PMModel();
        $pms = $pmModel->getReceived(1);

        $this->afk->view('pm/list', 
            array(
                'received' => $pmModel->getReceived(1),
                'sent' => $pmModel->getSent(1)
            )
        );
    }

    public function send($args) {
        $pmModel = new PMModel();
        $message = file_get_contents("http://www.iheartquotes.com/api/v1/random");
        
        for ($i=0; $i < 10; $i++) { 
            $pmModel->sendPM(1, rand(1, 10), $message);
        }
        for ($i=0; $i < 100; $i++) { 
            $pmModel->sendPM(rand(1,10), rand(1, 10), $message);
        }
        exit();

    }

    public function direct($args) {
        $pastemodel = new PasteModel();
        $paste = $pastemodel->getPaste($args['p']);
        if($paste === false) $this->notifyError('This paste doesn\'t exists, was deleted or has expired');

        header("Content-Type:text/plain; charset=utf-8");
        echo $paste['Paste'];
        exit();
    }

    public function add() {
        $form = new Form();
        $form->addField_('paste', 'textarea', array('label' => 'Paste here :', 'class' => 'paste'));
        $form->addSubmit('Paste it !');        

        $this->afk->view('pasteadd', array('form' => $form->generate(Helpers::makeUrl('', 'post'))));
    }

    public function post() {
        if(empty($_POST['paste'])) 
            $this->notifyError('Missing data');

        $pastemodel = new PasteModel();
        $pasteid = $pastemodel->addPaste($_POST);
        Helpers::redirect('', '', array('p' => $pasteid));
    }

    private function notifyError($error) {
        Helpers::notify('Error', $error, 'error');
        Helpers::redirect('');
    }
}