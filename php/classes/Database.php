<?php

/**
 * Database class
 * 
 * A class to assist with storing/retrieving values from the SQLite database.
 * This class also handles connections to the database.
 * 
 * @version 1.0
 * @author  David Baxter <daviga404@gmail.com>
 */
class Database { 
    /**
     * The PDO object used by the database class.
     * 
     * @var \PDO The $pdo object used by the class.
     */
    private static $pdo = null;

    /**
     * Salt used for hashing IP addresses, not randomly generated.
     * 
     * @var string The salt.
     */
    public static $salt = '5dfgDFrA3lIw%Ov?iaC!1+Iy2lO+!992<!uH)+USW_WEkEta)=(x>TlQ4?=2#4Nl';

    /**
     * Initializes the database object, and handles an exception upon error.
     */
    public static function init()
    {
        try {
            
            self::$pdo = new PDO('sqlite:' . $GLOBALS['phpdir'] . '/data/votes.db');
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (Exception $e) {
            
            self::handleError($e);

        }
    }

    /**
     * Retrieves an option from the `options` table.
     * 
     * @param  string $key The option key stored in the database.
     * @return object The option's value
     */
    public static function getOption($key)
    {
        $statement = self::$pdo->prepare('SELECT value FROM options WHERE key = ?');
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->bindParam(1, $key);
        if (!$statement->execute()) return null;

        while ($row = $statement->fetch()):
            return $row['value'];
        endwhile;

        return null;
    }

    /**
     * Sets an option in the `options` table.
     * 
     * @param  string $key   The option key stored in the database.
     * @param  string $value The option value.
     * @return boolean Whether the insert completed successfully or not.
     */
    public static function setOption($key, $value)
    {
        $statement = self::$pdo->prepare('INSERT OR REPLACE INTO options (id, value, key) VALUES ((SELECT id FROM options WHERE key = :key), :value, :key);');
        $statement->bindParam(':key', $key);
        $statement->bindParam(':value', $value);
        return $statement->execute();
    }

    /**
     * Retrieves information about a vote from the `votes_info` table.
     * 
     * @param  int    $id The ID column of the vote to get.
     * @return object An object containing the attributes of the row.
     */
    public static function getVoteInfo($id)
    {
        $statement = self::$pdo->prepare('SELECT * FROM votes_info WHERE id = ?');
        $statement->setFetchMode(PDO::FETCH_OBJ);
        $statement->bindParam(1, $id);
        if (!$statement->execute()) return array();

        while ($row = $statement->fetch()):
            return $row;
        endwhile;

        return array();
    }

    /**
     * Gets the amount of votes on either side of a vote.
     * 
     * @param  int   $id The ID in the votes_info column.
     * @return array An array containing 'left' and 'right' keys.
     */
    public static function getVotes($id)
    {
        $statement1 = self::$pdo->prepare('SELECT COUNT(*) FROM votes WHERE votes_info_id = ? AND side = 0');
        $statement2 = self::$pdo->prepare('SELECT COUNT(*) FROM votes WHERE votes_info_id = ? AND side = 1');

        $statement1->setFetchMode(PDO::FETCH_ASSOC);
        $statement2->setFetchMode(PDO::FETCH_ASSOC);
        $statement1->bindParam(1, $id);
        $statement2->bindParam(1, $id);
        if (!$statement1->execute() || !$statement2->execute()) {
            return array( 'left' => 0, 'right' => 0 );
        }

        return array( 'left' => $statement1->fetchColumn(), 'right' => $statement2->fetchColumn() );
    }

    /**
     * Checks if an IP with $_SERVER['REMOTE_ADDR'] has voted on vote id $id.
     * 
     * @param  int     $id The ID of the vote.
     * @return boolean Whether the user has voted or not.
     */
    public static function hasVoted($id)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $ip = md5($ip . self::$salt);
        $statement = self::$pdo->prepare('SELECT COUNT(*) FROM votes WHERE votes_info_id = ? AND ip = ?');

        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->bindParam(1, $id);
        $statement->bindParam(2, $ip);
        if (!$statement->execute()) return true; // Prevent duplicate voting if there's a bug.

        return $statement->fetchColumn() > 0;
    }

    /**
     * Checks if a votes_info row exists with $id.
     * 
     * @param  int     $id The ID of the row
     * @return boolean A boolean determining whether the row exists or not.
     */
    public static function voteInfoExists($id)
    {
        $statement = self::$pdo->prepare('SELECT id FROM votes_info WHERE id = ?');
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->bindParam(1, $id);
        if (!$statement->execute()) return false;

        return $statement->fetch() === false ? false : true;
    }

    /**
     * Adds a counting vote to an vote.
     * 
     * @param  int     $id   The ID of the votes_info row
     * @param  boolean $side The side of the vote (false is left, true is right), boolean to save space.
     * @return boolean Whether the insert was successful or not.
     */
    public static function addVote($id, $side)
    {
        if (!self::voteInfoExists($id)) {
            throw new Exception('Could not find a row with votes_info ID, ' . $id);
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $ip = md5($ip . self::$salt);

        $statement = self::$pdo->prepare('INSERT INTO votes VALUES(null, ?, ?,? )');
        $statement->bindParam(1, $side, PDO::PARAM_BOOL);
        $statement->bindParam(2, $id);
        $statement->bindParam(3, $ip);
        return $statement->execute();
    }

    /**
     * Handles an error outputted by the class.
     * 
     * @param Exception $e An exception to handle.
     */
    private static function handleError($e)
    {
        $errorObject = json_decode('{}');
        $errorObject->error     = true;
        $errorObject->exception = $e->getMessage();
        echo json_encode($errorObject);
    }

    /*====================================

                Admin Functions

    ======================================*/
    /**
     * Retrieves the username and (hashed) password of a user.
     * 
     * @return array An associative array containing the 'username' and 'password'.
     */
    public static function getCredentials($user)
    {
        $statement = self::$pdo->prepare('SELECT username,password FROM admins WHERE username = ?');
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->bindParam(1, $user);
        if (!$statement->execute()) return array();
        return $statement->fetch();
    }

    /**
     * Gets all of the votes (polls) in an array with information, left votes, and right votes.
     * 
     * @return array An array indexed by the poll ID with the information
     */
    public static function getAllVotes()
    {
        $statement = self::$pdo->prepare('SELECT A.id,(SELECT COUNT(*) FROM votes WHERE side = 0 AND votes_info_id = A.id) AS votes_left, (SELECT COUNT(*) FROM votes WHERE side = 1 AND votes_info_id = A.id) AS votes_right, A.vote_title, A.left_text, A.right_text FROM votes_info AS A JOIN votes AS B GROUP BY A.id');
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        if (!$statement->execute()) return array();
        $allvotes = array();
        while ($row = $statement->fetch()) {
            //$allvotes[$row['id']]['votes_' . ((boolean)$row['side'] ? 'right' : 'left')] = intval($row['votes']);
            $allvotes[$row['id']]['vote_title']  = $row['vote_title'];
            $allvotes[$row['id']]['left_text']   = $row['left_text'];
            $allvotes[$row['id']]['right_text']  = $row['right_text'];
            $allvotes[$row['id']]['votes_left']  = $row['votes_left'];
            $allvotes[$row['id']]['votes_right'] = $row['votes_right'];
            if (!array_key_exists('votes_left',  $allvotes[$row['id']])) $allvotes[$row['id']]['votes_left']  = 0;
            if (!array_key_exists('votes_right', $allvotes[$row['id']])) $allvotes[$row['id']]['votes_right'] = 0;
        }
        return $allvotes;
    }

    /**
     * Creates a new poll (or 'vote') with the title, left text, and right text.
     * 
     * @param  string  $title The vote title
     * @param  string  $left  The text on the left side
     * @param  string  $right The text on the right side
     * @return boolean Whether the query was successful.
     */
    public static function createPoll($title, $left, $right)
    {
        $statement = self::$pdo->prepare('INSERT INTO votes_info VALUES(NULL, ?, ?, ?)');
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->bindParam(1, $title);
        $statement->bindParam(2, $left);
        $statement->bindParam(3, $right);
        return $statement->execute();
    }

    /**
     * Deletes polls (or 'votes') from the votes_info table.
     * 
     * @param  string  $ids The IDs of the polls to delete.
     * @return boolean Whether they were deleted.
     */
    public static function deletePolls($ids)
    {
        $ids = explode(',', $ids);
        $idsArray  = implode(',', array_fill(0, count($ids), '?'));
        $statement = self::$pdo->prepare('DELETE FROM votes_info WHERE id IN(' . $idsArray . ')');

        foreach ($ids as $key => $value) {
            $statement->bindParam($key + 1, $value);
        }

        $result1 = $statement->execute();

        $statement = self::$pdo->prepare('DELETE FROM votes WHERE votes_info_id IN(' . $idsArray . ')');

        foreach ($ids as $key => $value) {
            $statement->bindParam($key + 1, $value);
        }

        $result2 = $statement->execute();

        return $result1 && $result2;
    }

}

Database::init();

?>