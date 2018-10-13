<?php
/*
 * Function return element of array or object
 */
function plugin_dot( $array, $key ) {
	if ( empty( $array ) ) return '';
	if ( is_array( $array ) ) return $array[ $key ];
	return $array->$key;
}
?>
