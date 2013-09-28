<?php

require_once('php/startup.php');

?>
<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">
    <title>DefianceCraft Poll</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="css/main.css" rel="stylesheet" type="text/css">

    <!--[if lt IE 9]>
    <script src="scripts/html5shiv.js"></script>
    <![endif]-->

</head>
<body>

    <header>

            <h1>Poll: <span class="poll-title"><?php Utils::output(Voting::getVoteInfo()->vote_title); ?></span></h1>

    </header>

    <section>

        <div class="left half">
            <div class="container">
                
                <h2><?php Utils::output(Voting::getVoteInfo()->left_text); ?></h2>
                <button class="btn green" data-action="vote:left">VOTE YES</button>

            </div>
        </div>

        <div class="right half">
            <div class="container">

                <h2><?php Utils::output(Voting::getVoteInfo()->right_text); ?></h2>
                <button class="btn red" data-action="vote:right">VOTE NO</button>

            </div>
        </div>

    </section>

    <script src="scripts/jquery-1.10.2.min.js"></script>
    <script src="scripts/jquery.transit.min.js"></script>
    <script src="scripts/dcpoll-1.0.js"></script>
    
</body>
</html>