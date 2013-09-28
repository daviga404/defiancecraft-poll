<?php

/**
 * Voting class
 * 
 * A class to help keep down the number of database queries by storing data in itself and providing
 * a means of accessing that data through public methods
 * 
 * @version 1.0
 * @author  David Baxter <daviga404@gmail.com>
 */
class Voting {

    /**
     * The variable that will hold the current vote ID.
     * 
     * @var int
     */
    private static $currentVote;

    /**
     * An array holding the amount of votes on either side (left and right)
     * 
     * @var array
     */
    private static $votes;

    /**
     * An object containing information about the current vote.
     * 
     * @var object
     */
    private static $voteInfo;

    /**
     * Initializes the variables and performs queries
     */
    public static function init()
    {
        // Do Database queries
        self::$currentVote = Database::getOption('current_vote');
        self::$votes = self::queryVotes();
        self::$voteInfo = self::queryVoteInfo();
    }

    /**
     * Gets the votes for either side in the current vote
     * 
     * @return array An associative array containing left and right keys.
     */
    public static function getVotes()
    {
        return self::$votes;
    }

    /**
     * Gets the current vote ID.
     * 
     * @return int
     */
    public static function getCurrentVote()
    {
        return self::$currentVote;
    }

    /**
     * Gets information about the current vote.
     * 
     * @return object An object containing the info.
     */
    public static function getVoteInfo()
    {
        return self::$voteInfo;
    }

    /**
     * Queries the database to get the votes array
     * 
     * @return array An associative array containing left and right keys.
     */
    private static function queryVotes()
    {
        return Database::getVotes(self::$currentVote);
    }

    /**
     * Queries the database to get vote information.
     * 
     * @return object An object containing information about the vote.
     */
    private static function queryVoteInfo()
    {
        return Database::getVoteInfo(self::$currentVote);
    }

    /**
     * Builds a response based on the amount of votes on either side.
     * 
     * @return object The response
     */
    public static function buildResponse()
    {
        $response = (object) array(
            'left_count'    => self::$votes['left'],
            'right_count'   => self::$votes['right'],
            'left_percent'  => round((self::$votes['left'] + self::$votes['right']) == 0 ? 0 : self::$votes['left'] /  (self::$votes['left'] + self::$votes['right']) * 100),
            'right_percent' => round((self::$votes['left'] + self::$votes['right']) == 0 ? 0 : self::$votes['right'] / (self::$votes['left'] + self::$votes['right']) * 100)
        );
        return $response;
    }

    /**
     * Adds a vote and sends a response.
     * 
     * @param  string $vote The string containing which side to vote on.
     * @return object The response from adding the vote.
     */
    public static function addVote($vote)
    {
        // Initialize vote variable based on $_POST
        if (!$vote):                                    throw new Exception('No vote parameter');
        elseif (empty($vote)):                          throw new Exception('Empty vote parameter');
        elseif ($vote !== 'left' && $vote !== 'right'): throw new Exception('Invalid vote parameter.');
        endif;

        // Initialize the response
        $response = self::buildResponse();

        // Add in response message, and add the vote if allowed
        if (Database::hasVoted(self::$currentVote)):                      // If they've already voted

            $response->message = 'You have already voted!';

        elseif (Database::addVote(self::$currentVote, $vote == 'right')): // If the vote was added

            self::$votes = self::queryVotes();
            $response = self::buildResponse();
            $response->message = 'Vote added!';

        else:                                                             // If the vote wasn't successful

            $response->message = 'Could not add vote.';

        endif;

        // Return the response
        return $response;
    }

}

Voting::init();

?>