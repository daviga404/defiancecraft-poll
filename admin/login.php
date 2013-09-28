<?php

require_once('../php/startup.php');
require_once('../php/admin.php');

if (Admin::isLoggedIn()):
    header('Location: index.php');
    exit();
endif;

?>
<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">
    <title>DefianceCraft Poll | Admin Panel</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="../css/main.css" rel="stylesheet" type="text/css">
    <link href="../css/admin/main.css" rel="stylesheet" type="text/css">

    <!--[if lt IE 9]>
    <script src="scripts/html5shiv.js"></script>
    <![endif]-->

</head>
<body>

    <header style="box-shadow: 0px 5px 0 rgba(0,0,0,0.4)">

        <h1 style="text-shadow: none;">Poll Admin Panel</h1>

    </header>

    <section>

        <form class="login" action="index.php" method="POST">

            <h1>Login</h1>
            <ul>
                <li><label for="username">Username: </label><input id="username" name="username" type="text"></li>
                <li><label for="password">Password: </label><input id="password" name="password" type="password"></li>
                <li><input type="submit" class="btn"></li>
            </ul>

            <input type="hidden" name="action" value="login" />

        </form>

    </section>

    <script src="../scripts/jquery-1.10.2.min.js"></script>
    <script src="../scripts/dcpoll.admin-1.0.js"></script>
    
</body>
</html>