<?php
/**
 * Product Workflow
 *
 * @author Anakeen 2002
 * @version \$Id: Class.WProduct.php,v 1.2 2003/11/04 14:20:36 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage INCIDENT
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Class.WProduct.php,v 1.2 2003/11/04 14:20:36 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Incident/Attic/Class.WProduct.php,v $
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





define ("virtual",  "virtual");   # N_("virtual")
define ("consumed", "consumed");  # N_("consumed")
define ("plocked",   "plocked");    # N_("plocked")
define ("asav",     "asav");      # N_("asav")
define ("aclient",  "aclient");   # N_("aclient")
define ("returned", "returned");  # N_("returned")
define ("stocked",  "stocked");   # N_("stocked")
define ("out",       "out");      # N_("out")



define ("Tvirtual",  "Tvirtual");   # N_("Tvirtual")
define ("Tconsumed", "Tconsumed");  # N_("Tconsumed")
define ("Tlocked",   "Tlocked");    # N_("Tlocked")
define ("Tasav",     "Tasav");      # N_("Tasav")
define ("Taclient",  "Taclient");   # N_("Taclient")
define ("Treturned", "Treturned");  # N_("Treturned")
define ("Tstocked",  "Tstocked");   # N_("Tstocked")
define ("Tout",      "Tout");       # N_("Tout")


/**
 * Product Workflow
 *
 */
Class WProduct extends WDoc {
  
  

  // ------------
  var $attrPrefix="PWF"; // prefix attribute
  var $firstState="virtual";

  var $transitions = array(
			   "Tvirtual"  =>array(""),
			   "Tconsumed" =>array(""),
			   "Tlocked"   =>array(""),
			   "Tasav"     =>array(""),
			   "Taclient"  =>array(""),
			   "Treturned" =>array(""),
			   "Tstocked"  =>array(""),
			   "Tout"      =>array(""));
  
    var $cycle = array(
			  array("e1"=>virtual,
				"e2"=>consumed, 
				"t"=>Tconsumed),	

			  array("e1"=>virtual,
				"e2"=>plocked, 
				"t"=>Tlocked),	

			  array("e1"=>virtual,
				"e2"=>asav, 
				"t"=>Tasav),	

			  array("e1"=>virtual,
				"e2"=>aclient, 
				"t"=>Taclient),	

			  array("e1"=>virtual,
				"e2"=>returned, 
				"t"=>Treturned),	

			  array("e1"=>virtual,
				"e2"=>stocked, 
				"t"=>Tstocked),	

			  array("e1"=>aclient,
				"e2"=>stocked, 
				"t"=>Tstocked),	

			  array("e1"=>aclient,
				"e2"=>returned, 
				"t"=>Treturned),	

			  array("e1"=>asav,
				"e2"=>stocked, 
				"t"=>Tstocked),	

			  array("e1"=>asav,
				"e2"=>aclient, 
				"t"=>Taclient),	

			  array("e1"=>asav,
				"e2"=>out, 
				"t"=>Tout),	

			  array("e1"=>asav,
				"e2"=>returned, 
				"t"=>Treturned),	

			  array("e1"=>returned,
				"e2"=>aclient, 
				"t"=>Taclient),			

			  array("e1"=>returned,
				"e2"=>out, 
				"t"=>Tout),		

			  array("e1"=>returned,
				"e2"=>stocked, 
				"t"=>Tstocked),		

			  array("e1"=>plocked,
				"e2"=>out, 
				"t"=>Tout),		

			  array("e1"=>plocked,
				"e2"=>stocked, 
				"t"=>Tstocked),	

			  array("e1"=>stocked,
				"e2"=>consumed, 
				"t"=>Tconsumed),	

			  array("e1"=>stocked,
				"e2"=>plocked, 
				"t"=>Tlocked),	

			  array("e1"=>stocked,
				"e2"=>asav, 
				"t"=>Tasav),	

			  array("e1"=>stocked,
				"e2"=>aclient, 
				"t"=>Taclient),	

			  array("e1"=>stocked,
				"e2"=>returned, 
				"t"=>Treturned)
		  

			 );
				    


 
	

  




 
}

?>
