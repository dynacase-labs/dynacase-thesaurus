<?php
// ---------------------------------------------------------------
// $Id: Class.Doc.php,v 1.82 2003/01/08 09:07:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.Doc.php,v $
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


$CLASS_DOC_PHP = '$Id: Class.Doc.php,v 1.82 2003/01/08 09:07:04 eric Exp $';

include_once("Class.QueryDb.php");
include_once("FDL/Class.DocCtrl.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Class.DocValue.php");
include_once("FDL/Class.DocAttr.php");
include_once('FDL/Class.ADoc.php');
include_once("VAULT/Class.VaultFile.php");


// define constant for search attributes in concordance with the file "init.freedom"



define ("FAM_BASE", 1);
define ("FAM_DIR", 2);
define ("FAM_ACCESSDOC", 3);
define ("FAM_ACCESSDIR", 4);
define ("FAM_SEARCH", 5);
define ("FAM_ACCESSSEARCH", 6);

Class Doc extends DocCtrl {

  var $fields = array ( "id",
			"owner",
			"title",
			"revision",
			"initid",
			"fromid",
			"doctype",
			"locked",
			"icon",
			"lmodify",
			"profid",
			"usefor",
			"revdate",
			"comment",
			"classname",
			"state",
			"wid",
			"values",
			"attrids");

  var $id_fields = array ("id");

  var $dbtable = "doc";

  var $order_by="title, revision desc";

  var $fulltextfields = array ("title");


  var $defProfFamId=FAM_ACCESSDOC;
  var $sqlcreate = "
create table doc ( id int not null,
                   primary key (id),
                   owner int,
                   title varchar(256),
                   revision float4 DEFAULT 0,
                   initid int,
                   fromid int,
                   doctype varchar(1) DEFAULT 'F',
                   locked int DEFAULT 0,
                   icon varchar(256),
                   lmodify varchar(1) DEFAULT 'N',
                   profid int DEFAULT 0,
                   usefor varchar(1)  DEFAULT 'N',
                   revdate int,  
                   comment text,
                   classname varchar(64),
                   state varchar(64),
                   wid int DEFAULT 0,  
                   values text,  
                   attrids text
                   );
create sequence seq_id_doc start 1000;
create unique index i_docir on doc(initid, revision);";

  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  var $obj_acl = array (); // set by childs classes

  // --------------------------------------------------------------------

  var $defaultview= "FDL:VIEWBODYCARD";
  var $defaultedit = "FDL:EDITBODYCARD";
  var $defaultabstract = "FDL:VIEWABSTRACTCARD";
 

  // --------------------------------------------------------------------

 

  var $defDoctype='F';

  var $hasChanged=false; // to indicate values modification
  var $isCacheble= false;

  var $paramRefresh=array();

  function Doc($dbaccess='', $id='',$res='',$dbid=0) {

    newDoc($this,$dbaccess, $id, $res, $dbid);
	   
  }



  function PostInit() {

    include_once("FDL/Class.QueryDir.php");
    $oqdv = new QueryDir($this->dbaccess,"2"); // just to create table if needed

    $sqlquery=$this->SqlTrigger();
    $msg=$this->exec_query($sqlquery,1);
    

    return $msg;
    

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
	$res = pg_exec($this->init_dbid(), "select nextval ('seq_id_doc')");
	$arr = pg_fetch_array ($res, 0);
	$this->id = $arr[0];
      }
      

      // set default values

      if ($this->initid == "") $this->initid=$this->id;
      if (chop($this->title) == "") $this->title =_("untitle document");
      if ($this->doctype == "")  $this->doctype = $this->defDoctype;
      if ($this->revision == "") $this->revision = "0";

      if ($this->profid == "") $this->profid = "0";
      if ($this->usefor == "") $this->usefor = "N";

      if ($this->lmodify == "") $this->lmodify = "N";
      if ($this->locked == "") $this->locked = "0";
      if ($this->owner == "") $this->owner = $this->userid;
      //      if ($this->classname == "") $this->classname= $this->defClassname; //get_class($this);// dont use this because lost of uppercase letters
      //      if ($this->state == "") $this->state=$this->firstState;
      // set creation date
      $date = gettimeofday();
      $this->revdate = $date['sec'];

      if ($this->wid > 0) {
	$wdoc = new Doc($this->dbaccess,$this->wid);
	$wdoc->Set($this); // set first state
      }
      return $err;
    } 

  


  // --------------------------------------------------------------------
  function PreUpdate()
    // --------------------------------------------------------------------
    {
      if (! isset($this->withoutControl)) {
	$err = $this-> Control("edit");
	if ($err != "") return ($err); 
      }
      
      if ($this->locked == -1) $this->lmodify='N';
      $this->RefreshTitle();
      if ($this->hasChanged) {
	if (chop($this->title) == "") $this->title =_("untitle document");
	// set modification date
	$date = gettimeofday();
	$this->revdate = $date['sec'];
      }

    }


  // modify without edit control
  function disableEditControl() {
    $this->withoutControl=true;
  }
  // default edit control enable
  function enableEditControl() {
    unset($this->withoutControl);
  }

  function isRevisable() {
    return (($this->doctype == 'F') && ($this->usefor != 'P'));
  }
 

  // convert to another family
  function convert($fromid) {
    
    $cdoc = createDoc($this->dbaccess, $fromid);
    
    if (! $cdoc) return false;
    
    $cdoc->id = $this->id;
    $values = $this->getValues();
    $this->delete(true); // delete before add to avoid double id (it is not authorized)

    $err=$cdoc->Add();
    reset($values);
    while(list($k,$v) = each($values)) {
      $cdoc->setValue($k,$v);
    }

    $err=$cdoc->Modify();
    
    return $cdoc;
    
  }

  // --------------------------------------------------------------------
  // test if the document can be revised now
  // ie must be locked by the current user
  function CanUpdateDoc() {
    // --------------------------------------------------------------------
    if ($this->userid == 1) return "";// admin can do anything
    $err="";

    if ($this->locked == -1) {
      $err = sprintf(_("cannot update file %s (rev %d) : fixed. Get the latest version"), $this->title,$this->revision);      
    }

  
    if ($this->locked == 0) {     
	$err = sprintf(_("the file %s (rev %d) must be locked before"), $this->title,$this->revision);      
    } else {
	if (abs($this->locked) != $this->userid) {
	  
	    $user = new User("", $this->locked);
	    $err = sprintf(_("you are not allowed to update the file %s (rev %d) is locked by %s."), $this->title,$this->revision,$user->firstname." ".$user->lastname); 
	  
	} else $err = $this-> Control( "edit");
    }
    
    return($err);
  }
  // --------------------------------------------------------------------
  // test if the document can be locked
  // ie not locked before, and latest revision (the old revision are locked
  function CanLockFile() {
    // --------------------------------------------------------------------
    if ($this->userid == 1) return ""; // admin can do anything
    $err="";

    
    if ($this->locked == -1) {
      
      $err = sprintf(_("cannot lock file %s (rev %d) : fixed. Get the latest version"), 
		     $this->title,$this->revision);
    }  else {
      if ($this->locked == 0) $err = $this-> Control( "edit");
      // test if is not already locked
      else {
	if ( abs($this->locked) != $this->userid) {
	  $user = new User("", $this->locked);
	  $err = sprintf(_("cannot lock file %s (rev %d): already locked by %s."), 
			 $this->title,$this->revision,$user->firstname." ".$user->lastname);
	}   else  {      
	  $err = $this-> Control( "edit");
	}
      }
    }
    

    return($err);  
  } 

  // --------------------------------------------------------------------
  // test if the document can be unlocked
  // ie like UpdateDoc
  function CanUnLockFile() {
    // --------------------------------------------------------------------
    if ($this->userid == 1) return "";// admin can do anything
    $err="";
    if ($this->locked != 0) // if is already unlocked
      $err=$this->CanUpdateDoc();
    else {      
      $err = $this-> Control( "edit");
    }
    return($err);
  
  }

  function isLocked() {
    return (($this->locked > 0) || ($this->locked < -1));
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
	      $v->refreshTitle();
	      $v->Modify();
	    }	  
	}      
    }


  // --------------------------------------------------------------------
  function GetDocWithSameTitle() {
    // --------------------------------------------------------------------
    $query = new QueryDb($this->dbaccess,"Doc".$this->fromid);
    $query->AddQuery("title='".addslashes($this->title)."'");
    $query->AddQuery("locked != -1");  // latest revision
    $query->AddQuery("fromid='".$this->fromid."'"); // same familly
    $query->AddQuery("id !='".$this->id."'");

    $table1 = $query->Query();


    if ($query->nb > 0) {
      return $table1;
    }
    return array();;

    
  }
  // --------------------------------------------------------------------
  function DeleteTemporary() {
    // --------------------------------------------------------------------

    $result = pg_exec($this->init_dbid(),"delete from doc where doctype='T'");

    
  }
  
  // --------------------------------------------------------------------
  function PreDelete()    
    // --------------------------------------------------------------------
    {
      
      if ($this->doctype == 'Z') return _("already deleted");
      $err = $this-> Control( "delete");
      
                  

      return $err;
      
    }


  function Delete($really=false) {

    if ($really) {
      DbObj::delete();
    } else {
    $msg=$this->PreDelete();
    if ($msg!='') return $msg;

    $this->doctype='Z'; // Zombie Doc
    $this->locked= -1; 
    $date = gettimeofday();
    $this->revdate = $date['sec']; // Delete date

    global $action;
    global $HTTP_SERVER_VARS;
    $this->AddComment(sprintf(_("delete by %s by action %s on %s from %s"),
			      $action->user->firstname." ".$action->user->lastname,
			      $HTTP_SERVER_VARS["REQUEST_URI"],
			      $HTTP_SERVER_VARS["HTTP_HOST"],
			      $HTTP_SERVER_VARS["REMOTE_ADDR"]));


    $msg=$this->PostDelete();
    }
  }

  // --------------------------------------------------------------------
  // Adaptation of affect Method from DbObj because of inheritance table
  // this function is call from QueryDb and all fields can not be instanciate
  function Affect($array) {
    // --------------------------------------------------------------------
  
    reset($array);
    $this->ofields = $this->fields;
    $this->fields=array();
    while(list($k,$v) = each($array)) {
      if (!is_integer($k)) {
	$this->fields[]=$k;
	$this->$k = $v;
      }
    }
    $this->Complete();
    $this->isset = true;
  }

  // --------------------------------------------------------------------
  function Description() {
    // -------------------------------------------------------------------- 
    
    return $this->title." - ".$this->revision;
  }


  // --------------------------------------------------------------------
  function GetProfileDoc()
    // --------------------------------------------------------------------
    {
      include_once("FDLGEN/Class.Doc{$this->defProfFamId}.php");
      $query = new QueryDb($this->dbaccess, "Doc".$this->defProfFamId);
      

      $query->Query();

      
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
  function GetFromDoc() {
    // -------------------------------------------------------------------- 
    // Return array of fathers doc id : class document 

    return $this->attributes->fromids;
  }

  // --------------------------------------------------------------------
  function GetChildFam() {
    // -------------------------------------------------------------------- 
    // Return array of child doc id : class document 
    if (! isset($this->childs)) {

      $this->childs=array();
      $query = new QueryDb($this->dbaccess, "DocFam");
      $query->AddQuery("fromid = ".$this->id);
      $table1 = $query->Query();

      if ($table1) {
	while (list($k,$v) = each($table1)) {
	  $this->childs[]=$v->id;

	  $this->childs=array_merge($this->childs, $v->GetChildFam());
	  
	}
      }
    }
    return $this->childs;
  }

  // --------------------------------------------------------------------
  function GetRevisions($type="LIST") {
    // -------------------------------------------------------------------- 
    // Return the document revision 
    $query = new QueryDb($this->dbaccess, get_class($this));

      
    //$query->AddQuery("revision <= ".$this->revision);
    $query->AddQuery("initid = ".$this->initid);
    $query->order_by="revision DESC";
      
    return $query->Query(0,0,$type);
  }

  // return the string label text for a id
  function GetLabel($idAttr)
    {

      if (isset($this->attributes->attr[$idAttr])) return $this->attributes->attr[$idAttr]->labelText;
      return _("unknow attribute");

    }

  
  // return the attribute object for a id
  // the attribute can be defined in fathers
  function GetAttribute($idAttr)
    {      
      $idAttr = strtolower($idAttr);
      if (isset($this->attributes->attr[$idAttr])) return $this->attributes->attr[$idAttr];
     

      return false;

    }

  // return all the attributes object 
  // the attribute can be defined in fathers

  function GetAttributes() 
    {     
      reset($this->attributes->attr);
      return $this->attributes->attr;
    }

  // return all the attributes object for abstract
  // the attribute can be defined in fathers
  function GetNormalAttributes()
    {      
      $tsa=array();
     
      if (isset($this->attributes->attr)) {
	reset($this->attributes->attr);
	while (list($k,$v) = each($this->attributes->attr)) {
	  if (get_class($v) == "normalattribute")  $tsa[$v->id]=$v;
	}
      }
      return $tsa;      
    } 

  function GetFieldAttributes()
    {      
      $tsa=array();
     
      
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if (get_class($v) == "fieldsetattribute")  $tsa[$v->id]=$v;
      }
      return $tsa;      
    }
  // return all the attributes object for abstract
  // the attribute can be defined in fathers
  function GetAbstractAttributes()
    {      
      $tsa=array();

      if (isset($this->attributes->attr)) {
	reset($this->attributes->attr);
	while (list($k,$v) = each($this->attributes->attr)) {
	  if ((get_class($v) == "normalattribute")&&($v->isInAbstract)) $tsa[$v->id]=$v;
	}
      }
      return $tsa;      
    }

  

  // return all the attributes object for title
  // the attribute can be defined in fathers
  function GetTitleAttributes()
    { 
      $tsa=array();
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if ((get_class($v) == "normalattribute") && ($v->isInTitle)) $tsa[$v->id]=$v;      
      }
      return $tsa;
    }

  // return all the attributes object for abstract
  // the attribute can be defined in fathers
  function GetFileAttributes()
    {      
      $tsa=array();
      
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if ((get_class($v) == "normalattribute") && (($v->type == "image") || 
						     ($v->type == "file"))) $tsa[$v->id]=$v;
      }
      return $tsa;      
    }
  // return all the attributes object for popup menu
  // the attribute can be defined in fathers
  function GetMenuAttributes()
    {      
      $tsa=array();
      
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if ((get_class($v) == "menuattribute")) $tsa[$v->id]=$v;
      }
      return $tsa;
    }

  // return all the necessary attributes 
  // the attribute can be defined in fathers
  function GetNeededAttributes()
    {            
      $tsa=array();
      
      if ($this->usefor != 'D') { // not applicable for default document
	reset($this->attributes->attr);
	while (list($k,$v) = each($this->attributes->attr)) {
	  if ((get_class($v) == "normalattribute") && ($v->needed)) $tsa[$v->id]=$v;      
	}
      }
      return $tsa;
    }


  // like normal attribut without files
  function GetExportAttributes()
    {      
      $tsa=array();
     
      if (isset($this->attributes->attr)) {
	reset($this->attributes->attr);
	while (list($k,$v) = each($this->attributes->attr)) {
	  
	  if (get_class($v) == "normalattribute")  {
	    if (($v->type != "image") &&($v->type != "file"))  $tsa[$v->id]=$v;
	  }
	}
      }
      return $tsa;      
    } 

  // return all the attributes object for import
  // the attribute can be defined in fathers
  function GetImportAttributes()
    {      

      $tsa=array();
      
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if ((get_class($v) == "normalattribute") && ($v->visibility == "W") 	    
	    && (($v->type != "image") &&($v->type != "file")) ) {
	  
	  
	  if (ereg("\(([^\)]+)\):(.+)", $v->phpfunc, $reg)) {
	  
	    $aout = explode(",",$reg[2]);
	    while (list($ka,$va) = each($aout)) {
	      $ra = $this->GetAttribute($va);
	      if ($ra) $tsa[strtolower($va)]=$ra;
	    }
	
      
	  }
	  $tsa[$v->id]=$v;
	}
      }


      uasort($tsa,"tordered"); 
      return $tsa;      
    }



  // recompute the title from attribute values
  function RefreshTitle() {

    if ($this->doctype == 'C') return; // no refresh for family  document
    if ($this->usefor == 'D') return; // no refresh for default document

    $ltitle = $this->GetTitleAttributes();

    $title1 = "";
    while(list($k,$v) = each($ltitle)) {
      if ($this->GetValue($v->id) != "") {
	$title1.= $this->GetValue($v->id)." ";
      }
    }
    if (chop($title1) != "")  $this->title = chop($title1);

  }
 
  // no in postUpdate method :: call this only if real change (values)
  function PostModify() {
    // to be defined in child class
    return "";
  }

  // optimize for speed 
  function PostUpdate() {
    global $gdocs;// optimize for speed :: reference is not a pointer !!
    $gdocs[$this->id]=&$this;
    
  }

  // recompute the title from attribute values
  function SetTitle($title) {
    $ltitle = $this->GetTitleAttributes();
    reset($ltitle);
    $otitle = current($ltitle);
    $idt=$otitle->id;

    $this->title=$title;
    $this->setvalue($idt,$title);


  }

 
  // return all the values
  function GetValues()  {
    $this->lvalues=array();
    if (isset($this->id) && ($this->id>0)) {

      $nattr = $this->GetNormalAttributes();
      reset($nattr);

      while (list($k,$v) = each($nattr)) {

	$this->lvalues[$v->id] = $this->GetValue($v->id);
      }
    }
      
      
    reset($this->lvalues);
    return $this->lvalues;
  }

  // return the value of an attribute object 
  function GetValue($idAttr, $def="")  {      
    
    $lidAttr=strtolower($idAttr);
    if (isset($this->$lidAttr) && ($this->$lidAttr != "")) return $this->$lidAttr;

      

    return $def;

  }
  

  function SetValue($attrid, $value) {
    // control edit before set values
	  
    if (! isset($this->withoutControl)) {
      $err = $this-> Control("edit");
      if ($err != "") return ($err); 
    }
      

    if (($value != ""))  {
      // change only if different
      $attrid = strtolower($attrid);
      $value=trim($value);// suppress white spaces end & begin

      if ($value == " ") $value=""; // erase value
      if (!isset($this->$attrid)) $this->$attrid="";

      if  ($this->$attrid != $value) 	  {
	
	  $this->hasChanged=true;
	  //   print "change $attrid  to <PRE>[{$this->$attrid}] [$value]</PRE><BR>";
	
      }
      $this->$attrid=($value); 
	
    }      
  }

  
  function DeleteValue($attrid) {
    return $this->SetValue($attrid," ");
  }


  // add values present in values field
  function GetMoreValues()  {      
    if (isset($this->values)) {
      $tvalues = explode("£",$this->values);
      $tattrids = explode("£",$this->attrids);
      
      while(list($k,$v) = each($tvalues)) {
	$attrid = $tattrids[$k];
	if (! isset($tattrids[$k])) {
	  print_r2($tattrids);
	  print_r2($tvalues);
	}
	if ($attrid != "") 	$this->$attrid=$v;
      }
    }      
  }

  // reset values present in values field
  function ResetMoreValues()  {      
    if (isset($this->values)) {
      $tattrids = explode("£",$this->attrids);
      
      while(list($k,$v) = each($tattrids)) {
	$attrid = $tattrids[$k];
	$this->$attrid="";
      }
    }      
  }

  // return the first attribute of type 'file'
  function GetFirstFileAttributes()
    {
      $t =  $this->GetFileAttributes();
      if (count($t) > 0) return current($t);
      return false;      
    }

  function AddComment($comment='') {
    if ($this->comment != '') $this->comment .= "\n".$comment;
    else $this->comment = $comment;
    $this->modify();
  }
  function AddRevision($comment='') {


    $this->locked = -1; // the file is archived
    $this->lmodify = 'N'; // not locally modified
    $this->owner = $this->userid; // rev user
    if ($comment != '') $this->comment .= "\n".$comment;

    $this->modify();
    //$listvalue = $this->GetValues(); // save copy of values

    // duplicate values
    $olddocid = $this->id;
    $this->id="";
    $this->locked = "0"; // the file is unlocked
    $this->comment = ""; // change comment
    $this->revision = $this->revision+1;

    $this->Add();

    
    

    return $this->id;
    
  }

  function lock($auto=false) {
    
    $err=$this->CanLockFile();
    if ($err != "") return $err;
      
    // test if is not already locked
    if ($auto) {
      if (($this->userid != 1) && ($this->locked == 0)) {
	$this->locked = -$this->userid;     
	$err=$this->modify();
      }
    } else { 
      if ($this->locked != $this->userid) {
	$this->locked = $this->userid;     
	$err=$this->modify();
      }
    }
    
    return $err;
  }
  function unlock($auto=false) {
    

    $err=$this->CanLockFile();
    if ($err != "") return $err;
      
    if ($auto) {
      if ($this->locked < -1) {
	$this->locked = "0";      
	$this->modify();
      }
    } else {
      if ($this->locked != 0) {
	$this->locked = "0";      
	$this->modify();
      }
    }
    
    return "";
  }

  // return icon file
  function getIcon() {

    global $action;
      
    if ($this->icon != "") {
    
      ereg ("(.*)\|(.*)", $this->icon, $reg); 
    
      $efile="FDL/geticon.php?vaultid=".$reg[2]."&mimetype=".$reg[1];
      return $efile;

    } else {
      if ($this->fromid == 0) {


	return  $action->GetImageUrl("doc.gif");

      }
      //$fdoc = new doc(newDoc($this->dbaccess, $this->fromid);
    
      return  $action->GetImageUrl("doc.gif");
      // don't recursivity to increase speed
      //    return $fdoc->geticon();
    }

  }


  // change icon for a class or a simple doc
  function changeIcon($icon) {

    if ($this->doctype == "C") { //  a class
      $query = new QueryDb($this->dbaccess,"Doc");
      $tableq=$query->Query(0,0,"LIST",
			    "update doc set icon='$icon' where (fromid=".$this->initid.") AND (doctype != 'C') AND ((icon is null) OR (icon = '".$this->icon."'))");
    


    } 
    //    $this->title = AddSlashes($this->title);
    $this->icon = $icon;
    $this->Modify();
  }

  function AddParamRefresh($in,$out) {
    // to know which attribut must be disabled in edit mode
    $this->paramRefresh[]=array("in"=>explode(",",strtolower($in)),
				"out"=>explode(",",strtolower($out)));
  }
  function SpecRefresh() {
    // Special Refresh
    // to define in child classes
  }
  // recompute all calculated attribut
  function Refresh() {	

    if ($this->locked == -1) return; // no refresh revised document
    if (($this->doctype != 'F')  && ($this->doctype != 'S')) return; // no refresh for family  document
    if ($this->usefor == 'D') return; // no refresh for default document
	  

    
    $err=$this->SpecRefresh();
	
    if ($this->hasChanged)    $this->modify(); // refresh title
    return $err;
	
  }
  
  
  function urlWhatEncode( $link) {
    // -----------------------------------
    global $action;
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $urllink="";
    for ($i=0; $i < strlen($link); $i++) {
      switch ($link[$i]) {
      
      case '%' :
	$i++;
	if ($link[$i+1] == "%") { 
	  // special link
	    
	  switch ($link[$i]) {
	  case "B": // baseurl	  
	    $urllink.=$action->GetParam("CORE_BASEURL");
	      
	    break;
	  case "S": // standurl	  
	    $urllink.=$action->GetParam("CORE_STANDURL");
	      
	    break;
	  case "I": // id	  
	    $urllink.=$this->id;
	      
	    break;
	  default:
	      
	    break;
	  }
	  $i++; // skip end '%'
	} else {
	  
	  $sattrid="";
	  while ($link[$i] != "%" ) {
	    $sattrid.= $link[$i];
	    $i++;
	  }
	  //	  print "attr=$sattrid";
	  
	  $ovalue = $this->GetValue($sattrid);
	  if ($ovalue == "") return false;
	  $urllink.=$ovalue;
	  
	  
	}
	break;

      case '{' :
	$i++;

	  
	$sattrid="";
	  while ($link[$i] != '}' ) {
	    $sattrid.= $link[$i];
	    $i++;
	  }
	  //	  print "attr=$sattrid";
	  
	  $ovalue = $action->GetParam($sattrid);
	  $urllink.=$ovalue;
	  
	  
	
	break;

      default:
	$urllink.=$link[$i];
      }
    }
    return ($urllink);
    
  }
  
  
  
  
  function GetHtmlValue($oattr, $value, $target="_self",$htmllink=true) {
    global $action;
    
    
    if (ereg("([a-z]+)\(\"(.*)\"\)",$oattr->type, $reg)) {
      $atype=$reg[1];
      $aformat=$reg[2];
    } else {
      $atype=$oattr->type;
      $aformat="";
    }
    
    switch ($atype)
      {
	
      case "image": 
	if ($target=="mail") $htmlval="cid:".$oattr->id;
	else
	  $htmlval=$action->GetParam("CORE_BASEURL").
	    "app=FDL"."&action=EXPORTFILE&docid=".$this->id."&attrid=".$oattr->id; // upload name
	      
	      
	break;
      case "url": 
	$htmlval="<A target=\"_blank\" href=\"". 
	  htmlentities($value)."\">".$value.
	  "</A>";
	break;
      case "mail": 
	$htmlval="<A href=\"mailto:". 
	  htmlentities($value)."\">".$value.
	  "</A>";
	break;
      case "file": 
	if (ereg ("(.*)\|(.*)", $value, $reg)) {
	  // reg[1] is mime type
	  $vf = new VaultFile($this->dbaccess, "FREEDOM");
	  if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
	  else $fname=_("vault file error");
	} else $fname=_("no filename");
	
	
	if ($target=="mail") {
	  $htmlval="<A target=\"_blank\" href=\"";
	  $htmlval.="cid:".$oattr->id
	    ."\">".$fname.
	    "</A>";;
	} else 
	  $htmlval="<A onclick=\"document.noselect=true;\" target=\"_blank\" href=\"".
	    $action->GetParam("CORE_BASEURL").
	    "app=FDL"."&action=EXPORTFILE&docid=".$this->id."&attrid=".$oattr->id
	    ."\">".$fname.
	    "</A>";
	
	break;
      case "textlist": 
      case "enumlist":
	if (strstr($value,"\n")) $oattr->link="";
	if ($aformat != "") {
	  $ta = explode("\n",$value);
	  while (list($k, $a) = each($ta)) {
	    $ta[$k]=stripslashes(sprintf($aformat,$a));
	  }
	  $htmlval=implode("<BR>",$ta);
	  
	} else {
	  $htmlval=nl2br(htmlentities(stripslashes($value)));
	}
	break;
      case "longtext": 
	$htmlval=nl2br(htmlentities(stripslashes($value)));
	break;
      case "password": 
	$htmlval=ereg_replace(".", "*", htmlentities(stripslashes($value)));
	
	break;
      default : 
	if ($aformat != "") {
	  $htmlval=(stripslashes(sprintf($aformat,$value)));
	} else {
	  $htmlval=htmlentities(stripslashes($value));
	}
	break;
	
      }
    
    
    // add link if needed
    if ($htmllink && ($oattr->link != "") && 
	($ulink = $this->urlWhatEncode( $oattr->link))) {
      $abegin="<A target=\"$target\" onclick=\"document.noselect=true;\" href=\"";
      $abegin.= $ulink;
      $abegin.="\">";
      $aend="</A>";
    } else {
      $abegin="";
      $aend="";
    }
    
    
    return $abegin.$htmlval.$aend;
  }
  
  function GetHtmlAttrValue($attrid, $target="_self",$htmllink=true) {
    return $this->GetHtmlValue($this->getAttribute($attrid),
			       $this->getValue($attrid),$target,$htmllink);
  }

  

  // --------------------------------------------------------------------
  function Control ($aclname) {
    // -------------------------------------------------------------------- 
    if (($this->IsAffected()) ) {	
      
      if (($this->profid <= 0) || ($this->userid == 1 )) return ""; // no profil or admin

      return $this->controlId($this->profid,$aclname);
    }

    return sprintf(_("cannot control : object not initialized : %s"),$aclname);
  }
  
 

  // --------------------------------------------------------------------
  // use triggers to update docvalue table
  // --------------------------------------------------------------------
  function SqlTrigger() {
    reset($this->attributes->fromids);
      
    $sql = "";

       
     
    while(list($k,$v) = each($this->attributes->fromids)) {

      $sql .="drop trigger UV{$this->fromid}_$v ON doc$this->fromid;
create trigger UV{$this->fromid}_$v BEFORE INSERT OR UPDATE ON doc$this->fromid FOR EACH ROW EXECUTE PROCEDURE upval$v();  
      ";
    }
    $sql .="drop trigger UVR{$this->fromid} ON doc$this->fromid;
     create trigger UVR{$this->fromid} BEFORE  UPDATE  ON doc$this->fromid FOR EACH ROW EXECUTE PROCEDURE resetvalues();   ";
    return $sql;
  }


  // --------------------------------------------------------------------
  // generate HTML code for view doc
  // --------------------------------------------------------------------
  function viewDoc($layout="FDL:VIEWBODYCARD",$target="_self",$ulink=true,$abstract=false) {
    global $action;
    if (! ereg("([A-Z]*):(.*)", $layout, $reg)) 
      $action->exitError(sprintf(_("error in pzone format %s"),$layout));
     
  
    $this->lay = new Layout($reg[1]."/Layout/".strtolower($reg[2]).".xml", $action);

    $method = strtolower($reg[2]);
    if (method_exists ( $this, $method)) {
      $this->$method($target,$ulink,$abstract);
    } else {
      $this->viewbodycard($target,$ulink,$abstract);
    }

    return $this->lay->gen();
  }

  // --------------------------------------------------------------------
  // construct layout for view card containt
  // --------------------------------------------------------------------
  function viewbodycard($target="_self",$ulink=true,$abstract=false) {

    $this->lay->Set("cursor",$ulink?"crosshair":"inherit");
  
    $frames= array();
  

     
    if ($abstract){
      // only 3 properties for abstract mode
      $listattr = $this->GetAbstractAttributes();
    } else {
      $listattr = $this->GetNormalAttributes();
    
    }
    

    $nattr = count($listattr); // attributes list count


    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $nbimg=0;// number of image in one frametext
    $currentFrameId="";

    $changeframe=false; // is true when need change frame
    $tableframe=array();
    $tableimage=array();
     

    $iattr=0;
    while (list($i,$attr) = each($listattr)) {
      $iattr++;

      //------------------------------
      // Compute value elements
	  
      $value = chop($this->GetValue($i));

    
      if ($value != "") // to define when change frame
	{
	  if ( $currentFrameId != $listattr[$i]->fieldSet->id) {
	    if ($currentFrameId != "") $changeframe=true;
	  }
	}
	


      //------------------------------
      // change frame if needed

      if (  // to generate  fiedlset
	  $changeframe)
	{
	  $changeframe=false;
	  if (($v+$nbimg) > 0) // one value detected
	    {
				      
	      $frames[$k]["frametext"]="[TEXT:".$this->GetLabel($currentFrameId)."]";
	      $frames[$k]["rowspan"]=$v+1; // for images cell
	      $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";

	      $this->lay->SetBlockData($frames[$k]["TABLEVALUE"],
				       $tableframe);
	      $frames[$k]["IMAGES"]="IMAGES_$k";
	      $this->lay->SetBlockData($frames[$k]["IMAGES"],
				       $tableimage);
	      unset($tableframe);
	      unset($tableimage);
	      $tableframe=array();
	      $tableimage=array();
	      $k++;
	    }
	  $v=0;
	  $nbimg=0;
	}


      //------------------------------
      // Set the table value elements
      if ($iattr <= $nattr)	{
      
	if (($value != "") && ($listattr[$i]->visibility != "H"))   {
		
	  $currentFrameId = $listattr[$i]->fieldSet->id;

	  // print values
	  switch ($listattr[$i]->type)
	    {
	      
	    case "image": 
		  
	      $tableimage[$nbimg]["imgsrc"]=$this->GetHtmlValue($listattr[$i],$value,$target,$ulink);
	      break;
		
		
	    case "file": 
		  
	      $tableframe[$v]["value"]=$this->GetHtmlValue($listattr[$i],$value,$target,$ulink);
	    
	      break;
		
	    default : 
	      $tableframe[$v]["value"]=$this->GetHtmlValue($listattr[$i],$value,$target,$ulink);
	      break;
		
	    }


	
	  // print name except image (printed otherthere)
	  if ($listattr[$i]->type != "image") {
	    $tableframe[$v]["name"]=$this->GetLabel($listattr[$i]->id);
	    $v++;
	  } else	{
	    $tableimage[$nbimg]["imgalt"]=$this->GetLabel($listattr[$i]->id);
	    $nbimg++;
	  }

	      
	}
      }
    }

    if (($v+$nbimg) > 0) // // last fieldset
      {
				      
	$frames[$k]["frametext"]=$this->GetLabel($currentFrameId);
	$frames[$k]["rowspan"]=$v+1; // for images cell
	$frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";

	$this->lay->SetBlockData($frames[$k]["TABLEVALUE"],
				 $tableframe);
	$frames[$k]["IMAGES"]="IMAGES_$k";
	$this->lay->SetBlockData($frames[$k]["IMAGES"],
				 $tableimage);
      }
    // Out



    $this->lay->SetBlockData("TABLEBODY",$frames);
  




  }
  
  // -----------------------------------
  function viewabstractcard($target="finfo",$ulink=true,$abstract="Y") {
    // -----------------------------------
    



    $listattr = $this->GetAbstractAttributes();
 
    $tableframe=array();
 
    while (list($i,$attr) = each($listattr)) {
  

      //------------------------------
      // Compute value elements
	  
      $value = chop($this->GetValue($i));

    


      
      if (($value != "") && ($listattr[$i]->visibility != "H"))   {
		
	switch ($attr->type)
	  {
	  case "image": 
		  
	    $img = "<IMG align=\"absbottom\" height=\"30px\" SRC=\"".
	      $this->GetHtmlValue($listattr[$i],$value,$target,$ulink).
	      "\">";
	    $tableframe[]=array("name"=>$attr->labelText,
				"aid"=>$attr->id,
				"value"=>$img);
	    break;
	  default : 
	    // print values
	    $tableframe[]=array("name"=>$attr->labelText,
				"aid"=>$attr->id,
				"value"=>$this->GetHtmlValue($listattr[$i],$value,$target,$ulink));
	
	    break;
	  }
	      
      
      }
    }




    $this->lay->SetBlockData("TABLEVALUE",$tableframe);
  




  }



  // -----------------------------------
  function viewattr($target="_self",$ulink=true,$abstract=false) {

 

  
    $listattr = $this->GetNormalAttributes();
    
    

    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value

    while (list($k,$v) = each($listattr)) {

   
      $value = chop($this->GetValue($v->id));

      //------------------------------
      // Set the table value elements
      
      if ($v->visibility != "H")	{	
	// don't see  non abstract if not
	if (($abstract) && (! $v->isInAbstract )) {
	  $this->lay->Set("V_".$v->id,"");
	  $this->lay->Set("L_".$v->id,"");
	} else {
	  $this->lay->Set("V_".strtoupper($v->id),$this->GetHtmlValue($v,$value,$target,$ulink));
	  $this->lay->Set("L_".strtoupper($v->id),$v->labelText);
	}
  
      }


    }

  
  
    $listattr = $this->GetFieldAttributes();
    
    

    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value

    while (list($k,$v) = each($listattr)) {
       
      $this->lay->Set("L_".strtoupper($v->id),$v->labelText);
      
  
    }

  }


  // view doc properties
  function viewprop($target="_self",$ulink=true,$abstract=false) {
    while (list($k,$v) = each($this->fields)) {

      $this->lay->Set(strtoupper($v),$this->$v);

    }  

  }

  // ---------------------------------------------------------------
  function editbodycard($dirid=0) {

    include_once("FDL/editutil.php");
    // --------------------------------
    // $classid   use when new doc or change class
    // $dirid =  directory to place doc if new doc
    // ---

 
    $docid = $this->id;        // document to edit
	  
	  
    // Set the globals elements
	    
  
  
      
  
    // ------------------------------------------------------
    //  new or modify ?
    if ($docid == 0)    {
	
      // new document


      if ($this->fromid > 0) {
	$cdoc= new Doc($this->dbaccess,$this->fromid);
	$this->lay->Set("TITLE", sprintf(_("new %s"),$cdoc->title));
     
      }
	
    }  else    {      
	
	
      // when modification 

      if (! $this->isAffected()) $action->ExitError(_("document not referenced"));
	
	
      $this->lay->Set("TITLE", $this->title);
	
    }
  
    // ------------------------------------------------------
 
  
  
  
    $this->lay->Set("id", $docid);
    $this->lay->Set("dirid", $dirid);
    $this->lay->Set("classid", $this->fromid);
  
  
  
    // ------------------------------------------------------
    // Perform SQL search for doc attributes
    // ------------------------------------------------------	        
  
  
  
 
    $frames=array();
    $listattr = $this->GetAttributes();
  
  
  
    $nattr = count($listattr); // number of attributes
    
    
    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $currentFrameId="";
    $changeframe=false;
    $ih = 0; // index for hidden values
    $thidden =array();
    $tableframe=array();

    $iattr=0;
    while (list($i,$attr) = each($listattr)) {
      if ((get_class($attr) != "normalattribute")) continue;
      $iattr++;
    
      // Compute value elements
	    
      if ($docid > 0) $value = $this->GetValue($listattr[$i]->id);
      else $value = $cdoc->GetValue($listattr[$i]->id);
	    	    

      if ( $currentFrameId != $listattr[$i]->fieldSet->id) {
	if ($currentFrameId != "") $changeframe=true;
      }
	    
      
      
      
      if ( $changeframe){  // to generate final frametext
	      
	$changeframe=false;
	if ($v > 0 ) {// one value detected	  
	      
	  $frames[$k]["frametext"]="[TEXT:".$this->GetLabel($currentFrameId)."]";
	  $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";
	  $this->lay->SetBlockData($frames[$k]["TABLEVALUE"],
				   $tableframe);
	  unset($tableframe);
	  $tableframe=array();
	  $k++;
	}
	$v=1;
      }
      
      
      //------------------------------
      // Set the table value elements
    
	      
      $currentFrameId = $listattr[$i]->fieldSet->id;
      if ( ($listattr[$i]->visibility == "H") || 
	   ($listattr[$i]->visibility == "R") && (substr_count($listattr[$i]->type,"text") > 0)) {
	// special case for hidden values
	$thidden[$ih]["hname"]= "_".$listattr[$i]->id;
	$thidden[$ih]["hid"]= $listattr[$i]->id;
	if ($value == "") $thidden[$ih]["hvalue"] = GetHttpVars($listattr[$i]->id);
	else $thidden[$ih]["hvalue"]=chop(htmlentities($value));
	  
	  
	$thidden[$ih]["inputtype"]=getHtmlInput($this,
						$listattr[$i],
						$value);
	$ih++;

      } else {
	$tableframe[$v]["value"]=chop(htmlentities($value));
	$label = $listattr[$i]->labelText;
	$tableframe[$v]["attrid"]=$listattr[$i]->id;
	$tableframe[$v]["name"]=chop("[TEXT:".$label."]");

	if ($listattr[$i]->needed ) $tableframe[$v]["labelclass"]="FREEDOMLabelNeeded";
	else $tableframe[$v]["labelclass"]="FREEDOMLabel";

	//$tableframe[$v]["name"]=$action->text($label);
	$tableframe[$v]["inputtype"]=getHtmlInput($this,
						  $listattr[$i],
						  $value);
		
		
		
		
	$v++;
		
      }
      
    }
  
    // Out
    if ($v > 0 ) {// latest fieldset
	  
	      
      $frames[$k]["frametext"]="[TEXT:".$this->GetLabel($currentFrameId)."]";
      $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";
      $this->lay->SetBlockData($frames[$k]["TABLEVALUE"],
			       $tableframe);
	    
    }
    
    $this->lay->SetBlockData("HIDDENS",$thidden);
    $this->lay->SetBlockData("TABLEBODY",$frames);
  
  

      
  
  
  }

  // -----------------------------------
  function editattr() {
    // -----------------------------------
  

    include_once("FDL/editutil.php");
    $listattr = $this->GetNormalAttributes();
    
    

    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value

    while (list($k,$v) = each($listattr)) {
      //------------------------------
      // Set the table value elements
      $value = chop($this->GetValue($v->id));
			
      $this->lay->Set("V_".strtoupper($v->id),
		      getHtmlInput($this,
				   $v, 
				   $value));
      
      $this->lay->Set("L_".strtoupper($v->id),$v->labelText);
      
    }
  
    $listattr = $this->GetFieldAttributes();

    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value

    while (list($k,$v) = each($listattr)) {
      $this->lay->Set("L_".strtoupper($v->id),$v->labelText);  
    }


    $this->setFamidInLayout();
  }


  function setFamidInLayout() {
    // add IDFAM_ attribute in layout
    global $tFamIdName;

    if (! isset($tFamIdName))  getFamIdFromName($this->dbaccess,"-");
  
    reset($tFamIdName);
    while(list($k,$v) = each($tFamIdName)) {
      $this->lay->set("IDFAM_$k", $v);
    }
  }
  function vault_filename($attrid) {

    $fileid= $this->getValue($attrid);
    $fname="";
    if (ereg ("(.*)\|(.*)", $fileid, $reg)) {	 
      // reg[1] is mime type
      $vf = new VaultFile($this->dbaccess, "FREEDOM");
      if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
    
    } 

    return $fname;
  }
}

?>
