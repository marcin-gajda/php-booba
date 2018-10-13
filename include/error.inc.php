<?
/*
 * Error Handling and Logging Functions
 * @Author Antoni Jakubiak <antek@sk8.pl>
 *
 * TODO Test
 * $Id: error.inc.php,v 1.12 2004/09/24 10:08:50 spiacy Exp $
 */

/*
 * php-booba - simple PHP web application network
 * Copyright (C) 2004  Marcin Gajda
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*
 * Exit execucion after error
 */
defined( 'E_EXIT' ) || define( 'E_EXIT', E_ALL );
/*
 * Max error to print on exit
 */
defined( 'E_MAX_PRINT' ) || define( 'E_MAX_PRINT' , 50 );
/*
 * Send html e-mail with error message. (false || emial address)
 * TODO. replace mailing with somthing else to store errors log.
 */
defined( 'E_EMAIL' ) || define( 'E_EMAIL', false );



/*
 * Global declaration
 */
$errors = array(); 

/*
 * Cathing error function
 * - Catch and save error to global declarated variavle 'error'
 * - Check error level and exit  
 */
function booba_error_handler ($errno, $errstr, $errfile, $errline)
{
	// exit if we don't want to report this error
	if ( ! ( $errno & error_reporting() ) ) return;

	global $errors;
	$errors[] = array(
		'errno'		=> $errno,
		'errstr'	=> $errstr,
		'errfile'	=> $errfile,
		'errline'	=> $errline,
		'backtrace'	=> function_exists( 'debug_backtrace' ) ? debug_backtrace() : false,
	);

	//
	if ( $errno & E_EXIT ) {
		exit( 1 );
	}
}

/*
 * Shutdown function 
 * - print no more than E_MAX_PRINT errors
 *	I want simplify this functin and i do not use any template engine,
 *	Errors are printed in XHTML
 * - TODO log error and ip to syslog
 * - TODO make a globbal definition to differentiate PRODUCTION and DEVELOP realease of version
 *	on production version do not print error information, only inform user that
 *	an error appear
 */
function booba_shutdown() {
	global $errors;
	$msg = '';
	if ( count( $errors ) > 0 ) {
		$msg .= "<br /><div align=\"center\">";
		$msg .= "<table style=\"font-size: 11px; font-family: tahoma, sans-serif;\" width=\"90%\" cellspacing=\"1\" cellpadding=\"2\" bgcolor=\"#ff0033\">";
		$msg .= "<tr>";
		$msg .= "\n<td><b><font color=\"white\">Id.</font></b></td>";
		$msg .= "\n<td><b><font color=\"white\">No.</font></b></td>";
		$msg .= "\n<td><b><font color=\"white\">Description</font></b></td>";
		$msg .= "\n<td><b><font color=\"white\">Line</font></b></td>";
		$msg .= "\n<td><b><font color=\"white\">File</font></b></td>";
		$msg .= "\n<td><b><font color=\"white\">Func</font></b></td>";
		$msg .= "\n<td><b><font color=\"white\">Args</font></b></td>";
		$msg .= "\n<td><b><font color=\"white\">Class</font></b></td>";
		$msg .= "</tr>";
		foreach( $errors as $key => $error ) {
			if ( $key > E_MAX_PRINT ) break;
			extract( $error );
			if ( $backtrace ) {
				$rowspan='rowspan="' . ( count( $backtrace ) - 1 ) . '"';
			} else {
				$rowspan='';
			}
			$msg .= "<tr valign=\"top\">";
			$msg .= "\n<td bgcolor=\"white\" $rowspan><font color=\"black\">$key</font></td>";
			$msg .= "\n<td bgcolor=\"white\" $rowspan><font color=\"black\">$errno</font></td>";
			$msg .= "\n<td bgcolor=\"white\" $rowspan><font color=\"black\">$errstr</font></td>";
			$msg .= "\n<td bgcolor=\"white\"><font color=\"black\">$errline</font></td>";
			$msg .= "\n<td bgcolor=\"white\"><font color=\"black\">$errfile</font></td>";
			if ( ! $backtrace ) {
				$msg .= "\n<td bgcolor=\"white\">&nbsp;</td>";
				$msg .= "\n<td bgcolor=\"white\">&nbsp;</td>";
				$msg .= "</tr>";
			} else foreach( $backtrace as $bk => $bv ) {
				if ( $bk == 0 ) continue;
				foreach( array( 'function', 'class', 'line', 'file' ) as $bvk ) {
					$$bvk = isset( $bv[ $bvk ] ) ? $bv[ $bvk ] : '';
				}
				$args = empty( $bv['args'] ) ? '' : htmlspecialchars ( var_export( $bv['args'], true ) );
				if ( $bk == 1 ) {
					$msg .= "\n<td bgcolor=\"white\"><font color=\"black\">$function</font></td>";
					$msg .= "\n<td bgcolor=\"white\"><font color=\"black\"><pre>$args</pre></font></td>";
					$msg .= "\n<td bgcolor=\"white\"><font color=\"black\">$class</font></td>";
					$msg .= "</tr>";
					continue;
				}
				$msg .= "<tr valign=\"top\">";
				$msg .= "\n<td bgcolor=\"white\"><font color=\"gray\">$line</font></td>";
				$msg .= "\n<td bgcolor=\"white\"><font color=\"gray\">$file</font></td>";
				$msg .= "\n<td bgcolor=\"white\"><font color=\"gray\">$function</font></td>";
				$msg .= "\n<td bgcolor=\"white\"><font color=\"gray\"><pre>$args</pre></font></td>";
				$msg .= "\n<td bgcolor=\"white\"><font color=\"gray\">$class</font></td>";
				$msg .= "</tr>";
			} 
		}
		$msg .= "</table></div><br />";
		echo $msg;
		if ( E_EMAIL ) {
			mail( 
					E_EMAIL,
					'PHP Error',
					"<html><body>$msg</body></html>",
					"Content-type: text/html\n"
			    );
		}
	}
}

/*
 * Old function used in PHP-BOOBA
 */
function error( $string )
{
	trigger_error( $string, E_USER_ERROR );	
}


/*
 * Registring shutdown function and error hanler 
 */
$old_error_handler = set_error_handler( 'booba_error_handler' );
register_shutdown_function( 'booba_shutdown' );


?>
