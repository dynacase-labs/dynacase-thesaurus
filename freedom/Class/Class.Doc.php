<?php
// ---------------------------------------------------------------
// $Id: Class.Doc.php,v 1.1 2001/11/09 09:41:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Attic/Class.Doc.php,v $
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
// $Log: Class.Doc.php,v $
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
//
// ---------------------------------------------------------------


$CLASS_CONTACT_PHP = '$Id: Class.Doc.php,v 1.1 2001/11/09 09:41:14 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');
include_once('Class.DbObjCtrl.php');
include_once("FREEDOM/freedom_util.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/Class.FileDisk.php");

  
Class Doc extends DbObjCtrl
{
  var $fields = array ( "id","owner","title","state","revision","initid","fromid","doctype","locked","icon","lmodify");

  var $id_fields = array ("id");

  var $dbtable = "doc";

  var $order_by="title, revision";

  var $fulltextfields = array ("title");

  var $sqlcreate = "
create table doc ( id      int not null,
                     primary key (id),
                     owner int,
                     title varchar(256),
                     state int,
                     revision float4,
                     initid int,
                     fromid int,
                     doctype varchar(1),
                     locked int,
                     icon varchar(256),
                     lmodify varchar(1)
                   );
create sequence seq_id_doc start 10";

  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  var $obj_acl = array (
			array(
			      "name"		=>"delete",
			      "description"	=>"delete own card", // N_("delete own card")
			      "group_default"       =>"Y"),
			array(
			      "name"               =>"fdelete",
			      "description"        =>"delete any card"),// N_("delete any card")
			array(
			      "name"               =>"modify",
			      "description"        =>"modify own card",// N_("modify own card")
			      "group_default"       =>"Y"),
			array(
			      "name"               =>"fmodify",
			      "description"        =>"modify any card"),// N_("modify any card")
			array(
			      "name"               =>"pmodify",
			      "description"        =>"modify public card",// N_("modify public card")
			      "group_default"       =>"Y")
			);

  // --------------------------------------------------------------------

  function PostInit() {
    $this->id=1;
    $this->owner=1; //admin

    $this->title=N_("basic documentation class");
    $this->state=0;
    $this->revision=0;
    $this->doctype='C'; // class type
    
    
    $this->Add();
  }

  // --------------------------------------------------------------------
  function PostInsert()
    // --------------------------------------------------------------------    
    {

      $this->Select($this->id);
      $this->SetControl();
    }
  
  // --------------------------------------------------------------------
  function PreInsert()
    // --------------------------------------------------------------------
    {
      $err="";
      if (! $this->action->HasPermission("FREEDOM")) {
	$err = _("Cannot Add : need FREEDOM privilege");
      } else {
	// compute new id
	if ($this->id == "") {
	  $res = pg_exec($this->dbid, "select nextval ('seq_id_doc')");
	  $arr = pg_fetch_array ($res, 0);
	  $this->id = $arr[0];
	  if ($this->initid == "") $this->initid=$this->id;
	}
      }
      if (chop($this->title) == "") $this->title =_("untitle document");
      return $err;
    } 

  
  // --------------------------------------------------------------------
  function CanUpdate()
    // --------------------------------------------------------------------
    {
      if (! $this->action->HasPermission("FREEDOM")) {
	$err = _("Cannot Modify : need FREEDOM privilege");
      } if ($this->action->HasPermission("ADMIN")) {
	$err = ""; // ADMIN privilege can modify all cards
      } 
      return $err;
    }

  // --------------------------------------------------------------------
  function PreUpdate()
    // --------------------------------------------------------------------
    {
      $err = $this->CanUpdate() ;
      if ($err != "") return ($err); 
      
      if (chop($this->title) == "") $this->title =_("untitle document");
      if ($this->locked <= 0) $this->lmodify='N';

    }


 
  // --------------------------------------------------------------------
  // test if the document can be revised now
  // ie must be locked by the current user
  function CanUpdateDoc() {
  // --------------------------------------------------------------------
    $err="";
    if ($this->locked == 0) {     
      $err = sprintf(_("the file %s (rev %d) must be locked before"), $this->title,$this->revision);      
    } else
    if ($this->locked != $this->action->user->id) {
      if ($this->locked > 0) {
	$user = new User("", $this->locked);
	$err = sprintf(_("you are not allowed to update the file %s (rev %d) is locked by %s."), $this->title,$this->revision,$user->firstname." ".$user->lastname); 
      } else {
	$err = sprintf(_("cannot update file %s (rev %d) : fixed. Get the latest version"), $this->title,$this->revision);
      


      }
    }
    return($err);
  }
  // --------------------------------------------------------------------
  // test if the document can be locked
  // ie not locked before, and latest revision (the old revision are locked
  function CanLockFile() {
  // --------------------------------------------------------------------
    $err="";
    if ($this->locked > 0) {
      // test if is not already locked
      if ($this->locked != $this->action->user->id) {
	$user = new User("", $this->locked);
	$err = sprintf(_("cannot lock file %s (rev %d): already locked by %s."), $this->title,$this->revision,$user->firstname." ".$user->lastname);
      } 
    } else if ($this->locked != 0) {
      
      $err = sprintf(_("cannot lock file %s (rev %d) : fixed. Get the latest version"), $this->title,$this->revision);
    } 
    return($err);
  
  } 

  // --------------------------------------------------------------------
  // test if the document can be unlocked
  // ie like UpdateDoc
  function CanUnLockFile() {
  // --------------------------------------------------------------------
    $err="";
    if ($this->locked != 0) // if is already unlocked
      $err=$this->CanUpdateDoc();
    
    return($err);
  
  }

  // ----------------------------------------------------------------------
  function GetFreedomFromTitle($title) {
    // --------------------------------------------------------------------
    $query = new QueryDb($this->dbaccess,"Doc");
      $query->basic_elem->sup_where=array ("title='".$title."'");


      $table1 = $query->Query();
    $id=0;
      if ($query->nb > 0)
	{
	  $id = $table1[0]->id;

	  unset ($table1);
	}
      return $id;
  }



  // --------------------------------------------------------------------
  function UpdateTitles()
    // --------------------------------------------------------------------
    {
      $query = new QueryDb($this->dbaccess,"Doc");

      
    
      $table1 = $query->Query();

     
      if ($query->nb > 0)
	{
	  while(list($k,$v) = each($table1)) 
	    {	     
	      $v->title=GetTitle($this->dbaccess,$table1[$k]->id);
	      //$v->Modify();
	    }
	  
	}
      
    }


  
  // --------------------------------------------------------------------
  function PreDelete()    
    // --------------------------------------------------------------------
    {
      
      if ($this->action->HasPermission("ADMIN")) {
	$err = ""; // ADMIN privilege can delete all cards
      } else  {

	if (($err = $this-> Control( "fdelete")) != "") { 
	  // second control : must be owner if not super privilege
	  if (($err = $this-> Control( "delete")) != "")
	    return $err;
	  if ($this->owner != $this->operm->id_user) {	  	  
	    return _("Cannot Delete : Not Owner");
	  } else {
	    $err="";
	  }
	}
      }
      
      

      return $err;
      
    }

  function PostDelete()    
    {

      // ------------------------------
      // delete POSGRES image files
  
      

      $images = GetImagesFiles($this->dbaccess,$this->id);

      while(list($k,$v) = each($images) )
	unlink($v);

      // ------------------------------
      // delete POSGRES values
      $bdvalue = new DocValue($this->dbaccess);
      $bdvalue -> DeleteValues ($this->id);
  
      // ------------------------------
      // delete control object
      DbObjCtrl::PostDelete();


      
    }

  // --------------------------------------------------------------------
  function Description() {
    // -------------------------------------------------------------------- 
    // This function should be replaced by the Child Class 
    return $this->title;
  }


  // --------------------------------------------------------------------
  function GetFathersDoc() {
    // -------------------------------------------------------------------- 
    // Return array of father doc id
    if (! isset($this->fathers)) {
      $this->fathers=array();
      if ($this->fromid > 0) {
	$fdoc= new Doc($this->dbaccess,$this->fromid);
	$this->fathers=array_merge($this->fromid, $fdoc->GetFathersDoc());
      }
    }
    return $this->fathers;
  }
  


  // return the string label text for a id
  function GetLabel($idAttr)
    {

      if (! isset($this->fathers)) $this->GetFathersDoc();


      $query = new QueryDb($this->dbaccess,"DocAttr");

      $sql_cond_doc = sql_cond(array_merge($this->fathers,$this->initid), "docid");
      $query->AddQuery($sql_cond_doc);
      $query->AddQuery ("id=$idAttr");
    
      $table1 = $query->Query();

     
      if ($query->nb > 0)
	{
	  return (String)$table1[0]->labeltext;
	}
      else
	{
	  return "unknow attribute";
	}
    }

  
  // return the attribute object for a id
  // the attribute can be defined in fathers
  function GetAttribute($idAttr)
    {

      if (! isset($this->fathers)) $this->GetFathersDoc();


      $query = new QueryDb($this->dbaccess,"DocAttr");

      $sql_cond_doc = sql_cond(array_merge($this->fathers,$this->initid), "docid");
      $query->AddQuery($sql_cond_doc);
      $query->AddQuery ("id=$idAttr");
    
      $table1 = $query->Query();

     
      if ($query->nb > 0)
	{
	  return $table1[0];
	}
      else
	{
	  return "unknow attribute";
	}
    }

  function AddRevision() {

    $this->locked = -1; // the file is archived
    $this->lmodify = 'N'; // not locally modified
    $this->modify();


    $olddocid = $this->id;
    $this->id="";
    $this->locked = "0"; // the file is unlocked
    $this->revision = $this->revision+1;
    $this->Add();

    // duplicate values
    $query = new QueryDb($this->dbaccess,"DocValue");
    $query->AddQuery("docid = ".$olddocid);
    
    $value = new DocValue($this->dbaccess);
    $value->docid = $this->id;
    $listvalue = $query->Query();
    
    
    while(list($k,$v) = each($listvalue)) {
      $value->attrid = $v->attrid;
      $value->value = $v->value;
      $value->Add();
      
    }

    return $this->id;
    
  }

  function lock() {
    
    $err=$this->CanLockFile();
    if ($err != "") return $err;
      
    // test if is not already locked
    if ($this->locked != $this->action->user->id) {
      $this->locked = $this->action->user->id;      
      $this->modify();
    }
    
    return "";
  }
  function unlock() {
    

      $err=$this->CanLockFile();
      if ($err != "") return $err;
      
      $this->locked = "0";      
      $this->modify();
    
    return "";
  }

  // return icon file
  function geticon() {

  if ($this->icon != "") {
    
    ereg ("(.*)\|(.*)", $this->icon, $reg); 
    

    $efile=$this->action->GetParam("CORE_BASEURL").
       "app=".$this->action->parent->name.
       "&action=EXPORTFILE&docid=".$this->id.
       "&vaultid=".$reg[2]; // upload name
    return $efile;

  } else {
    if ($this->fromid == 0)
      return  $this->action->GetImageUrl("doc.gif");

    $fdoc = new doc($this->dbaccess, $this->fromid);
    return $fdoc->geticon();
  }

  }
}
?>
