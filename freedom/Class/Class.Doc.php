<?php
// ---------------------------------------------------------------
// $Id: Class.Doc.php,v 1.19 2001/12/21 13:58:35 eric Exp $
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


$CLASS_DOC_PHP = '$Id: Class.Doc.php,v 1.19 2001/12/21 13:58:35 eric Exp $';

include_once('Class.QueryDb.php');
include_once('Class.Log.php');
include_once('Class.DbObjCtrl.php');
include_once("FREEDOM/freedom_util.php");
include_once("FREEDOM/Class.DocAttr.php");


// define constant for search attributes in concordance with the file "init.freedom"
define ("QA_TITLE", 1);
define ("QA_KEY",  20);
define ("QA_LAST", 21);
define ("QA_CASE", 22);
define ("QA_FROM", 23);


define ("FAM_BASE", 1);
define ("FAM_DIR", 2);
define ("FAM_ACCESSDOC", 3);
define ("FAM_ACCESSDIR", 4);
define ("FAM_SEARCH", 5);
define ("FAM_ACCESSSEARCH", 6);
Class Doc extends DbObjCtrl
{
  var $fields = array ( "id","owner","title","revision","initid","fromid","doctype","locked","icon","lmodify","profid","useforprof","revdate","comment","cprofid","classname","state");

  var $id_fields = array ("id");

  var $dbtable = "doc";

  var $order_by="title, revision desc";

  var $fulltextfields = array ("title");

  var $sqlcreate = "
create table doc ( id int not null,
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
                   useforprof bool,
                   revdate int,  
                   comment varchar(1024),
                   cprofid int,
                   classname varchar(64),
                   state varchar(64)
                   );
create sequence seq_id_doc start 1000";

  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  var $obj_acl = array (); // set by childs classes

  // --------------------------------------------------------------------


  // --------------------------------------------------------------------
  //----------------------  TRANSITION DEFINITION --------------------
  var $transitions = array();// set by childs classes


  var $defDoctype='F';


  function Doc($dbaccess='', $id='',$res='',$dbid=0) {

         $this= newDoc($dbaccess, $id, $res, $dbid);
  
  }



  function PostInit() {

    include_once("FREEDOM/Class.QueryDir.php");
    $oqdv = new QueryDir($this->dbaccess,"2"); // just to create table if needed
    include_once("FREEDOM/Class.QueryDirV.php");
    $oqdv = new QueryDirV($this->dbaccess,"2");// just to create table if needed

    include_once("FREEDOM/freedom_import.php");
    add_import_file($this->action, 
    $this->action->GetParam("CORE_PUBDIR")."/FREEDOM/init.freedom");

    return "";
    

  }

  function Complete()
    {
    global $lprof;
    //print "select $this->id <BR>";
      if ($this->profid < 0) {

	if (! isset($lprof[$this->id])) {
	$lprof[$this->id] =  new ObjectPermission("", 
                                       array($this->action->parent->user->id,
				             $this->id ));
	//	print "SET $this->id : controlled  <BR>";
	}
	$this->operm= $lprof[$this->id];

	//	print "$this->id : controlled <BR>";
      } else if ($this->profid > 0) {

	if (! isset($lprof[$this->profid])) {
	  $pdoc = new Doc($this->dbaccess, $this->profid);
	  if (isset($pdoc ->operm))
	    $lprof[$this->profid] = $pdoc ->operm;
	  //	print "SET $this->id : controlled by $this->profid <BR>";
	} 
	if (isset($lprof[$this->profid]))
	  $this ->operm= $lprof[$this->profid];
	//	print "$this->id : controlled by $this->profid <BR>";
      }



    }

  // --------------------------------------------------------------------
  function PostInsert()
    // --------------------------------------------------------------------    
    {
      // controlled will be set explicitly
      //$this->SetControl();

      $this->Select($this->id);
    }
  
  // --------------------------------------------------------------------
  function PreInsert()
    // --------------------------------------------------------------------
    {
      $err="";
	// compute new id
       if ($this->id == "") {
 	  $res = pg_exec($this->dbid, "select nextval ('seq_id_doc')");
 	  $arr = pg_fetch_array ($res, 0);
 	  $this->id = $arr[0];
 	}
      

      // set default values

      if ($this->initid == "") $this->initid=$this->id;
      if (chop($this->title) == "") $this->title =_("untitle document");
      if ($this->doctype == "") $this->doctype = $this->defDoctype;
      if ($this->revision == "") $this->revision = "0";
      if ($this->useforprof == "") $this->useforprof = "f";
      if ($this->profid == "") $this->profid = "0";
      if ($this->cprofid == "") $this->cprofid = "0";
      if ($this->lmodify == "") $this->lmodify = "N";
      if ($this->locked == "") $this->locked = "0";
      if ($this->owner == "") $this->owner = $this->action->user->id;
      if ($this->classname == "") $this->classname= $this->defClassname; //get_class($this);// dont use this because lost of uppercase letters
      // set creation date
      $date = gettimeofday();
      $this->revdate = $date['sec'];
      return $err;
    } 

  


  // --------------------------------------------------------------------
  function PreUpdate()
    // --------------------------------------------------------------------
    {
      $err = $this-> Control("edit");//$this->CanUpdateDoc() ;
      if ($err != "") return ($err); 
      
      if (chop($this->title) == "") $this->title =_("untitle document");
      if ($this->locked < 0) $this->lmodify='N';
      // set modification date
      $date = gettimeofday();
      $this->revdate = $date['sec'];

    }



  function isRevisable() {
    return (($this->doctype == 'F') && ($this->useforprof == 'f'));
  }
 
  // --------------------------------------------------------------------
  // test if the document can be revised now
  // ie must be locked by the current user
  function CanUpdateDoc() {
  // --------------------------------------------------------------------
    if ($this->action->parent->user->id == 1) return "";// admin can do anything
    $err="";

    if (! $this->isRevisable()) $err = $this-> Control("edit"); // only revisable can be locked
    else {
      if ($this->locked == 0) {     
	$err = sprintf(_("the file %s (rev %d) must be locked before"), $this->title,$this->revision);      
      } else {
	if ($this->locked != $this->action->user->id) {
	  if ($this->locked > 0) {
	    $user = new User("", $this->locked);
	    $err = sprintf(_("you are not allowed to update the file %s (rev %d) is locked by %s."), $this->title,$this->revision,$user->firstname." ".$user->lastname); 
	  } else {
	    $err = sprintf(_("cannot update file %s (rev %d) : fixed. Get the latest version"), $this->title,$this->revision);
      


	  } 
	} else $err = $this-> Control( "edit");
      }
    }
    return($err);
  }
  // --------------------------------------------------------------------
  // test if the document can be locked
  // ie not locked before, and latest revision (the old revision are locked
  function CanLockFile() {
  // --------------------------------------------------------------------
    if ($this->action->parent->user->id == 1) return ""; // admin can do anything
    $err="";
    if ($this->doctype != 'F') $err = _("this document cannot be locked : it is not a revisable document");  // only document 'F' can be locked
    else {
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
    }
    return($err);  
  } 

  // --------------------------------------------------------------------
  // test if the document can be unlocked
  // ie like UpdateDoc
  function CanUnLockFile() {
  // --------------------------------------------------------------------
    if ($this->action->parent->user->id == 1) return "";// admin can do anything
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
	      $v->title=GetTitleF($this->dbaccess,$table1[$k]->id);
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
  function GetClassesDoc($classid=1)
    // --------------------------------------------------------------------
    {
      $query = new QueryDb($this->dbaccess,"Doc");

      
      $query->AddQuery("doctype='C'");

      $cdoc = new Doc($this->dbaccess, $classid);
      if ($cdoc->useforprof == "t") $query->AddQuery("(useforprof)");
      else {
	$query->AddQuery("(not useforprof)");
      switch ($classid) {
      case FAM_ACCESSDOC:
      case FAM_ACCESSDIR:
      case FAM_ACCESSSEARCH:
	$query->AddQuery("(useforprof)");
      break;
      case FAM_SEARCH:
	$query->AddQuery("(id = ".FAM_SEARCH.")");
      break;
      case FAM_DIR:
	$query->AddQuery("(id = ".FAM_DIR.")");
      break;
      default:	
	$query->AddQuery("(id = 1) OR (id > 9)");
      }
      //      $query->AddQuery("initid=id");
      }
      
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
	$fdoc= new Doc($this->dbaccess,$this->fromid);
	$this->fathers=array_merge($this->fromid, $fdoc->GetFathersDoc());
      }
    }
    return $this->fathers;
  }
  

  // --------------------------------------------------------------------
  function GetRevisions() {
    // -------------------------------------------------------------------- 
    // Return the document revision 
      $query = new QueryDb($this->dbaccess, get_class($this));

      
      $query->AddQuery("revision <= ".$this->revision);
      $query->AddQuery("initid = ".$this->initid);
      $query->order_by="revision DESC";
      
      return $query->Query();
  }

  // return the string label text for a id
  function GetLabel($idAttr)
    {

      if (! isset($this->fathers)) $this->GetFathersDoc();


      $query = new QueryDb($this->dbaccess,"DocAttr");

      $sql_cond_doc = GetSqlCond(array_merge($this->fathers,$this->initid), "docid");
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

      $sql_cond_doc = GetSqlCond(array_merge($this->fathers,$this->initid), "docid");
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

  // return all the attributes object 
  // the attribute can be defined in fathers
  function GetAttributes()
    {
      $query = new QueryDb($this->dbaccess,"DocAttr");
      // initialise query with all fathers doc
      // 
      $sql_cond_doc = GetSqlCond(array_merge($this->GetFathersDoc(),$this->initid), "docid");
      $query->AddQuery($sql_cond_doc);
    
      $query->AddQuery("type != 'frame'");
      $query->order_by="ordered";
      return $query->Query();      
    }

  // return all the attributes object for abstract
  // the attribute can be defined in fathers
  function GetAbstractAttributes()
    {
      $query = new QueryDb($this->dbaccess,"DocAttr");
      // initialise query with all fathers doc
      // 
      $sql_cond_doc = GetSqlCond(array_merge($this->GetFathersDoc(),$this->initid), "docid");
      $query->AddQuery($sql_cond_doc);
    
      $query->AddQuery("type != 'frame'");
      $query->AddQuery("abstract = 'Y'");
      $query->order_by="ordered";
      return $query->Query();      
    }



  // return the first attribute of type 'file'
  function GetFirstFileAttributes()
    {
      $query = new QueryDb($this->dbaccess,"DocAttr");
      // initialise query with all fathers doc
      // 
      $sql_cond_doc = GetSqlCond(array_merge($this->GetFathersDoc(),$this->initid), "docid");
      $query->AddQuery($sql_cond_doc);
    
      $query->AddQuery("type = 'file'");
      $query->order_by="ordered";
      $rq = $query->Query();   
      if ($query->nb > 0)  return $rq[0];	    	      
      return false;      
    }

  function AddRevision($comment='') {

    $this->locked = -1; // the file is archived
    $this->lmodify = 'N'; // not locally modified
    $this->owner = $this->action->parent->user->id; // rev user
    $this->comment = $comment;
    $this->modify();


    $olddocid = $this->id;
    $this->id="";
    $this->locked = "0"; // the file is unlocked
    $this->comment = _("current revision"); // change comment
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
       "app=FREEDOM".
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

  // recompute all calculated attribut
  function Refresh() {
    $lattr = $this->GetAttributes();

    while(list($k,$v) = each($lattr)) {
      if (($v->visibility != "W") && 
	  (chop($v->phpfile) != "") && 
	  (chop($v->phpfunc) != "") ) {
	// it's a calculated attribute
	


	if (! @include("PLUGGINGS/$v->phpfile")) {
	  return(sprintf(_("the external pluggin file %s cannot be read"), $v->phpfile));
	}
	

	if (! ereg("(.*)\((.*)\)\:(.*)", $v->phpfunc, $reg))
	  return(sprintf(_("the pluggins function description '%s' is not conform"), $v->phpfunc));
	
  
	$argids = split(",",$reg[2]);  // input args
	$rargids = split(",",$reg[3]); // return args


	while (list($k, $v) = each($argids)) {
	  if ($v == "A") $arg[$k]= &$this->action;
	  else if ($v == "D") $arg[$k]= $this->dbaccess;
	  else if ($v == "T") $arg[$k]= &$this;
	  else {
	    $ovalue = new DocValue($this->dbaccess, array($this->id, $v));
	    $arg[$k]= $ovalue->value;
	  }
	}
	// activate plug	
	$res = call_user_func_array($reg[1], $arg);
	if (is_array($res)) {
	  reset($res);
	  while (list($k, $v) = each($res)) {
	    $ovalue = new DocValue($this->dbaccess, array($this->id, $rargids[$k]));
	    $ovalue->docid=$this->id;
	    $ovalue->attrid=$rargids[$k];
	    $ovalue->value=$v;
	    $ovalue->modify();
	  }
	}
      }
    }
    
  }
  
  // --------------------------------------------------------------------
  function Control ($aclname) {
    // -------------------------------------------------------------------- 
    if (($this->IsAffected()) )
      if (isset($this->operm) )
	return $this->operm->Control($this, $aclname);
      else return "";

    return "object not initialized ; $aclname";
  }
  
  
  // --------------------------------------------------------------------
  function ChangeState ($newstate) {

    if ($this->state == $newstate) return ""; // no change => no action
    // search if possible change in concordance with transition array
    $foundFrom = false;
    $foundTo = false;
    while (list($k, $trans) = each($this->transitions)) {
      if ($this->state == $trans["e1"]) {
	// from state OK
	$foundFrom = true;
	if ($newstate == $trans["e2"]) {
	  $foundTo = true;
	  $tr = $trans;
	}
	  
      }
    }

    if (! $foundFrom) return (sprintf(_("ChangeState :: the initial state '%s' is not known"), $this->state));
    if (! $foundTo) return (sprintf(_("ChangeState :: the new state '%s' is not known or is not allowed"), $newstate));

    if ($tr["m1"] != "") {
      // apply first method (condition for the change)
      
      if (! method_exists($this, $tr["m1"])) return (sprintf(_("the method '%s' is not known for the object class %s"), $tr["m1"], get_class($this)));
      
      $err = call_user_method ($tr["m1"], $this, $newstate);

      if ($err != "") return (sprintf(_("ChangeState :: the method '%s' return the following error %s"), $tr["m1"], $err));


    }

    // change the state
    $this->state = $newstate;
    $err = $this->modify();
    if ($err != "") return $err;

    $this->AddRevision(sprintf(_("change state to %s"), $newstate));

    // post action
    if ($tr["m2"] != "") {
      if (! method_exists($this, $tr["m2"])) return (sprintf(_("the method '%s' is not known for the object class %s"), $tr["m2"], get_class($this)));
      $err = call_user_method ($tr["m2"], $this, $newstate);
      if ($err != "") return (sprintf(_("ChangeState :: the state has been realized but the post method '%s' return the following error %s"), $tr["m2"], $err));
      
    }

    return ""; // its OK 
  }
    
  
  // --------------------------------------------------------------------
  function GetFollowingStates () {
    // search if following states in concordance with transition array

    $fstate = array();
    reset($this->transitions);
    while (list($k, $tr) = each($this->transitions)) {
      if ($this->state == $tr["e1"]) {
	// from state OK
	$fstate[] = $tr["e2"];
      }
    }
    return $fstate;
  }
  
}

?>
