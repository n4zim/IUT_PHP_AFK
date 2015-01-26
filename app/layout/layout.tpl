<!doctype html>
<html>
<head>
    <title>?$pageTitle {$pageTitle} -  $pageTitle?CookieCatch AFK</title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="assets/css/top.css"/>
    <link rel="icon" href="assets/favicon.ico" />
    <link rel="shortcut icon" sizes="16x16 24x24 32x32 48x48 64x64" href="assets/favicon.ico">
(( head ))
</head>
<body>
    <header>
        <div id="title"><a href="index.php">CookieCatch AFK</a></div>
        <div id="user">
        ?$layout.user
            Bienvenue <a href="{$layout.profileLink}">{$layout.user.username}</a>
            <a href="{$layout.profileLink}"><img src="assets/img/profile.png"></a>
            <a href="{$layout.friendlistLink}"><img src="assets/img/friends.png"></a>
            <a href="{$layout.upcomingLink}"><img src="assets/img/calendar.png"></a>
            <a href="{$layout.pmLink}"><img src="assets/img/msg.png"> ({$layout.pmUnread})</a>
            <a href="{$layout.logoutLink}"><img src="assets/img/disconnect.png"></a>
        @else@
            <form action="{$loginAction}" class="headlogin" method="POST">
                <input name="username" type="text" placeholder="Nom d'utilisateur" />
                <input name="password" type="password" placeholder="Mot de passe" /><br />
                <input id="h-checkbox-remember" type="checkbox" name="remember" checked="checked"/><label for="f-checkbox-remember">Se souvenir</label><br />
                <input type="submit" value="Connexion" /><br />
            </form>
        $layout.user?
        </div>
    </header>
    <!--<h1><a href="index.php">CookieCatch</a></h1>-->
?$layout.notification
<div class="notification {$layout.notification.type}" onclick="this.style.display = 'none';">
  ?$layout.notification.title
    <strong>{$layout.notification.title}</strong><br />
    {$layout.notification.message}
  $layout.notification.title?
</div>
$layout.notification?
    <div id="menu">
        <a href="{$layout.homeLink}">Accueil</a> - 
        <a href="{$layout.factionLink}">Factions</a> - 
        <a href="{$layout.eventsLink}">Evénements</a> - 
        <a href="{$layout.directoryLink}">Annuaire</a> - 
        <a href="{$layout.createEventsLink}">Créer un événement</a>
        ?$layout.adminLink - <a href="{$layout.adminLink}">Admninistration</a> $layout.adminLink?
    </div>
    <div id="content">(( content ))</div>
</body>
</html>
