<?php

/**
 * Limp - less is more in PHP
 * @copyright   Bill Rocha - http://plus.google.com/+BillRocha
 * @license     MIT
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.0.1
 * @package     Limp
 * @access      public
 * @since       0.3.0
 *
 */

namespace Limp\App;

use Limp\Data;

class Debug
{


	//Error handler function
	static function errorHandler($errno, $errstr, $errfile, $errline) {
	    switch ($errno) {
	    case E_USER_ERROR:
	        echo "<b>ERROR:</b> [$errno] $errstr<br>
	        <b>File: </b>$errfile [$errline]<br>
	        Aborting...<br>";
	        exit(1);
	        break;

	    case E_USER_WARNING:
	        echo "<b>WARNING:</b> [$errno] $errstr<br><b>File:</b> $errfile [$errline]";
	        break;

	    case E_USER_NOTICE:
	        echo "<b>NOTICE:</b> [$errno] $errstr<br><b>File:</b> $errfile [$errline]";
	        break;

	    default:
	        echo "<b>Unknown error type:</b> [$errno] $errstr<br><b>File:</b> $errfile [$errline]";
	        break;
	    }

	    /* Don't execute PHP internal error handler */
	    return true;
	}

	//Exception function
	static function exceptionHandler($e) {
	    if(get_class($e) == 'PDOException'){
	        $err = $e->getMessage().'<br>code: '.$e->getCode();
	    } else {
	        $err = 
	        '<b>Code:</b>'.$e->getCode().'<br>'.
	        '<b>Message:</b> <i>'.$e->getMessage().'</i><br>'.
	        '<b>Thrown in: </b>'.$e->getFile().' ['.$e->getLine().']<br>'.
	        '<b>Stack trace:</b><pre>'.$e->getTraceAsString().'</pre>';
	        
	    }
	    exit($err);
	}

}