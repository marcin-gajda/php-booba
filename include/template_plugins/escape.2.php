<?php
/*
 * Purpose:  Escape the string according to escapement type
 *
 * Code from Smarty: http://smarty.php.net/
 * Smarty is avalible under LGPL: smarty.license.txt
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

function plugin_escape($string, $esc_type = 'html') {
	switch ($esc_type) {
		case 'html': 		return htmlspecialchars($string, ENT_QUOTES);
		case 'htmlall':		return htmlentities($string, ENT_QUOTES);
		case 'url':		return urlencode($string);
					// escape unescaped single quotes
		case 'quotes': 		return preg_replace("%(?<!\\\\)'%", "\\'", $string);
					// escape quotes and backslashes and newlines
		case 'javascript':	return strtr($string, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n'));
					// escape every character into hex
		case 'hex': 
			$return = '';
			for ($x=0; $x < strlen($string); $x++) {
				$return .= '%' . bin2hex($string[$x]);
			}
			return $return;
		case 'hexentity':
			$return = '';
			for ($x=0; $x < strlen($string); $x++) {
				$return .= '&#x' . bin2hex($string[$x]) . ';';
			}
			return $return;

		default:
			trigger_error( 'Undefined escape type', E_USER_NOTICE );
			return $string;
	}
}
?>
