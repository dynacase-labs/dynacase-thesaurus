<?php
// ---------------------------------------------------------------
// $Id: Class.DocCtrl.php,v 1.1 2002/11/07 16:00:00 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocCtrl.php,v $
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

$CLASS_DOCFILE_PHP = '$Id: Class.DocCtrl.php,v 1.1 2002/11/07 16:00:00 eric Exp $';



include_once("Class.DbObj.php");
include_once('Class.Application.php');
include_once("FDL/Class.DocPerm.php");

define ("POS_INIT", 0);
define ("POS_VIEW", 1);
define ("POS_EDIT", 2);
define ("POS_DEL",  3);
define ("POS_SEND", 4);
define ("POS_OPEN", 5);
define ("POS_EXEC", 5); // idem OPEN : alias
define ("POS_CONT", 6);
// 7 up 11 undefined for the moment

define ("POS_WF", 12); // begin of workflow privilege definition 
// end of privilege is 31 : (coded on 32bits)

Class DocCtrl extends DbObj
{
  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  // access privilege definition 
  var $dacls = array (
			"init" =>array("pos"		=>POS_INIT,
					"description"	=>"control initialized"), 
			      
			"view" =>array("pos"		=>POS_VIEW,  // N_("view document")
					 "description"	=>"view document"), 			      
			"send" =>array("pos"               =>POS_SEND, // N_("send document")
					 "description"        =>"send document"),
			"edit" =>array("pos"               =>POS_EDIT, // N_("edit document")
					 "description"        =>"edit document"),
			"delete"  =>array("pos"               =>POS_DEL, // N_("delete document")
					 "description"        =>"delete document"),
			"open" =>array("pos"               =>POS_OPEN, // N_("open folder or execute search")
					 "description"        =>"open folder or execute search"),

			"modify" =>array("pos"               =>POS_CONT, // N_("modify folder")
					 "description"        =>"modify folder")
			
			);

  // --------------------------------------------------------------------
  function DocCtrl ($dbaccess='', $id='',$res='',$dbid=0) {
    // --------------------------------------------------------------------

    global $action; // necessary to see information about user privilege

    if (isset($action)) {
      $this->userid=$action->parent->user->id;
    }
    DbObj::DbObj($dbaccess, $id,$res,$dbid);      
  }

  function isControlled() {
    return ($this->profid != 0);
  }

  // --------------------------------------------------------------------
  function ControlId ($docid,$aclname) {
    // --------------------------------------------------------------------     
    
    if (! isset($this->uperm)) {
      
      $perm = new DocPerm($this->dbaccess, array($docid,$this->userid));

      if ($perm -> IsAffected()) $this->uperm = $perm->uperm;
      else $this->uperm = $perm->getUperm($docid,$this->userid);
      //    print "$docid,$aclname:".$this->uperm."<BR>";
            
    }
    if (isset($this->dacls[$aclname])) {
      return (($this->uperm & (1 << ($this->dacls[$aclname]["pos"] ))) != 0)?"":sprintf(_("no privilege %s"),$aclname);
    } else {
      return sprintf(_("unknow privilege %s"),$aclname);
    }
  }
}

?>