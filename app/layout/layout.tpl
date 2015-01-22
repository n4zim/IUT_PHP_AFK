<!doctype html>
<html>
<head>
    <title>?$pageTitle {$pageTitle} -  $pageTitle?CookieCatch</title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="assets/css/top.css"/>
(( head ))
</head>
<body>
?$layout.user
    <header>
        <div id="title"><a href="index.php">CookieCatch</a></div>
        <div id="user">
            Bienvenue <a href="{$layout.profileLink}">{$layout.user.username}</a>
            <a href="{$layout.profileLink}"><img src="assets/img/profile.png"></a>
            <a href="{$layout.logoutLink}"><img src="assets/img/disconnect.png"></a>
            <a href="{$layout.friendlistLink}">amis</a>
            <a href="{$layout.pmLink}">msg ({$layout.pmUnread})</a>
        </div>
    </header>
@else@
    <h1><a href="index.php">CookieCatch</a></h1>
$layout.user?
?$layout.notification
<div class="notification {$layout.notification.type}" onclick="this.style.display = 'none';">
  ?$layout.notification.title
    <strong>{$layout.notification.title}</strong><br />
    {$layout.notification.message}
  $layout.notification.title?
</div>
$layout.notification?
    <div id="menu" style="background: pink;">
        <a href="{$layout.homeLink}">Accueil</a> - 
        <a href="{$layout.factionLink}">Factions</a> - 
        <a href="{$layout.eventsLink}">Events</a> - 
        <a href="{$layout.directoryLink}">Annuaire</a> - 
        <a href="{$layout.createEventsLink}">Créer un event</a>
    </div>
    <div id="content">(( content ))</div>
</body>
</html>
