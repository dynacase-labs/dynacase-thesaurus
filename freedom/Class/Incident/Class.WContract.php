<?php
/**
 * Product Workflow
 *
 * @author Anakeen 2002
 * @version \$Id: Class.WContract.php,v 1.1 2003/12/15 08:38:52 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage INCIDENT
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Class.WContract.php,v 1.1 2003/12/15 08:38:52 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Incident/Attic/Class.WContract.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------



include_once("FDL/Class.WDoc.php");





define ("created",      "created");     # N_("created")
define ("progressing",  "progressing"); # N_("progressing")
define ("clotured",     "clotured");    # N_("clotured")



define ("Tprogressing",  "Tprogressing"); # N_("Tprogressing")
define ("Tclotured",     "Tclotured");    # N_("Tclotured")


/**
 * Product Workflow
 *
 */
Class WContract extends WDoc {
  
  

  // ------------
  var $attrPrefix="COW"; // prefix attribute
  var $firstState="created";

  var $transitions = array(
			   "Tprogressing"  =>array(""),
			   "Tclotured" =>array(""));
  
  var $cycle = array(
			  array("e1"=>created,
				"e2"=>progressing, 
				"t"=>Tprogressing),	

			  array("e1"=>progressing,
				"e2"=>clotured, 
				"t"=>Tclotured),	

			  array("e1"=>clotured,
				"e2"=>progressing, 
				"t"=>Tprogressing)

			 );
				    

 
}

?>
