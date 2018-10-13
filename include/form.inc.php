<?

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

require_once( PHP_BOOBA_ROOT.'verifier.inc.php' );

class BoobaForm
{
	/*
	 * An associative array describing fields in form. Each field is described
	 * by associative array with following fields:
	 * o name     - name of field in form
	 * o required - indicates whenever field must be present in incoming
	 *              request during verify process
	 * o type     - type of field, used by verifier; this is not PHP type,
	 *              but one of valid VER_* identifiers
	 */
	var $fields;

	/*
	 * Verifies and corrects incoming request against info about expected
	 * fields stored in this object. If an extra functionality is required,
	 * this class should be subclassed and this function redefined.
	 *
	 * Parameter for this function should be passed by reference. In other
	 * case corrections will be invisible for caller.
	 *
	 * Function returns boolean values indicating verify result.
	 */
	function verify( &$incomingRequest )
	{
		$result = true;
		
		foreach( $this->fields as $name => $field )
			if( !isset( $incomingRequest[ $name ] ) )
				$result = false;
			else
			{
				$value = verifyFromArray( $field[ 'verifier' ] , &$incomingRequest , $name );

				if( is_null( $value ) )
					$result = false;
				else
					$incomingRequest[ $name ] = $value;
			}

		return $result;
	}
	
	/*
	 * Imports form content from string, which was previously saved in
	 * ticket with dump() function.
	 */
	function import( $data )
	{
		$this->fields = unserialize( $data );
	}

	/*
	 * Dumps form content into string, which could be stored in ticket.
  	 */	 
	function dump()
	{
		return serialize( $this->fields );
	}
}

?>
