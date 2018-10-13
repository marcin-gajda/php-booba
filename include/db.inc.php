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

class BoobaDB
{
	var $conn;
	var $_query_parameters;
	var $error;
	var $notice;

	function polacz()
	{
		if( $this->conn )
			$this->odlacz();

		$config = &getConfig();

		$dbConfig = $config->getParameterValue( 'DB' );

		$this->conn = pg_connect(
			'dbname='.$dbConfig[ 'dbname' ].' '
			.'host='.$dbConfig[ 'host' ].' '
			.'port='.$dbConfig[ 'port' ].' '
			.'user='.$dbConfig[ 'user' ].' '
			.'password='.$dbConfig[ 'password' ] );

		if( !$this->conn )
			return false;

		return true;
	}
	
	function odlacz()
	{
		if( $this->conn == NULL )
			return;

		pg_close( $this->conn );

		$this->conn = NULL;
	}

	function _polish_parameter( $type , $value )
	{
		/*
		 * First check if it is NULL.
		 */
		if( is_null( $value ) )
			return 'NULL';
	
		switch( $type )
		{
			case 'd':
				return $value;

			case 's':
				return '\''.$this->quote( $value ).'\'';

			case 'b':
				return $value ? 'TRUE' : 'FALSE';
		}
	}

	function make_array( $arr , $type )
	{
		if( count( $arr ) == 0 )
			return '\'{}\'';

		$result = 'ARRAY[ ';

		if( is_array( $arr[0] ) )
			$result .= $this->make_array( $arr[0] , $type );
		else
			$result .= $this->_polish_parameter( $type , $arr[0] );

		for( $i=1; $i<count( $arr ); $i++ )
			if( is_array( $arr[$i] ) )
				$result .= ' , '.$this->make_array( $arr[$i] , $type );
			else
				$result .= ' , '.$this->_polish_parameter( $type , $arr[$i] );

		return $result.' ]';
	}
	
	function _apply_parameters_to_query( $matches )
	{
		/*
		 * First check for double % - replace it with single %.
		 */
		if( $matches[0][1] == '%' )
			return '%';

		if( is_array( $this->_query_parameters[0] ) )
			return $this->make_array( array_shift( $this->_query_parameters ) , $matches[0][1] );
		else
			return $this->_polish_parameter( $matches[0][1] , array_shift( $this->_query_parameters ) );
	}

	function query( $query , $parameters = NULL )
	{
		if( $this->conn == NULL )
			return NULL;

		if( is_array( $parameters ) )
		{
			$this->_query_parameters = &$parameters;

			$query = preg_replace_callback( "/%[%bds]/" , array( &$this, '_apply_parameters_to_query' ) , $query );
		}

		$result = pg_query( $this->conn , $query );

		$this->notice = pg_last_notice( $this->conn );
		
		if( $result )
		{
			$this->error = pg_result_error( $result );

			$wynik = array();
			$ile_wierszy = pg_numrows( $result );

			for( $i=0; $i<$ile_wierszy; $i++ )
				array_push( $wynik , pg_fetch_array( $result , $i , PGSQL_ASSOC ) );

			pg_freeresult( $result );

			return $wynik;
		}
		else
		{
			$this->error = pg_last_error( $this->conn );

			return NULL;
		}
	}

	/*
	 * Return the first row of a query. If query returns no rows - return NULL.
	 */
	function getFirst( $query , $parameters = NULL )
	{
		if( count( $rows = $this->query( $query , $parameters ) ) == 0 )
			return NULL;
		else
			return $rows[0];
	}

	/*
	 * Returns the first field of the first row of a query. If query returns
	 * no rows - return NULL.
	 */
	function getOne( $query , $parameters = NULL )
	{
		if( is_array( $row = $this->getFirst( $query , $parameters ) ) )
			return array_shift( $row );
		else
			return NULL;
	}

	/*
	 * Returns associative array created from rows returned from query.
	 */
	function getAssoc( $query , $parameters = NULL , $keyField = 'key' , $valueField = 'value' )
	{
		$result = array();
		
		$rows = $this->query( $query , $parameters );

		if( count( $rows ) > 0 )
		{
			if( isset( $rows[0][ $keyField ] ) && isset( $rows[0][ $valueField ] ) )
				for( $i=0; $i<count( $rows ); $i++ )
					$result[ $rows[$i][ $keyField ] ] = $rows[$i][ $valueField ];
			else
				return NULL;
		}

		return $result;
	}

	function quote( $string )
	{
		return pg_escape_string( $string );
	}
}

function &getDB()
{
	global $_BOOBA_DB;

	if( !is_object( $_BOOBA_DB ) )
	{
		$_BOOBA_DB = new BoobaDB();

		$_BOOBA_DB->polacz();
	}

	return $_BOOBA_DB;
}

?>
