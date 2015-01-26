<?php
class Layout {
    public static function prepareLayout() {
        $data = array('user' => false, 'notification' => false);
        
        // if loggued in
        if(isset($_SESSION['u.id'])) {
            $data['user'] = array('id' => $_SESSION['u.id'], 'username' => $_SESSION['u.username']);
            $data['pmUnread'] = PM::countUnread();
        }

        // if there is a notfication
        if(isset($_SESSION['n.message'])) {
            $data['notification'] = array('message' => $_SESSION['n.message'], 'title' => $_SESSION['n.title'], 'type' => $_SESSION['n.type']);
            Helpers::unsetNotification();
        }

        // do links in that way allows for easier url rewriting as the makeUrl
        // could be configured to do so
        $data['homeLink'] = 'index.php';
        $data['loginLink'] = Helpers::makeUrl('login');
        $data['logoutLink'] = Helpers::makeUrl('login', 'out');
        $data['registerLink'] = Helpers::makeUrl('register');
        $data['profileLink'] = Helpers::makeUrl('user', 'profile');
        $data['eventsLink'] = Helpers::makeUrl('event');
        $data['createEventsLink'] = Helpers::makeUrl('event', 'create');
        $data['directoryLink'] = Helpers::makeUrl('user');
        $data['factionLink'] = Helpers::makeUrl('faction');
        $data['friendlistLink'] = Helpers::makeUrl('user', 'friendlist');
        $data['pmLink'] = Helpers::makeUrl('pm');
        $data['upcomingLink'] = Helpers::makeUrl('event', 'upcoming');
        $data['legalLink'] = Helpers::makeUrl('', 'legal');
        $data['linksLink'] = Helpers::makeUrl('', 'links');
        $data['loginAction'] = Helpers::makeUrl('login', 'post');
        
        // if admin
        if(isset($_SESSION['u.admin']))
            $data['adminLink'] = Helpers::makeUrl('admin');

        return $data;
    }
}