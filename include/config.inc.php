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

/*
 * Define these two constants in your application config file.
 *
define( 'PHP_BOOBA_ROOT' , '' );
define( 'PHP_BOOBA_PAGES_ROOT' , '' );
 */

class Config
{
	function getParameterValue( $parameterName )
	{
	}

	function setParameters( $parameters )
	{
	}
}

class ArrayConfig extends Config
{
	var $parameters;

	function ArrayConfig()
	{
		$this->parameters =
			array(
				'DB' => array(
					'host' => 'kicha3' ,
					'dbname' => 'base' ,
					'user' => 'm' ,
					'password' => '' ,
					'port' => 5432 ) );

	}

	function getParameterValue( $parameterName )
	{
		return isset( $this->parameters[ $parameterName ] ) ? $this->parameters[ $parameterName ] : NULL;
	}

	function setParameters( $parameters )
	{
		foreach( $parameters as $name => $value )
			$this->parameters[ $name ] = $value;
	}
}

function &getConfig()
{
	global $_BOOBA_CONFIG;

	if( !is_object( $_BOOBA_CONFIG ) )
		$_BOOBA_CONFIG = new ArrayConfig();

	return $_BOOBA_CONFIG;
}

?>
