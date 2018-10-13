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
  * An abstract base class for all *Page classes.
  */
 class AbstractPage
 {
	 /*
	  * Array with data provided by ticket.
	  */
	 var $savedRequest;

	 /*
	  * Array with data provided by user's request.
	  */
	 var $incomingRequest;

	 /*
	  * Constructor.
	  */
	 function AbstractPage( $savedRequest = array() , $incomingRequest = array() )
	 {
		 $this->savedRequest = $savedRequest;
		 $this->incomingRequest = $incomingRequest;
	 }
	 
	 /*
	  * If you want to reach this page via index.php?tid=name, where
	  * name means page class name without Page suffix, with big letters
	  * replaced by small letters and underscores between words, this
	  * function must return true. Otherwise return false from this function.
	  */
	 function acceptsStaticTickets()
	 {
		 /*
		  * Defaults no static tickets are allowed.
		  */
		 return false;
	 }
	 
	 /*
	  * Returns additional ticket data for pages reached via static ticket.
	  */
	 function &getAdditionalTicketInfo()
	 {
		 return $this->incomingRequest[ '__additional__' ];
	 }
	 
	 function render()
	 {
		 return 'AbstractPage';
	 }
 }
 
 ?>
