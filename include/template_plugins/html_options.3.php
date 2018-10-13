<?php
/*
 * Printing html <option> tag
 *
This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
function plugin_html_options( $options, $selected, $with_keys ) {
	$ret = '';
	if ( ! is_array( $options ) ) return $ret;
	if ( $with_keys ) {
		foreach( $options as $k => $v ) {
			$ret .= '<option';
			$ret .= $k == $selected || ( is_array( $selected ) && in_array( $k, $selected ) ) ? ' selected' : '';
			$ret .= ' value="' . htmlspecialchars( $k, ENT_QUOTES ) . '">';
			$ret .= htmlspecialchars( $v, ENT_QUOTES );
			$ret .= '</option>';
		}
	} else {
		foreach( $options as $k => $v ) {
			$ret .= '<option';
			$ret .= $v == $selected || ( is_array( $selected ) && in_array( $v, $selected ) ) ? ' selected' : '';
			$ret .= '>';
			$ret .= htmlspecialchars( $v, ENT_QUOTES );
			$ret .= '</option>';
		}
	}
	return $ret;
}
?>
