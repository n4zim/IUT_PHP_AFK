<!doctype html>
<html>
<head>
    <title>CookieCatch</title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="assets/css/top.css"/>
    (( head ))
</head>
<body>

?$layout.user
    <header>
        <div id="title">CookieCatch</div>
        <div id="user">
            Bienvenue {$layout.user.username}
            <a href="{$layout.profileLink}"><img src="assets/img/profile.png"></a>
            <a href="index.php?action=login&amp;method=out"><img src="assets/img/disconnect.png"></a>
        </div>
    </header>
@else@
    <h1>CookieCatch</h1>
$layout.user?


?$layout.notification
<div class="notification {$layout.notification.type}" onclick="this.style.display = 'none';">
  ?$layout.notification.title
    <strong>{$layout.notification.title}</strong><br />
    {$layout.notification.message}
  $layout.notification.title?
</div>
$layout.notification?

    <div id="content">
    (( content ))
    </div>
</body>
</html>