<!doctype html>
<html>
<head>
    <title>CookieCatch</title>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="assets/css/dev.css"/>
    (( head ))
</head>
<body>

?$layout.notification
<div class="notification {$layout.notification.type}">
  ?$layout.notification.title
    <strong>{$layout.notification.title}</strong><br />
    {$layout.notification.message}
  $layout.notification.title?
</div>
$layout.notification?

?$layout.user
    <strong>{$layout.user.username}</strong> (#{$layout.user.id}) <a href="index.php?action=login&amp;method=out">Logout</a> 
@else@
    <a href="index.php?action=login">Login</a> 
$layout.user?

    <section id="content">
    (( content ))
    </section>
</body>
</html>