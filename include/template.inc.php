<?php
/*******************************************************************************
 * T E M P L A T E   S Y S T E M
 *
 * 	Antoni Jakubuak <antek@sk8.pl>
 * 	(c) Firma "JAKUBIAK" 2004
 * 	http://www.jakubiak.biz/
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
 * 	Yet Another Template Engine but I use Polish notation 
 * (or reverese Polish notation) for parsing data. 
 *
 * >>Reverse Polish notation (RPN) (aka postfix notation) is an arithmetic formula 
 * notation, derived from the polish notation introduced in 1920 by the Polish 
 * mathematician Jan £ukasiewicz. RPN was invented by Australian philosopher 
 * and computer scientist Charles Hamblin in the mid-1950s, 
 * to enable zero-address memory stores.<<
 * (http://en.wikipedia.org/wiki/Reverse_Polish_Notation)
 *
 * TODO: copy example to documentation:
 *
 * Why Polish Notation:
 *	- it's fast 
 *	- do not need compilation
 *	- it has functionality of programming language
 * 	- it's extremely easy to parse
 *
 * Example 1:
 * <test.html>
 *	{get a}
 * <test.php>
 *	$t = new Template;
 *	$t->assign( 'a', 'Hello World' );
 *	$t->fetch( 'test.html' );
 * <result>
 *	Hello World
 * 
 * Example 2:
 * <!-- ENCODING iso-8859-2 -->
 * <test2.html>
 * 	<table>
 *		{foreach get a get row item key}
 *		<!-- BEGIN row -->
 *		<tr>
 * 			<td>{get key}</td>
 *			<td>{get item}</td>
 *			<td>{+ get item 10}</td>
 *		</tr>
 *		<!-- END row -->
 *	</table>
 * <test2.php>
 *	$t = new Template;
 *	$t->assign( 'a', range( 10, 12 ) );
 *	$t->fetch( 'test2.html' );
 * <result retuns html table containg information>
 * 	0	10	20
 *	1	11	21
 *	2	12	22
 *	
 *
 *
 *
 * Variable declaration:
 *	Like PHPLIB but do not need calling set_block
 * <!-- BEGIN label -->
 * <!-- END label -->
 *	
 * Executing:
 * {}
 *
 * Source file encoding declaration:
 * <!-- ENCODING blabla -->
 *
 * $Id: template.inc.php,v 1.20 2004/10/16 09:10:42 spiacy Exp $
 */

class BoobaTemplate {
	/*
	** Use or not reverse Polish notation
	*/
	var $reverse_Polish_notation = false;
	/*
	** Destination encoding, 
	** Find encoding declaration in file and convert it to destination 
	*/
	var $encoding = 'utf-8';

	/*
	** Max depth in recursion parsing
	*/
	var $depth = 50;

	/*
	** Sciê¿ka do otwieranych plików
	*/
	var $root = './';
	
	/*
	** Variables
	*/
	var $vars = array(
		'NULL'		=> null,
	);

	/*
	** Functions
	*/
	var $functions = array(
		// basic operators
		'get'		=> array( 1, '_call_get' ),
		'parse'		=> array( 1, '_call_parse' ),

		// loops
		'foreach'	=> array( 4, '_call_foreach' ),
		// 
		'default'	=> array( 2, '_call_default' ),
		'if'		=> array( 3, '_call_if' ),
		// mathematical
		'+'		=> array( 2, '_call_plus' ),
		'-'		=> array( 2, '_call_minus' ),
		'*'		=> array( 2, '_call_mul' ),
		'/'		=> array( 2, '_call_div' ),
		'%'		=> array( 2, '_call_mod' ),
		// logical
		'=='		=> array( 2, '_call_eq' ),
		'!='		=> array( 2, '_call_neq' ),
		'<'		=> array( 2, '_call_lt' ),
		'>'		=> array( 2, '_call_gt' ),
		'<='		=> array( 2, '_call_lteq' ),
		'>='		=> array( 2, '_call_gteq' ),
		// aliases for logical 
		'eq'		=> array( 2, '_call_eq' ),
		'neq'		=> array( 2, '_call_neq' ),
		'lt'		=> array( 2, '_call_lt' ),
		'gt'		=> array( 2, '_call_gt' ),
		'lteq'		=> array( 2, '_call_lteq' ),
		'gteq'		=> array( 2, '_call_gteq' ),
		'not'		=> array( 1, '_call_not' ),
		'empty'		=> array( 1, '_call_empty' ),

		// PHP wrappers
		'trim'		=> array( 1, 'trim' ), 
		'count'		=> array( 1, 'count' ),
		'pop'		=> array( 1, 'array_pop' ),
		'shift'		=> array( 1, 'array_shift' ),
		'strtolower'	=> array( 1, 'strtolower' ),
		'strtoupper'	=> array( 1, 'strtoupper' ),
	);
	
	function BoobaTemplate( $root = './' ) {
		$this->root = $root;
		$this->use_plugins_directory( dirname( __FILE__ ) . '/template_plugins/' );
	}


	/*
	** Funkcje dla kompatybilno¶ci z SMARTY
	*/
	function assign( $varname, $value = '' ) { 
		$this->set_var( $varname, $value ); 
	}
	function fetch( $filename ) {
		$block = uniqid( time() );
		$this->set_file( $block, $filename );
		return $this->get( $block );
	}

	/*
	** Kasowanie zmiennej z templata
	*/
	function clearvar( $key ) {
		unset( $this->vars[ $key ] );
	}

	/*
	** U¿yj podanej œcie¿ki jako katalogu z pluginami.
	** Przeszukuje katalog, wybierz pliki pasuj¹ce do wzorca
	** dopisz do listy funkcji
	*/
	function use_plugins_directory( $dir ) {
		if ( ! is_dir( $dir ) ) return;
		if ( $handle = opendir( $dir ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) { 
				if ( preg_match( '/(.*).(x|[0-9]{1,3}).(inc|php)$/', $file, $r ) ) {
					$this->functions[ $r[1] ] = array( 
						$r[2] == 'x' ? '*' : $r[2], 
						'plugin_' . $r[1], 
						$dir . $r[0] 
					);
				}
			}
			closedir($handle); 
		}
	}


	/*
	** Wczytanie plików
	*/
	function set_file( $varname, $filename = '' ) {
		if ( is_array( $varname ) ) {
			foreach( $varname as $k => $v ) $this->set_file( $k, $v );
			return;
		}
		// loading file
		$str = file_get_contents( $this->root . $filename );
		// searching src encoding
		if ( preg_match( '/<!--\s+ENCODING\s+([-_a-zA-Z0-9]+)\s+-->/', $str, $r ) ) {
			// del encoding tag declaration
			$str = preg_replace( '/<!--\s+ENCODING\s+([-_a-zA-Z0-9]+)\s+-->/', '', $str );
			if ( function_exists( 'mb_convert_encoding' ) ) {
				$str = mb_convert_encoding( $str, $this->encoding, $r[1] );
			} elseif ( function_exists( 'iconv' ) ) {
				$str = iconv( $r[1], $this->encoding, $str );
			} else {
				trigger_error( 'Character encoding converstion function not found', E_USER_NOTICE );
			}
		}
		// lets be a block
		$this->set_block( $varname, $str );
	}

	/*
	** Wyszukanie bloków i wpakowanie ich do zmiennych
	** Przypisanie tego co zostanie do zmiennej
	*/
	function set_block( $varname, $value = '' ) {
		if ( is_array( $varname ) ) {
			foreach( $varname as $k => $v ) $this->set_block( $k, $v );
			return;
		}
		$value = preg_replace_callback( 
			'/<!--\s+BEGIN\s+([-_a-zA-Z0-9]+)\s+-->(.*?)<!--\s+END\s+\1\s+-->/sm',
			array( &$this, '_set_callback' ),
			$value
		);
		$this->vars[ $varname ] = $value;
	}
	/*
	** Przypisanie zmiennych
	*/
	function set_var( $varname, $value = '' ) {
		if ( is_array( $varname ) ) {
			foreach( $varname as $k => $v ) $this->set_var( $k, $v );
			return;
		}
		if ( isset( $this->functions[ $varname ] ) ) {
			trigger_error( 
				"Trying to overwrite function: $varname with variable. It's unpossible.", 
				E_USER_NOTICE 
			);
		}
		$this->vars[ $varname ] = $value;
	}

	/*
	** Parsowanie zmiennej i pobranie warto¶ci
	*/
	function get( $varname ) {
		$tokens = array( $varname, 'get', 'parse' );
		if ( $this->reverse_Polish_notation ) {
			$tokens = array( $varname, 'get', 'parse' );
		} else {
			$tokens = array( 'parse', 'get', $varname );
		}
		$ret = $this->_get( $tokens );
		return array_pop( $ret );
	}


	/*
	** Stack evaluation
	*/
	function _get( $tokens = array() ) {
		$stack = array();

		if ( $this->reverse_Polish_notation === false ) {
			$tokens = array_reverse( $tokens );
		}
	
		foreach( $tokens as $token ) {
			switch( true ) {
				case is_numeric( $token ):
					// number
					array_push( $stack, $token );
					break;
				case isset( $this->functions[ $token ] );
					// function
					$params = array();
					if ( $this->functions[ $token ][ 0 ] == '*' ) {
						// operation on stack
						$this->_call( $token, array( &$stack ) );
					} else {
						// getting a number of params from stack
						for ( $i = 0; $i < $this->functions[ $token ][ 0 ]; $i++ ) {
							$params[] = array_pop( $stack );
						}
						$ret = $this->_call( $token, $params );
						array_push( $stack, $ret );
					}
					break;
				default:
					// label
					array_push( $stack, $token );
			}
		}
		return $stack;
	}


	



	
	
	/*
	** Zapisanie znalezionego bloku do zmiennej i usuniêcie bloku
	*/
	function _set_callback( $matches ) {
		$this->set_block( $matches[1], $matches[2] );
		return '';
	}

	/*
	** Podstawienie
	*/
	function _get_callback( $matches ) {
		$tokens = split( ' ', $matches[1] );
		$ret = $this->_get( $tokens );
		$val = array_pop( $ret );
		return $val;
	}

	
	/*
	** Pobranie warto¶ci zmiennej
	*/
	function _get_var( $varname ) {
		// zmienna mo¿e byæ tabli± lub obiektem
		$parts = split( '\.', $varname );
		$ret = $this->vars;
		foreach( $parts as $key ) {
			switch( true ) {
				case is_array( $ret )	: $ret = isset( $ret[ $key ] ) ? $ret[ $key ] : ''; break;
				case is_object( $ret )	: $ret = isset( $ret->$key ) ? $ret->key : ''; break;
				default:
			}
		}
		return $ret;
	}




	
	/*
	** Wykonanie instrukcji
	*/
	function _call( $function, $params = array() ) {
		$f = &$this->functions[ $function ];
		$call = &$f[ 1 ];
		if ( method_exists( $this, $call ) ) {
			$ret = call_user_func_array( array( &$this, $call ), &$params );
		} elseif ( function_exists( $call ) ) {
			$ret = call_user_func_array( $call, &$params );
		} elseif( isset( $f[ 2 ] ) ) {
			require_once( $f[ 2 ] );
			$ret = call_user_func_array( $call, &$params );
		} else {
			trigger_error( "Calling undefined function: $call", E_USER_ERROR );
			$ret = null;
		}
		return $ret;
	}

	/*
	** Internal declared functions
	*/


	/*
	** Getting a value of variable assinged with function set_var or set_block
	*/
	function _call_get( $label ) {
		return $this->_get_var( $label );
	}

	/*
	** Internal parsing a string
	** Searching of {...} and executing stack
	*/
	function _call_parse( $string ) {
		$this->depth--;
		if ( $this->depth == 0 ) {
			$ret = $string;
		} else {
			$ret = preg_replace_callback( 
				//				'/{([_$a-zA-Z0-9][^}]*)}/', 
				'/{([^}]+)}/', 
				array( &$this, '_get_callback' ), 
				$string
			);
		}
		$this->depth++;
		return $ret;
	}

	

	/*
	** foreach loop
 	**	loops all ellement of 'array'. set variable 'item' and 'key'
	**	returns concaneted parsed 'string'
	*/
	function _call_foreach( $array, $string, $item, $key ) {
		$ret = '';
		if ( is_array( $array ) ) foreach( $array as $k => $v ) {
			// przypisanie nowych zmiennych do szablonu
			$this->set_var( $item, $v );
			$this->set_var( $key, $k );
			$ret .= $this->_call_parse( $string );
		}
		return $ret;
	}

	/*
	** Math functions
	*/
	function _call_plus( $a, $b )	{ return $a + $b; }
	function _call_minus( $a, $b )	{ return $a - $b; }
	function _call_mul( $a, $b )	{ return $a * $b; }
	function _call_div( $a, $b )	{ return $a / $b; }
	function _call_mod( $a, $b )	{ return $a % $b; }

	/*
	** Equations
	*/
	function _call_eq( $a, $b )	{ return $a == $b; }
	function _call_neq( $a, $b )	{ return $a != $b; }
	function _call_lt( $a, $b )	{ return $a < $b; }
	function _call_lteq( $a, $b )	{ return $a <= $b; }
	function _call_gt( $a, $b )	{ return $a > $b; }
	function _call_gteq( $a, $b )	{ return $a >= $b; }
	function _call_not( $a )	{ return ! $a; }
	function _call_empty( $a )	{ return empty( $a ); }

	/*
	** Instrukcja warunkowa
	*/
	function _call_if( $if, $then, $else ) {
		return $if ? $then : $else;
	}

	/*
	** Warto¶æ domy¶lna
	*/
	function _call_default( $variable, $default ) {
		return $variable != '' ? $variable : $default;
	}

}
