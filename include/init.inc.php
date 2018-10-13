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

class Init
{
	function get()
	{
		require_once( PHP_BOOBA_ROOT.'db.inc.php' );
		require_once( PHP_BOOBA_ROOT.'ticket.inc.php' );
		require_once( PHP_BOOBA_ROOT.'verifier.inc.php' );
		require_once( PHP_BOOBA_ROOT.'error.inc.php' );

		$ticket = &getTicketService();

		/*
		 * Get the ticket.
		 */
		$tid = verifyFromREQUEST( VER_TICKET , 'tid' , 'main' );

		/*
		 * Explode ticket by dot. Everything after dot is additional
		 * ticket data and will be passed to $incomingRequest.
		 */
		$ticketData = explode( '.' , $tid );

		/*
		 * Get the request for ticket.
		 */
		$request = $ticket->findTicket( $ticketData[0] );

		/*
		 * Clean tickets if ticket isn't soft or static.
		 */
		if( !isset( $request[ '__soft__' ] ) && !isset( $request[ '__static__' ] ) )
			$ticket->clear( isset( $request[ 'ticket_group' ] ) ? $request[ 'ticket_group' ] : NULL );

		/*
		 * Get the class for page render. If ticket is static, get page via
		 * ticket name, otherwise search for "page" key in stored request.
		 */
		if( isset( $request[ '__static__' ] ) )
			$className = $ticketData[0];
		else
			$className = verifyFromArray( VER_NOT_ZERO_LENGTH_STRING , $request , 'page' , 'main' );

		/*
		 * Get the page by class name.
		 */
		$page = &$this->getPageByClassName( $className );

		/*
		 * For static tickets check if they are allowed for desired page.
		 */
		$incomingRequest = $_REQUEST;

		/*
		 * Set additional ticket data, if any provided.
		 */
		$incomingRequest[ '__additional__' ] = array_slice( $ticketData , 1 );

		$page->savedRequest = $request;
		$page->incomingRequest = $incomingRequest;
		
		if( isset( $request[ '__static__' ] ) )
			if( !$page->acceptsStaticTickets() )
			{
				$page = &$this->getPageByClassName( 'main' );

				$page->savedRequest = $request;
				$page->incomingRequest = $incomingRequest;
			}

		/*
		 * Render the page.
		 */
		$result = $page->render();
		
		/*
		 * Save the tickets.
		 */
		$ticket->saveTickets();

		/*
		 * Return rendered result.
		 */
		return $result;
	}

	/*
	 * Returns page object by class name. Class name does't mean class name
	 * from page class file, but string from ticket (without big letters,
	 * with underscores between words).
	 */
	function &getPageByClassName( $className )
	{
		if( !is_readable( PHP_BOOBA_PAGES_ROOT.$className.'/'.$className.'.inc.php' ) )
			$className = 'main';

		/*
		 * Include file for desired page.
		 */
		require_once( PHP_BOOBA_PAGES_ROOT.$className.'/'.$className.'.inc.php' );

		/*
		 * Page classes start with upper case. They have 'Page' suffix also.
		 * All underscores are trimmed.
		 */
		$class = ucfirst( str_replace( '_' , '' , $className ) ).'Page';

		$page = new $class();

		return $page;
	}
}

?>
