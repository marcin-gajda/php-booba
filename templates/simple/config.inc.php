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
 * Simple config file for Booba.
 */

/*
 * These constants are required by Booba.
 */
define( 'PHP_BOOBA_ROOT' , '/path/to/Booba/classes/in/your/system/' );
define( 'PHP_BOOBA_PAGES_ROOT' , '/path/to/Booba/page/class/of/your/application' );

/*
 * Include config base class.
 */
require_once( PHP_BOOBA_ROOT.'config.inc.php' );

/*
 * Start the session. Put session configuration (eg. cookie name) here.
 */
session_start();

/*
 * Get config object.
 */
$config = &getConfig();

$config->setParameters(
	array(
		'foo' => 'bar' ,
		'x' => array( 0 , 1 , 2 ) ) );

/*
 * Well done!
 */

?>
