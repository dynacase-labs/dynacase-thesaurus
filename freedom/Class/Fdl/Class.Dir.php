<?php
// ---------------------------------------------------------------
// $Id: Class.Dir.php,v 1.3 2002/02/22 15:34:54 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.Dir.php,v $
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
$CLASS_DIR_PHP = '$Id: Class.Dir.php,v 1.3 2002/02/22 15:34:54 eric Exp $';


include_once("FDL/Class.Doc.php");

include_once("FDL/Class.QueryDir.php");
include_once("FDL/Lib.Dir.php");



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
  var $defClassname='Dir';

  function Dir($dbaccess='', $id='',$res='',$dbid=0) {
    DbObjCtrl::DbObjCtrl($dbaccess, $id, $res, $dbid);
    if ($this->fromid == "") $this->fromid= FAM_DIR;
  }


  // get the home directory
  function GetHome() {
    
    $query = new QueryDb($this->dbaccess, get_class($this));
    $query->AddQuery("owner = -". $this->userid);
    
    $rq = $query->Query();
    if ($query->nb > 0)      $home = $rq[0];
    else {
      $home = new Dir($this->dbaccess);
      $home ->owner = -$this->userid;
      include_once("Class.User.php");
      $user = new User("", $this->userid);
      $home ->title = $user->firstname." ".$user->lastname;
      $home -> Add();    
    }
    return $home;
  }
    

  // add a file in this folder
  function AddFile($docid, $mode="latest") {
    

  $qf = new QueryDir($this->dbaccess);

  switch ($mode) {
  case "static":

    $qf->qtype='F'; // fixed document
    $qf->childid=$docid; // initial doc
  break;
  case "latest":
  default:
    $doc= new Doc($this->dbaccess, $docid);
    $qf->qtype='S'; // single user query
    $qf->childid=$doc->initid; // initial doc
    
  break;
  }  


  $qf->dirid=$this->initid; // the reference directory is the initial id
  $qf->query="";
  $err = $qf->Add();
  return $err;
  }


  // delete reference to a  file in this folder
  function DelFile($docid ) {
    
    $err="";


   
    $qids = getQids($this->dbaccess,$this->initid, $docid);

    if (count($qids) == 0) $err = sprintf(_("cannot delete link : link not found for doc %d in directory %d"),$docid, $this->initid);
    if ($err != "") return $err;

    // search original query
    $qf = new QueryDir($this->dbaccess, $qids[0]);
    if (!($qf->isAffected())) $err = sprintf(_("cannot delete link : initial query not found for doc %d in directory %d"),$docid, $this->initid);
  
    if ($err != "") return $err;

    if ($qf->qtype == "M") $err = sprintf(_("cannot delete link for doc %d in directory %d : the document comes from a user query. Delete initial query if you want delete this document"),$docid, $this->initid);
  
    if ($err != "") return $err;
        $qf->Delete();

  

  
    return $err;
  }

}

?>