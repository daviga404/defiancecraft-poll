<?php

/**
 * Admin class
 * 
 * A class that controlls the administrative functions
 * of the website at /admin.
 * 
 * @author  David Baxter <daviga404@gmail.com>
 * @version 1.0
 */
class Admin {

    /**
     * A list of error code strings. Used to prevent against things like
     * XSS if the action has a JSON response.
     * 
     * @var array The array of errors.
     */
    public static $errors = array(
        0 => 'Invalid username/password. Please try again.',
        1 => 'Invalid CSRF token!',
        2 => 'Not logged in.',
        3 => 'Action does not exist.',
        4 => 'Invalid parameters: needed username (string), password (string)',
        5 => 'Not enough arguments.',
        6 => 'SQL query failed.'
    );

    /**
     * Attempts to login and set the session variables (including CSRF
     * token). Returns false if the login was unsuccessful, or true if
     * it was successful.
     * 
     * @return boolean
     */
    private static function attemptLogin($params)
    {
        if (!array_key_exists('username', $params)
            || !array_key_exists('password', $params)) {
            return 4;
        }

        $details  = Database::getCredentials($params['username']);
        $password = $params['password'];
        if (password_verify($password, $details['password'])) {

            $_SESSION['csrf']     = md5(uniqid(mt_rand(), true));
            $_SESSION['loggedin'] = true;
            return true;

        } else {

            return 0;

        }
    }

    /**
     * Checks if the user is logged in
     * 
     * @return boolean Whether the user is logged in or not.
     */
    public static function isLoggedIn()
    {
        return array_key_exists('loggedin', $_SESSION) && $_SESSION['loggedin'] === true;
    }

    /**
     * Triggers the appropriate action_ function and verifies CSRF
     * token or logs the user in. Can throw an exception which should
     * be handled by the index page for the admin panel in JSON format.
     * 
     * @return mixed False if there was never an action, or an array
     *               if the action executed (successfully or not).
     */
    public static function triggerAction()
    {
        if (array_key_exists('action', $_POST)) {

            // If the action is to login, there is no need to verify CSRF token.
            if ($_POST['action'] == 'login') {

                // Check if the login failed and get the error code.
                if (($result = self::attemptLogin($_POST)) !== true):

                    return array(
                        'action'    => 'login',
                        'success'   => false,
                        'errorCode' => $result
                    );

                // Return success!
                else:

                    return array(
                        'action'  => 'login',
                        'success' => true
                    );

                endif;

            } else {

                // Verify CSRF token
                if (!array_key_exists('csrf', $_SESSION) || !array_key_exists('csrf', $_POST) || $_POST['csrf'] !== $_SESSION['csrf']):

                    return array(
                        'action'    => $_POST['action'],
                        'success'   => false,
                        'errorCode' => 1
                    );

                // Verify the user is logged in
                elseif (!self::isLoggedIn()):

                    return array(
                        'action'    => $_POST['action'],
                        'success'   => false,
                        'errorCode' => 2
                    );

                endif;

                // Execute the action, if existant.
                if (method_exists(get_class(), 'action_' . $_POST['action'])) {

                    $params = $_POST;
                    unset($params['action']);
                    unset($params['csrf']);

                    ksort($params);

                    if (($result = call_user_func_array('Admin::action_' . $_POST['action'], $params)) && !array_key_exists('errorCode', $result)):

                        return array(
                            'action'  => $_POST['action'],
                            'success' => true,
                            'extra'   => $result['extra']
                        );

                    else:

                        return array(
                            'action'    => $_POST['action'],
                            'success'   => false,
                            'errorCode' => $result['errorCode']
                        );

                    endif;
                } else {
                    return array(
                        'action'    => $_POST['action'],
                        'success'   => false,
                        'errorCode' => 3
                    );
                }

            }

        } else {

            return false;

        }
    }

    /**
     * All of the below functions are dynamic functions which trigger based on a POST request.
     * The PHPDoc in this block applies to all of the functions.
     * 
     * @param  object - One or more parameters, alphabetically sorted which correspond to the
     *                  POST variables.
     * @return array    An array containing an 'errorCode' key if there was an error, or an
     *                  'extra' key with extra info if there was success. 
     */

    private static function action_changeCurrentVote($currentVote = null, $delete = array())
    {

        if ($currentVote === null
         || $delete      === null)                              return array( 'errorCode' => 5 );
        if (!Database::setOption('current_vote', $currentVote)) return array( 'errorCode' => 6 );

        if (count($delete) > 0) {
            Database::deletePolls($delete);
        }

        return array( 'extra' => '' );
    }

    private static function action_createPoll($left_text = null, $right_text = null, $vote_title = null)
    {
        if ($left_text  === null  || empty($left_text)
         || $right_text === null  || empty($right_text)
         || $vote_title === null) || empty($vote_title)                  return array( 'errorCode' => 5 );
        if (!Database::createPoll($vote_title, $left_text, $right_text)) return array( 'errorCode' => 6 );

        return array( 'extra' => '' );
    }

}

?>