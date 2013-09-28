<?php

require_once('../php/startup.php');
require_once('../php/admin.php');


try {
    
    if (!array_key_exists('action', $_POST) &&
        !Admin::isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    if (($result = Admin::triggerAction()) !== false):

        $response = (object) $result;

        if (array_key_exists('errorCode', $response))
            $response->error = Admin::$errors[$response->errorCode];

        if ($response->action == 'login') {
            if (!$response->success):
                header('Location: login.php?error=' . $response->errorCode);
                exit();
            else:
                header('Location: index.php');
                exit();
            endif;
        }

        exit(json_encode($response));

    endif;

} catch (Exception $e) {
    $response = (object) array(
        'error'     => true,
        'exception' => $e->getMessage()
    );
    exit(json_encode($response));
}

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

        <h1 style="text-shadow: none;">Poll Admin Panel<small><a href="logout.php">Logout</a></small></h1>

    </header>

    <section>

        <h3>Current Poll: <span class="nobold"><?php echo Voting::getVoteInfo()->vote_title; ?></span></h3>
        <div class="votes">
            <span class="progress left" style="width:<?php echo Voting::buildResponse()->left_percent; ?>%;"><b><?php echo Voting::buildResponse()->left_count; ?> votes</b></span>
            <span class="progress right" style="width:<?php echo Voting::buildResponse()->right_percent; ?>%;"><b><?php echo Voting::buildResponse()->right_count; ?> votes</b></span>
        </div>

        <h3>Previous Polls</h3>
        <form action="index.php" method="POST" class="polls">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Votes Left</th>
                        <th>Votes Right</th>
                        <th>Left Text</th>
                        <th>Right Text</th>
                        <th>Current</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    foreach (Database::getAllVotes() as $key => $poll) {
                        $title = $poll['vote_title'];
                        $left  = $poll['votes_left'];
                        $right = $poll['votes_right'];
                        $ltext = $poll['left_text'];
                        $rtext = $poll['right_text'];
                        $id    = $key;
                        $radio = Voting::getCurrentVote() == $id ? 'checked' : '';
                        ?>
                        <tr class="<?php echo $radio; ?>">
                            <td><?php echo $title; ?></td>
                            <td><?php echo $left; ?></td>
                            <td><?php echo $right; ?></td>
                            <td><?php echo $ltext; ?></td>
                            <td><?php echo $rtext; ?></td>
                            <td><input type="radio" name="currentvote" value="<?php echo $id; ?>" <?php echo $radio; ?>></td>
                            <td><input type="checkbox" name="delete" value="<?php echo $id; ?>"></td>
                        </tr>
                        <?php
                    }

                    ?>
                </tbody>
            </table>
            <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">
            <input type="hidden" name="action" value="changeCurrentVote">
            <input type="submit" class="btn" value="Big ol' save button!">
        </form>

        <h3 class="omega">Create a new poll</h3>
        <form action="index.php" method="POST" class="polls">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Left Text</th>
                        <th>Right Text</th>
                        <th>Current</th>
                    </tr>
                </thead>
                <tbody>
                    <td><input type="text" name="vote_title"></td>
                    <td><input type="text" name="left_text"></td>
                    <td><input type="text" name="right_text"></td>
                    <td><input type="checkbox" disabled></td>
                </tbody>
            </table>
            <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">
            <input type="hidden" name="action" value="createPoll">
            <input type="submit" class="btn" value="Big ol' create button!">
            <!--

            TODO: Make submit buttons work

            -->
        </form>

    </section>

    <script src="../scripts/jquery-1.10.2.min.js"></script>
    <script src="../scripts/jquery.transit.min.js"></script>
    <script src="../scripts/dcpoll.admin-1.0.js"></script>
    
</body>
</html>