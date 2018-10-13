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
 * Ticket system service.
 */
class TicketService
{
	var $tickets;
	var $ticket_groups;

	function TicketService()
	{
		$this->getTickets();
	}
	
	/*
	 * Save all tickets into session.
	 */
	function saveTickets()
	{
		$_SESSION[ 'tickets' ] = $this->tickets;
		$_SESSION[ 'ticket_groups' ] = $this->ticket_groups;
	}

	/*
	 * Get tickets from session.
	 */
	function getTickets()
	{
		$this->tickets = isset( $_SESSION[ 'tickets' ] ) ? $_SESSION[ 'tickets' ] : array();
		$this->ticket_groups = isset( $_SESSION[ 'ticket_groups' ] ) ? $_SESSION[ 'ticket_groups' ] : array();
	}

	/*
	 * Add ticket.
	 *
	 * Ticket value is returned. Ticket group may be passed into second
	 * parameter.
	 */
	function addTicket( $valuesToStore , $group = NULL )
	{
		$tid = $this->getNewTicketName();
		
		$this->addExplicitTicket( $tid , $valuesToStore , $group );

		return $tid;
	}

	/*
	 * Add ticket with explicit name.
	 */
	function addExplicitTicket( $tid , $valuesToStore , $group = NULL )
	{
		if( !is_null( $group ) )
		{
			if( !isset( $this->ticket_groups[ $group ] ) )
				$this->ticket_groups[ $group ] = array();

			$this->ticket_groups[ $group ][] = $tid;

			$valuesToStore[ 'ticket_group' ] = $group;
		}
		
		$this->tickets[ $tid ] = $valuesToStore;
	}

	/*
	 * Add ticket, which will not clean tickets after getting this ticket
	 * by user. Usefull for pages with a lot of popups.
	 */
	function addSoftTicket( $valuesToStore , $group = NULL )
	{
		$tid = $this->addTicket( $valuesToStore , $group );

		$this->tickets[ $tid ][ '__soft__' ] = true;

		return $tid;
	}

	/*
	 * Return unique ticket value, which is not present yet.
	 */
	function getNewTicketName()
	{
		do
		{
			$tid = sha1( time().mt_rand( 0 , 65535 ) );
		}
		while( $this->isTicketSet( $tid ) );

		return $tid;
	}

	/*
	 * Check if ticket is setted.
	 */
	function isTicketSet( $tid )
	{
		return isset( $this->tickets[ $tid ] );
	}

	/*
	 * Get ticket.
	 */
	function getTicket( $tid )
	{
		return $this->isTicketSet( $tid ) ? $this->tickets[ $tid ] : NULL;
	}

	/*
	 * Clear tickets.
	 *
	 * If group is not null, only tickets from that group are removed.
	 */
	function clear( $group = NULL )
	{
		if( is_null( $group ) )
			$this->tickets = array();
		else
		{
			if( isset( $this->ticket_groups[ $group ] ) )
				foreach( $this->ticket_groups[ $group ] as $tid )
					unset( $this->tickets[ $tid ] );

			unset( $this->ticket_groups[ $group ] );
		}
	}

	/*
	 * Find ticket.
	 */
	function findTicket( $tid )
	{
		$request = $this->getTicket( $tid );

		return $request == NULL ? array( '__static__' => true ) : $request;
	}
}

/*
 * Return common ticket service object.
 */
function &getTicketService()
{
	global $_BOOBA_TICKET;

	if( !is_object( $_BOOBA_TICKET ) )
		$_BOOBA_TICKET = new TicketService();
	
	return $_BOOBA_TICKET;
}

?>
