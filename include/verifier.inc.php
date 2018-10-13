<?

/*
 * Cudo do automatycznych weryfikacji danych wszelakich.
 */
define( 'VER_TICKET' , 0 );
define( 'VER_NOT_ZERO_LENGTH_STRING' , 1 );
define( 'VER_INTEGER' , 2 );
define( 'VER_EVERYTHING' , 3 );
define( 'VER_CHECKBOX' , 4 );
define( 'VER_FLOAT' , 5 );
define( 'VER_EMAIL' , 6 );
define( 'VER_DATE' , 7 );
define( 'VER_TIME' , 8 );
define( 'VER_TIMESTAMP' , 9 );

class Verifier
{
	function verify( $type , $value , $defaultValue )
	{
		switch( $type )
		{
			case VER_TICKET:
				return $value;
			
			case VER_NOT_ZERO_LENGTH_STRING:
				return is_string( $value ) && strlen( $value ) > 0 ? $value : $defaultValue;

			case VER_INTEGER:
				return preg_match( '/^\d+$/' , $value ) ? $value : $defaultValue;

			case VER_EVERYTHING:
				return is_null( $value ) ? $defaultValue : $value;

			case VER_CHECKBOX:
				return $value == 'on';

			case VER_FLOAT:
				return is_numeric( $value ) ? $value : $defaultValue;

			case VER_EMAIL:
				return eregi( "^[\'_a-z0-9-]+(\.[\'_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$" , $value ) ? $value : $defaultValue;

			case VER_DATE:
				if( !preg_match( '/^(\d{4})-(\d\d)-(\d\d)$/' , $value , $preg_out ) )
					return $defaultValue;

				return checkdate( $preg_out[2] , $preg_out[3] , $preg_out[1] ) ? $value : $defaultValue;

			case VER_TIME:
				return preg_match( '/^[0-2]\d:[0-5]\d:[0-5]\d$/' ) && $value < '24:00:00' ? $value : $defaultValue;

			case VER_TIMESTAMP:
				if( !preg_match( '/^(\d{4})-(\d\d)-(\d\d)$/' , substr( $value , 0 , 10 ) , $preg_out ) )
					return $defaultValue;

				return checkdate( $preg_out[2] , $preg_out[3] , $preg_out[1] ) && preg_match( '/^ [0-2]\d:[0-5]\d:[0-5]\d$/' , substr( $value , 10 ) ) ? $value : $defaultValue;
		}

		return $value;
	}
}

function verify( $type , $value , $defaultValue )
{
	$ver = &getVerifier();

	return $ver->verify( $type , $value , $defaultValue );
}

function verifyFromArray( $type , &$array , $index , $defaultValue )
{
	if( !isset( $array[ $index ] ) )
		return $defaultValue;
	
	return verify( $type , $array[ $index ] , $defaultValue );
}

/*
 * Poniewa¿ w _REQUEST zawsze s± stringi, mo¿na sobie pozwoliæ
 * na warto¶æ domy¶ln±.
 */
function verifyFromREQUEST( $type , $index , $defaultValue = '' )
{
	return verifyFromArray( $type , $_REQUEST , $index , $defaultValue );
}

function &getVerifier()
{
	global $_BOOBA_VERIFIER;

	if( !is_object( $_BOOBA_VERIFIER ) )
		$_BOOBA_VERIFIER = new Verifier();
	
	return $_BOOBA_VERIFIER;
}

?>
