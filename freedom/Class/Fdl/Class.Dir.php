<?php
// ---------------------------------------------------------------
// $Id: Class.Dir.php,v 1.8 2002/10/31 08:09:22 eric Exp $
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
$CLASS_DIR_PHP = '$Id: Class.Dir.php,v 1.8 2002/10/31 08:09:22 eric Exp $';


include_once("FDL/Class.PDir.php");

include_once("FDL/Class.QueryDir.php");


define ("UNCLASS_FLD",10); // folder for unclassable document

Class Dir extends PDir
{
  
  var $defDoctype='D';

  function Dir($dbaccess='', $id='',$res='',$dbid=0) {
    PDir::PDir($dbaccess, $id, $res, $dbid);
    if ($this->fromid == "") $this->fromid= FAM_DIR;
  }


  // get the home folder
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

      $privlocked = new DocSearch($this->dbaccess);
      $privlocked->title=(_("locked files of ").$home ->title);
      $privlocked->Add();
      $privlocked->AddQuery("select * from doc where (doctype='F') ".
			    "and (locked=".$this->userid.") ".
			    "and (not useforprof)");
      $home -> AddFile($privlocked->id); 
    }
    return $home;
  }
    

  // add a file in this folder
  function AddFile($docid, $mode="latest") {
    
    // need this privilege
    $err = $this->Control("modify");
    if ($err!= "") return $err;


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


  $qf->dirid=$this->initid; // the reference folder is the initial id
  $qf->query="";
  $err = $qf->Add();
  if ($err == "") AddLogMsg(sprintf(_("Add %s in %s folder"), $doc->title, $this->title));
  return $err;
  }


  // delete reference to a  file in this folder
  function DelFile($docid ) {
    


    // need this privilege
    $err = $this->Control("modify");
    if ($err!= "") return $err;

   
    $qids = getQids($this->dbaccess,$this->initid, $docid);

    if (count($qids) == 0) $err = sprintf(_("cannot delete link : link not found for doc %d in folder %d"),$docid, $this->initid);
    if ($err != "") return $err;

    // search original query
    $qf = new QueryDir($this->dbaccess, $qids[0]);
    if (!($qf->isAffected())) $err = sprintf(_("cannot delete link : initial query not found for doc %d in folder %d"),$docid, $this->initid);
  
    if ($err != "") return $err;

    if ($qf->qtype == "M") $err = sprintf(_("cannot delete link for doc %d in folder %d : the document comes from a user query. Delete initial query if you want delete this document"),$docid, $this->initid);
  
    if ($err != "") return $err;
        $qf->Delete();

  

  
    return $err;
  }

}

?>