<?php
class Route {
    public static function getRoutes() {
        $r = array();
        $r[''] = $r['home'] = $r['index'] = 'Home';
        $r['users'] = $r['user'] = 'User';
        $r['login'] = 'Login';
        $r['register'] = 'Register';
        $r['pm'] = 'PM';
        $r['event'] = 'Event';
        $r['admin'] = 'Admin';

        return $r;
    }
}