<?php

/**
 * Utilities class
 * 
 * A class containing helper methods.
 * 
 * @version 1.0
 * @author  David Baxter <daviga404@gmail.com>
 */
class Utils {

    /**
     * Outputs an HTML-escaped string.
     * 
     * @param string $str The string to escape.
     */
    public static function output($str)
    {
        echo htmlspecialchars($str);
    }  

}

?>