<?php
class Layout {
    public static function prepareLayout() {
        $data = array('user' => false, 'notification' => false);
        
        if(isset($_SESSION['u.id']))
            $data['user'] = array('id' => $_SESSION['u.id'], 'username' => $_SESSION['u.username']);

        if(isset($_SESSION['n.message'])) {
            $data['notification'] = array('message' => $_SESSION['n.message'], 'title' => $_SESSION['n.title'], 'type' => $_SESSION['n.type']);
            Helpers::unsetNotification();
        }

        $data['loginLink'] = Helpers::makeUrl('login');
        $data['registerLink'] = Helpers::makeUrl('register');
        $data['profileLink'] = Helpers::makeUrl('user', 'profile');

        return $data;
    }
}