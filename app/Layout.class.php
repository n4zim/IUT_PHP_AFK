<?php
class Layout {
    public static function prepareLayout() {
        $data = array('user' => false, 'notification' => false);
        
        if(isset($_SESSION['u.id'])) {
            $data['user'] = array('id' => $_SESSION['u.id'], 'username' => $_SESSION['u.username']);
            $data['pmUnread'] = PM::countUnread();
        }

        if(isset($_SESSION['n.message'])) {
            $data['notification'] = array('message' => $_SESSION['n.message'], 'title' => $_SESSION['n.title'], 'type' => $_SESSION['n.type']);
            Helpers::unsetNotification();
        }

        $data['loginLink'] = Helpers::makeUrl('login');
        $data['logoutLink'] = Helpers::makeUrl('login', 'out');
        $data['registerLink'] = Helpers::makeUrl('register');
        $data['profileLink'] = Helpers::makeUrl('user', 'profile');
        $data['eventsLink'] = Helpers::makeUrl('event');
        $data['directoryLink'] = Helpers::makeUrl('user');
        $data['friendlistLink'] = Helpers::makeUrl('user', 'friendlist');
        $data['pmLink'] = Helpers::makeUrl('pm');
        
        if(isset($_SESSION['u.admin']))
            $data['adminLink'] = Helpers::makeUrl('admin');

        return $data;
    }
}