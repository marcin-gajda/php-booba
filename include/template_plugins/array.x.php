<?php
/* 
 * This function make array by join a number of elements 
 * from stack
 */
function plugin_array( &$stack ) {
	$count = array_pop( $stack );
	if ( $count == 'all' ) {
		$stack = array( $stack );
	} else {
		$args = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$args[] = array_pop( $stack );
		}
		array_push( $stack, $args );
	}
}
