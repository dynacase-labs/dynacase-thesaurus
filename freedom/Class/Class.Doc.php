<?php
// ---------------------------------------------------------------
// $Id: Class.Doc.php,v 1.4 2001/11/15 17:51:50 eric Exp $
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
// Revision 1.4  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.3  2001/11/14 15:31:03  eric
// optimisation & divers...
//
// Revision 1.2  2001/11/09 18:54:21  eric
// et un de plus
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
//
// ---------------------------------------------------------------


$CLASS_CONTACT_PHP = '$Id: Class.Doc.php,v 1.4 2001/11/15 17:51:50 eric Exp $';

include_once('Class.QueryDb.php');
include_once('Class.Log.php');
include_once('Class.DbObjCtrl.php');
include_once("FREEDOM/freedom_util.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/Class.FileDisk.php");




Class Doc extends DbObjCtrl
{
  var $fields = array ( "id","owner","title","revision","initid","fromid","doctype","locked","icon","lmodify","profid","useforprof");

  var $id_fields = array ("id");

  var $dbtable = "doc";

  var $order_by="title, revision";

  var $fulltextfields = array ("title");

  var $sqlcreate = "
create table doc ( id      int not null,
                     primary key (id),
                     owner int,
                     title varchar(256),
                     revision float4,
                     initid int,
                     fromid int,
                     doctype varchar(1),
                     locked int,
                     icon varchar(256),
                     lmodify varchar(1),
                     profid int,
                     useforprof bool
                   );
create sequence seq_id_doc start 10";

  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  var $obj_acl = array (
			array(
			      "name"		=>"view",
			      "description"	=>"view document", // N_("view document")
			      "group_default"       =>"Y"),
			array(
			      "name"               =>"edit",
			      "description"        =>"edit document"),// N_("edit document")
			array(
			      "name"               =>"delete",
			      "description"        =>"delete document",// N_("delete document")
			      "group_default"       =>"N")
			);

  // --------------------------------------------------------------------


  var $defDoctype='F';

  function PostInit() {
    $this->id=1;
    $this->initid=$this->id;
    $this->owner=1; //admin
    $this->title=N_("basic documentation family");
    $this->revision="0";
    $this->doctype='C'; // class type        
    $this->Add();

    $oattr=new DocAttr($this->dbaccess);
    $oattr->labeltext=_("title");
    $oattr->title = "Y";
    $oattr->abstract = "N";
    $oattr->docid = $this->initid;
    $oattr ->Add();

    // 같같같같같같같같같같같같같같같같같같같같
    $this->id=2;
    $this->initid=$this->id;
    $this->owner=1; //admin
    $this->title=N_("directory class");
    $this->revision="0";
    $this->doctype='C'; //  class type        
    $this->Add();

    $oattr=new DocAttr($this->dbaccess);
    $oattr->labeltext=_("title");
    $oattr->title = "Y";
    $oattr->abstract = "N";
    $oattr->docid = $this->initid;
    $oattr ->Add();

    // 같같같같같같같같같같같같같같같같같같같같
    $this->id=3;
    $this->fromid=1; // from basic doc
    $this->initid=$this->id;
    $this->owner=1; //admin
    $this->title=N_("profile documentation access class");
    $this->revision="0";
    $this->doctype='C'; //  class type        
    $this->Add();

    $oattr=new DocAttr($this->dbaccess);
    $oattr->labeltext=_("title");
    $oattr->title = "Y";
    $oattr->abstract = "N";
    $oattr->docid = $this->initid;
    $oattr ->Add();

    // 같같같같같같같같같같같같같같같같같같같같
    $this->id=4;
    $this->initid=$this->id;
    $this->fromid=2; // from directory
    $this->owner=1; //admin
    $this->title=N_("profile directory access class");
    $this->revision="0";
    $this->doctype='C'; //  class type        
    $this->Add();

    $oattr=new DocAttr($this->dbaccess);
    $oattr->labeltext=_("title");
    $oattr->title = "Y";
    $oattr->abstract = "N";
    $oattr->docid = $this->initid;
    $oattr ->Add();

  }

  // --------------------------------------------------------------------
  function PostInsert()
    // --------------------------------------------------------------------    
    {
      // controlled will be set explicitly
      $this->Select($this->id);
      //$this->SetControl();
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
  function PreUpdate()
    // --------------------------------------------------------------------
    {
      $err = $this-> Control("edit");//$this->CanUpdateDoc() ;
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
    } else $err = $this-> Control( "edit");
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
    } else {      
	$err = $this-> Control( "edit");
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
    else {      
	$err = $this-> Control( "edit");
    }
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
	$err = $this-> Control( "delete");
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
    
    return $this->title." - ".$this->revision;
  }

 // --------------------------------------------------------------------
  function GetClassesDoc()
    // --------------------------------------------------------------------
    {
      $query = new QueryDb($this->dbaccess,"Doc");

      
      $query->AddQuery("doctype='C'");
      $query->AddQuery("(id = 1) OR (id > 9)");
      //      $query->AddQuery("initid=id");
    
      
      return $query->Query();
    }


 // --------------------------------------------------------------------
  function GetProfileDoc()
    // --------------------------------------------------------------------
    {
      $query = new QueryDb($this->dbaccess, get_class($this));

      
      $query->AddQuery("useforprof");
      $query->AddQuery("doctype='$this->defDoctype'");
    
      
      return $query->Query();
    }
  // --------------------------------------------------------------------
  function GetFathersDoc() {
    // -------------------------------------------------------------------- 
    // Return array of father doc id : class document 
    if (! isset($this->fathers)) {
      $this->fathers=array();
      if ($this->fromid > 0) {
	$fdoc= newDoc($this->dbaccess,$this->fromid);
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
  function getIcon() {

  if ($this->icon != "") {
    
    ereg ("(.*)\|(.*)", $this->icon, $reg); 
    

    $efile=$this->action->GetParam("CORE_BASEURL").
       "app=".$this->action->parent->name.
       "&action=EXPORTFILE".
       "&vaultid=".$reg[2]; // upload name
    return $efile;

  } else {
    if ($this->fromid == 0)
      return  $this->action->GetImageUrl("doc.gif");

    $fdoc = new doc($this->dbaccess, $this->fromid);
    return $fdoc->geticon();
  }

  }


  // change icon for a class or a simple doc
  function changeIcon($icon) {

    if ($this->doctype == "C") { //  a class
      $query = new QueryDb($this->dbaccess,"Doc");
      $tableq=$query->Query(0,0,"LIST",
			    "update doc set icon='$icon' where (fromid=".$this->initid.") AND (doctype != 'C') AND ((icon is null) OR (icon = '".$this->icon."'))");
    


    } 
    $this->title = AddSlashes($this->title);
    $this->icon = $icon;
    $this->Modify();
  }

  
  // --------------------------------------------------------------------
  function Control ($aclname) {
    // -------------------------------------------------------------------- 
    if ($this->IsAffected())
      if ($this->profid != 0) 
	return $this->operm->Control($this, $aclname);
      else return "";

    return "object not initialized ; $aclname";
  }
  
}

?>
