<?php
/**
 * Document Object Definition
 *
 * @author Anakeen 2002
 * @version $Id: Class.Doc.php,v 1.204 2004/06/11 16:15:29 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */



include_once("Class.QueryDb.php");
include_once("FDL/Class.DocCtrl.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Class.DocValue.php");
include_once("FDL/Class.DocAttr.php");
include_once('FDL/Class.ADoc.php');
include_once("VAULT/Class.VaultFile.php");


// define constant for search attributes in concordance with the file "init.freedom"

/**#@+
 * constant for document family identificator in concordance with the file "FDL/init.freedom"
 * 
 */
define ("FAM_BASE", 1);
define ("FAM_DIR", 2);
define ("FAM_ACCESSDOC", 3);
define ("FAM_ACCESSDIR", 4);
define ("FAM_SEARCH", 5);
define ("FAM_ACCESSSEARCH", 6);
define ("FAM_ACCESSFAM", 23);
/**#@-*/
/**
 * max cache document
 */
define ("MAXGDOCS", 20);
/**
 * Document Class
 *
 */
Class Doc extends DocCtrl
{
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
			"attrids",
			"postitid",
			"cvid",
			"name",
			"dprofid");

  /**
   * identificator of the document
   * @var int
   */
  var $id;
  /**
   * user identificator for the creator
   * @var int
   */
  var $owner;
  /**
   * the title of the document
   * @var string
   */
  var $title;
  /**
   * number of the revision. First is zero
   * @var int
   */
  var $revision;
  /**
   * identificator of the first revision document
   * @var int
   */
  var $initid;
  /**
   * identificator of the family document
   * @var int
   */
  var $fromid;
  /**
   * the type of document
   *
   * F : normal document (default)
   * C : family document
   * D : folder document
   * P : profil document
   * S : search document
   * T : temporary document
   * W : workflow document
   * Z : zombie document
   *
   * @var char
   */
  var $doctype;
  /**
   * user identificator for the locker
   * @var int
   */
  var $locked;
  /**
   * filename or vault id for the icon
   * @var string
   */
  var $icon;
  /**
   * set to 'Y' if the document has been modify until last revision
   * @var char
   */
  var $lmodify;
  /**
   * identificator of the profil document
   * @var int
   */
  var $profid;
  /**
   * to precise a special use of the document
   * @var char
   */
  var $usefor;
  /**
   * date of the last modification (the revision date for fixed docuemnt)
   * @var int
   */
  var $revdate;
  /**
   * comment for the history
   * @var string
   */
  var $comment;
  /**
   * class name in case of special family (only set in family document)
   * @var string
   */
  var $classname;
  /**
   * state of the document if it is associated with a workflow
   * @var string
   */
  var $state;
  /**
   * identificator of the workflow document
   * 
   * if 0 then no workflow
   * @var int
   */
  var $wid;
  /**
   * identificator of the control view document
   * 
   * if 0 then no special control view
   * @var int
   */
  var $cvid;
  /**
   * string identificator of the document
   * 
   * @var string
   */
  var $name;
  /**
   * identificator of the mask document
   * 
   * if 0 then no mask
   * @var int
   */
  var $mid=0;
  /**
   * identificator of dynamic profil
   * 
   * if 0 then no dynamic profil
   * @var int
   */
  var $dprofid=0;

  /**
   * identification of special views
   * 
   * @var array
   */
  var $cviews=array("FDL:VIEWBODYCARD",
		    "FDL:VIEWABSTRACTCARD",
		    "FDL:VIEWTHUMBCARD");
  var $eviews=array("FDL:EDITBODYCARD");



  var $id_fields = array ("id");

  var $dbtable = "doc";

  var $order_by="title, revision desc";

  var $fulltextfields = array ("title");

  /**
   * default family id for the profil access
   * @var int
   */
  var $defProfFamId=FAM_ACCESSDOC;
  var $sqlcreate = "
create table doc ( id int not null,
                   primary key (id),
                   owner int,
                   title varchar(256),
                   revision int DEFAULT 0,
                   initid int,
                   fromid int,
                   doctype char DEFAULT 'F',
                   locked int DEFAULT 0,
                   icon varchar(256),
                   lmodify char DEFAULT 'N',
                   profid int DEFAULT 0,
                   usefor char  DEFAULT 'N',
                   revdate int,  
                   comment text,
                   classname varchar(64),
                   state varchar(64),
                   wid int DEFAULT 0,  
                   values text,  
                   attrids text,  
                   postitid int,
                   cvid int,
                   name text,
                   dprofid int DEFAULT 0
                   );
create table docfrom ( id int not null,
                   primary key (id),
                   fromid int);
create sequence seq_id_doc start 1000;
create sequence seq_id_tdoc start 1000000000;
create unique index i_docname on doc(name);
create unique index i_docir on doc(initid, revision);";

  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  var $obj_acl = array (); // set by childs classes

  // --------------------------------------------------------------------

  /**
   * default view to view card
   * @var string
   */
  var $defaultview= "FDL:VIEWBODYCARD";
  /**
   * default view to edit card
   * @var string
   */
  var $defaultedit = "FDL:EDITBODYCARD";
  /**
   * default view for abstract card
   * @var string 
   */
  var $defaultabstract = "FDL:VIEWABSTRACTCARD";
  /**
   * for email : the same as $defaultview by default
   * @var string 
   */
  var $defaultmview = ""; 
 

  // --------------------------------------------------------------------

 

  var $defDoctype='F';

  /**
   * to indicate values modification
   * @var bool 
   * @access private
   */
  var $hasChanged=false; 

  var $isCacheble= false;

  var $paramRefresh=array();

  /**
   * optimize: compute mask in needed only
   * @var bool 
   * @access private
   */
  var $_maskApplied=false; // optimize: compute mask in needed only
 
  /** 
   * Document Constructor
   * 
   * @see DbObj::DbObj()
   * @see newDoc()
   * @return void
   */
  function Doc($dbaccess='', $id='',$res='',$dbid=0) {
    newDoc($this,$dbaccess, $id, $res, $dbid);
	   
    if (! isset($this->attributes->attr)) $this->attributes->attr=array();
  }




  /**
   * Increment sequence of family and call to {@see PostCreated()}
   * 
   * 
   * @return void
   */
  function PostInsert()
    // --------------------------------------------------------------------    
    {
      // controlled will be set explicitly
      //$this->SetControl();

      if (($this->revision == 0) && ($this->doctype != "T")) {
	// increment family sequence
	$this->nextSequence();
      }
      $this->Select($this->id);
      if ($this->doctype != "T") $this->PostCreated(); 
    }  

  /**
   * set default values and creation date
   * the access control is provided by {@see createDoc()} function.
   * call {@see Doc::PreCreated()} method before execution
   * 
   * @return string error message, if no error empty string
   */
  function PreInsert() {

      $err=$this->PreCreated(); 
      if ($err != "") return $err;
      
      // compute new id
      if ($this->id == "") {
	if ($this->doctype=='T') $res = pg_exec($this->init_dbid(), "select nextval ('seq_id_tdoc')");
	else $res = pg_exec($this->init_dbid(), "select nextval ('seq_id_doc')");
	$arr = pg_fetch_array ($res, 0);
	$this->id = $arr[0];

      }
      

      // set default values

      if ($this->initid == "") $this->initid=$this->id;
      if (chop($this->title) == "") {
	$fdoc=$this->getFamDoc();
	$this->title =sprintf(_("untitle %s %d"),$fdoc->title,$this->initid);
      }
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
      if ($this->revision==0) $this->Addcomment(_("creation"));

      if ($this->wid > 0) {
	$wdoc = new Doc($this->dbaccess,$this->wid);
	$wdoc->Set($this); // set first state
      }
      return $err;
    }   


    /** 
     * Verify control edit
     * 
     * if {@link disableEditControl()} is call before control permission is desactivated
     * if attribute values are changed the modification date is updated
     * @return string error message, if no error empty string
     */
  function PreUpdate() {
      if ($this->id == "") return _("cannot update no initialized document");
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
	//	$this->postModify(); // in modcard function
      }
      
    }

  /**
   * optimize for speed : memorize object for future use
   * @global array optimize for speed :: reference is not a pointer !!
   */
  function PostUpdate() {
    global $gdocs;// optimize for speed :: reference is not a pointer !!
    if (count($gdocs) < MAXGDOCS)    $gdocs[$this->id]=&$this;    
    if ($this->hasChanged) {
      $this->computeDProfil();
    }
    $this->hasChanged=false;
  }

  /**
   * get current sequence number :: number of doc for this family
   * @return int
   */
  function getCurSequence() {
    if ($this->doctype=='C') return 0;
    if ($this->fromid == "") return 0;
    // cannot use currval if nextval is not use before
    $res = pg_exec($this->init_dbid(), "select nextval ('seq_doc".$this->fromid."')");
    $arr = pg_fetch_array ($res, 0);
    $cur = intval($arr[0]) - 1;
    $res = pg_exec($this->init_dbid(), "select setval ('seq_doc".$this->fromid."',$cur)");
    
    return $cur;
  }
  // set next sequence family
  function nextSequence($fromid=0) {
    if ($fromid==0) $fromid=$this->fromid;
    if ($this->fromid==0)    return 0;
    if ($this->doctype=='C') return 0;
    // cannot use currval if nextval is not use before
    $res = pg_exec($this->init_dbid(), "select nextval ('seq_doc".$fromid."')");   
    $arr = pg_fetch_array ($res, 0);
    $cur = intval($arr[0]) ;        
    return $cur;
  }

  /**
   * modify without edit control
   */
  function disableEditControl() {
    $this->withoutControl=true;
  }
  /**
   * default edit control enable
   */
  function enableEditControl() {
    unset($this->withoutControl);
  }
  /**
   * to know if the document can be revised
   *
   * @return bool true is revisable
   */
  function isRevisable() {
    if (($this->doctype == 'F') && ($this->usefor != 'P')) {
      $fdoc = $this->getFamDoc();
      if ($fdoc->schar != "S") return true;

    }
    return false;
  }
 
  /**
   * copy values from anothers document (must be same family or descendant)
   *
   * @param Doc &$from document source for the transfert
   */
  function transfertValuesFrom(&$from) {
    
    $values = $from->getValues();
    

    foreach($values as $k=>$v) {
      $this->setValue($k,$v);
    }
  }
  /**
   * convert to another family
   * @param int $fromid family identificator where the document will be converted
   * @param array $prevalues values which will be added before conversion
   * @return doc the document converted (don't reuse $this) if error return string message
   */
  function convert($fromid, $prevalues=array()) {
    
    if ($this->fromid  == $fromid) return false; // no convert if not needed
    $cdoc = createDoc($this->dbaccess, $fromid);
    if (! $cdoc) return false;
    
    $cdoc->id = $this->id;
    $cdoc->initid=$this->initid;
    $cdoc->revision=$this->revision;
    $cdoc->locked=$this->locked;
    $cdoc->comment=$this->comment;
    $values = $this->getValues();

    $err=$this->delete(true,false,true); // delete before add to avoid double id (it is not authorized)
    if ($err != "") return $err;

    foreach($prevalues as $k=>$v) {
       $cdoc->setValue($k,$v);
    }
    $err=$cdoc->Add();
    if ($err != "") return $err;

    reset($values);
    while(list($k,$v) = each($values)) {
      $cdoc->setValue($k,$v);
    }

    $err=$cdoc->Modify();
    
    return $cdoc;
    
  }

  /**
   * test if the document can be revised now
   * it must be locked by the current user
   * @return bool
   */
  function CanUpdateDoc() {

    if ($this->locked == -1) {
      $err = sprintf(_("cannot update file %s (rev %d) : fixed. Get the latest version"), $this->title,$this->revision);      
    }

    if ($this->userid == 1) return "";// admin can do anything but not modify fixed doc
    $err="";
  
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

  /**
   * test if the document can be locked
   * it is not locked before, and the current user can edit document
   * @return bool
   */
  function CanLockFile() {
    $err="";
    
    if ($this->locked == -1) {
      
      $err = sprintf(_("cannot lock file %s (rev %d) : fixed. Get the latest version"), 
		     $this->title,$this->revision);
    }  else {
      if ($this->userid == 1) return ""; // admin can do anything
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

 
  /** 
   * test if the document can be unlocked
   * @see CanLockFile()
   * @see CanUpdateDoc()
   * @return bool
   */
  function CanUnLockFile() {
    if ($this->userid == 1) return "";// admin can do anything
    $err="";
    if ($this->locked != 0) { // if is already unlocked
      if ($this->profid > 0) 	$err = $this->Control("unlock"); // first control unlock privilege
      else $err=_("cannot unlock"); // not control unlock if the document is not controlled
    }
    if ($err != "") $err=$this->CanUpdateDoc();
    else {      
	$err = $this->Control("edit");
	if ($err != "") {
	  if ($this->profid > 0) {
	    $err = $this->Control("unlock");
	  }  
      }
    }
    return($err);
  
  }

  /** 
   * test if the document is locked
   * @see CanLockFile()
   * 
   * @return bool true if locked
   */
  function isLocked() {
    return (($this->locked > 0) || ($this->locked < -1));
  }

  /** 
   * return the family document where the document comes from
   * 
   * @return Doc
   */
  function getFamDoc() {
    if (! isset($this->famdoc)||($this->famdoc->id != $this->fromid)) $this->famdoc= new Doc($this->dbaccess, $this->fromid);
    return $this->famdoc;
  }

  /**
   * search the first document from its title
   * @param string $title the title to search (must be exactly the same title)
   * @return int document identificator
   */
  function GetFreedomFromTitle($title) {

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


 /**
   * return family parameter
   * 
   * @param string $idp parameter identificator
   * @param string $def default value if parameter not found or if it is null
   * @return string parameter value
   */
  function getParamValue($idp, $def="") {
    $fdoc=$this->getFamDoc();

    return $fdoc->getParamValue($idp,$def);
    
  }
  


  /**
   * return similar documents
   * 
   * @param string $key1 first attribute id to perform search 
   * @param string $key2 second attribute id to perform search 
   * @return string parameter value
   */
  function GetDocWithSameTitle($key1="title",$key2="") {
    include_once("FDL/Lib.Dir.php");
    // --------------------------------------------------------------------



    if ($this->initid>0)$filter[]="initid !='".$this->initid."'";  // not itself
    $filter[]="$key1='".addslashes($this->getValue($key1))."'";
    if ($key2 != "") $filter[]="$key2='".addslashes($this->getValue($key2))."'";
    $tpers = getChildDoc($this->dbaccess, 0,0,"ALL", $filter,1,"LIST",$this->fromid);
  
    return $tpers;

    
  }

   
  /** 
   * return the latest revision id with the indicated state 
   * For the user the document is in the trash
   * @param string $state wanted state
   * @return int document id (0 if no found)
   */
  function getRevisionState($state) {    
    $ldoc = $this->GetRevisions("TABLE");
    $vdocid=0;
    while (list($k,$v) = each($ldoc)) {
      if (strpos($v["state"], $state)===0) {
	$vdocid = $v["id"];
	break;
      }	  	  
    }
    return $vdocid;
  }    

  // --------------------------------------------------------------------
  function DeleteTemporary() {
    // --------------------------------------------------------------------

    $result = pg_exec($this->init_dbid(),"delete from doc where doctype='T'");

    
  }
  
  /** 
   * Control if the doc can be deleted
   * @access private
   * @return string error message, if no error empty string
   * @see Doc::Delete()
   */
  function PreDocDelete()    
    {
      
      if ($this->doctype == 'Z') return _("already deleted");
      $err = $this->Control("delete");
                        
      return $err;      
    }

  /** 
   * Really delete document from database
   * @return string error message, if no error empty string
   */
  function ReallyDelete($nopost) {
    return DbObj::delete($nopost);
  }

  /** 
   * Set the document to zombie state
   * For the user the document is in the trash
   * @param bool $really if true call {@link ReallyDelete} really delete from database
   * @param bool $control if false don't control 'delete' acl
   * @param bool $nopost if true don't call {@link PostDelete} and {@link PreDelete}
   * @return void
   */
  function Delete($really=false,$control=true,$nopost=false) {

    if ($control) {
    // Control if the doc can be deleted
      $msg = $this->Control("delete");
      if ($msg!='') return $msg;
    }

    if ($really) {
      if ($this->id != "") {
	// delete all revision also
	$rev=$this->GetRevisions();
	while (list($k,$v) = each($rev)) {
	  $v->ReallyDelete($nopost);
	}
      }
    } else {
      // Control if the doc can be deleted
      if ($this->doctype == 'Z') $msg= _("already deleted");       
      if ($msg!='') return $msg;

      if (!$nopost) $msg=$this->PreDelete();
      if ($msg!='') return $msg;

      $this->doctype='Z'; // Zombie Doc
      $this->locked= -1; 
      $date = gettimeofday();
      $this->revdate = $date['sec']; // Delete date

      global $action;
      global $_SERVER;
      $this->AddComment(sprintf(_("delete by %s by action %s on %s from %s"),
				$action->user->firstname." ".$action->user->lastname,
				$_SERVER["REQUEST_URI"],
				$_SERVER["HTTP_HOST"],
				$_SERVER["REMOTE_ADDR"]));


      if (!$nopost) $msg=$this->PostDelete();


      // delete all revision also
      $rev=$this->GetRevisions();
      while (list($k,$v) = each($rev)) {
	
	if ($v->doctype != 'Z') {
	  $v->doctype='Z'; // Zombie Doc
	  $v->locked= -1; 
	  $v->modify();
	}
	    
      }
    
      return $msg;
    }
  }


  /** 
   * Adaptation of affect Method from DbObj because of inheritance table
   * this function is call from QueryDb and all fields can not be instanciate
   * @return void
   */
  function Affect($array) {  
    reset($array);
    $this->ofields = $this->fields;
    $this->fields=array();
    unset($this->uperm); // force recompute privileges
    while(list($k,$v) = each($array)) {
      if (!is_integer($k)) {
	if ($k != "uperm") $this->fields[]=$k; // special for uperm : it is a function
	$this->$k = $v;
      }
    }
    $this->Complete();
   
    $this->isset = true;
  }
  /** 
   * Set to default values before add new doc
   * @return void
   */
  function Init() {         
    $this->isset = false;
    $this->id="";
    $this->initid="";
    $this->comment="";
    $nattr = $this->GetNormalAttributes();
    foreach($nattr as $k=>$v) {
      if (isset($this->$k) && ($this->$k != "")) $this->$k ="";
    }	
    unset($this->lvalues);
    
  }

  // --------------------------------------------------------------------
  function Description() {
    // -------------------------------------------------------------------- 
    
    return $this->title." - ".$this->revision;
  }


 
  // --------------------------------------------------------------------
  function GetFathersDoc() {
    // -------------------------------------------------------------------- 
    // Return array of father doc id : class document 
    if (! isset($this->fathers)) {

      $this->fathers=array();
      if ($this->fromid > 0) {
	$fdoc= $this->getFamDoc();
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
  function GetChildFam($id=-1, $controlcreate=false) {
    // -------------------------------------------------------------------- 
    // Return array of child doc id : class document 
    
    if ($id == 0) return array();
    if (($id!=-1) || (! isset($this->childs))) {

      if ($id==-1) 	$id= $this->id;	
      if (! isset($this->childs)) $this->childs=array();
      $query = new QueryDb($this->dbaccess, "DocFam");
      $query->AddQuery("fromid = ".$id);
      if ($controlcreate) $query->AddQuery("hasdocprivilege(".$this->userid.",profid,".(1<<intval(POS_CREATE)).")");
      $table1 = $query->Query(0,0,"TABLE");
      
      if ($table1) {
	while (list($k,$v) = each($table1)) {
	  $this->childs[$v["id"]]=$v;

	  $this->GetChildFam($v["id"],$controlcreate);
	  
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

  // get Latest Id
  function latestId() {
    if ($this->id == "") return false;
    if ($this->locked != -1) return $this->id;
    
    $query = new QueryDb($this->dbaccess, get_class($this));

    $query->AddQuery("initid = ".$this->initid);
    $query->AddQuery("locked != -1");
      
    $rev= $query->Query(0,0,"TABLE");
    return $rev[0]["id"];
  }

  // return the string label text for a id
  function GetLabel($idAttr)
    {

      if (isset($this->attributes->attr[$idAttr])) return $this->attributes->attr[$idAttr]->labelText;
      return _("unknow attribute");

    }

  
  /** 
   * return the attribute object for a id
   * the attribute can be defined in fathers
   * @param string $idAttr attribute identificator
   * @return DocAttribute
   */
  function GetAttribute($idAttr)
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
      $idAttr = strtolower($idAttr);
      if (isset($this->attributes->attr[$idAttr])) return $this->attributes->attr[$idAttr];
     

      return false;

    }

  /**
   * return all the attributes object 
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  function GetAttributes() 
    {     
      if (!$this->_maskApplied) $this->ApplyMask();
      reset($this->attributes->attr);
      return $this->attributes->attr;
    }


  /**
   * set visibility mask
   *
   * @param int $mid mask ident
   */
  function setMask($mid) {
    $this->mid=$mid;
    if (isset($this->attributes->attr)) {
      // reinit mask before apply
      foreach($this->attributes->attr as $k=>$v) {
	$this->attributes->attr[$k]->mvisibility=$v->visibility;
      }
    }
    $this->ApplyMask($mid);
  }
  /**
   * apply visibility mask
   *
   * @param int $mid mask ident, if not set it is found from possible workflow
   */
  function ApplyMask($mid = 0) {
    
    // copy default visibilities
    if (isset($this->attributes->attr)) {

      foreach($this->attributes->attr as $k=>$v) {
	//	if (is_object($v))
	$this->attributes->attr[$k]->mvisibility=ComputeVisibility($v->visibility,$v->fieldSet->mvisibility);

      }
    }

    $this->_maskApplied=true;
    // modify visibilities if needed
    if ($mid == 0) $mid=$this->mid;
    if ($mid == 0) {
      if (($this->wid > 0) && ($this->wid != $this->id)) {
	// search mask from workflow
	$wdoc=new Doc($this->dbaccess,$this->wid);
	if ($wdoc->isAlive()) {
	  if ($this->id == 0) {	  
	    $wdoc->set($this);
	  }
	  $mid = $wdoc->getValue($wdoc->attrPrefix."_MSKID".$this->state);
	}      
      }	
    }
    if ($mid > 0) { 

      $mdoc = new Doc($this->dbaccess,$mid );
      if ($mdoc->isAlive()) {
	$tvis = $mdoc->getCVisibilities();
	  
	while (list($k,$v)= each ($tvis)) {
	  if (isset($this->attributes->attr[$k])) {
	    if ($v != "-") $this->attributes->attr[$k]->mvisibility=$v;	      
	  }
	}
	// modify needed attribute also
	$tneed = $mdoc->getNeedeeds();
	while (list($k,$v)= each ($tneed)) {
	  if (isset($this->attributes->attr[$k])) {
	    if ($v == "Y") $this->attributes->attr[$k]->needed=true;
	    else if ($v == "N") $this->attributes->attr[$k]->needed=false;
	  }
	}
      }
    }
  }

  /**
   * return all the attributes except frame & menu
   * 
   * @return array DocAttribute
   */
  function GetNormalAttributes()
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
      if ((isset($this->attributes)) && (method_exists($this->attributes,"GetNormalAttributes")))
	return $this->attributes->GetNormalAttributes();      
      else return array();
    } 

  function GetFieldAttributes()
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();
     
      
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if (get_class($v) == "fieldsetattribute")  $tsa[$v->id]=$v;
      }
      return $tsa;      
    }
  /**
   * return all the attributes object for abstract
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  function GetAbstractAttributes()
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();

      if (isset($this->attributes->attr)) {
	reset($this->attributes->attr);
	while (list($k,$v) = each($this->attributes->attr)) {
	  if ((get_class($v) == "normalattribute")&&($v->isInAbstract)) $tsa[$v->id]=$v;
	}
      }
      return $tsa;      
    }

  

  /**
   * return all the attributes object for title
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  function GetTitleAttributes() { 
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
    if (isset($this->attributes->attr)) {
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if ((get_class($v) == "normalattribute") && ($v->isInTitle)) $tsa[$v->id]=$v;      
      }
    }
    return $tsa;
  }

  /**
   * return all the attributes that can be use in profil
   * 
   * @return array DocAttribute
   */
  function GetProfilAttributes() { 
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
    if (isset($this->attributes->attr)) {
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if ((get_class($v) == "normalattribute") && ($v->type=="docid") && (!$v->inArray())) $tsa[$v->id]=$v;      
      }
    }
    return $tsa;
  }


  /** 
   * return all the attributes object for to e use in edition
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  function GetInputAttributes()
    { 
      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if ((get_class($v) == "normalattribute") && (!$v->inArray()) && 
	    ($v->mvisibility != "I" )) {  // I means not editable
	  if ((($this->usefor=="Q") && ($v->usefor=="Q")) ||
	      (($this->usefor!="Q") && ($v->usefor!="Q")))
	    $tsa[$v->id]=$v;    //special parameters
	}
      }
      return $tsa;
    }

  /**
   * return all the attributes object for abstract
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  function GetFileAttributes()
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();
      
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if ((get_class($v) == "normalattribute") && (($v->type == "image") || 
						     ($v->type == "file"))) $tsa[$v->id]=$v;
      }
      return $tsa;      
    }

  /** return all the attributes object for popup menu
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  function GetMenuAttributes()
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();
      
      reset($this->attributes->attr);
      while (list($k,$v) = each($this->attributes->attr)) {
	if (((get_class($v) == "menuattribute"))&&($v->visibility != 'H')) $tsa[$v->id]=$v;
	  
	
      }
      return $tsa;
    }

  /**
   * return all the necessary attributes 
   * @return array DocAttribute
   */
  function GetNeededAttributes()
    {         
      if (!$this->_maskApplied) $this->ApplyMask();   
      $tsa=array();
      
      if ($this->usefor != 'D') { // not applicable for default document
	reset($this->attributes->attr);
	while (list($k,$v) = each($this->attributes->attr)) {
	  if ((get_class($v) == "normalattribute") && ($v->needed) && ($v->usefor!='Q')) $tsa[$v->id]=$v;      
	}
      }
      return $tsa;
    }

  function isCompleteNeeded() {
    $tsa=$this->GetNeededAttributes();
    $err="";
    while (list($k,$v) = each($tsa)) {
      if ($this->getValue($v->id) == "") $err .= sprintf(_("%s needed\n"),$v->labelText);
    }
    return $err;
  }


  // like normal attribut without files
  function GetExportAttributes()
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
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

  /**
   * return all the attributes object for import
   * @return array DocAttribute
   */
  function GetImportAttributes()
    {      

      $tsa=array();
      $tattr = $this->attributes->attr;
      while (list($k,$v) = each($tattr)) {

	if ((get_class($v) == "normalattribute") && 
	    (($v->visibility == "W") || ($v->visibility == "O") || ($v->type == "docid")) &&
	    ($v->type != "array")  ) {
	  
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


  /**
   * return all the attributes which can be sorted
   * @return array DocAttribute
   */
  function GetSortAttributes()  {      
    $tsa = array();
    $nattr = $this->GetNormalAttributes();
    reset($nattr);

    while (list($k,$a) = each($nattr)) {
      if ($a->repeat || ($a->visibility == "H")|| ($a->visibility == "O") || ($a->type == "longtext") || 
	  ($a->type == "docid") ||  ($a->type == "htmltext") ||
	  ($a->type == "image") || ($a->type == "file" ) || ($a->fieldSet->visibility == "H" )) continue;
      $tsa[$a->id]=$a;
    }
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

    if (chop($title1) != "")  $this->title = substr(chop(str_replace("\n"," ",$title1)),0,255);// restric to 256 char

  }
 
  // no in postUpdate method :: call this only if real change (values)
  function PostModify() {
    // to be defined in child class
    return "";
  }

  // no in postInsert method :: call this only in modcard function
  function PostCreated() {
    // to be defined in child class
    return "";
  }

  function PreCreated() {
    // to be defined in child class
    return "";
  }


  /**
   * recompute values from title
   * the first value use for title will be modify to have the new title
   * @param string $title new title
   */
  function SetTitle($title) {
    $ltitle = $this->GetTitleAttributes();
    reset($ltitle);
    $otitle = current($ltitle);
    $idt=$otitle->id;

    $this->title=str_replace("\n"," ",$title);
    $this->setvalue($idt,$title);


  }

 
  
  /**
   * return all attribute values
   *
   * @return array all attribute values 
   */
  function GetValues()  {
    $this->lvalues=array();
    //    if (isset($this->id) && ($this->id>0)) {

      $nattr = $this->GetNormalAttributes();
      foreach($nattr as $k=>$v) {
	$this->lvalues[$v->id] = $this->GetValue($v->id);
      }
      // }
    $this->lvalues=array_merge($this->lvalues,$this->mvalues); // add more values possibilities
    reset($this->lvalues);
    return $this->lvalues;
  }
  //-------------------------------------------------------------------


  /**
   * return the value of an attribute document 
   * @param string $idAttr identificator of attribute
   * @param string $def default value returned if attribute not found or if is empty
   * @return string the attribute value 
   */
  function GetValue($idAttr, $def="")  {      
    
    $lidAttr=strtolower($idAttr);
    if (isset($this->$lidAttr) && ($this->$lidAttr != "")) return $this->$lidAttr;
         
    return $def;
  }
  //-------------------------------------------------------------------

  /**
   * return the value of an list attribute document
   *
   * the attribute must be in an array or of a type '*list' like enumlist or textlist
   * @param string $idAttr identificator of list attribute 
   * @param string $def default value returned if attribute not found or if is empty
   * @return array the list of attribute values 
   */
  function GetTValue($idAttr, $def="",$index=-1)  { 
    $t = $this->_val2array($this->getValue("$idAttr",$def));
    if ($index == -1) return $t;
    if (isset($t[$index])) return $t[$index];
    else return $def;
  }
  //-------------------------------------------------------------------

  function SetValue($attrid, $value) {
    // control edit before set values
	  
    if (! isset($this->withoutControl)) {
      if ($this->id > 0) { // no control yet if no effective doc
	$err = $this-> Control("edit");
	if ($err != "") return ($err); 
      }
    }
      

    if (is_array($value)) {
      $value = $this->_array2val($value);
    }
    if (($value !== ""))  {
      // change only if different
      $attrid = strtolower($attrid);

      $oattr=$this->GetAttribute($attrid);
      if ($oattr->mvisibility=="I") return sprintf(_("no permission to modify this attribute %s"),$attrid);
      if ($value == " ") {
	$value=""; // erase value
	$this->hasChanged=true;
	$this->$attrid="";
      } else {

	$value=trim($value," \x0B\r");// suppress white spaces end & begin
	if (!isset($this->$attrid)) $this->$attrid="";

	if  ($this->$attrid != $value) 	  {
	  $this->hasChanged=true;
	  //   print "change $attrid  to <PRE>[{$this->$attrid}] [$value]</PRE><BR>";
	
	}

	if ($oattr->repeat) {
	  $tvalues = $this->_val2array($value);
	} else {
	  $tvalues[]=$value;
	}
    
	if ($this->usefor != 'D') { // not for default values
	  while (list($kvalue, $avalue) = each($tvalues)) {
	    if ($avalue != "") {
	    if ($oattr) {
	      switch($oattr->type) {
	      case double:
	      case money:
		$tvalues[$kvalue]=str_replace(",",".",$avalue);
		$tvalues[$kvalue]=str_replace(" ","",$tvalues[$kvalue]);
		$tvalues[$kvalue]=doubleval($tvalues[$kvalue]);
		break;
	      case integer:
		$tvalues[$kvalue]=intval($avalue);
		break;
	      case time:
		list($hh,$mm) = explode(":",$avalue);
		$tvalues[$kvalue]=sprintf("%02d:%02d",intval($hh)%24,intval($mm)%60);
		break;
	      case date:
		list($dd,$mm,$yy) = explode("/",$avalue);
		$yy = intval($yy);
		$mm = intval($mm); 
		$dd = intval($dd); 
	      
		if (($mm == 0) || ($dd == 0)) AddWarningMsg(sprintf(_("the date '%s' for %s attribute is not correct. It has been corrected automatically"),$avalue,$oattr->labelText));
		if ($mm == 0) $mm=1; // 1st january
		if ($dd == 0) $dd=1; // 1st day
		$tvalues[$kvalue]=sprintf("%02d/%02d/%04d",$dd,$mm,
					  ($yy<30)?2000+$yy:(($yy<100)?1900+$yy:$yy));
		break;
	      }
	    }
	    }
	  }
	}
	//      print $oattr->id."-".$oattr->type;print_r2($tvalues);
	$this->$attrid=implode("\n",$tvalues); 

	
      }      
    }
  }

  /**
   * return the related value by linked attributes
   */
  function GetRValue($RidAttr, $def="",$latest=true)  {      
    
    $tattrid = explode(":",$RidAttr);
    $lattrid=array_pop($tattrid); // last attribute

    $doc=$this;
    reset($tattrid);
    while(list($k,$v) = each($tattrid)) { 
      $docid= $doc->getValue($v);
      if ($docid == "") return $def;
      $doc = new Doc($this->dbaccess, $docid);
      if ($latest) {
	if ($doc->locked == -1) { // it is revised document
	  $ldocid = $doc->latestId();
	  if ($ldocid != $doc->id) $doc = new Doc($this->dbaccess, $ldocid);
	}
      }

      if (! $doc->isAlive())  return $def;

    }

    return $doc->getValue($lattrid, $def);


  }
  
  function DeleteValue($attrid) {
    return $this->SetValue($attrid," ");
  }


  // add values present in values field
  function GetMoreValues()  {      
    if (isset($this->values)) {
      $tvalues = explode("£",$this->values);
      $tattrids = explode("£",$this->attrids);
      
      foreach($tvalues as $k=>$v) {
	$attrid = $tattrids[$k];
	if (! isset($tattrids[$k])) {
	  //print_r2($tattrids);
	  //print_r2($tvalues);
	}
	if ($attrid != "") {
	  $this->$attrid=$v;
	  $this->mvalues[$attrid]=$v; // to be use in getValues()
	}
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
    $this->mvalues=array();
  }

  function GetValueMethod($value, $attrid='') {
    
    if ($this->usefor != 'D') {
      $value=$this->ApplyMethod($value,$value);
    }
    return $value;
  } 

  function ApplyMethod($method,$def="",$index=0) {
    $value=$def;
    if (ereg("::([^\(]+)\(([^\)]*)\)",$method, $reg)) {
      if (method_exists ( $this, $reg[1])) {
	if ($reg[2] == "") {
	  // without argument
	      
	  $value=call_user_method($reg[1],$this);
	} else {
	  // with argument
	  $args = explode(",",$reg[2]);
	      
	  if ($attrid != "") {
	    $this->AddParamRefresh($reg[2],$attrid);
	  }
	      
	  while(list($k,$v) = each($args)) { 
	    if ($attr=$this->getAttribute($v)) {
	      if ($attr->inArray())   $args[$k]=$this->GetTValue($v,"",$index);
	      else $args[$k]=$this->GetValue($v);
	    }
	    else $args[$k]=$v; // not an attribute just text
	    //   $args[$k]=$this->GetTValue($args[$k],$def,$index);
	  }
	  $value=call_user_method_array($reg[1],$this,$args);
	}
      } 
	
    }
    return $value;
  }
 
  /**
   * verify attribute constraint
   *
   * @param string $attrid attribute identificator
   * @return array array of 2 items ("err" + "sug"). 
   * The err is the string error message (empty means no error)
   * The sug is an array of possibles corrections
   */
  function verifyConstraint($attrid, $index=0) {
    $ok=array("err"=>"",
	      "sug"=>array());
    $oattr = $this->getAttribute($attrid);
    if (($oattr->phpconstraint != "") ){

       $res = $this->applyMethod($oattr->phpconstraint,'KO',$index);
       if ($res !== true) return $res;
    }

    return $ok;
     
  }

  function verifyAllConstraints() {
    
    $listattr = $this->GetNormalAttributes();
    foreach ($listattr as $k => $v) {
      if ($v->phpconstraint != "") {
	if ($v->inArray()) {
	  $tv = $this->getTValue($v->id);
	  for ($i=0;$i<count($tv);$i++) {
	    $res= $this->verifyConstraint($v->id,$i);
	    if ($res["err"]!="") return false;
	  }
	} else {
	  $res= $this->verifyConstraint($v->id);
	  //	  print print_r2($res);
	  if ($res["err"]!="") return false;
	}
      }
    }
    return true;
  }
  // return the first attribute of type 'file'
  function GetFirstFileAttributes()
    {
      $t =  $this->GetFileAttributes();
      if (count($t) > 0) return current($t);
      return false;      
    }

  function AddComment($comment='') {
    global $action;
    $commentdate = sprintf("%s [%s %s] %s",
			   date("d/m/Y H:i"),
			   $action->user->firstname,$action->user->lastname,
			   $comment);

    if ($this->comment != '') $this->comment = $commentdate."\n".$this->comment;
    else $this->comment = $commentdate;
    $this->modify(false,array("comment"));
  }
  function AddRevision($comment='') {

    if ($this->locked == -1) return _("document already revised");

    $fdoc = $this->getFamDoc();
   
    if ($fdoc->schar == "S") return sprintf(_("the document of %s family cannot be revised"),$fdoc->title);

    $this->locked = -1; // the file is archived
    $this->lmodify = 'N'; // not locally modified
    $this->owner = $this->userid; // rev user 
    $postitid = $this->postitid; // transfert post-it to latest revision
    $this->postitid=0;
    $date = gettimeofday();
    $this->revdate = $date['sec']; // change rev date
    if ($comment != '') $this->Addcomment($comment);


    $err=$this->modify();
    if ($err != "") return $err;

    //$listvalue = $this->GetValues(); // save copy of values

    // duplicate values
    $olddocid = $this->id;
    $this->id="";
    $this->locked = "0"; // the file is unlocked
    $this->comment = ""; // change comment
    $this->revision = $this->revision+1;
    $this->postitid=$postitid;
   
    $err=$this->Add();
    if ($err != "") return $err;
    if ($this->dprofid > 0) $this->setProfil($this->dprofid); // recompute profil if needed

    $err=$this->modify(); // need to applicate SQL triggers
       
    return $err;
    
  }

  /**
   * return the copy of the document
   * the copy is created to the database
   * the profil of the copy is the default profil according to his family
   * the copy is not locked and if it is related to a workflow, his state is the first state
   * @param bool $temporary if true the document create is a temporary document
   * @return Doc in case of error return a string that indicate the error
   */
  function Copy($temporary=false,$control=true) {
    $copy=createDoc($this->dbaccess, $this->fromid, $control);
    if (! is_object($copy)) return false;
    
    $copy->transfertValuesFrom($this);
    
    $copy->id = "";
    $copy->initid = "";
    $copy->revision = "0";
    $copy->locked = "0";
    $copy->state = "";
    $copy->comment = "";

    if ($temporary) $copy->doctype = "T";
    $cdoc= $this->getFamDoc();
    $copy->setProfil($cdoc->cprofid);
    $copy->addComment(sprintf(_("copy from document #%d -%s-"),$this->id, $this->title));
    $err = $copy->Add();

    if ($err != "") return $err;
    $copy->Modify();

    return $copy;
  }


  function translate($docid, $translate) {
    $doc = new Doc($this->dbaccess, $docid);
    if ($doc->isAlive()) {      
      while(list($afrom,$ato) = each($translate)) {
	$this->setValue($ato, $doc->getValue($afrom));
      }
    }
  }

  /** 
   * lock document
   * 
   * the auto lock is unlocked when the user discard edition or when he's modify document
   * @param bool $auto if true it is a automatic lock due to an edition (@see editcard()}
   * @param int $userid if set lock with another userid, the edit control will be disabled
   * 
   * @return string error message, if no error empty string, if message
   * @see Doc::CanLockFile()
   * @see Doc::unlock()
   */
  function lock($auto=false,$userid="") {

    $err="";
    if ($userid=="") {
      $err=$this->CanLockFile();
      if ($err != "") return $err;
      $userid=$this->userid;
    } else {
      $this->disableEditControl();
    }
    

    // test if is not already locked
    if ($auto) {
      if (($userid != 1) && ($this->locked == 0)) {
	$this->locked = -$userid; // in case of auto lock the locked id is negative
	$err=$this->modify(false,array("locked"));
      }
    } else { 
      if ($this->locked != $userid) {
	$this->locked = $userid;     
	$err=$this->modify(false,array("locked"));
      }
    }
    $this->enableEditControl();
    
    return $err;
  }

  /** 
   * unlock document
   * 
   * the automatic unlock is done only if the lock has been set automatically also
   * the explicit unlock, unlock in all case (if CanUnLockFile)
   * @param bool $auto if true it is a automatic unlock 
   * 
   * @return string error message, if no error empty string, if message
   * @see Doc::CanUnLockFile()
   * @see Doc::lock()
   */
  function unlock($auto=false) {
    

    $err=$this->CanUnLockFile();
    if ($err != "") return $err;
      
    if ($auto) {
      if ($this->locked < -1) {
	$this->locked = "0";      
	$this->modify(false,array("locked"));
      }
    } else {
      if ($this->locked != 0) {
	$this->locked = "0";      
	$this->modify(false,array("locked"));
      }
    }
    
    return "";
  }

  /**
   * return icon url
   * if no icon found return doc.gif
   * @return string icon url
   */
  function getIcon($idicon="") {

    global $action;
    if ($idicon=="") $idicon=$this->icon;
    if ($idicon != "") {
    
      if (ereg ("(.*)\|(.*)", $idicon, $reg)) {    
	$efile="FDL/geticon.php?vaultid=".$reg[2]."&mimetype=".$reg[1];
      } else {
	$efile=$action->GetImageUrl($idicon);
      }
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
			    "update doc set icon='$icon' where (fromid=".$this->initid.") AND (doctype != 'C') and ((icon='".$this->icon."') or (icon is null))");
    


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
  function SpecRefreshGen() {
    // Special Refresh Generated automatically
    // is define in generated child classes
  }
  // recompute all calculated attribut
  function Refresh() {	
    
    if ($this->locked == -1) return; // no refresh revised document
    if (($this->doctype == 'C') || ($this->doctype == 'Z') ) return; // no refresh for family  and zombie document
    if ($this->usefor == 'D') return; // no refresh for default document
   

    $err=$this->SpecRefresh();
    // if ($this->id == 0) return; // no refresh for no created document
	

    $err.=$this->SpecRefreshGen();

    if ($this->hasChanged)  {
      $this->modify(); // refresh title
    }
    return $err;
	
  }
  
  
  function urlWhatEncode( $link, $k=-1) {
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
	  if ($k >= 0) {
	    $tval= $this->GetTValue($sattrid);
	    $ovalue = chop($tval[$k]);
	  } else {
	    $ovalue = $this->GetValue($sattrid);
	  }
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
    return (chop($urllink));
    
  }
  
  function _val2array($v) {
    
    return explode("\n", str_replace("\r","",$v));
  }
  
  function _array2val($v) {    
    if (count($v) == 0) return "";
    return implode("\n", $v);
  }
  
  
  function GetHtmlValue($oattr, $value, $target="_self",$htmllink=true, $index=-1) {
    global $action;
    
    $aformat=$oattr->format;
    $atype=$oattr->type;

    if (($oattr->repeat)&&($index <= 0)){
      $tvalues = explode("\n",$value);
    } else {
      $tvalues[$index]=$value;
    }
    $idocfamid=$oattr->format;
    
    $attrid=$oattr->id;
    while (list($kvalue, $avalue) = each($tvalues)) {
      $htmlval="";
      switch ($atype)
	{

	 case "idoc":
	   $aformat=""; 
	   
	   /*
	   if (($oattr->repeat) && (!$oattr->inArray())){ // old idoclist type
	    
	    
	     $value=explode("\n",$value);
	     // printf($value.length)
	     $input="";
	     // printf($input);
	     while (list($x,$xmlencode) = each($value)) {
	       if ($xmlencode!=""){
		 printf("ici");
		 //printf($attrid);
		 $input.=recup_argument_from_xml(base64_decode($xmlencode),"title");
		 $attrid.="_$x";
		 //printf($attrid);

		 $input.="<FORM><INPUT id='_$attrid' TYPE=\"hidden\"  name='_$attrid' value=\"".$xmlencode." \"></input></FORM>";
		 $input.="<iframe name='iframe_$attrid' id='iframe_$attrid' style='display:none' marginwidth=0 marginheight=0  width='100%' heigth=200></iframe>";
		 
		 // print_r($input);
		 $input.="<input type=\"button\" value=\"view in frame\"".
		   " title=\"voir dans une frame\"".
		   " onclick=\"viewidoc_in_frame('iframe_$attrid','_$attrid','$idocfamid')\">";
		 
		 $input.="<input type=\"button\" value=\"close frame\"".
		   " title=\"fermer la frame\"".
		   " onclick=\"close_frame('iframe_$attrid')\">";
		 
		 $input.="<input type=\"button\" value=\"view\"".
		   " title=\"voir dans une nouvelle fenêtre\"".
		   " onclick=\"subwindowm(400,400,'_$attrid','[CORE_STANDURL]&app=FREEDOM&action=VIEWICARD');viewidoc('_$attrid','$idocfamid')\">";
		 $input.="<BR/>";
		 
	       }
	       
	     }
	     //printf($input);
	     //print_r($input);
	     $htmlval=$input;
	     
	     
	   }
	   
	   else {
	   */
	   $value=$avalue;
	     if($value!=""){
	       // printf("la ");
	       $temp=base64_decode($value);
	       $entete="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
	       $xml=$entete;
	       $xml.=$temp; 
	       $title=recup_argument_from_xml($xml,"title");//in freedom_util.php
	     }
	     $attrid=$attrid.$index;
	     $htmlval="<FORM><INPUT id=\"_" .$attrid."\" TYPE=\"hidden\"  name=\"_".$attrid."\" value=\"".$value." \">$title</input></FORM>";
	     $htmlval.="<iframe name='iframe_$attrid' id='iframe_$attrid' style='display:none' marginwidth=0 marginheight=0  width='100%' heigth=200></iframe>";
	     
	     
	     $htmlval.="<input type=\"button\" value=\"view in frame\"".
	       " title=\"voir dans une frame\"".
	       " onclick=\"viewidoc_in_frame('iframe_$attrid','_$attrid','$idocfamid')\">";
	     
	     $htmlval.="<input type=\"button\" value=\"close frame\"".
	       " title=\"fermer la frame\"".
	       " onclick=\"close_frame('iframe_$attrid')\">";
	     
	     $htmlval.="<input type=\"button\" value=\"view\"".
	       " title=\"voir dans une nouvelle fenêtre\"".
	       " onclick=\"subwindowm(400,400,'_$attrid','[CORE_STANDURL]&app=FREEDOM&action=VIEWICARD');viewidoc('_$attrid','$idocfamid')\">";
	     
	     //print_r($htmlval);
	     
	     
	     // }
	   break;
	   
     
	case "image": 
	  if ($target=="mail") {
	    $htmlval="cid:".$oattr->id;
	    if ($index >= 0) $htmlval.="+$index";
	  } else {
	    $vid="";
	    if (ereg ("(.*)\|(.*)", $avalue, $reg)) {
	      $vid=$reg[2];
	
	      $htmlval=$action->GetParam("CORE_BASEURL").
		"app=FDL"."&action=EXPORTFILE&vid=$vid&docid=".$this->id."&attrid=".$oattr->id."&index=$index"; // upload name
	    } else {
	      $htmlval="Images/noimage.png";
	    }
	  }
	      
	  break;
	case "file": 
	  $vid="";
	  if (ereg ("(.*)\|(.*)", $avalue, $reg)) {
	    // reg[1] is mime type
	    $vid=$reg[2];
	    $vf = new VaultFile($this->dbaccess, "FREEDOM");
	    if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
	    else $fname=_("vault file error");
	  } else $fname=_("no filename");
	
	
	  if ($target=="mail") {
	    $htmlval="<A target=\"_blank\" href=\"";
	    $htmlval.="cid:".$oattr->id;	    
	    if ($index >= 0) $htmlval.="+$index";
	    $htmlval.=  "\">".$fname."</A>";
	  } else 
	    $htmlval="<A onmousedown=\"document.noselect=true;\" target=\"_blank\" href=\"".
	      $action->GetParam("CORE_BASEURL").
	      "app=FDL"."&action=EXPORTFILE&vid=$vid"."&docid=".$this->id."&attrid=".$oattr->id."&index=$index"
	      ."\">".$fname.
	      "</A>";
	
	  break;
	case "longtext": 	  
	  $htmlval=str_replace(array("[","$"),array("&#091;","&#036;"),nl2br(htmlentities(stripslashes(str_replace("<BR>",
											"\n",$avalue)))));
	  break;
	case "password": 
	  $htmlval=ereg_replace(".", "*", htmlentities(stripslashes($avalue)));
	
	  break;
	case "enum": 
	  $enumlabel = $oattr->getEnumlabel();
	  if (isset($enumlabel[$avalue]))  $htmlval=$enumlabel[$avalue];
	  else $htmlval=$avalue;
	
	  break;    
	case "array": 

	  $lay = new Layout("FDL/Layout/viewarray.xml", $action);
	  if (! method_exists($this->attributes,"getArrayElements")) {	    
	    break;
	  }
	   
	  
	  $ta = $this->attributes->getArrayElements($oattr->id);
	  $talabel=array();
	  $tvattr = array();
	  $lay->set("caption",$oattr->labelText);

	  $emptyarray=true;
	  while (list($k, $v) = each($ta)) {
	    if (($v->mvisibility=="H")||($v->mvisibility=="O")) continue;
	    $talabel[] = array("alabel"=>$v->labelText);	
	    $tval[$k]=$this->getTValue($k);
	    if ($emptyarray && ($this->getValue($k)!="")) $emptyarray=false;
	   
	  }
	  $lay->setBlockData("TATTR",$talabel);
	  if (! $emptyarray) {
	    
	    reset($tval);
	    $nbitem= count(current($tval));
	    $tvattr = array();
	    for ($k=0;$k<$nbitem;$k++) {
	      $tvattr[]=array("bevalue" => "bevalue_$k");
	      reset($ta);
	      $tivalue=array();
	      while (list($ka, $va) = each($ta)) {	  
		if ($va->mvisibility=="H") continue;
		$hval = $this->getHtmlValue($va,$tval[$ka][$k],$target,$htmllink,$k);
		if ($va->type=="image") $hval="<img width=\"128\" src=\"".$hval."\">";
		$tivalue[]=array("evalue"=>$hval);
	      }
	      $lay->setBlockData("bevalue_$k",$tivalue);
	    }
	    $lay->setBlockData("EATTR",$tvattr);
      
	    $htmlval =$lay->gen(); 
	  } else {
	    $htmlval = "";
	  }
	  break;
 
	case "doc": 

	  $htmlval = "";
	  if ($avalue != "") {
	    if ($kvalue>-1)   $idocid=$this->getTValue($aformat,"",$kvalue);
	    else $idocid=$this->getValue($aformat);
	    
	    if ($idocid>0) {
	      //$lay = new Layout("FDL/Layout/viewadoc.xml", $action);
	      //$lay->set("id",$idocid);
	      $idoc = new Doc($this->dbaccess,$idocid);
	      $htmlval =$idoc->viewDoc("FDL:VIEWTHUMBCARD:T","finfo");

	      //$htmlval =$lay->gen(); 
	    }
	  }
	  break;
	case money:    


	  $htmlval=money_format('%!.2n', doubleval($avalue));
	  $htmlval=str_replace(" ","&nbsp;",$htmlval); // need to replace space by non breaking spaces
	  break;
	
	case htmltext:  
	  $htmlval="<DIV>$avalue</DIV>";
	
	  break;
	case time:  
	  $htmlval=substr($avalue,0,5); // do not display second
	
	  break;

	default : 
	
	  $htmlval=str_replace(array("[","$"),array("&#091;","&#036;"),htmlentities(stripslashes($avalue)));
	  
	  break;
	
	}
    
      if (($aformat != "") && ($atype != "doc")){
	//printf($htmlval);
	$htmlval=sprintf($aformat,$htmlval);
      } 
      // add link if needed
      if ($htmllink && ($oattr->link != "") ) {
	$ititle="";
	$hlink=$oattr->link;
	if ($hlink[0] == "[") {
	  if (ereg('\[(.*)\](.*)', $hlink, $reg)) {   
	    $hlink=$reg[2];
	    $ititle=addslashes($reg[1]);
	  }
	}
	if ($ulink = $this->urlWhatEncode( $hlink, $kvalue)) {


	  if ($target == "mail") {
	    $abegin="<A target=\"$target\"  href=\"";
	    $abegin.= $action->GetParam("CORE_ABSURL")."/".$ulink;
	    $abegin.="\">";
	  } else {
	    $abegin="<A target=\"$target\" title=\"$ititle\"onmousedown=\"document.noselect=true;\" href=\"";
	    $abegin.= $ulink."\" ";;
	    if ($htmllink > 1){
	      $turl=parse_url($ulink);
	      if (($turl["scheme"] == "") || ($turl["scheme"] == "http")) {
		if ($turl["scheme"] == "") $ulink.="&ulink=1";
		$abegin.=" oncontextmenu=\"popdoc(event,'$ulink');return false;\" ";
	      }
	    }
	    $abegin.=">";
	  }
	  $aend="</A>";
	

	} else {
	  $abegin="";
	  $aend="";
	}
      } else {
	$abegin="";
	$aend="";
      }
    
      $thtmlval[$kvalue]=$abegin.$htmlval.$aend;
    }
    
    return implode("<BR>",$thtmlval);
  }
  
  function GetHtmlAttrValue($attrid, $target="_self",$htmllink=true) {
    return $this->GetHtmlValue($this->getAttribute($attrid),
			       $this->getValue($attrid),$target,$htmllink);
  }

  

  /**
   * Control Access privilege for document
   *
   * @param string $aclname identificator of the privilige to test
   * @return string empty means access granted else it is an error message (access unavailable)
   */
  function Control ($aclname) {
    // -------------------------------------------------------------------- 
    if (($this->IsAffected()) ) {	
      
      if (($this->profid <= 0) || ($this->userid == 1 )) return ""; // no profil or admin

      return $this->controlId($this->profid,$aclname);
    }
    return "";
    return sprintf(_("cannot control : object not initialized : %s"),$aclname);
  }
  
  /**
   * verify that the document exists and is not in trash (not a zombie)
   * @return bool
   */
  function isAlive() {
    return ((DbObj::isAffected()) && ($this->doctype != 'Z'));
  }

  // --------------------------------------------------------------------
  // use triggers to update docvalue table
  // --------------------------------------------------------------------
  function SqlTrigger($drop=false) {

    if (get_class($this) == "docfam") {
      $cid = "fam";
    } else {
      if ($this->doctype == 'C') return;
      if (intval($this->fromid) == 0) return;
      
      $cid = $this->fromid;
    }
    
      
    $sql = "";

    // delete all relative triggers
    $sql .= "select droptrigger('doc".$cid."');";
     
    if ($drop) return $sql; // only drop
    if (is_array($this->attributes->fromids)) {
    reset($this->attributes->fromids);
    while(list($k,$v) = each($this->attributes->fromids)) {

      $sql .="create trigger UV{$cid}_$v BEFORE INSERT OR UPDATE ON doc$cid FOR EACH ROW EXECUTE PROCEDURE upval$v();";
     
    }
    }
    // the reset trigger must begin with 'A' letter to be proceed first (pgsql 7.3.2)
    $sql .="create trigger AUVR{$cid} BEFORE UPDATE  ON doc$cid FOR EACH ROW EXECUTE PROCEDURE resetvalues();";
    $sql .="create trigger FIXDOC{$cid} AFTER INSERT ON doc$cid FOR EACH ROW EXECUTE PROCEDURE fixeddoc();";
    
    return $sql;
  }

  function SetDefaultAttributes() {
  // transform hidden to writted attribut for default document
  if ($this->usefor == "D") {
    $listattr = $this->GetAttributes();
    while (list($i,$attr) = each($listattr)) {
      if (($attr->mvisibility == "H") || ($attr->mvisibility == "R") || ($attr->mvisibility == "S")) {
	$this->attributes->attr[$i]->mvisibility="W";
      }
    }
  }
  }

  /**
   * set default values define in family document
   * the format of the string which define default values is like
   * [US_ROLE|director][US_SOCIETY|alwaysNet]...
   * @param string $defval the default values
   * @access private
   */
  function setDefaultValues($tdefval) {
    if (is_array($tdefval)) {

      foreach ($tdefval as $aid=>$dval) {

// 	$aid=substr($v, 0, strpos($v,'|'));
// 	$dval=substr(strstr($v,'|'),1);


	$this->setValue($aid, $this->GetValueMethod($dval));

      }              
    }
  }

  // --------------------------------------------------------------------
  // generate HTML code for view doc
  // --------------------------------------------------------------------
  function viewDoc($layout="FDL:VIEWBODYCARD",$target="_self",$ulink=true,$abstract=false) {
    global $action;

 
    if (! ereg("([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}", $layout, $reg)) 
      $action->exitError(sprintf(_("error in pzone format %s"),$layout));
     
  
    $this->SetDefaultAttributes();

    $this->lay = new Layout(getLayoutFile($reg[1],strtolower($reg[2]).".xml"), $action);

    $method = strtolower($reg[2]);

   
    if (method_exists ( $this, $method)) {
      $this->$method($target,$ulink,$abstract);
    } else {
      $this->viewdefaultcard($target,$ulink,$abstract);
    }


    if (! $ulink) {
      // suppress href attributes
      return preg_replace(array("/href=\"([^c]|c[^i]|ci[^d])[^\"]*\"/i", "/onclick=\"[^\"]*\"/i","/ondblclick=\"[^\"]*\"/i"), 
			  array("","","") ,$this->lay->gen() );
    }
    if ($target=="mail") {
      // suppress session id
      return preg_replace("/\?session=[^&]*&/", "?" ,$this->lay->gen() );
    }
    return $this->lay->gen();
  }
  // --------------------------------------------------------------------

  /**
   * default construct layout for view card containt
   *
   * @param string $target window target name for hyperlink destination
   * @param bool $ulink if false hyperlink are not generated
   * @param bool $abstract if true only abstract attribute are generated
   */
  function viewdefaultcard($target="_self",$ulink=true,$abstract=false) {
    $this->viewattr($target,$ulink,$abstract);
    $this->viewprop($target,$ulink,$abstract);
  }
  // --------------------------------------------------------------------

  /**
   * construct layout for view card containt
   *
   * @param string $target window target name for hyperlink destination
   * @param bool $ulink if false hyperlink are not generated
   * @param bool $abstract if true only abstract attribute are generated
   */
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

      $goodvalue=((($value != "") || ( $attr->type=="array")) && 
		  ($attr->mvisibility != "H") && ($attr->mvisibility != "O") && (! $attr->inArray()));
      if ($goodvalue)   {
	 
	$htmlvalue=$this->GetHtmlValue($attr,$value,$target,$ulink);
      } else $htmlvalue="";
    
      if ($htmlvalue !== "") // to define when change frame
	{
	  if ( $currentFrameId != $attr->fieldSet->id) {	    
	    if (($currentFrameId != "") && ($attr->fieldSet->visibility != "H")) $changeframe=true;
	  }
	}
	


      //------------------------------
      // change frame if needed

      if (  // to generate  fieldset
	  $changeframe)
	{
	  $changeframe=false;
	  if (($v+$nbimg) > 0) // one value detected
	    {
				      
	      $frames[$k]["frametext"]="[TEXT:".$this->GetLabel($currentFrameId)."]";
	      $frames[$k]["frameid"]=$currentFrameId;
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
    
      if ($goodvalue)   {
	  	 
	switch ($attr->type)
	  {	      
	  case "image": 		  
	    $tableimage[$nbimg]["imgsrc"]=$htmlvalue;
	    break;
	  default : 
	    $tableframe[$v]["value"]=$htmlvalue;
	    break;
		
	  }

	if (($attr->fieldSet->visibility!="H")&&($htmlvalue!=="")) $currentFrameId = $attr->fieldSet->id;


	
	// print name except image (printed otherthere)
	if ($attr->type != "image") {	
	  $tableframe[$v]["wvalue"]=($attr->type == "array")||($attr->type == "htmltext")?"1%":"30%"; // width
	  $tableframe[$v]["name"]=$this->GetLabel($attr->id);
	  if (( $attr->type != "array")&&( $attr->type != "htmltext"))  $tableframe[$v]["ndisplay"]="";
	  else $tableframe[$v]["ndisplay"]="none";
	  $v++;
	} else	{
	  $tableimage[$nbimg]["imgalt"]=$this->GetLabel($attr->id);
	  $nbimg++;
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
  
  /**
   * write layout for thumb view
   */
  function viewthumbcard($target="finfo",$ulink=true,$abstract=true) {
    $this->viewabstractcard($target,$ulink,$abstract);
    $this->viewprop($target,$ulink,$abstract);
    $this->lay->set("iconsrc",$this->getIcon());
    if ($this->state != "") $this->lay->set("state",_($this->state));
    else $this->lay->set("state","");
  }
  /**
   * write layout for abstract view
   */
  function viewabstractcard($target="finfo",$ulink=true,$abstract=true) {
    // -----------------------------------
    



    $listattr = $this->GetAbstractAttributes();
 
    $tableframe=array();
 
    while (list($i,$attr) = each($listattr)) {
  

      //------------------------------
      // Compute value elements
	  
      $value = chop($this->GetValue($i));

    


      if (($value != "") && ($attr->mvisibility != "H"))   {
		
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
      
     	
	// don't see  non abstract if not
      if (($v->mvisibility == "H") || (($abstract) && (! $v->isInAbstract ))) {
	$this->lay->Set("V_".strtoupper($v->id),"");
	$this->lay->Set("L_".strtoupper($v->id),"");
      } else {
	$this->lay->Set("V_".strtoupper($v->id),$this->GetHtmlValue($v,$value,$target,$ulink));
	$this->lay->Set("L_".strtoupper($v->id),$v->labelText);
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
	$cdoc= $this->getFamDoc();
	$this->lay->Set("TITLE", sprintf(_("new %s"),$cdoc->title));
     
      }
	
    }  else    {      
	
	
      // when modification 

      if (! $this->isAlive()) $action->ExitError(_("document not referenced"));
	
	
      $this->SetDefaultAttributes();
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
    $listattr = $this->GetInputAttributes();
  

  
    $nattr = count($listattr); // number of attributes
    
    
    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $currentFrameId="";
    $changeframe=false;
    $ih = 0; // index for hidden values
    $thidden =array();
    $tableframe=array();

    $iattr=0;
    reset($listattr);
    while (list($i,$attr) = each($listattr)) {
      
      $iattr++;
    
      // Compute value elements
	    
	
      if ($docid > 0) $value = $this->GetValue($listattr[$i]->id);
      else {
	$value = $this->GetValue($listattr[$i]->id);
//	$value = $this->GetValueMethod($this->GetValue($listattr[$i]->id));
      }
	    	    

      if ( $currentFrameId != $listattr[$i]->fieldSet->id) {
	if ($currentFrameId != "") $changeframe=true;
      }
	    
      
      
      
      if ( $changeframe){  // to generate final frametext
	      
	$changeframe=false;
	if ($v > 0 ) {// one value detected	  
	      
	  $frames[$k]["frametext"]="[TEXT:".$this->GetLabel($currentFrameId)."]";
	  $frames[$k]["frameid"]=$currentFrameId;
	  $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";
	  $this->lay->SetBlockData($frames[$k]["TABLEVALUE"],
				   $tableframe);
	  unset($tableframe);
	  $tableframe=array();
	  $k++;
	}
	$v=0;
      }
      
      
      //------------------------------
      // Set the table value elements
    
	      
      $currentFrameId = $listattr[$i]->fieldSet->id;
      if ( ($listattr[$i]->mvisibility == "H") || 
	   ($listattr[$i]->mvisibility == "R") ) {
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
		
		
	$tableframe[$v]["NORMALROW"]="NORMALROW$i";		
	$tableframe[$v]["ARRAYROW"]="ARRAYROW$i";

	if ($listattr[$i]->type=="array") $this->lay->SetBlockData("ARRAYROW$i",array(array("zou")));
	else	$this->lay->SetBlockData("NORMALROW$i",array(array("zou")));
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
      if ($v->needed == "Y") $this->lay->Set("L_".strtoupper($v->id),"<B>".$v->labelText."</B>");
      else $this->lay->Set("L_".strtoupper($v->id),$v->labelText);
      
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

 // =====================================================================================
  // ================= methods use for XML ======================
  function toxml($withdtd,$id_doc="")  {

    global $action;
    $doctype=$this->doctype; 
    
    $docid=intval($this->id);
    if ($id_doc==""){
      $id_doc=$docid;
    }

    $title=$this->title;
    $fromid=$this->fromid;
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $fam_doc=new Doc($this->dbaccess,$this->fromid);
    $name=str_replace(" ","_",$fam_doc->title);


    if ($withdtd==true) {
      $dtd="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
      $dtd.="<!DOCTYPE $name [";
      $dtd.=$this->todtd();
      $dtd.="]>";
    }
    else{ $dtd="";}

    $this->lay = new Layout("FDL/Layout/viewxml.xml", $action);
    $this->lay->Set("DTD",$dtd);
    $this->lay->Set("NOM_FAM",$name);
    $this->lay->Set("id_doc",$id_doc);
    $this->lay->Set("TITRE",$title);
    $this->lay->Set("ID_FAM",$fam_doc->name);  
    $this->lay->Set("revision",$this->revision);
    $this->lay->Set("revdate",$this->revdate);


    //$this->lay->Set("IDOBJECT",$docid);
    //$this->lay->Set("IDFAM",$fromid);
    //$idfam=$fam_doc->classname;
    //$this->lay->Set("TYPEOBJECT",$doctype);



    ////debut
    $listattr= $this->GetNormalAttributes();

    $frames= array();


    $nattr = count($listattr); // attributes list count


    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $currentFrameId="";

    $changeframe=false; // is true when need change frame
    $tableframe=array();
     

    $iattr=0;

    while (list($i,$attr) = each($listattr)) {
      $iattr++;
      // printf($listattr[$i]->id);

      if ((chop($listattr[$i]->id)!="") && ($listattr[$i]->id!="FIELD_HIDDENS")){

	//------------------------------
	// Compute value elements
	  
       
	if ( $currentFrameId != $listattr[$i]->fieldSet->id) {
	  if ($currentFrameId != "") $changeframe=true;
	}
	  


	//------------------------------
	// change frame if needed

	if (  // to generate  fiedlset
	    $changeframe)
	  {
	    $changeframe=false;
	    if ($v > 0) // one value detected
	      {
				      
		$frames[$k]["FIELD"]=$currentFrameId;
		$frames[$k]["ARGUMENT"]="ARGUMENT_$k";

		$this->lay->SetBlockData($frames[$k]["ARGUMENT"],
					 $tableframe);
		$frames[$k]["nom_fieldset"]=$this->GetLabel($currentFrameId);
		unset($tableframe);
		$tableframe=array();
		$k++;
	      }
	    $v=0;
      
	  }



	// Set the table value elements
	if (($iattr <= $nattr) && ($this->Getvalue($i)!="") )	{
	  $attrtype_idoc=false;
	  $attrtype_list=false;

	  if (strstr($listattr[$i]->type,"textlist")!=false){
	    $attrtype_list=true;
	  }
	  if ((strstr($listattr[$i]->type,"idoclist"))!=false){
	    $attrtype_list=true;
	    $attrtype_idoc=true;
	  }
	  if ((strstr($listattr[$i]->type,"idoc"))!=false){
	    $attrtype_idoc=true;
	  }
	  if($listattr[$i]->inArray()){
  	  $attrtype_list=true;
	  }

	  if ($attrtype_list){
	    // $value=htmlspecialchars($this->GetValue($i));
	    $value=$this->GetValue($i);
	    $textlist=$this->_val2array($value);
	      
	    while ($text = each($textlist)){
	      $currentFrameId = $listattr[$i]->fieldSet->id;
	      $tableframe[$v]["id"]=$listattr[$i]->id;
	      if  ($attrtype_idoc){
		$tableframe[$v]["value"]=base64_decode($text[1]);
		$tableframe[$v]["type"]="idoc";
	      }
	      else{
		$tableframe[$v]["value"]=$text[1];
		$tableframe[$v]["type"]=base64_encode($listattr[$i]->type);
	      }
	      $tableframe[$v]["labelText"]=(str_replace(array("%","\""),
							array("","\\\""), $listattr[$i]->labelText));
	      //$tableframe[$v]["type"]=$listattr[$i]->type;
	      //$tableframe[$v]["visibility"]=$listattr[$i]->visibility;
	      //$tableframe[$v]["needed"]=$listattr[$i]->needed;
	      $v++;
	    }

	  }
		
	  else{
	  
	    if ($attrtype_idoc){
	      $value=base64_decode($this->GetValue($i));
	      $tableframe[$v]["type"]="idoc";
	      //printf($value);
	   
	    }
	    else{
	      $value=htmlspecialchars($this->GetValue($i));
	      $tableframe[$v]["type"]=base64_encode($listattr[$i]->type);
	    }

	    $currentFrameId = $listattr[$i]->fieldSet->id;
	    $tableframe[$v]["id"]=$listattr[$i]->id;
	    $tableframe[$v]["value"]=$value;
	    $tableframe[$v]["labelText"]=addslashes($listattr[$i]->labelText);
	    //$tableframe[$v]["type"]=$listattr[$i]->type;
	    //$tableframe[$v]["visibility"]=$listattr[$i]->visibility;
	    //$tableframe[$v]["needed"]=$listattr[$i]->needed;
	    $v++;

	  }
	
	}


      }
    }
 


    if ($v > 0) // last fieldset
      {
				      
	$frames[$k]["FIELD"]=$currentFrameId;
	$frames[$k]["ARGUMENT"]="ARGUMENT_$k";

	$this->lay->SetBlockData($frames[$k]["ARGUMENT"],
				 $tableframe);
	$frames[$k]["nom_fieldset"]=$this->GetLabel($currentFrameId);
	unset($tableframe);
	$tableframe=array();
	$tableimage=array();
	$k++;
      }
 



 




    $this->lay->SetBlockData("FIELDSET",$frames);
    return $this->lay->gen();
  }
  
  function todtd() {


    global $action;
    $this->lay = new Layout("FDL/Layout/viewdtd.xml", $action);

    $fam_doc=$this->getFamDoc();
    $name=str_replace(" ","_",$fam_doc->title);
    $this->lay->Set("doctype",$this->doctype);
    $this->lay->Set("idfam",$this->fromid);
    $this->lay->Set("nom_fam",$name);
    $this->lay->Set("id_fam",$name);

    $listattr= $this->GetNormalAttributes();

    $frames= array();

    $nattr = count($listattr); // attributes list count

    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $currentFrameId="";

    $changeframe=false; // is true when need change frame
    $needed=false;
    $tableattrs=array();
    $tablesetting=array();
    $iattr=0;

    while (list($i,$attr) = each($listattr)) {
      $iattr++;
      //------------------------------
      // Compute value elements
	  
      if ( $currentFrameId != $listattr[$i]->fieldSet->id) {
	if ($currentFrameId != "") $changeframe=true;
      }

      //------------------------------
      // change frame if needed

      if (  // to generate  fiedlset
	  $changeframe)
	{
	  $changeframe=false;
	  


	  if ($v > 0) // one value detected
	    {
	      	     
	      $frames[$k]["name"]=$currentFrameId;
	      $elements[$k]["name"]=$currentFrameId;
	      if ($needed){
		$elements[$k]["name"].=", ";
	      }
	      else{
		$elements[$k]["name"].="?, ";
	      }
	      $needed=false;

	      $frames[$k]["ATTRIBUT_NAME"]="ATTRIBUT_NAME_$k";
	      $frames[$k]["ATTRIBUT_SETTING"]="ATTRIBUT_SETTING_$k";

	      $this->lay->SetBlockData($frames[$k]["ATTRIBUT_NAME"],
				       $tableattrs);
              
	      $this->lay->SetBlockData($frames[$k]["ATTRIBUT_SETTING"],
				       $tablesetting);
	      unset($tableattrs);
	      unset($tablesetting);
	      $tableattrs=array();
	      $tablesetting=array();

	      $k++;
	    }
	  $v=0;


	}





      // Set the table value elements
      if ($iattr <= $nattr)	{
   		  
	$currentFrameId = $listattr[$i]->fieldSet->id;
	$tablesetting[$v]["name_attribut"]=$listattr[$i]->id;
	$tablesetting[$v]["labelText"]=addslashes(str_replace("%","",$listattr[$i]->labelText));
	$tablesetting[$v]["type"]=base64_encode($listattr[$i]->type);
	$tablesetting[$v]["visibility"]=$listattr[$i]->visibility;
	if ($listattr[$i]->needed){
	  $needed=true;
	}
	 

	if ($v==0){
	  $insert=$listattr[$i]->id;
	  if ($listattr[$i]->type=="textlist"){
	    if ($listattr[$i]->needed){
	      $insert.="+";$tableattrs[$v]["name_attribut"]=$insert;
	    }
	    else{
	      $insert.="*";$tableattrs[$v]["name_attribut"]=$insert;
	    }
	    
	  }
	  else{
	    if ($listattr[$i]->needed){
	      $tableattrs[$v]["name_attribut"]=$insert;
	    }
	    else{
	      $tableattrs[$v]["name_attribut"]=($insert ."?");
	    }
	  }

	}
	else{
	  $insert=(", " .$listattr[$i]->id);          
	  if ($listattr[$i]->type=="textlist"){
	    if ($listattr[$i]->needed){
	      $insert.="+";
	    }
	    else{
	      $insert.="*";
	    }
	    $tableattrs[$v]["name_attribut"]=$insert;
	  }
	  else{
	    if ($listattr[$i]->needed){
	      $tableattrs[$v]["name_attribut"]=$insert;
	    }
	    else{
	      $tableattrs[$v]["name_attribut"]=($insert ."?");
	    }
	  }

        }
	$v++;
    
      }

    }
 
 


    if ($v > 0) // last fieldset
      {
	$frames[$k]["name"]=$currentFrameId;
	if ($needed){
	  $elements[$k]["name"]=$currentFrameId;
	}
	else{
	  $elements[$k]["name"]=($currentFrameId ."?");
	}
	$needed=false;
	$frames[$k]["ATTRIBUT_NAME"]="ATTRIBUT_NAME_$k";
	$frames[$k]["ATTRIBUT_SETTING"]="ATTRIBUT_SETTING_$k";
	$this->lay->SetBlockData($frames[$k]["ATTRIBUT_NAME"],
				 $tableattrs);

	$this->lay->SetBlockData($frames[$k]["ATTRIBUT_SETTING"],
				 $tablesetting);
	unset($tableattrs);
	unset($tablesetting);
	$tableattrs=array();
	$tablesetting=array();

	$k++;

	     	     
      }



    $this->lay->SetBlockData("FIELDSET",$frames);
    $this->lay->SetBlockData("ELEMENT",$elements);
    return $this->lay->gen();
  }

  
  // =====================================================================================
  // ================= methods use for calculated attributes ======================


  // return the personn doc id conform to firstname & lastname of the user
  function userDocId() {
    global $action;

    
    return $action->user->fid;
    include_once("FDL/Lib.Dir.php");
    $famid=getFamIdFromName($this->dbaccess,"IUSER");
    $filter[]="us_whatid = '".$this->userid."'";
    
    $tpers = getChildDoc($this->dbaccess, 0,0,1, $filter,$action->user->id,"TABLE",$famid);
    if (count($tpers) > 0)    return($tpers[0]["id"]);
    
    return "";
    
  }
  // return the personn doc id conform to firstname & lastname of the user
  function userName() {
    global $action;

    return $action->user->lastname;
    return $action->user->lastname." ".$action->user->firstname;
  }

  function myAttribute($idattr) {
    $mydoc=new Doc($this->dbaccess,$this->userDocId());

    return $mydoc->getValue($idattr);
  }


  function getTitle($id) {
    if (! is_numeric($id)) return ""; 
    
    $t = getTDoc($this->dbaccess,$id);
    if ($t)    return $t["title"];

    return " "; // delete title
  }

  function getDate($daydelta=0) {
    $delta = abs(intval($daydelta));
    if ($daydelta > 0) {
      return date("d/m/Y",strtotime ("+$delta day"));
    } else if ($daydelta < 0) {
      return date("d/m/Y",strtotime ("-$delta day"));
    }
    return date("d/m/Y");
  }
  function getDocValue($docid, $attrid) {
    if (intval($docid) > 0) {
      $doc = new Doc($this->dbaccess, $docid);
      if ($doc->isAlive()) {
	return $doc->getRValue($attrid);
      }
    }
    return "";
  }

  function refreshDocTitle($nameId,$nameTitle) {
  
    // gettitle(D,SI_IDSOC):SI_SOCIETY,SI_IDSOC

    $this->AddParamRefresh("$nameId","$nameTitle");
    $doc=new Doc($this->dbaccess, $this->getValue($nameId));
    if ($doc->isAlive())  $this->setValue($nameTitle,$doc->title);
    else {
      // suppress
      $this->deleteValue($nameId);
    }
  }

  /**
   * use only for paramRefresh in attribute definition of a family
   */
  function nothing($a="",$b="",$c="") {
    return "";
  }


}

?>
