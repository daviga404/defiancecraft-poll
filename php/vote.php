<?php

try {
 
    require_once('startup.php');

    echo json_encode(Voting::addVote(array_key_exists('vote', $_POST) ? $_POST['vote'] : false));

} catch (Exception $e) {
    $response = (object) array(
        'error'     => true,
        'exception' => $e->getMessage()
    );
    exit(json_encode($response));
}

?>