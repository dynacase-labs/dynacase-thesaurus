<?php
// ---------------------------------------------------------------
// $Id: Class.Dir.php,v 1.3 2001/11/30 15:13:39 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Attic/Class.Dir.php,v $
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
// $Log: Class.Dir.php,v $
// Revision 1.3  2001/11/30 15:13:39  eric
// modif pour Css
//
// Revision 1.2  2001/11/28 13:40:10  eric
// home directory
//
// Revision 1.1  2001/11/21 08:40:34  eric
// ajout historique
//
// Revision 1.2  2001/11/09 18:54:21  eric
// et un de plus
// ---------------------------------------------------------------
$CLASS_CONTACT_PHP = '$Id: Class.Dir.php,v 1.3 2001/11/30 15:13:39 eric Exp $';


include_once("FREEDOM/Class.Doc.php");




Class Dir extends Doc
{
    // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  var $obj_acl = array (
			array(
			      "name"		=>"view",
			      "description"	=>"view directory information", // N_("view directory")
			      "group_default"       =>"Y"),
			array(
			      "name"               =>"edit",
			      "description"        =>"edit directory information"),// N_("edit directory")
			array(
			      "name"               =>"delete",
			      "description"        =>"delete directory",// N_("delete directory")
			      "group_default"       =>"N"),
			array(
			      "name"               =>"open",
			      "description"        =>"open directory",// N_("open directory")
			      "group_default"       =>"N")
			);
  var $defDoctype='D';

  function Dir($dbaccess='', $id='',$res='',$dbid=0) {
    DbObjCtrl::DbObjCtrl($dbaccess, $id, $res, $dbid);
    if ($this->fromid == "") $this->fromid= FAM_DIR;
  }


  // get the home directory
  function GetHome() {
    
    $query = new QueryDb($this->dbaccess, get_class($this));
    $query->AddQuery("owner = -". $this->action->user->id);
    
    $rq = $query->Query();
    if ($query->nb > 0)      $home = $rq[0];
    else {
      $home = new Dir($this->dbaccess);
      $home ->owner = -$this->action->user->id;
      include_once("Class.User.php");
      $user = new User($this->dbaccess, $this->action->user->id);
      $home ->title = $user->firstname." ".$user->lastname;
      $home -> Add();    
    }
    return $home;
  }
    
}

?>