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

class Parameters
{
	var $parameters;

	function Parameters( $parameters )
	{
		$this->parameters = $this->parseParameters( $parameters );
	}

	function parseParameters( $parameters )
	{
		$this->parameters = unserialize( $parameters );
	}

	function setParameterValue( $key , $value )
	{
		$this->parameters[ $key ] = $value;
	}

	function getParameterValue( $key )
	{
		return isset( $this->parameters[ $key ] ) ? $this->parameters[ $key ] : NULL;
	}

	/*
	 * Zwraca parametry w postaci stringa gotowe np. do zapisu do bazy.
	 */
	function serializeParameters()
	{
		return serialize( $this->parameters );
	}
}

class DelimiterSeparatedParameters extends Parameters
{
	/*
	 * Ci±g oddzielaj±cy poszczególne parametry.
	 */
	var $parameterDelimiter;

	/*
	 * Ci±g oddzielaj±cy klucz od warto¶ci w parametrze.
	 */
	 var $keyValueDelimiter;

	function DelimiterSeparatedParameters( $parameters , $pDelimiter , $kvDelimiter )
	{
		$this->parameterDelimiter = $pDelimiter;
		$this->keyValueDelimiter = $kvDelimiter;

		parent::Parameters( $parameters );
	}

	function parseParameters( $parameters )
	{
		foreach( explode( $this->parameterDelimiter , $parameters ) as $parameter )
		{
			$explodedParameter = explode( $this->keyValueDelimiter , $parameter );

			if( count( $explodedParameter ) >= 2 )
				$this->parameters[ $explodedParameter[0] ] = implode( $this->keyValueDelimiter , array_slice( $explodedParameter , 1 ) );
		}
	}

	function serializeParameters()
	{
		$implodedParameters = array();

		foreach( $this->parameters as $key => $value )
			$implodedParameters[] = $key.$this->keyValueDelimiter.$value;

		return implode( $this->parameterDelimiter , $implodedParameters );
	}
}

class SemicolonSeparatedParameters extends DelimiterSeparatedParameters
{
	function SemicolonSeparatedParameters( $parameters )
	{
		parent::DelimiterSeparatedParameters( $parameters , ';' , '=' );
	}
}

?>
