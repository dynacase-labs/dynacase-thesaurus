<?php
/**
 * Document Object Definition
 *
 * @author Anakeen 2002
 * @version $Id: Class.Doc.php,v 1.375 2007/05/07 15:48:48 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
/**
 */



include_once("Class.QueryDb.php");
include_once("FDL/Class.DocCtrl.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Class.DocVaultIndex.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Class.DocHisto.php");
include_once('FDL/Class.ADoc.php');
include_once("FDL/Lib.Util.php");


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
define ("MENU_ACTIVE", 1);
define ("MENU_INVISIBLE", 2);
define ("MENU_INACTIVE", 0);

define('POPUP_INACTIVE',0);
define('POPUP_ACTIVE',1);
define('POPUP_CTRLACTIVE',3);
define('POPUP_CTRLINACTIVE',4);
define('POPUP_INVISIBLE',2);
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
  public $fields = array ( "id","owner","title",
			   "revision","version",
			   "initid","fromid",
			   "doctype",
			   "locked",
			   "allocated",
			   "icon",
			   "lmodify",
			   "profid",
			   "usefor",
			   "cdate",
			   "adate",
			   "revdate",
			   "comment",
			   "classname",
			   "state",
			   "wid",
			   "postitid",
			   "cvid",
			   "name",
			   "dprofid",
			   "atags",
			   "prelid",
			   "confidential",
			   "ldapdn");

  public $sup_fields= array("values",
			    "attrids",
			    "fulltext"); // not be in fields else trigger error

  /**
   * identificator of the document
   * @public int
   */
  public $id;
  /**
   * user identificator for the creator
   * @public int
   */
  public $owner;
  /**
   * the title of the document
   * @public string
   */
  public $title;
  /**
   * number of the revision. First is zero
   * @public int
   */
  public $revision;
  /**
   * tag for version
   * @public string
   */
  public $version;
  /**
   * identificator of the first revision document
   * @public int
   */
  public $initid;
  /**
   * identificator of the family document
   * @public int
   */
  public $fromid;
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
   * @public char
   */
  public $doctype;
  /**
   * user identificator for the locker
   * @public int
   */
  public $locked;
  /**
   * filename or vault id for the icon
   * @public string
   */
  public $icon;
  /**
   * set to 'Y' if the document has been modify until last revision
   * @public char
   */
  public $lmodify;
  /**
   * identificator of the profil document
   * @public int
   */
  public $profid;
  /**
   * to precise a special use of the document
   * @public char
   */
  public $usefor;
  /**
   * date of the last modification (the revision date for fixed document)
   * @public int
   */
  public $revdate;
  /**
   * date of creation
   * @public date
   */
  public $cdate;
  /**
   * date of latest access
   * @public date
   */
  public $adate;
  /**
   * date of the last modification (the revision date for fixed docuemnt)
   * @public int
   */
  public $comment;
  /**
   * class name in case of special family (only set in family document)
   * @public string
   */
  public $classname;
  /**
   * state of the document if it is associated with a workflow
   * @public string
   */
  public $state;
  /**
   * identificator of the workflow document
   * 
   * if 0 then no workflow
   * @public int
   */
  public $wid;
  /**
   * identificator of the control view document
   * 
   * if 0 then no special control view
   * @public int
   */
  public $cvid;
  /**
   * string identificator of the document
   * 
   * @public string
   */
  public $name;
  /**
   * identificator of the mask document
   * 
   * if 0 then no mask
   * @public int
   */
  public $mid=0;
  /**
   * identificator of dynamic profil
   * 
   * if 0 then no dynamic profil
   * @public int
   */
  public $dprofid=0;
  /**
   * primary relation id
   * 
   * generally towards a folder
   * @public int
   */
  public $prelid=0;
 
  /**
   * applications tag 
   * use by specifics applications to search documents by these tags
   * 
   * @public string
   */
  public $atag;
  /**
   * confidential level
   * if not 0 this document is confidential, only user with the permission 'confidential' can read this
   * 
   * @public int
   */
  public $confidential;
  /**
   * Distinguish Name for LDAP use
   * 
   * @public text
   */
  public $ldapdn; 
  /**
   * Allocate user id
   * 
   * @public int
   */
  public $allocated;

  /**
   * identification of special views
   * 
   * @public array
   */
  public $cviews=array("FDL:VIEWBODYCARD",
		    "FDL:VIEWABSTRACTCARD",
		    "FDL:VIEWTHUMBCARD");
  public $eviews=array("FDL:EDITBODYCARD");


  public static $sqlindex=array("doc_initid"=>array("unique"=>false,
					     "on"=>"initid"),
				"doc_title"=>array("unique"=>false,
						   "on"=>"title"),
				"doc_full"=>array("unique"=>false,
						  "using"=>"gist",
						  "on"=>"fulltext"),
				"doc_profid"=>array("unique"=>false,
						    "on"=>"profid"));
  public $id_fields = array ("id");

  public $dbtable = "doc";

  public $order_by="title, revision desc";

  public $fulltextfields = array ("title");
  private $mvalues = array();

  /**
   * default family id for the profil access
   * @public int
   */
  public $defProfFamId=FAM_ACCESSDOC;
  public $sqlcreate = "
create table doc ( id int not null,
                   primary key (id),
                   owner int,
                   title varchar(256),
                   revision int DEFAULT 0,
                   initid int,
                   fromid int,
                   doctype char DEFAULT 'F',
                   locked int DEFAULT 0,
                   allocated int DEFAULT 0,
                   icon varchar(256),
                   lmodify char DEFAULT 'N',
                   profid int DEFAULT 0,
                   usefor char  DEFAULT 'N',
                   revdate int, 
                   version text,
                   cdate timestamp,  
                   adate timestamp,  
                   comment text,
                   classname varchar(64),
                   state varchar(64),
                   wid int DEFAULT 0,  
                   values text,  
                   attrids text,  
                   fulltext tsvector,  
                   postitid int,
                   cvid int,
                   name text,
                   dprofid int DEFAULT 0,
                   prelid int DEFAULT 0,
                   atags text,
                   confidential int DEFAULT 0,
                   ldapdn text
                   );
create table docfrom ( id int not null,
                   primary key (id),
                   fromid int);
create table docname ( name text not null,
                   primary key (name),
                   id int,
                   fromid int);
create sequence seq_id_doc start 1000;
create sequence seq_id_tdoc start 1000000000;
create index i_docname on doc(name);
create unique index i_docir on doc(initid, revision);";

  // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  public $obj_acl = array (); // set by childs classes

  // --------------------------------------------------------------------

  /**
   * default view to view card
   * @public string
   */
  public $defaultview= "FDL:VIEWBODYCARD";
  /**
   * default view to edit card
   * @public string
   */
  public $defaultedit = "FDL:EDITBODYCARD";
  /**
   * default view for abstract card
   * @public string 
   */
  public $defaultabstract = "FDL:VIEWABSTRACTCARD";
  /**
   * for email : the same as $defaultview by default
   * @public string 
   */
  public $defaultmview = ""; 
 

  /**
   * use when family wants to define a special context menu
   * @public array
   */
  public $specialmenu=array();

 

  public $defDoctype='F';

  /**
   * to indicate values modification
   * @public bool 
   * @access private
   */
  private $hasChanged=false; 

  public $isCacheble= false;

  public $paramRefresh=array();

  /**
   * optimize: compute mask in needed only
   * @public bool 
   * @access private
   */
  private $_maskApplied=false; // optimize: compute mask if needed only
 
 



  /**
   * Increment sequence of family and call to {@see PostCreated()}
   * 
   * 
   * @return void
   */
final public function PostInsert()  {
      // controlled will be set explicitly
      //$this->SetControl();
      if (($this->revision == 0) && ($this->doctype != "T")) {
	// increment family sequence
	$this->nextSequence();
	$this->Addcomment(_("document creation"),HISTO_NOTICE,"CREATE");
      }
      $this->Select($this->id);
      // set creation date
      $this->cdate=$this->getTimeDate(0,true);
      $this->adate=$this->cdate;
      $date = gettimeofday();
      $this->revdate = $date['sec'];
      $this->modify(true,array("cdate","adate","revdate"),true); // to force also execute sql trigger
      if ($this->doctype != "T") {
	$err=$this->PostCreated(); 
	if ($err!="") AddWarningMsg($err);
	if ($this->dprofid >0) {
	  $this->setProfil($this->dprofid);// recompute profil if needed
	}
	$this->UpdateVaultIndex();
      }
    }  

  /**
   * set default values and creation date
   * the access control is provided by {@see createDoc()} function.
   * call {@see Doc::PreCreated()} method before execution
   * 
   * @return string error message, if no error empty string
   */
  final public function PreInsert() {

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
      $this->RefreshTitle();
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
      $this->version=$this->getVersion();

      if ($this->wid > 0) {
	$wdoc = new_Doc($this->dbaccess,$this->wid);
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
	$this->version=$this->getVersion();
	$this->lmodify='Y';
	//	$this->postModify(); // in modcard function
      }
      
    }

  /**
   * optimize for speed : memorize object for future use
   * @global array optimize for speed :: reference is not a pointer !!
   */
  function PostUpdate() {
    global $gdocs;// optimize for speed :: reference is not a pointer !!
    unset($gdocs[$this->id]); // clear cache
    if ($this->hasChanged) {
      $this->computeDProfil();
      $this->UpdateVaultIndex();
      $this->updateRelations();
    }
    $this->hasChanged=false;
  }

  /**
   * Set relation doc id use on docrel table
   */
  function updateRelations() {
    return; // for the moment
    include_once("FDL/Class.DocRel.php");
    $nattr = $this->GetNormalAttributes();
    $or=new DocRel($this->dbaccess);
    $or->sinitid=$this->initid;
    $or->resetRelations();

    foreach($nattr as $k=>$v) {
      if (isset($this->$k) && ($this->$k != "") && ($v->type=="docid")) {
	if ($v->inArray()) $tv=array_unique($this->getTValue($v->id));
	else $tv=array($this->$k);
	foreach ($tv as $val) {
	  $t=getTDoc($this->dbaccess,$val);
	  $or->cinitid=$t["initid"];
	  if ($or->cinitid>0) {
	    $or->ctitle=$t["title"];
	    $or->cicon=$t["icon"];
	    $or->stitle=$this->title;
	    $or->sicon=$this->icon;
	    $or->type=$v->id;
	    $or->Add();
	  }
	}
      }
    }
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
  final public function disableEditControl() {
    $this->withoutControl=true;
  }
  /**
   * default edit control enable
   */
  final public function enableEditControl() {
    unset($this->withoutControl);
  }
  /**
   * to know if the document can be revised
   *
   * @return bool true is revisable
   */
  final public function isRevisable() {
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
  final public function transfertValuesFrom(&$from) {
    
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
  final public function convert($fromid, $prevalues=array()) {
    
    if ($this->fromid  == $fromid) return false; // no convert if not needed
    $cdoc = createDoc($this->dbaccess, $fromid);
    if (! $cdoc) return false;
    if ($cdoc->fromid == 0) return false;
    $f1doc=$this->getFamDoc();
    $f1from=$f1doc->title."[".$f1doc->id."]";
    $f2doc=$cdoc->getFamDoc();
    $f2from=$f2doc->title."[".$f2doc->id."]";
    
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
    $cdoc->AddComment(sprintf(_("convertion from %s to %s family"),$f1from,$f2from));
			      
    
    return $cdoc;
    
  }

  /**
   * test if the document can be revised now
   * it must be locked by the current user
   * @return string empty means user can update else message of the raison
   */
  final public function CanUpdateDoc() {

    if ($this->locked == -1) {
      $err = sprintf(_("cannot update file %s (rev %d) : fixed. Get the latest version"), $this->title,$this->revision);      
      return $err;
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
   * test if the document can be edit by the current user
   * the diffence between ::canUpdateDoc is that document is not need to be locked
   * @return string empty means user can update else message of the raison
   */
  public function CanEdit() {

    if ($this->locked == -1) {
      $err = sprintf(_("cannot update file %s (rev %d) : fixed. Get the latest version"), $this->title,$this->revision);  
      return($err);     
    }

    if ($this->userid == 1) return "";// admin can do anything but not modify fixed doc
    $err="";
  
    
    if  (($this->locked != 0) && (abs($this->locked) != $this->userid)) {
	  
      $user = new User("", $this->locked);
      $err = sprintf(_("you are not allowed to update the file %s (rev %d) is locked by %s."), $this->title,$this->revision,$user->firstname." ".$user->lastname); 
	  
    } else $err = $this->Control("edit");
    
    
    return($err);
  }

  /**
   * test if the document can be locked
   * it is not locked before, and the current user can edit document
   * @return string empty means user can update else message of the raison
   */
  final public function CanLockFile() {
    $err="";
    
    if ($this->locked == -1) {
      
      $err = sprintf(_("cannot lock document %s [%d] (rev %d) : fixed. Get the latest version"), 
		     $this->title,$this->id,$this->revision);
    }  else {
      if ($this->userid == 1) return ""; // admin can do anything
      if ($this->locked == 0) $err = $this-> Control( "edit");
      // test if is not already locked
      else {
	if ( abs($this->locked) != $this->userid) {
	  $user = new User("", $this->locked);
	  $err = sprintf(_("cannot lock file %s [%d] : already locked by %s."), 
			 $this->title,$this->id,$user->firstname." ".$user->lastname);
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
   * @return string empty means user can update else message of the raison
   */
  final public function CanUnLockFile() {
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
   * @param bool $my if true test if it is lock of current user
   * 
   * @return bool true if locked. If $my return true if it is locked by another user
   */
  final public function isLocked($my=false) {
    if ($my) {      
      if (($this->user->id == 1) || (abs($this->locked) == $this->userid)) return false;
    }
    return (($this->locked > 0) || ($this->locked < -1));
  }

  /** 
   * test if the document is confidential
   * 
   * @return bool true if confidential and current user is not authorized
   */
  final public function isConfidential() {
    return (($this->confidential > 0) && ($this->control('confidential')!=""));
  }
  /** 
   * return the family document where the document comes from
   * 
   * @return Doc
   */
  final public function getFamDoc() {
    if (! isset($this->famdoc)||($this->famdoc->id != $this->fromid)) $this->famdoc= new_Doc($this->dbaccess, $this->fromid);
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
  public function getParamValue($idp, $def="") {
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
  final public function GetDocWithSameTitle($key1="title",$key2="") {
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
  final public function getRevisionState($state) {    
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
  final public function DeleteTemporary() {
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
      if ($this->isLocked(true)) return _("locked");
      $err = $this->Control("delete");
                        
      return $err;      
    }

  /** 
   * Really delete document from database
   * @return string error message, if no error empty string
   */
  final public function ReallyDelete($nopost) {
    $err= DbObj::delete($nopost);
    if ($err=="") {      
      $dvi = new DocVaultIndex($this->dbaccess);
      $err = $dvi->DeleteDoc($this->id);
    }
    return $err;
  }

  /** 
   * Set the document to zombie state
   * For the user the document is in the trash
   * @param bool $really if true call {@link ReallyDelete} really delete from database
   * @param bool $control if false don't control 'delete' acl
   * @param bool $nopost if true don't call {@link PostDelete} and {@link PreDelete}
   * @return void
   */
  final public function Delete($really=false,$control=true,$nopost=false) {

    if ($control) {
      // Control if the doc can be deleted
      $msg = $this->PreDocDelete();
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

      if ($this->doctype != 'Z') {
	$this->doctype='Z'; // Zombie Doc
	$this->locked= -1; 
	$date = gettimeofday();
	$this->revdate = $date['sec']; // Delete date
	$this->owner=$this->userid; // to the trash of the current user

	global $action;
	global $_SERVER;
	$this->AddComment(sprintf(_("delete by action %s/%s from %s"),
				  $action->parent->name,$action->name,
				  $_SERVER["REMOTE_ADDR"]),HISTO_NOTICE);
	$this->AddComment(_("document deleted"));


	if (!$nopost) $msg=$this->PostDelete();
	$this->modify(true,array("doctype","revdate","locked","owner"),true);

	// delete all revision also
	$rev=$this->GetRevisions();
	foreach($rev as $k=>$v) {	
	  if ($v->doctype != 'Z') {
	    $v->doctype='Z'; // Zombie Doc
	    if ($v->locked == -1)  $v->modify(true,array("doctype"),true);
	  }	    
	}
      }
      return $msg;
    }
  }


  /** 
   * To restore a document which is in the trash
   * @return void
   */
  final public function revive() { 
    if (($this->owner == $this->userid) || ($this->userid==1)) {
      if (! $this->isAlive()) {
	$this->doctype=$this->defDoctype;
	$this->locked=0;
	$this->modify(true,array("doctype","locked"),true);
	$this->AddComment(_("revival document"));
	
	$rev=$this->GetRevisions();

	foreach($rev as $k=>$v) {
	  if ($v->doctype=='Z') {
	    $v->doctype=$v->defDoctype;
	    $v->modify(false,array("doctype"),false);
	  }
	}

      } else return sprintf(_("document %s [%d] is not in the trash"),$doc->title,$doc->id);
    } else return _("Only owner of document can restore it");
  }

  /** 
   * Adaptation of affect Method from DbObj because of inheritance table
   * this function is call from QueryDb and all fields can not be instanciate
   * @param array $array the data array
   * @param bool $more add values from values attributes needed only if cast document
   * @return void
   */
  final public function Affect($array,$more=false) { 
    if (is_array($array)) {
      if ($more)  $this->ResetMoreValues();   
      unset($this->uperm); // force recompute privileges
      foreach($array as $k=>$v) {
	if (!is_integer($k)) {
	  $this->$k = $v;
	}
      }
      $this->Complete();
      if ($more)  $this->GetMoreValues();
      
      $this->isset = true;
    }
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
  final public function GetFathersDoc() {
    // -------------------------------------------------------------------- 
    // Return array of father doc id : class document 
    if (! isset($this->fathers)) {

      $this->fathers=array();
      if ($this->fromid > 0) {
	$fdoc= $this->getFamDoc();
	$this->fathers=$fdoc->GetFathersDoc();
	array_push($this->fathers,$this->fromid);
      }
    }
    return $this->fathers;
  }
  
  /**
   * Return array of fathers doc id : class document 
   * @return array
   */
  final public function GetFromDoc() {   
    return $this->attributes->fromids;
  }

  /**
   * Return array of child doc id : class document 
   * @return array
   */
  final public function GetChildFam($id=-1, $controlcreate=false) {        
    if ($id == 0) return array();
    if (($id!=-1) || (! isset($this->childs))) {

      if ($id==-1) 	$id= $this->id;	
      if (! isset($this->childs)) $this->childs=array();
      $query = new QueryDb($this->dbaccess, "DocFam");
      $query->AddQuery("fromid = ".$id);
      if ($controlcreate) $query->AddQuery("hasdocprivilege(".$this->userid.",profid,".(1<<intval(POS_ICREATE)).")");
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

  /**
   * return all revision documents
   */
  final public function GetRevisions($type="LIST",$limit=200) {
    // Return the document revision 
    $query = new QueryDb($this->dbaccess, strtolower(get_class($this)));

      
    //$query->AddQuery("revision <= ".$this->revision);
    $query->AddQuery("initid = ".$this->initid);
    $query->order_by="revision DESC LIMIT $limit";
      
    $rev= $query->Query(0,0,$type);
    if ($query->nb == 0) return array();
    return $rev;
  }

  /** get Latest Id of document
   * 
   * @param bool $fixed if true latest fixed revision
   * @param bool $forcequery if true force recompute of id (use it in case of modification by another program)
   * @return int identificator of latest revision
   */
  final public function latestId($fixed=false,$forcequery=false) {
    if ($this->id == "") return false;
    if (!$forcequery) {
      if (($this->locked != -1) && (!$fixed)) return $this->id;
      if ($fixed && ($this->lmodify == "L")) return $this->id;
    }
    $query = new QueryDb($this->dbaccess, strtolower(get_class($this)));
    $query->AddQuery("initid = ".$this->initid);
    if ($fixed) $query->AddQuery("lmodify = 'L'");
    elseif ($this->doctype!='Z') $query->AddQuery("locked != -1");
    else {
      $query->order_by="id desc";
    }
      
    $rev= $query->Query(0,1,"TABLE");

    return $rev[0]["id"];
  }

  /**
   * get version of document 
   * must be redefined by child document classes if needed
   * @return string
   */
  final public function getVersion() { 
    $tversion=array();
    if (isset($this->attributes->attr)) {
      foreach($this->attributes->attr as $k=>$v) {
	if ((get_class($v) == "NormalAttribute") && ($v->getOption("version") == "yes")) {
	  $tversion[]=$this->getValue($v->id);
	}
      }
    }
    $version=implode(" ",$tversion);
    return $version;
  }


  /**
   * return the string label text for a id
   * @return string
   */
  final public function GetLabel($idAttr)  {
      if (isset($this->attributes->attr[$idAttr])) return $this->attributes->attr[$idAttr]->labelText;
      return _("unknow attribute");
    }

  
  /** 
   * return the attribute object for a id
   * the attribute can be defined in fathers
   * @param string $idAttr attribute identificator
   * @return DocAttribute
   */
  final public function &GetAttribute($idAttr)   {      
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
  final public function GetAttributes()     {     
      if (!$this->_maskApplied) $this->ApplyMask();
      reset($this->attributes->attr);
      return $this->attributes->attr;
    }


  /**
   * set visibility mask
   *
   * @param int $mid mask ident
   */
  final public function setMask($mid) {
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
  final public function ApplyMask($mid = 0) {
    
    // copy default visibilities
    if (isset($this->attributes->attr)) {

      foreach($this->attributes->attr as $k=>$v) {
	//	if (is_object($v))
	$this->attributes->attr[$k]->mvisibility=ComputeVisibility($v->visibility,$v->fieldSet->mvisibility);

      }
    }

    $this->_maskApplied=true;
    // modify visibilities if needed
    if ((! is_numeric($mid)) && ($mid!="")) $mid=getIdFromName($this->dbaccess,$mid);  
    if ($mid == 0) $mid=$this->mid;
    if ($mid == 0) {
      if (($this->wid > 0) && ($this->wid != $this->id)) {
	// search mask from workflow
	$wdoc=new_Doc($this->dbaccess,$this->wid);
	if ($wdoc->isAlive()) {
	  if ($this->id == 0) {	  
	    $wdoc->set($this);
	  }
	  $mid = $wdoc->getValue($wdoc->attrPrefix."_MSKID".$this->state);
	  if ((! is_numeric($mid)) && ($mid!="")) $mid=getIdFromName($this->dbaccess,$mid);  
	}      
      }	
    }
    if ($mid > 0) { 

      $mdoc = new_Doc($this->dbaccess,$mid );
      if ($mdoc->isAlive()) {
	$tvis = $mdoc->getCVisibilities();
	  
	while (list($k,$v)= each ($tvis)) {
	  if (isset($this->attributes->attr[$k])) {
	    if ($v != "-") $this->attributes->attr[$k]->mvisibility=$v;	      
	  }
	}
	$tdiff=array_diff(array_keys($this->attributes->attr),array_keys($tvis));
	// recompute loosed attributes
	foreach	($tdiff	as $k)	{
	  $v=$this->attributes->attr[$k];
	  $this->attributes->attr[$k]->mvisibility=ComputeVisibility($v->visibility,$v->fieldSet->mvisibility);
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
    uasort($this->attributes->attr,"tordered"); 
  }

  /**
   * return all the attributes except frame & menu & action
   * 
   * @return array DocAttribute
   */
  final public function GetNormalAttributes($onlyopt=false)
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
      if ((isset($this->attributes)) && (method_exists($this->attributes,"GetNormalAttributes")))
	return $this->attributes->GetNormalAttributes($onlyopt);      
      else return array();
    } 


  /**
   * return  frame attributes  
   * 
   * @return array FieldSetAttribute
   */
  final public function GetFieldAttributes() {      
      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();
           
      foreach($this->attributes->attr as $k=>$v) {
	if (get_class($v) == "FieldSetAttribute")  $tsa[$v->id]=$v;
      }
      return $tsa;      
    }



  /**
   * return action attributes  
   * 
   * @return array ActionAttribute
   */
  final public function GetActionAttributes() {      
      if (!$this->_maskApplied) $this->ApplyMask();
      
      return $this->attributes->GetActionAttributes();      
    }
  /**
   * return all the attributes object for abstract
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  final public function GetAbstractAttributes()
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();

      if (isset($this->attributes->attr)) {
	foreach($this->attributes->attr as $k=>$v) {
	  if ((get_class($v) == "NormalAttribute")&&($v->isInAbstract)) $tsa[$v->id]=$v;
	}
      }
      return $tsa;      
    }

  

  /**
   * return all the attributes object for title
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  final public function GetTitleAttributes() { 
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
    if (isset($this->attributes->attr)) {
      foreach($this->attributes->attr as $k=>$v) {
	if ((get_class($v) == "NormalAttribute") && ($v->isInTitle)) $tsa[$v->id]=$v;      
      }
    }
    return $tsa;
  }

  /**
   * return all the attributes that can be use in profil
   * 
   * @return array DocAttribute
   */
  final public function GetProfilAttributes() { 
    if (!$this->_maskApplied) $this->ApplyMask();
    $tsa=array();
    if (isset($this->attributes->attr)) {
      foreach($this->attributes->attr as $k=>$v) {
	if ((get_class($v) == "NormalAttribute") && ($v->type=="docid") && (!$v->inArray())) $tsa[$v->id]=$v;      
      }
    }
    return $tsa;
  }


  /** 
   * return all the attributes object for to e use in edition
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  final public function GetInputAttributes($onlyopt=false)
    { 
      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();


      foreach($this->attributes->attr as $k=>$v) {
	if ((get_class($v) == "NormalAttribute") && (!$v->inArray()) && 
	    ($v->mvisibility != "I" )) {  // I means not editable
	  if ((($this->usefor=="Q") && ($v->usefor=="Q")) ||
	      (($this->usefor!="Q") && 
	       ((($v->usefor!="Q")&&(!$onlyopt)) || (($v->usefor=="O")&&($onlyopt))  )))
	    $tsa[$v->id]=$v;    //special parameters
	}
      }
      return $tsa;
    }
  /** 
   * return all the parameters definition for its family
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  final public function getParamAttributes()    { 
     
      if (!$this->_maskApplied) $this->ApplyMask();
      if ((isset($this->attributes)) && (method_exists($this->attributes,"getParamAttributes")))
	return $this->attributes->getParamAttributes();      
      else return array();
    }


  /**
   * return all the attributes object for abstract
   * the attribute can be defined in fathers
   * @return array DocAttribute
   */
  final public function GetFileAttributes()
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();
      
      foreach($this->attributes->attr as $k=>$v) {
	if ((get_class($v) == "NormalAttribute") && (($v->type == "image") || 
						     ($v->type == "file"))) $tsa[$v->id]=$v;
      }
      return $tsa;      
    }


  /**
   * return files properties of file attributes
   * 
   * @return array 
   */
  final public function GetFilesProperties() {      
    $dvi = new DocVaultIndex($this->dbaccess);
    $tvid=$dvi->getVaultIds($this->id);
    $tinfo=array();
    $vf = newFreeVaultFile($this->dbaccess);
    foreach ($tvid as $vid) {
      $err=$vf->Retrieve($vid, $info);
      $t=get_object_vars($info);
      $t["vid"]=$vid;
      if ($err=="") $tinfo[]=$t;
    }

    return $tinfo;
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
      foreach($this->attributes->attr as $k=>$v) {
	if (((get_class($v) == "MenuAttribute"))&&($v->visibility != 'H')) $tsa[$v->id]=$v;
	  
	
      }
      return $tsa;
    }

  /**
   * return all the necessary attributes 
   * @return array DocAttribute
   */
  final public function GetNeededAttributes()
    {         
      if (!$this->_maskApplied) $this->ApplyMask();   
      $tsa=array();
      

      foreach($this->attributes->attr as $k=>$v) {
	  if ((get_class($v) == "NormalAttribute") && ($v->needed) && ($v->usefor!='Q')) $tsa[$v->id]=$v;
      }
      
      return $tsa;
    }

  final public function isCompleteNeeded() {
    $tsa=$this->GetNeededAttributes();
    $err="";
    while (list($k,$v) = each($tsa)) {
      if ($this->getValue($v->id) == "") $err .= sprintf(_("%s needed\n"),$v->labelText);
    }
    return $err;
  }


 
  /**
   * return list of attribut which can be exported
   * @param bool $withfile true if export also file attribute
   * @return array DocAttribute
   */
  final public function GetExportAttributes($withfile=false)
    {      
      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();
     
      if (isset($this->attributes->attr)) {
	foreach($this->attributes->attr as $k=>$v) {	  
	  if (get_class($v) == "NormalAttribute")  {
	    if ($withfile || (($v->type != "image") &&($v->type != "file")))  $tsa[$v->id]=$v;
	  }
	}
      }
      return $tsa;      
    } 

  /**
   * return all the attributes object for import
   * @return array DocAttribute
   */
  final public function GetImportAttributes()
    {      

      if (!$this->_maskApplied) $this->ApplyMask();
      $tsa=array();
      $tattr = $this->attributes->attr;

      foreach($tattr as $k=>$v) {

	if ((get_class($v) == "NormalAttribute") && 
	    (($v->mvisibility == "W") || ($v->mvisibility == "O") || ($v->type == "docid")) &&
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
  final public function GetSortAttributes()  {      
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
  final public function RefreshTitle() {

    if ($this->doctype == 'C') return; // no refresh for family  document

    $ltitle = $this->GetTitleAttributes();

    $title1 = "";
    while(list($k,$v) = each($ltitle)) {
      if ($this->GetValue($v->id) != "") {
	if ($v->type=="enum") $title1.= $this->GetHtmlValue($v,$this->GetValue($v->id))." ";
	else $title1.= $this->GetValue($v->id)." ";
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
   * call when doc is being imported before any modification
   * if return non null string import will ne aborted
   * @return string error message, if no error empty string
   */
  function preImport() {}
  /**
   * call when doc is imported after databases modification
   * the error message will appeared like message
   * @return string warning message, if no warning empty string
   */
  function postImport() {}

  /**
   * recompute values from title
   * the first value use for title will be modify to have the new title
   * @param string $title new title
   */
  final public function SetTitle($title) {
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
  final public function GetValues()  {
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
  final public function GetValue($idAttr, $def="")  {      
    
    $lidAttr=strtolower($idAttr);
    if (isset($this->$lidAttr) && ($this->$lidAttr != "")) return $this->$lidAttr;
         
    return $def;
  }

  /**
   * return the value of an list attribute document
   *
   * the attribute must be in an array or of a type '*list' like enumlist or textlist
   * @param string $idAttr identificator of list attribute 
   * @param string $def default value returned if attribute not found or if is empty
   * @param string $index the values for $index row (default value -1 means all values)
   * @return array the list of attribute values 
   */
  final public function GetTValue($idAttr, $def="",$index=-1)  { 
    $v=$this->getValue("$idAttr",$def);
    if ($v == "") {
     if ($index == -1) return array();
     else return $def;
    }
    $t = $this->_val2array($v);
    if ($index == -1) return $t;
    if (isset($t[$index])) return $t[$index];
    else return $def;
  }

  /**
   * return the array of values for an array attribute
   *
   * the attribute must  an array type
   * @param string $idAttr identificator of array attribute 
   * @param string $index the values for $index row (default value -1 means all values)
   * @return array all values of array order by rows (return false if not an array attribute)
   */
  final public function GetAValues($idAttr, $index=-1)  { 
    $a=$this->getAttribute($idAttr);
    if ($a->type=="array") {
      $ta=$this->attributes->getArrayElements($a->id);
      $ti=array();
      // transpose
      foreach($ta as $k=>$v) {
        $tv[$k]=$this->getTValue($k);
	$ix=max($ix,count($tv[$k]));
      }
      for ($i=0;$i<$ix;$i++) {
        $ti[$i]=array();
      }
      foreach($ta as $k=>$v) {
        for ($i=0;$i<$ix;$i++) {
          $ti[$i]+=array($k=>$tv[$k][$i]);
        }
      }    
      if ($index==-1) return $ti;
      else return $ti[$index];
    }
    return false;        
  }

  /**
   * affect value for $attrid attribute
   *
   * the affectation is only in object. To set modification in database the modify method must be 
   * call after modification
   * If value is empty no modification are set. To reset a value use Doc::DeleteValue method.
   * an array can be use as value for values which are in arrays
   * @param string $idAttr identificator of attribute 
   * @param string $value new value for the attribute
   * @return string error message, if no error empty string
   */
  final public function SetValue($attrid, $value) {
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
      if ($oattr === false) return sprintf(_("attribute %s unknow in family %s [%d]"),$attrid, $this->title, $this->id);
      if ($oattr->mvisibility=="I") return sprintf(_("no permission to modify this attribute %s"),$attrid);
      if ($value === " ") {
	$value=""; // erase value
	if  ($this->$attrid != "") {
	  $this->hasChanged=true;
	  //print "change by delete $attrid  <BR>";
	  $this->$attrid="";
	}
      } else {

	$value=trim($value," \x0B\r");// suppress white spaces end & begin
	if (!isset($this->$attrid)) $this->$attrid="";

	if  ($this->$attrid != $value) 	  {
	  $this->hasChanged=true;
	  //	  print "change $attrid  to <PRE>[{$this->$attrid}] [$value]</PRE><BR>";
	

	if ($oattr->repeat) {
	  $tvalues = $this->_val2array($value);
	} else {
	  $tvalues[]=$value;
	}
    

	while (list($kvalue, $avalue) = each($tvalues)) {
	  if ($avalue != "") {
	    if ($oattr) {
	      switch($oattr->type) {
	      case 'docid':
		if  (!is_numeric($avalue)) {		  
		  $tvalues[$kvalue]=getIdFromName($this->dbaccess,$avalue);
		}
		break;
	      case 'double':
	      case 'money':
		$tvalues[$kvalue]=str_replace(",",".",$avalue);
		$tvalues[$kvalue]=str_replace(" ","",$tvalues[$kvalue]);
		$tvalues[$kvalue]=round(doubleval($tvalues[$kvalue]),2);
		break;
	      case 'integer':
	      case 'int':
		$tvalues[$kvalue]=intval($avalue);
		break;
	      case 'time':
		$tt=explode(":",$avalue);
		if (count($tt)==2) {
		  list($hh,$mm) = $tt;
		  $tvalues[$kvalue]=sprintf("%02d:%02d",intval($hh)%24,intval($mm)%60);
		} else if (count($tt)==3) {
		  list($hh,$mm,$ss) = $tt;
		  $tvalues[$kvalue]=sprintf("%02d:%02d:%02d",intval($hh)%24,intval($mm)%60,intval($ss)%60);		  
		}
		break;
	      case 'date':
		list($dd,$mm,$yy) = explode("/",$avalue);
		if (($mm == 0) || ($dd == 0)) list($yy,$mm,$dd) = explode("-",$avalue); // iso8601
		$yy = intval($yy);
		$mm = intval($mm); 
		$dd = intval($dd); 
	      
		if (($mm == 0) || ($dd == 0)) AddWarningMsg(sprintf(_("the date '%s' for %s attribute is not correct. It has been corrected automatically"),$avalue,$oattr->labelText));
		if ($mm == 0) $mm=1; // 1st january
		if ($dd == 0) $dd=1; // 1st day
		//	$tvalues[$kvalue]=sprintf("%04d-%02d-%02d", ($yy<30)?2000+$yy:(($yy<100)?1900+$yy:$yy),$mm,$dd);
		$tvalues[$kvalue]=sprintf("%02d/%02d/%04d",$dd,$mm, ($yy<30)?2000+$yy:(($yy<100)?1900+$yy:$yy));
		break;
	      }
	    }
	  }
	}
	
	//   print $oattr->id."-".$oattr->type;print_r2($tvalues);
	$this->_oldvalue[$attrid]=$this->$attrid;
	$this->$attrid=implode("\n",$tvalues); 

	}
	
      }      
    }
  }

   /**
   * affect text value in $attrid file attribute
   *
   * create a new file in Vault to replace old file
   * @param string $idAttr identificator of file attribute 
   * @param string $value new value for the attribute
   * @param string $ftitle the name of file (if empty the same as before)
   * @return string error message, if no error empty string
   */
  final public function SetTextValueInFile($attrid, $value,$ftitle="") {   
    $a=$this->getAttribute($attrid);     
    if ($a->type == "file") {
      $err="file conversion";
      $vf = newFreeVaultFile($this->dbaccess);
      $fvalue=$this->getValue($attrid);
      $basename="";
      if (ereg ("(.*)\|(.*)", $fvalue, $reg)) {
	$vaultid= $reg[2];
	$mimetype=$reg[1];
	
	$err=$vf->Retrieve($vaultid, $info);

	if ($err == "") {
	  $basename=$info->name;
	}
      }
      $filename=uniqid("/tmp/_html").".html";
      $nc=file_put_contents($filename,$value);
      $err=$vf->Store($filename, false , $vid);
      if ($ftitle != "") {
	$vf->Rename($vid,$ftitle);
      } else {
	if ($basename!="") { // keep same file name
	  $vf->Rename($vid,$basename);
	}
      }
      if ($err == "") {
	$mime=trim(`file -ib $filename`);
	$value="$mime|$vid";
	$err=$this->setValue($attrid,$value);
	//$err="file conversion $mime|$vid";
      }
      if ($nc>0) unlink($filename);	     
    } 	
    return $err;
  } 
   /**
   * get text value from $attrid file attribute
   *
   * get content of a file (must be an ascii file)
   * @param string $idAttr identificator of file attribute 
   * @param string &$text the content of the file
   * @return string error message, if no error empty string
   */
  final public function getTextValueFromFile($attrid, &$text) {   
    $a=$this->getAttribute($attrid);     
    if ($a->type == "file") {
      $vf = newFreeVaultFile($this->dbaccess);
      $fvalue=$this->getValue($attrid);
      $basename="";
      if (ereg ("(.*)\|(.*)", $fvalue, $reg)) {
	$vaultid= $reg[2];
	$mimetype=$reg[1];
	
	$err=$vf->Retrieve($vaultid, $info);

	if ($err == "") {
	  $basename=$info->name;
	}
      }
      $filename=$info->path;
      $text=file_get_contents($filename);  
    } 	
    return $err;
  } 

  /**
   * save stream file in an file attribute
   *
   * replace a new file in Vault to replace old file
   * @param string $idAttr identificator of file attribute 
   * @param stream $stream file resource from fopen
   * @return string error message, if no error empty string
   */
  final public function saveFile($attrid, $stream,$ftitle="") {   
    if (is_resource($stream) && get_resource_type($stream) == "stream") {


    $a=$this->getAttribute($attrid);     
    if ($a->type == "file") {
      $err="file conversion";
      $vf = newFreeVaultFile($this->dbaccess);
      $fvalue=$this->getValue($attrid);
      $basename="";
      if (ereg ("(.*)\|(.*)", $fvalue, $reg)) {
	$vaultid= $reg[2];
	$mimetype=$reg[1];
	
	$err=$vf->Retrieve($vaultid, $info);

	if ($err == "") {
	  $basename=$info->name;
	}
      }
      if ($ftitle) {
	$path_parts = pathinfo($ftitle);
	$ext=$path_parts['extension'];
      } 
      if ($ext=="") $ext="nop";
      
      $filename=uniqid("/var/tmp/_fdl").".$ext";
      $tmpstream=fopen($filename,"w");
      while (!feof($stream)) {
	if (false === fwrite($tmpstream, fread($stream, 4096))) {
	  $err = "403 Forbidden"; 
	  break;
	}
      }
      fclose($tmpstream);
      // verify if need to create new file in case of revision
      $newfile=($basename=="");

      if ($this->revision > 0) {
	$trev=$this->GetRevisions("TABLE",2);
	$revdoc=$trev[1];
	$prevfile=getv($revdoc,strtolower($attrid));
	if ($prevfile == $fvalue) $newfile=true;
	error_log("FDL STORE PREV : $prevfile - ".$revdoc["id"]);
      }

      if (! $newfile) {
	$err=$vf->Save($filename, false , $vaultid);
	error_log("FDL SAVE :".$filename."-".$vaultid);
      } else {
	$err=$vf->Store($filename, false , $vaultid);
	error_log("FDL STORE [$err]:".$filename."-".$vaultid);	
      }
      if ($ftitle != "") {
	$vf->Rename($vaultid,$ftitle);
      } else {
	if ($basename!="") { // keep same file name
	  $vf->Rename($vaultid,$basename);
	}
      }
      if ($err == "") {
	$mime=trim(`file -ib $filename`);
	$value="$mime|$vaultid";
	$err=$this->setValue($attrid,$value);

	//$err="file conversion $mime|$vid";
      }
      unlink($filename);
      $this->AddComment(sprintf(_("modify file %s"),$ftitle));
    } 	
    }
    return $err;
  }

  /**
   * return the related value by linked attributes
   */
  final public function GetRValue($RidAttr, $def="",$latest=true)  {      
    
    $tattrid = explode(":",$RidAttr);
    $lattrid=array_pop($tattrid); // last attribute

    $doc=$this;
    reset($tattrid);
    while(list($k,$v) = each($tattrid)) { 
      $docid= $doc->getValue($v);
      if ($docid == "") return $def;
      $doc = new_Doc($this->dbaccess, $docid);
      if ($latest) {
	if ($doc->locked == -1) { // it is revised document
	  $ldocid = $doc->latestId();
	  if ($ldocid != $doc->id) $doc = new_Doc($this->dbaccess, $ldocid);
	}
      }

      if (! $doc->isAlive())  return $def;

    }

    return $doc->getValue($lattrid, $def);


  }
  

  /**
   * return the previous value for a attibute set before Doc::SetValue
   * can be use in Doc::postModify generaly
   * @param string $attrid identificator of attribute 
   * @return string the old value (false if not modified before)
   * 
   */
  final public function getOldValue($attrid) {
    if (isset($this->_oldvalue[$attrid])) return $this->_oldvalue[$attrid];
    return false;
  }
  final public function getOldValues() {
    if (isset($this->_oldvalue)) return $this->_oldvalue;
    return false;
  }


  final public function DeleteValue($attrid) {
    return $this->SetValue($attrid," ");
  }


  /**
   * add values present in values field
   */
  private function GetMoreValues()  {      
    if (isset($this->values)) {
      $tvalues = explode("£",$this->values);
      $tattrids = explode("£",$this->attrids);
      
      foreach($tvalues as $k=>$v) {
	$attrid = $tattrids[$k];	
	if (($attrid != "") &&  ($this->$attrid == "")) {
	    $this->$attrid=$v;
	    $this->mvalues[$attrid]=$v; // to be use in getValues()
	  }
      }      
    }      
  }

  /**
   * reset values present in values field
   */
  private function ResetMoreValues()  {      
    if (isset($this->values) && $this->id) {
      $tattrids = explode("£",$this->attrids);
      
      while(list($k,$v) = each($tattrids)) {
	$attrid = $tattrids[$k];
	if ($attrid) $this->$attrid="";
      }
    } 
    $this->mvalues=array();
  }

  final public function GetValueMethod($value, $attrid='') {
    
    $value=$this->ApplyMethod($value,$value);
    
    return $value;
  } 

  final public function ApplyMethod($method,$def="",$index=-1) {
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
  final public function verifyConstraint($attrid, $index=-1) {
    $ok=array("err"=>"",
	      "sug"=>array());
    $oattr = $this->getAttribute($attrid);
    if (trim($oattr->phpconstraint) != "") {

       $res = $this->applyMethod($oattr->phpconstraint,'KO',$index);
       if ($res !== true) return $res;
    }

    return $ok;
     
  }


  /**
   * verify if constraint ore OK
   * @return string error message (empty means no error)
   */
  final public function verifyAllConstraints() {
    $err="";
    $listattr = $this->GetNormalAttributes();
    foreach ($listattr as $k => $v) {
      if ($v->phpconstraint != "") {
	if ($v->inArray()) {
	  $tv = $this->getTValue($v->id);
	  for ($i=0;$i<count($tv);$i++) {
	    $res= $this->verifyConstraint($v->id,$i);
	    if ($res["err"]!="") return sprintf(_("constraint return [%s] for %s attribute"),
						$res["err"],$v->labelText);
	  }
	} else {
	  $res= $this->verifyConstraint($v->id);
	  //	  print print_r2($res);
	  if ($res["err"]!="") return sprintf(_("constraint return [%s] for %s attribute"),
						$res["err"],$v->labelText);
	}
      }
    }
    return $err;
  }
  /** return the first attribute of type 'file'
   * @return Attribute 
   */
  final public function GetFirstFileAttributes()
    {
      $t =  $this->GetFileAttributes();
      if (count($t) > 0) return current($t);
      return false;      
    }

  /**
   * Add a comment line in history document
   * note : modify is call automatically
   * @param string $comment the comment to add
   */
  final public function AddCommentOld($comment='') {
    global $action;
    $commentdate = sprintf("%s [%s %s] %s",
			   date("d/m/Y H:i"),
			   $action->user->firstname,$action->user->lastname,
			   $comment);

    if ($this->comment != '') $this->comment = $commentdate."\n".$this->comment;
    else $this->comment = $commentdate;
    if ($this->isAlive()) $this->modify(true,array("comment"),true);
  }
  /**
   * Add a comment line in history document
   * note : modify is call automatically
   * @param string $comment the comment to add
   * @param string $level level of comment
   * @param string $code use when memorize notification
   * @param string $uid user identificator : by default its the current user
   */
  final public function AddComment($comment='',$level=HISTO_INFO,$code='',$uid='') {
    global $action;
    if ($this->id=="") return;

    $h=new DocHisto($this->dbaccess);

    $h->id=$this->id;
    $h->initid=$this->initid;
    if (isUTF8($comment)) $comment=utf8_decode($comment);
    $h->comment=$comment;
    $h->date=date("d-m-Y H:i:s");
    if ($uid > 0) {
      $u=new User("",$uid);
      $h->uid=$u->id;
      $h->uname=sprintf("%s %s",$u->firstname,$u->lastname);      
    } else {
      $h->uname=sprintf("%s %s",$action->user->firstname,$action->user->lastname);
      $h->uid=$action->user->id;
    }
    $h->level=$level;
    $h->code=$code;

    $err=$h->Add();
    return $err;
  }
  /**
   * Get history for the document
   * @param bool $all set true if want for all revision
   * 
   * @return array of different comment
   */
  public function getHisto($allrev=false) {    
    include_once("Class.QueryDb.php");
    $q=new QueryDb($this->dbaccess,"dochisto");
    if ($allrev) $q->AddQuery("initid=".$this->initid);
    else $q->AddQuery("id=".$this->id);
    $q->order_by="date desc";
    $l=$q->Query(0,0,"TABLE");

    if (is_array($l))  return $l;
    return array();
  }
  /**
   * Add a application tag for the document
   * if it is already set no set twice
   * @param string $atg the tag to add
   */
  final public function AddATag($tag) {
    if ($this->atags == "") {
      $this->atags =  $tag;
    } else {
      $this->atags .= "\n$tag";
      // not twice
      $tmeth = explode("\n",$tag);
      $tmeth=array_unique($tmeth);
      $this->atags =  implode("\n",$tmeth);
    }    
  } 

  /**
   * Add a user tag for the document
   * if it is already set no set twice
   * @param int the system user identificator
   * @param string $tag the tag to add
   */
  final public function addUTag($uid,$tag,$comment="") { 
    if ($tag == "") return _("no user tag specified");
    $this->delUTag($uid,$tag);

    global $action;
    $h=new DocUTag($this->dbaccess);

    $h->id=$this->id;
    $h->initid=$this->initid;
  
    $h->date=date("d-m-Y H:i:s");
    if ($uid > 0) {
      $u=new User("",$uid);
      $h->uid=$u->id;
      $h->uname=sprintf("%s %s",$u->firstname,$u->lastname);      
    } 
    $h->fromuid=$action->user->id;
      
    $h->tag=$tag;
    $h->comment=$comment;

    $err=$h->Add();
    return $err;
  } 

  /**
   * Test if current user has the u tag specified
   * 
   * @param string $tag the tag to verify
   */
  final public function hasUTag($tag) {
    include_once("FDL/Class.DocUTag.php");
    $utag=new DocUTag($this->dbaccess,array($this->initid,$this->userid,$tag));
    return $utag->isAffected();
  }

  /**
   * Get current user tag specified
   * 
   * @param string $tag the tag to verify
   */
  final public function getUTag($tag) {
    include_once("FDL/Class.DocUTag.php");
    $utag=new DocUTag($this->dbaccess,array($this->initid,$this->userid,$tag));
    if ($utag->isAffected()) return $utag;
    return false;
  }
  

  /**
   * Remove a user tag for the document
   * if it is already set no set twice
   * @param int $uid the system user identificator
   * @param string $atg the tag to add
   */
  final public function delUTag($uid,$tag) { 
    if ($tag == "") return _("no user tag specified");
    include_once("FDL/Class.DocUTag.php");
    global $action;
    $h=new DocUTag($this->dbaccess,array($this->initid,$uid,$tag));
    if ($h->isAffected()) {
      $err=$h->delete();
    } else {
      return false;
    }

    return $err;
  }
  /**
   * Remove all user tag for the document
   * 
   * @param int $uid the system user identificator
   * @param string $atg the tag to add
   */
  final public function delUTags($tag) { 
    if ($tag == "") return _("no user tag specified");
    include_once("FDL/Class.DocUTag.php");
    $q=new QueryDb($this->dbaccess,"docUTag");
    $q->Query(0,0,"TABLE",
	      "delete from docutag where initid=".$this->initid);
    

    return $err;
  }
  /**
   * Refresh all user tag for the document in case of revision
   * 
   */
  final public function refreshUTags() { 
    include_once("FDL/Class.DocUTag.php");
    $q=new QueryDb($this->dbaccess,"docUTag");
    $q->Query(0,0,"TABLE",
	      sprintf("update docutag set id=%d where initid=%d",$this->id,$this->initid));

    return $err;
  }
  /**
   * Create a new revision of a document
   * the current document is revised (became a fixed document)
   * a new revision is created
   * @param string $comment the comment of the revision
   * @return string error text (empty if no error)
   */
  final public function addRevision($comment='') {

    if ($this->locked == -1) return _("document already revised");

    $fdoc = $this->getFamDoc();
   
    if ($fdoc->schar == "S") return sprintf(_("the document of %s family cannot be revised"),$fdoc->title);
    $locked=$this->locked;
    $allocated=$this->allocated;
    $postitid = $this->postitid; // transfert post-it to latest revision

    $this->locked = -1; // the file is archived
    $this->lmodify = 'N'; // not locally modified
    $this->allocated = 0; // cannot allocated fixed document
    $this->owner = $this->userid; // rev user 
    $this->postitid=0;
    $date = gettimeofday();
    $this->revdate = $date['sec']; // change rev date
    if ($comment != '') $this->Addcomment($comment,HISTO_MESSAGE,"REVISION");
    
    $err=$this->modify();
    if ($err != "") return $err;

    //$listvalue = $this->GetValues(); // save copy of values

    // duplicate values
    $olddocid = $this->id;
    $this->id="";
    $this->locked = $locked; // report the lock
    $this->allocated = $allocated; // report the allocate
    $this->comment = ""; // change comment
    $this->revision = $this->revision+1;
    $this->postitid=$postitid;
   
    $err=$this->Add();
    if ($err != "") return $err;
    if ($this->dprofid > 0) $this->setProfil($this->dprofid); // recompute profil if needed

    $err=$this->modify(); // need to applicate SQL triggers
       
    $this->UpdateVaultIndex();
    if ($allocated > 0)	 $this->refreshUTags();
    return $err;
    
  }
  /**
   * Set a free state to the document
   * for the document without workflow
   * a new revision is created
   * @param string $newstateid the document id of the state (FREESTATE family)
   * @param string $comment the comment of the state change
   * @param bool $revision if false no revision are made
   * @return string error text (empty if no error)
   */
  final public function changeFreeState($newstateid,$comment='',$revision=true) {
    if ($this->wid > 0) return sprintf(_("cannot set free state in workflow controlled document %s"),$this->title);
    if ($this->wid == -1) return sprintf(_("cannot set free state for document %s: workflow not allowed"),$this->title);
    if (! $this->isRevisable()) return sprintf(_("cannot set free state for document %s: document cannot be revised"),$this->title);
    if ($newstateid==0) {
      $this->state="";
      $err=$this->modify(false,array("state")); 
      if ($err == "") { 
	$comment=sprintf(_("remove state : %s"),$comment);
	if ($revision) $err=$this->addRevision($comment);
	else $err=$this->addComment($comment);
      }
    } else {

      $state=new_doc($this->dbaccess,$newstateid);
      if (! $state->isAlive()) return sprintf(_("invalid freestate document %s"),$newstateid);
      if ($state->fromid != 39) return sprintf(_("not a freestate document %s"),$state->title);

      $this->state=$state->id;
      $err=$this->modify(false,array("state")); 
      if ($err == "") { 
	$comment=sprintf(_("change state to %s : %s"),$state->title,$comment);
	if ($revision) $err=$this->addRevision($comment);
	else $err=$this->addComment($comment);
      }
    }
    return $err;
  } 

  /**
   * return the state of a document
   * if document has workflow it is the key
   * if document state is a free state it is the name of the state
   * 
   * @return string the state - empty if no state
   */
  final public function getState() {
    if ($this->wid > 0) return $this->state;
    if (is_numeric($this->state) && ($this->state>0) ) {
      $state=$this->getTitle($this->state);
      return $state;
    }

    return $this->state;
  }
  /**
   * return the color associated for the state of a document
   * if document has workflow : not implemeted yet
   * if document state is a free state the color
   * 
   * @return string the color of the state - empty if no state
   */
  final public function getStateColor($def="") {
    if ($this->wid > 0) {      
	$wdoc = new_Doc($this->dbaccess,$this->wid);
	if ($wdoc->isAffected()) return $wdoc->getColor($this->state,$def);
    } else {
      if (is_numeric($this->state) && ($this->state>0) ) {
	$state=$this->getDocValue($this->state,"frst_color",$def);
	return $state;
      }
    }
    return $def;
  }
  /**
   * return the copy of the document
   * the copy is created to the database
   * the profil of the copy is the default profil according to his family
   * the copy is not locked and if it is related to a workflow, his state is the first state
   * @param bool $temporary if true the document create is a temporary document
   * @param bool $control if false don't control acl create (generaly use when temporary is true)
   * @param bool $linkfld if true and document is a folder then document included in folder are also inserte in the copy 
   * @return Doc in case of error return a string that indicate the error
   */
  final public function Copy($temporary=false,$control=true,$linkfld=false) {

    $copy=createDoc($this->dbaccess, $this->fromid, $control);
    if (! is_object($copy)) return false;
    
    $copy->transfertValuesFrom($this);
    
    $copy->id = "";
    $copy->initid = "";
    $copy->revision = "0";
    $copy->locked = "0";
    $copy->allocated = "0";
    $copy->state = "";
    $copy->comment = "";
    $copy->icon = $this->icon;;

    if ($temporary) $copy->doctype = "T";
    $cdoc= $this->getFamDoc();
    $copy->setProfil($cdoc->cprofid);

    $err = $copy->PreCopy();
    if ($err != "") return false;

    $err = $copy->Add();
    if ($err != "") return $err;
    $copy->addComment(sprintf(_("copy from document #%d -%s-"),$this->id, $this->title));

    $copy->PostCopy();
    if ($err != "") AddWarningMsg($err);

    $copy->Modify();
    if ($linkfld && method_exists($copy,"insertFolder")) {
      $copy->insertFolder($this->initid);
    }

    return $copy;
  }

  function PreCopy() {
    // to be defined in child class
    return "";
  }

  function PostCopy() {
    // to be defined in child class
    return "";
  }


  final public function translate($docid, $translate) {
    $doc = new_Doc($this->dbaccess, $docid);
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
  final public function lock($auto=false,$userid="") {

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
   * @return string error message, if no error empty string
   * @see Doc::CanUnLockFile()
   * @see Doc::lock()
   */
  final public function unLock($auto=false) {
    

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
   * allocate document
   * 
   * affect a document to a user
   * @param int $userid the system identificator of the user to affect
   * @param bool $revision if false no revision are made
   * 
   * @return string error message, if no error empty string, if message
   */
  final public function allocate($userid,$comment="",$revision) {

    $err="";
    $err=$this->canEdit();
    if ($err != "") $err=_("Affectation aborded")."\n".$err;
    if ($err == "") {
      $u=new User("",$userid);
      if ($u->isAffected()) {

	if ($err != "") $err=_("Affectation aborded")."\n".$err;
	$err=$this->ControlUser($u->id,"edit");
	if ($err != "") $err=sprintf(_("Affectation aborded\n%s for user %s %s"),$err,$u->firstname,$u->lastname);

	if ($err == "") {
	  $this->addComment(sprintf(_("Affected to %s %s"),$u->firstname,$u->lastname));
	  if ($revision) {
	    $this->addRevision(sprintf(_("Affected for %s"),$comment));
	  } else {
	    $this->addComment(sprintf(_("Affected for %s"),$comment));
	  }
	  $this->delUTags("AFFECTED");
	  $this->addUTag($userid,"AFFECTED",$comment);	  
	  $err=$this->lock(false,$userid);
	}
      } else {
	$err=_("Affectation aborded : user not know");
      }
    }
    if ($err=="") {
      $this->allocated=$userid;
      $this->modify(true,array("allocated"),true);      
    }

    return $err;
  } 

  /** 
   * unallocate document
   * 
   * unaffect a document to a user
   * only the allocated user can unallocate and also users which has unlock acl
   * @param bool $revision if false no revision are made
   * 
   * @return string error message, if no error empty string, if message
   */
  final public function unallocate($comment="",$revision=true) {

    $err="";
    $err=$this->canEdit();

    
    if ($err == "") {
      if ($this->userid != $this->allocated) $err=$this->control("unlock");
    }

    if ($err == "") {
      $u=new User("",$this->allocated);
      if ($u->isAffected()) {
	
	$err=$this->unlock();

	if ($err == "") {
	  $this->delUTags("AFFECTED");
	  if ($revision) $this->addRevision(sprintf(_("Unallocated of %s %s : %s"),$u->firstname,$u->lastname,$comment));
	  else $this->addComment(sprintf(_("Unallocated of %s %s: %s"),$u->firstname,$u->lastname,$comment));
	}
      } else {
	$err=_("user not know");
      }
    }
    if ($err=="") {
      $this->allocated=0;
      $this->modify(true,array("allocated"),true);      
    }

    if ($err != "") $err=_("Unallocate aborded")."\n".$err;
    return $err;
  }
  /**
   * return icon url
   * if no icon found return doc.gif
   * @return string icon url
   */
  final public function getIcon($idicon="") {

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
      //$fdoc = new_Doc(newDoc($this->dbaccess, $this->fromid);
    
      return  $action->GetImageUrl("doc.gif");
      // don't recursivity to increase speed
      //    return $fdoc->geticon();
    }

  }


  // change icon for a class or a simple doc
  final public function changeIcon($icon) {

    if ($this->doctype == "C") { //  a class
      $fromid=$this->initid;

      // need disabled triggers to increase speed
      $qt[]="begin";
      $qt[]="ALTER TABLE doc$fromid DISABLE TRIGGER ALL";
      $qt[]="update doc$fromid set icon='$icon' where (fromid=".$fromid.") AND (doctype != 'C') and ((icon='".$this->icon."') or (icon is null))";
      $qt[]="ALTER TABLE doc$fromid ENABLE TRIGGER ALL";
      $qt[]="update docread set icon='$icon' where (fromid=".$fromid.") AND (doctype != 'C') and ((icon='".$this->icon."') or (icon is null))";
      $qt[]="commit";

      $this->exec_query(implode(";",$qt));  

    } 
    //    $this->title = AddSlashes($this->title);
    $this->icon = $icon;
    $this->Modify();
  }
  /**
   * declare a dependance between several attributes
   * @param array $in attributes id use for compute $out attributes
   * @param array $out attributes id calculated by $in attributes
   */
  final public function AddParamRefresh($in,$out) {
    // to know which attribut must be disabled in edit mode
    $this->paramRefresh[]=array("in"=>explode(",",strtolower($in)),
				"out"=>explode(",",strtolower($out)));
  }

  /** 
   * compute new visibility with depended attributes
   * @return array of visibilities computed with dependance between attributes
   */
  public function getRefreshVisibility() {
    $tv=array();
    foreach ($this->attributes->attr as $k=>$v) {
      $tv[$v->id]=$v->mvisibility;      
    }
    foreach ($this->paramRefresh as $k=>$v) {
      reset($v["in"]);
      $val=true;
      while ($val && (list($ka, $va) = each($v["in"]))) {
	$val=$this->getValue($va);
      }
      if ($val) {
	foreach ($v["out"] as $oa) {
	  if (($tv[$oa]=="W") || ($tv[$oa]=="O"))
	    $tv[$oa]="S";
	}
      }
    }
        
    return $tv;
  }

  /**
   * Special Refresh
   * to define in child classes
   */
  function SpecRefresh() {}  
  /**
   * Special Refresh Generated automatically
   * is defined in generated child classes
   */
  function SpecRefreshGen() {}

  /**
   * recompute all calculated attribut
   * and save the document in database if changes occurred
   */
  final public function Refresh() {	
    
    if ($this->locked == -1) return; // no refresh revised document
    if (($this->doctype == 'C') || ($this->doctype == 'Z') ) return; // no refresh for family  and zombie document
   

    $err=$this->SpecRefresh();
    // if ($this->id == 0) return; // no refresh for no created document
	

    $err.=$this->SpecRefreshGen();

    if ($this->hasChanged)  {
      $this->modify(); // refresh title
    }
    return $err;
	
  }
  
  
  final public function urlWhatEncode( $link, $k=-1) {
    // -----------------------------------
    global $action;
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $urllink="";
    $mi=strlen($link);
    for ($i=0; $i < $mi; $i++) {
      switch ($link[$i]) {
      
      case '%' :
	$i++;
	if ($link[$i] == "%") { 
	  $urllink.= "%"; // %% is %
	} else {
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

	    case "T": // title  
	      $urllink.=$this->title;	      
	      break;	    	    

	    default:
	      
	      break;
	    }
	    $i++; // skip end '%'
	  } else {
	  
	    $sattrid="";
	    while (($i < $mi) && ($link[$i] != "%" )) {
	      $sattrid.= $link[$i];
	      $i++;
	    }
	    $oa=$this->GetAttribute($sattrid);
	    if (($k >= 0)&&($oa && $oa->inArray())) {
	      $tval= $this->GetTValue($sattrid);
	      $ovalue = chop($tval[$k]);
	    } else {
	      $ovalue = $this->GetValue($sattrid);
	    }
	    if ($ovalue == "") return false;
	    //$urllink.=urlencode($ovalue); // encode because url values must be encoded
	    //$urllink.=urlencode($ovalue); // not encode cause url will became invalid
	    if ($ovalue[0]=='[') $urllink.=urlencode($ovalue);
	    else $urllink.=($ovalue); // not encode cause url will became invalid	  	  
	  }
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
    $urllink=$this->urlWhatEncodeSpec($urllink); // complete in special case families

    return (chop($urllink));
    
  }
  
  /**
   * virtual method must be use in child families if needed complete url
   */
  public function urlWhatEncodeSpec($l) {return $l;}

  public static function _val2array($v) {
    return explode("\n", str_replace("\r","",$v));
  }
  
  public static function _array2val($v) {    
    $v=str_replace("\n","<BR>",$v);	  
    if (count($v) == 0) return "";
    return implode("\n", $v);
  }
  /**
   * return an html anchor to a document
   * @param int $id identificator of document
   * @param string $target window target
   * @param bool $htmllink must be true else return nothing
   * @return string the html anchor
   */
  final public function getDocAnchor($id,$target="_self",$htmllink=true) {
    $a="";
    if ($htmllink) {
      $title=$this->getTitle($id);
      
      if ($title == "") {
	$a="<a>".sprintf(_("unknown document id %s"),$id)."</a>";
      } else {
	$u=getParam("CORE_STANDURL");
	$ul="$u&app=FDL&action=FDL_CARD&latest=Y&id=$id";
	$a="<a oncontextmenu=\"popdoc(event,'$ul');return false;\" target=\"$target\" href=\"$ul\">$title</a>";
      }
      
    }
    return $a;
  }
  final public function GetHtmlValue($oattr, $value, $target="_self",$htmllink=true, $index=-1) {
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
	     $htmlval="<FORM style=\"display:inline\"><INPUT id=\"_" .$attrid."\" TYPE=\"hidden\"  name=\"_".$attrid."\" value=\"".$value." \">";
	     $htmlval.="<a onclick=\"subwindow(400,400,'_$attrid','');viewidoc('_$attrid','$idocfamid')\" ";
	     $htmlval.="oncontextmenu=\"viewidoc_in_popdoc(event,'$attrid','_$attrid','$idocfamid');return false\">$title</a>";
// 	     $htmlval.="<input id='ivc_$attrid' type=\"button\" value=\"x\"".
// 	       " title=\""._("close beside window")."\"".
// 	       " style=\"display:none\"".
// 	       " onclick=\"close_frame('$attrid')\">";

	  //    $htmlval.="<input type=\"button\" value=\"o\"".
// 	       " title=\""._("view in other window")."\"".
// 	       " onclick=\"viewidoc_in_frame('$attrid','_$attrid','$idocfamid')\">";

	     $htmlval.="</FORM>";
	     //     $htmlval.="<iframe name='iframe_$attrid' id='iframe_$attrid' style='display:none' marginwidth=0 marginheight=0  width='100%' heigth=200></iframe>";
	     
	     
	     

	     
	     //print_r($htmlval);
	     
	     
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
	      $htmlval=$action->GetImageUrl($avalue);
	    }
	  }
	  break;
	case "file": 
	  $vid="";
	  if (ereg ("(.*)\|(.*)", $avalue, $reg)) {
	    // reg[1] is mime type
	    $vid=$reg[2];
	    $mime=$reg[1];
	    include_once("FDL/Lib.Dir.php");
	    $vf = newFreeVaultFile($this->dbaccess);
	    if ($vf->Show ($reg[2], $info) == "") $fname = $info->name;
	    else $fname=_("vault file error");
	  } else $fname=_("no filename");
	
	
	  if ($target=="mail") {
	    $htmlval="<A target=\"_blank\" href=\"";
	    $htmlval.="cid:".$oattr->id;	    
	    if ($index >= 0) $htmlval.="+$index";
	    $htmlval.=  "\">".$fname."</A>";
	  } else {
	    if ($info) {
	    $umime = trim(`file -ib $info->path`);
	    $size=round($info->size/1024)._("AbbrKbyte");
	    $utarget= ($action->Read("navigator","")=="NETSCAPE")?"_self":"_blank";
	    $htmlval="<A onmousedown=\"document.noselect=true;\" title=\"$size\" target=\"$utarget\" type=\"$mime\" href=\"".
	      $action->GetParam("CORE_BASEURL").
	      "app=FDL"."&action=EXPORTFILE&vid=$vid"."&docid=".$this->id."&attrid=".$oattr->id."&index=$index"
	      ."\">".$fname.
	      "</A>";
	    }
	    /*
	    
	    $htmlval.=" <A onmousedown=\"document.noselect=true;\" target=\"_blank\" type=\"$mime\" href=\"".
	      "http://".$_SERVER["HTTP_HOST"].
	      "/davfreedom/doc".$this->id."/$fname".
	      "\">"."[DAV:$vid]($mime)".
	      "</A>";

	    
	    $htmlval.=" <A onmousedown=\"document.noselect=true;\" target=\"_blank\" type=\"$umime\" href=\"".
	      "http://".$_SERVER["HTTP_HOST"].
	      "/davfreedom/doc".$this->id."/$fname".
	      "\">"."[DAV:$vid]($umime)".
	      "</A>";
	    */
	     }
	
	  break;
	case "longtext":  
	  $bvalue=nl2br(htmlentities(stripslashes(str_replace("<BR>","\n",$avalue))));
	  $shtmllink=$htmllink?"true":"false";
	  $bvalue = preg_replace("/\[ADOC ([^\]]*)\]/e",
                         "\$this->getDocAnchor('\\1',\"$target\",$shtmllink)",
                         $bvalue);	  
	  $htmlval=str_replace(array("[","$"),array("&#091;","&#036;"),$bvalue);
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

	  $lay = new Layout("FDL/Layout/viewdocarray.xml", $action);
	  if (! method_exists($this->attributes,"getArrayElements")) {	    
	    break;
	  }
	   
	  
	  $ta = $this->attributes->getArrayElements($oattr->id);
	  $talabel=array();
	  $tvattr = array();
	  $lay->set("caption",$oattr->labelText);

	  $emptyarray=true;
	  $nbitem=0;
	  while (list($k, $v) = each($ta)) {
	    if (($v->mvisibility=="H")||($v->mvisibility=="O")) continue;
	    $talabel[] = array("alabel"=>$v->labelText,
			       "cwidth"=>$v->getOption("cwidth","auto"));	
	    $tval[$k]=$this->getTValue($k);
	    $nbitem= max($nbitem,count($tval[$k]));
	    if ($emptyarray && ($this->getValue($k)!="")) $emptyarray=false;
	   
	  }
	  $lay->setBlockData("TATTR",$talabel);
	  if (! $emptyarray) {	    
	    
	    if ($nbitem > 10) $lay->set("caption",$oattr->labelText." ($nbitem)");
	    else $lay->set("caption",$oattr->labelText);
	    $tvattr = array();
	    for ($k=0;$k<$nbitem;$k++) {
	      $tvattr[]=array("bevalue" => "bevalue_$k");
	      reset($ta);
	      $tivalue=array();
	      while (list($ka, $va) = each($ta)) {	  
		if ($va->mvisibility=="H") continue;
		$hval = $this->getHtmlValue($va,$tval[$ka][$k],$target,$htmllink,$k);
		if ($va->type=="image") $hval="<img width=\"128\" src=\"".$hval."\">";
		$tivalue[]=array("evalue"=>$hval,
				 "color"=>$va->getOption("color","inherits"),
				 "bgcolor"=>$va->getOption("bgcolor","inherits"),
				 "align"=>$va->getOption("align","inherits") );
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
	      $idoc = new_Doc($this->dbaccess,$idocid);
	      $htmlval =$idoc->viewDoc("FDL:VIEWTHUMBCARD:T","finfo");

	      //$htmlval =$lay->gen(); 
	    }
	  }
	  break;
	case "option": 
	  $lay = new Layout("FDL/Layout/viewdocoption.xml", $action);
	  $htmlval = "";
	 
	  if ($kvalue>-1) $di=$this->getTValue($oattr->format,"",$kvalue);
	  else $di=$this->getValue($oattr->format);
	  if ($di > 0) {	    
	    $lay->set("said",$di);
	    $lay->set("uuvalue",urlencode($avalue));

	    $htmlval =$lay->gen(); 
	  }
	  break;
	case money:    


	  $htmlval=money_format('%!.2n', doubleval($avalue));
	  $htmlval=str_replace(" ","&nbsp;",$htmlval); // need to replace space by non breaking spaces
	  break;
	
	case htmltext:  
	  $shtmllink=$htmllink?"true":"false";
	  $avalue = preg_replace("/\[ADOC ([^\]]*)\]/e",
                         "\$this->getDocAnchor('\\1',\"$target\",$shtmllink)",
                         $avalue);
	  $htmlval="<DIV>$avalue</DIV>";	
	  break;
	case date:  
	  if ($aformat!="") {
	    $htmlval=strftime($aformat,FrenchDateToUnixTs($avalue));
	    $aformat="";
	  } else {
	    $htmlval=$avalue; 
	  }	
	  break;
	case time:  
	  if ($aformat!="") {
	    $htmlval=strftime($aformat,strtotime($avalue));
	    $aformat="";
	  } else {
	    $htmlval=substr($avalue,0,5); // do not display second
	  }
	
	  break;
	case timestamp:   
	  if ($aformat!="") {
	    $htmlval=strftime($aformat,FrenchDateToUnixTs($avalue));
	    $aformat="";
	  } else {
	    $htmlval=substr($avalue,0,16); // do not display second
	  }
	
	  break;
	case ifile:  
	  $lay = new Layout("FDL/Layout/viewifile.xml", $action);
	  $lay->set("aid",$oattr->id);
	  $lay->set("id",$this->id);
	  $htmlval =$lay->gen(); 
	
	  break;
	  
	case color:  	  
	  $htmlval=sprintf("<span style=\"background-color:%s\">%s</span>",$avalue,$avalue);	
	  break;

	default : 
	
	  $htmlval=str_replace(array("[","$"),array("&#091;","&#036;"),htmlentities(stripslashes($avalue)));
	  
	  break;
	
	}
    
      if (($aformat != "") && ($atype != "doc") && ($atype != "array")&& ($atype != "option") ){
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
	    $ititle=str_replace("\"","'",$reg[1]);
	  }
	}
	if ($ulink = $this->urlWhatEncode( $hlink, $kvalue)) {


	  if ($target == "mail") {
	      $scheme="";
	      if (ereg("^([[:alpha:]]*):(.*)",$ulink,$reg)) {
		$scheme=$reg[1];
	      }
	    $abegin="<A target=\"$target\"  href=\"";
	    if ($scheme == "") $abegin.= $action->GetParam("CORE_ABSURL")."/".$ulink;
	    else $abegin.= $ulink;
	    $abegin.="\">";
	  } else {
	    $ltarget=$oattr->getOption("ltarget");
	    if ($ltarget != "") $target=$ltarget;
	    $ltitle=$oattr->getOption("ltitle");
	    if ($ltitle != "") $ititle=str_replace("\"","'",$ltitle);
	    $abegin="<A target=\"$target\" title=\"$ititle\" onmousedown=\"document.noselect=true;\" href=\"";
	    $abegin.= $ulink."\" ";;
	    if ($htmllink > 1){
	      $scheme="";
	      if (ereg("^([[:alpha:]]*):(.*)",$ulink,$reg)) {
		$scheme=$reg[1];
	      }
	      if (($scheme == "") || ($scheme == "http")) {
		if ($scheme == "") $ulink.="&ulink=1";
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
  
  final public function GetHtmlAttrValue($attrid, $target="_self",$htmllink=2) {
    $v=$this->getValue($attrid);
    if ($v=="") return "";
    return $this->GetHtmlValue($this->getAttribute($attrid),
			       $v,$target,$htmllink);
  }

  

  /**
   * Control Access privilege for document for current user
   *
   * @param string $aclname identificator of the privilege to test
   * @return string empty means access granted else it is an error message (access unavailable)
   */
  public function Control ($aclname) {
    // -------------------------------------------------------------------- 
    if (($this->IsAffected()) ) {	
      
      if (($this->profid <= 0) || ($this->userid == 1 )) return ""; // no profil or admin

      return $this->controlId($this->profid,$aclname);
    }
    return "";
    return sprintf(_("cannot control : object not initialized : %s"),$aclname);
  } 

  /**
   * Control Access privilege for document for other user
   *
   * @param int $uid user identificator
   * @param string $aclname identificator of the privilege to test
   * @return string empty means access granted else it is an error message (access unavailable)
   */
  public function ControlUser($uid,$aclname) {
    // -------------------------------------------------------------------- 
    if ($this->IsAffected() ) {	      
      if (($this->profid <= 0) || ($uid == 1 )) return ""; // no profil or admin
      if (! $uid) return _("control :: user identificator is null");
      return $this->controlUserId($this->profid,$uid,$aclname);
    }
    return "";
  }
  
  /**
   * verify that the document exists and is not in trash (not a zombie)
   * @return bool
   */
  final public function isAlive() {
    return ((DbObj::isAffected()) && ($this->doctype != 'Z'));
  }

  /**
   * use triggers to update docvalue table
   */
  final public function SqlTrigger($drop=false) {

    if (get_class($this) == "DocFam") {
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
    $sql .="create trigger VFULL{$cid} BEFORE UPDATE  ON doc$cid FOR EACH ROW EXECUTE PROCEDURE fulltext();";
    $sql .="create trigger FIXDOC{$cid} AFTER INSERT ON doc$cid FOR EACH ROW EXECUTE PROCEDURE fixeddoc();";
    $sql .="create trigger zread{$cid} AFTER INSERT OR UPDATE OR DELETE ON doc$cid FOR EACH ROW EXECUTE PROCEDURE setread();";
    
    return $sql;
  }

  /**
   * add specials SQL indexes
   */
  final public function GetSqlIndex() {
    $t="";
    $id=$this->fromid;
    if ($this->sqlindex)  $sqlindex=array_merge($this->sqlindex,Doc::$sqlindex);
    else $sqlindex=Doc::$sqlindex;
    foreach ($sqlindex as $k=>$v) {
      
      if ($v["unique"])  $unique="unique";
      else $unique="";
      if ($v["using"]!= "") {
	$t.=sprintf("CREATE $unique INDEX %s$id on  doc$id using %s(%s);\n",$k,$v["using"],$v["on"]);
      } else {
	$t.=sprintf("CREATE $unique INDEX %s$id on  doc$id(%s);\n",$k,$v["on"]);
      }
    }
    return $t;
  }
  

  /** 
   * return the character in third part of zone
   * @return char
   */
  public function getZoneOption($zone="") {
    if ($zone=="") $zone=$this->defaultview;
    if (ereg("([A-Z_-]+):([^:]+):{0,1}([A-Z]{0,1})", $zone, $reg)) {
      return $reg[3];
    }
  }
  /**
   * set default values define in family document
   * the format of the string which define default values is like
   * [US_ROLE|director][US_SOCIETY|alwaysNet]...
   * @param string $defval the default values
   * @param bool  $method set to false if don't want interpreted values
   * @access private
   */
  final public function setDefaultValues($tdefval,$method=true) {
    if (is_array($tdefval)) {
      if ($method) {
	foreach ($tdefval as $aid=>$dval) {
	  $this->setValue($aid, $this->GetValueMethod($dval));
	}             
      } else {
	foreach ($tdefval as $aid=>$dval) {
	  $this->setValue($aid, $dval);
	}             
      }
    }
  }

   /**
   * set all attribute in W visibility 
   * 
   * 
   */
  function SetWriteVisibility() {
    // transform hidden to writted attribut for default document
   
    $listattr = $this->GetAttributes();
    while (list($i,$attr) = each($listattr)) {
	if (($attr->mvisibility == "H") || ($attr->mvisibility == "R") || ($attr->mvisibility == "S")) {
	  $this->attributes->attr[$i]->mvisibility="W";
	}
    }
    
  }

  /**
   * Return the main path relation
   * list of prelid properties (primary relation)
   * the first item is the direct parent, the second:the grand-parent , etc.
   * @return array key=id , value=title of relation
   */ 
  function getMainPath() {
    $tr=array();

    if ($this->prelid > 0) {

      $d=getTDoc($this->dbaccess,$this->prelid);
      $fini=false;
      while (! $fini) {
	if ($d) {
	  if (controlTDoc($d,"view")) {
	    if (! in_array($d["initid"],array_keys($tr))) {
	      $tr[$d["initid"]]=$d["title"];
	      if ($d["prelid"] > 0) $d=getTDoc($this->dbaccess,$d["prelid"]);
	      else $fini=true;

	    } else $fini=true;
	  } else $fini=true;
	} else {
	  $fini=true;
	}
	 
      }
    }
    return $tr;
  }

  /**
   * generate HTML code for view doc
   * @param string $layout layout to use to view document
   * @param string $target window target name for hyperlink destination
   * @param bool $ulink if false hyperlink are not generated
   * @param bool $abstract if true only abstract attribute are generated
   * @param bool $changelayout if true the internal layout ($this->lay) will be replace by the new layout
   */
  final public function viewDoc($layout="FDL:VIEWBODYCARD",$target="_self",$ulink=true,$abstract=false,$changelayout=false) {
    global $action;

    if (ereg("(.*)\?(.*)",$layout, $reg)) {
      // in case of arguments in zone
      global $ZONE_ARGS;
      $layout=$reg[1];
      $zargs = explode("&", $reg[2] );
      while (list($k, $v) = each($zargs)) {
	if (ereg("([^=]*)=(.*)",$v, $regs)) {
	  // memo zone args for next action execute
	   $ZONE_ARGS[$regs[1]]=urldecode($regs[2]);
	}
      }
    }
 
    if (! ereg("([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}", $layout, $reg)) 
      $action->exitError(sprintf(_("error in pzone format %s"),$layout));
     
    
    
    if (!$changelayout) {
      $play=$this->lay;
    }
    $this->lay = new Layout(getLayoutFile($reg[1],strtolower($reg[2]).".xml"), $action);
    
    $this->lay->set("_readonly",($this->Control('edit')!=""));
    $method = strtolower($reg[2]);

   
    if (method_exists ( $this, $method)) {
      $this->$method($target,$ulink,$abstract);
    } else {
      $this->viewdefaultcard($target,$ulink,$abstract);
    }


    $laygen=$this->lay->gen();
    
    if (!$changelayout)       $this->lay=$play;
    
    if (! $ulink) {
      // suppress href attributes
      return preg_replace(array("/href=\"index\.php[^\"]*\"/i", "/onclick=\"[^\"]*\"/i","/ondblclick=\"[^\"]*\"/i"), 
			  array("","","") ,$laygen );
    }
    if ($target=="mail") {
      // suppress session id
      return preg_replace("/\?session=[^&]*&/", "?" ,$laygen );
    }
    return $laygen;
  }
  // --------------------------------------------------------------------

  /**
   * default construct layout for view card containt
   *
   * @param string $target window target name for hyperlink destination
   * @param bool $ulink if false hyperlink are not generated
   * @param bool $abstract if true only abstract attribute are generated
   */
  final public function viewdefaultcard($target="_self",$ulink=true,$abstract=false) {
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
   * @param bool $onlyopt if true only optionnal attributes are displayed
   */
  function viewbodycard($target="_self",$ulink=true,$abstract=false,$onlyopt=false) {
    global $action;
  
    $frames= array();
  

     
    if ($abstract){
      // only 3 properties for abstract mode
      $listattr = $this->GetAbstractAttributes();
    } else {
      $listattr = $this->GetNormalAttributes($onlyopt);    
    }
    

    $nattr = count($listattr); // attributes list count


    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $nbimg=0;// number of image in one frametext
    $currentFrameId="";

    $changeframe=false; // is true when need change frame
    $tableframe=array();
    $tableimage=array();
    $ttabs=array();

    $iattr=0;
    foreach($listattr as $i=>$attr) {
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
	    if (($currentFrameId != "") && ($attr->fieldSet->mvisibility != "H")) $changeframe=true;
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
	      $frames[$k]["tag"]="";
	      $frames[$k]["TAB"]=false;;
	      if (($currentFrame->fieldSet->id!="")&&($currentFrame->fieldSet->id!="FIELD_HIDDENS")) {
		$frames[$k]["tag"]="TAG".$currentFrame->fieldSet->id;
		$frames[$k]["TAB"]=true;
		$ttabs[$currentFrame->fieldSet->id]=array("tabid"=>$currentFrame->fieldSet->id,
							    "tabtitle"=>ucfirst($currentFrame->fieldSet->labelText));
	      }
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
	    $tableimage[$nbimg]["itarget"]=($action->Read("navigator","")=="NETSCAPE")?"_self":"_blank";
	    $width=$attr->getOption("iwidth","80px");
	    $tableimage[$nbimg]["imgwidth"]=$width;
	    if (strstr($htmlvalue,'index.php'))   $tableimage[$nbimg]["imgthumbsrc"]=$htmlvalue."&width=".intval($width);
	    else $tableimage[$nbimg]["imgthumbsrc"]=$htmlvalue;
	    break;
	  default : 
	    $tableframe[$v]["value"]=$htmlvalue;
	    break;
		
	  }

	if (($attr->fieldSet->mvisibility!="H")&&($htmlvalue!=="")) {
	  $currentFrameId = $attr->fieldSet->id;
	  $currentFrame = $attr->fieldSet;
	}


	
	// print name except image (printed otherthere)
	if ($attr->type != "image") {	
	  $tableframe[$v]["wvalue"]=($attr->type == "array")||($attr->type == "htmltext")?"1%":"30%"; // width
	  $tableframe[$v]["name"]=$this->GetLabel($attr->id);
	  if ($attr->type != "array")  $tableframe[$v]["ndisplay"]="inherit";
	  else $tableframe[$v]["ndisplay"]="none";
	  if ( ($attr->type == "htmltext") && (count($tableframe)==1)) {
	    $keys=array_keys($listattr);
	    
	    $na=$listattr[$keys[$iattr]]; // next attribute
	    if ($na->fieldSet->id != $attr->fieldSet->id)   $tableframe[$v]["ndisplay"]="none";
	  }
	  

	  $tableframe[$v]["classback"]=($attr->usefor=="O")?"FREEDOMOpt":"FREEDOMBack1";
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
	$frames[$k]["frameid"]=$currentFrameId;
	$frames[$k]["tag"]="";
	$frames[$k]["TAB"]=false;;
	if (($currentFrame->fieldSet->id!="")&&($currentFrame->fieldSet->id!="FIELD_HIDDENS")) {
	  $frames[$k]["tag"]="TAG".$currentFrame->fieldSet->id;
	  $frames[$k]["TAB"]=true;
	  $ttabs[$currentFrame->fieldSet->id]=array("tabid"=>$currentFrame->fieldSet->id,
						      "tabtitle"=>ucfirst($currentFrame->fieldSet->labelText));
	}
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
    $this->lay->SetBlockData("TABS",$ttabs);
    $this->lay->Set("ONETAB",count($ttabs)>0);
    if (count($ttabs)>0)     $this->lay->Set("firsttab",$ttabs[0]["tabid"]);
  
  }
  
  /**
   * write layout for thumb view
   */
  function viewthumbcard($target="finfo",$ulink=true,$abstract=true) {
    $this->viewabstractcard($target,$ulink,$abstract);
    $this->viewprop($target,$ulink,$abstract);
    $this->lay->set("iconsrc",$this->getIcon());
    $state=$this->getState();
    if ($state != "") $this->lay->set("state",_($state));
    else $this->lay->set("state","");
  }  
  /**
   * write layout for thumb view
   */
  function viewproperties($target="finfo",$ulink=true,$abstract=true) {

    $this->viewprop($target,$ulink,$abstract);
    $this->lay->set("iconsrc",$this->getIcon());
    $fdoc=$this->getFamDoc();
    $this->lay->Set("ficonsrc", $fdoc->getIcon());
    $owner = new User("", abs($this->owner));
    $this->lay->Set("username", $owner->firstname." ".$owner->lastname);
    $this->lay->Set("userid", $owner->fid);
    $user = new User("", abs($this->locked));
    // $this->lay->Set("locked", $user->firstname." ".$user->lastname);
    $this->lay->Set("lockedid", $user->fid);
    $state=$this->getState();
    if ($state != "") {
      if (($this->locked == -1)||($this->lmodify != 'Y'))  $this->lay->Set("state", _($state));
      else $this->lay->Set("state", sprintf(_("current (<i>%s</i>)"),_($state)));
    } else $this->lay->set("state",_("no state"));
    if (is_numeric($this->state) && ($this->state>0) && (! $this->wid)) {
      $this->lay->set("freestate",$this->state);
    }
    else $this->lay->set("freestate",false);

    $this->lay->set("hasrevision",($this->revision > 0));
    $this->lay->Set("moddate", strftime ("%d/%m/%Y %H:%M:%S",$this->revdate));
    $this->lay->set("moddatelabel",_("last modification date"));
    if ($this->locked == -1) {
      if ($this->doctype=='Z') $this->lay->set("moddatelabel",_("suppression date"));
      else $this->lay->set("moddatelabel",_("revision date"));
    }
    if (GetParam("CORE_LANG") == "fr_FR") { // date format depend of locale
      setlocale (LC_TIME, "fr_FR");
      $this->lay->Set("revdate", strftime ("%a %d %b %Y %H:%M",$this->revdate));
    } else {
      $this->lay->Set("revdate", strftime ("%x %T",$this->revdate));
    }
    $this->lay->Set("version", $this->version);
    
    $this->lay->Set("profid", abs($this->profid));
    if ((abs($this->profid) > 0) && ($this->profid != $this->id)) {
      $pdoc = new_Doc($this->dbaccess, abs($this->profid));
      $this->lay->Set("profile", $pdoc->title);
    } else {
      if ($this->profid == 0)
	$this->lay->Set("profile", _("no access control"));
      else {
	if ($this->dprofid==0) $this->lay->Set("profile", _("specific control"));
	else {
	
	  $this->lay->Set("profile", _("dynamic control"));
	  $this->lay->Set("profid", abs($this->dprofid));
	}
      }
    }
    if ($this->cvid == 0) {
      $this->lay->Set("cview", _("no view control"));
    } else {  
      $cvdoc= new_Doc($dbaccess, $this->cvid);
      $this->lay->Set("cview", $cvdoc->title);
    }
    if ($this->allocated == 0) {
      $this->lay->Set("allocate", _("no allocate"));      
      $this->lay->Set("allocateid", false);
    } else {
      $user = new User("", ($this->allocated));
      $this->lay->Set("allocate", $user->firstname." ".$user->lastname);
      $this->lay->Set("allocateid", $user->fid);
    }
  
    
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
	      "&height=30\">";
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
  final public function viewattr($target="_self",$ulink=true,$abstract=false) {
    $listattr = $this->GetNormalAttributes();
    
    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
    foreach($listattr as $k=>$v) {
      $value = chop($this->GetValue($v->id));

      //------------------------------
      // Set the table value elements
      
     	
	$this->lay->Set("S_".strtoupper($v->id),($value!=""));
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
    foreach($listattr as $k=>$v) {
      $this->lay->Set("L_".strtoupper($v->id),$v->labelText);      
    }

  }


  // view doc properties
  final public function viewprop($target="_self",$ulink=true,$abstract=false) {
    foreach($this->fields as $k=>$v) {
      $this->lay->Set(strtoupper($v),($this->$v===null)?false:$this->$v);
    }  
  }

  /**
   * view only option values
   * @param int $dirid   directory to place doc if new doc
   * @param bool $onlyopt if true only optionnal attributes are displayed
   */
  final public function viewoptcard($target="_self",$ulink=true,$abstract=false) {
    return $this->viewbodycard($target,$ulink,$abstract,true);
  }

  /**
   * edit only option
   * @param int $dirid   directory to place doc if new doc
   * @param bool $onlyopt if true only optionnal attributes are displayed
   */
  final public function editoptcard($target="_self",$ulink=true,$abstract=false) {
    return $this->editbodycard($target,$ulink,$abstract,true);
  }
  /**
   * value for edit interface
   * @param bool $onlyopt if true only optionnal attributes are displayed
   */
  function editbodycard($target="_self",$ulink=true,$abstract=false,$onlyopt=false) {

    include_once("FDL/editutil.php");
 
    $docid = $this->id;        // document to edit
	        
  
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
	       
      $this->lay->Set("TITLE", $this->title);
	
    }
  
    // ------------------------------------------------------
 
  
  
  
    $this->lay->Set("id", $docid);
    $this->lay->Set("classid", $this->fromid);
  
  
  
    // ------------------------------------------------------
    // Perform SQL search for doc attributes
    // ------------------------------------------------------	        
  
  
 
    $frames=array();
    $listattr = $this->GetInputAttributes($onlyopt);

  
    $nattr = count($listattr); // number of attributes
    
    
    $k=0; // number of frametext
    $v=0;// number of value in one frametext
    $currentFrameId="";
    $changeframe=false;
    $ih = 0; // index for hidden values
    $thidden =array();
    $tableframe=array();
    $ttabs=array();

    $iattr=0;

    foreach($listattr as $i=>$attr) {
      
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
	  $frames[$k]["tag"]="";
	  $frames[$k]["TAB"]=false;;
	  if (($currentFrame->fieldSet->id!="")&&($currentFrame->fieldSet->id!="FIELD_HIDDENS")) {
	    $frames[$k]["tag"]="TAG".$currentFrame->fieldSet->id;
	    $frames[$k]["TAB"]=true;
	    $ttabs[$currentFrame->fieldSet->id]=array("tabid"=>$currentFrame->fieldSet->id,
						      "tabtitle"=>ucfirst($currentFrame->fieldSet->labelText));
	  }
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
      $currentFrame = $listattr[$i]->fieldSet;
      if ( ($listattr[$i]->mvisibility == "H") || 
	   ($listattr[$i]->mvisibility == "R") ) {
	// special case for hidden values
	$thidden[$ih]["hname"]= "_".$listattr[$i]->id;
	$thidden[$ih]["hid"]= $listattr[$i]->id;
	if (($value == "")&&($this->id==0)) $thidden[$ih]["hvalue"] = GetHttpVars($listattr[$i]->id);
	else $thidden[$ih]["hvalue"]=chop(htmlentities($value));
	  
	  
	$thidden[$ih]["inputtype"]=getHtmlInput($this,
						$listattr[$i],
						$value);
	$ih++;

      } else {
	$tableframe[$v]["value"]=chop(htmlentities($value));
	$label = $listattr[$i]->labelText;
	$tableframe[$v]["attrid"]=$listattr[$i]->id;
	$tableframe[$v]["name"]=$label;

	if ($listattr[$i]->needed ) $tableframe[$v]["labelclass"]="FREEDOMLabelNeeded";
	else $tableframe[$v]["labelclass"]="FREEDOMLabel";

	$tableframe[$v]["classback"]=($attr->usefor=="O")?"FREEDOMOpt":"FREEDOMBack1";
	//$tableframe[$v]["name"]=$action->text($label);
	$tableframe[$v]["inputtype"]=getHtmlInput($this,
						  $listattr[$i],
						  $value);
		
		
	$tableframe[$v]["NORMALROW"]="NORMALROW$i";		
	$tableframe[$v]["ARRAYROW"]="ARRAYROW$i";

	if (($listattr[$i]->type=="array")||($listattr[$i]->type=="htmltext")) $this->lay->SetBlockData("ARRAYROW$i",array(array("zou"=>"zou")));
	else	$this->lay->SetBlockData("NORMALROW$i",array(array("zou"=>"zou")));
	$v++;
		
      }
      
    }
  
    // Out
    if ($v > 0 ) {// latest fieldset
	  
	      
      $frames[$k]["frametext"]="[TEXT:".$this->GetLabel($currentFrameId)."]";
      $frames[$k]["frameid"]=$currentFrameId;
      $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";
      $frames[$k]["tag"]="";
      $frames[$k]["TAB"]=false;;
      if (($currentFrame->fieldSet->id!="")&&($currentFrame->fieldSet->id!="FIELD_HIDDENS")) {
	$frames[$k]["tag"]="TAG".$currentFrame->fieldSet->id;
	$frames[$k]["TAB"]=true;
	$ttabs[$currentFrame->fieldSet->id]=array("tabid"=>$currentFrame->fieldSet->id,
						      "tabtitle"=>ucfirst($currentFrame->fieldSet->labelText));
      }
      $this->lay->SetBlockData($frames[$k]["TABLEVALUE"],
			       $tableframe);
	    
    }
    
    $this->lay->SetBlockData("HIDDENS",$thidden);
    $this->lay->SetBlockData("TABLEBODY",$frames);
    $this->lay->SetBlockData("TABS",$ttabs);
    $this->lay->Set("ONETAB",count($ttabs)>0);
    if (count($ttabs)>0)     $this->lay->Set("firsttab",$ttabs[0]["tabid"]);
  
  

      
  
  
  }

  /**
   * create input fields for attribute document
   * @param bool $withtd set to false if don't wan't <TD> tag in the middle
   */
  final public function editattr($withtd=true) {
 
    include_once("FDL/editutil.php");
    $listattr = $this->GetNormalAttributes();
        

    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value

    while (list($k,$v) = each($listattr)) {
      //------------------------------
      // Set the table value elements
      $value = chop($this->GetValue($v->id));
			
      if ($v->mvisibility=="R") $v->mvisibility="H"; // don't see in edit mode
      $this->lay->Set("V_".strtoupper($v->id),
		      getHtmlInput($this,
				   $v, 
				   $value,"",(!$withtd)));
      if ($v->needed == "Y") $this->lay->Set("L_".strtoupper($v->id),"<B>".$v->labelText."</B>");
      else $this->lay->Set("L_".strtoupper($v->id),$v->labelText);
      $this->lay->Set("W_".strtoupper($v->id),($v->mvisibility!="H"));
      
    }
  
    $listattr = $this->GetFieldAttributes();

    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value

    while (list($k,$v) = each($listattr)) {
      $this->lay->Set("L_".strtoupper($v->id),$v->labelText);  
    }


    $this->setFamidInLayout();
  }


  final public function setFamidInLayout() {
    // add IDFAM_ attribute in layout
    global $tFamIdName;

    if (! isset($tFamIdName))  getFamIdFromName($this->dbaccess,"-");
  
    reset($tFamIdName);
    while(list($k,$v) = each($tFamIdName)) {
      $this->lay->set("IDFAM_$k", $v);
    }
  }
  /**
   * get vault file name or server path of filename
   * @param string $idAttr identificator of file attribute 
   * @param bool false return original file name (basename) , true the real path
   * @return string the file name of the attribute
   */
  final public function vault_filename($attrid,$path=false) {

    $fileid= $this->getValue($attrid);
    $fname="";
    if (ereg ("(.*)\|(.*)", $fileid, $reg)) {	 
      // reg[1] is mime type
      $vf = newFreeVaultFile($this->dbaccess);
      if ($vf -> Show ($reg[2], $info) == "") {
	if ($path) $fname = $info->path;
	else $fname = $info->name;
      }
    
    } 

    return $fname;
  }  
  /**
   * get vault file name or server path of filename
   * @param string $idAttr identificator of file attribute 
   * @param bool false return original file name (basename) , true the real path
   * @return array of properties :
   *         [name] => search.svg
   *         [size] => 166137
   *         [public_access] => 
   *         [path] => /var/freedom/fs/1/6132.svg
   */
  final public function vault_properties($attr) {
    if ($attr->inArray()) $fileids= $this->getTValue($attr->id);
    else $fileids[]= $this->getValue($attr->id);
   
    $tinfo=array();
    foreach ($fileids as $k=>$fileid) {
      if (ereg ("(.*)\|(.*)", $fileid, $reg)) {	 
	// reg[1] is mime type
	$vf = newFreeVaultFile($this->dbaccess);
	if ($vf->Show ($reg[2], $info) == "") {	
	  $tinfo[$k]= get_object_vars($info);
	  $tinfo[$k]["vid"]=$reg[2];
	}
      }
    } 

    return $tinfo;
  }

 // =====================================================================================
  // ================= Methods use for XML ======================
  final public function toxml($withdtd=false,$id_doc="")  {

    global $action;
    $doctype=$this->doctype; 
    
    $docid=intval($this->id);
    if ($id_doc==""){
      $id_doc=$docid;
    }

    $title=$this->title;
    $fromid=$this->fromid;
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $fam_doc=new_Doc($this->dbaccess,$this->fromid);
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

    foreach($listattr as $i=>$attr) {
      $iattr++;

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
  
  final public function todtd() {


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

  
  /**
   * return possible dynamic title
   * this method can be redefined in child if the title is variable by other parameters than containt
   */
  function getSpecTitle() {
    return $this->title;
  }
  final public function refreshDocTitle($nameId,$nameTitle) {
  
    // gettitle(D,SI_IDSOC):SI_SOCIETY,SI_IDSOC

    $this->AddParamRefresh("$nameId","$nameTitle");
    $doc=new_Doc($this->dbaccess, $this->getValue($nameId));
    if ($doc->isAlive())  $this->setValue($nameTitle,$doc->title);
    else {
      // suppress
      if (! $doc->isAffected()) $this->deleteValue($nameId);
    }
  }


  /**
   * get image emblem for the doc like lock/nowrite
   * @return string the url of the image
   */
  function getEmblem() {
    global $action;
    if ($this->confidential >0) return  $action->getImageUrl("confidential.gif");
    else if ($this->locked == -1) return  $action->getImageUrl("revised.gif");
    else if ($this->allocated == $this->userid) return $action->getImageUrl("clef3.gif");
    else if ((abs($this->locked) == $this->userid)) return $action->getImageUrl("clef1.gif");
    else if ($this->locked != 0) return $action->getImageUrl("clef2.gif");
    else if ($this->control("edit") != "") return $action->getImageUrl("nowrite.gif");
    else return $action->getImageUrl("1x1.gif");
  }

  /**
   * use only for paramRefresh in attribute definition of a family
   */
  function nothing($a="",$b="",$c="") {
    return "";
  }

  //----------------------------------------------------------------------
  //   USUAL METHODS USE FOR CALCULATED ATTRIBUTES OR FUNCTION SEARCHES
  //----------------------------------------------------------------------
  // ALL THESE METHODS NAME MUST BEGIN WITH 'GET'

  /**
   * return title of document
   * @see Doc::getSpecTitle()
   */
  final public function getTitle($id="-1") {
    if ($id=="-1") {
      if ($this->isConfidential())  return _("confidential document");      
      return $this->getSpecTitle();
    }
    if (! is_numeric($id)) $id=getIdFromName($this->dbaccess,$id);
    if ($id > 0) {    
      $t = getTDoc($this->dbaccess,$id);
      if ($t)    return $t["title"];
      return " "; // delete title
    }
    return ""; 
  }

  /**
   * return the today date with european format DD/MM/YYYY
   * @param int $daydelta to have the current date more or less day (-1 means yesterday, 1 tomorrow)
   * @return string DD/MM/YYYY
   */
  public static function getDate($daydelta=0,$dayhour="",$daymin="") {
    $delta = abs(intval($daydelta));
    if ($daydelta > 0) {
      $nd =strtotime ("+$delta day");
    } else if ($daydelta < 0) {
       $nd =strtotime ("-$delta day");
    } else {
      $nd =time();
    }

    if ($dayhour==="")  return date("d/m/Y",$nd);
    else {
      $delta=abs(intval($dayhour));
      if ($dayhour > 0) {
	$nd =strtotime ("+$delta hour",$nd);
      } else if ($dayhour < 0) {
	$nd = strtotime ("-$delta hour",$nd);
      }
      $delta=abs(intval($daymin));
      if ($daymin > 0) {
	$nd =strtotime ("+$delta min",$nd);
      } else if ($daymin < 0) {
	$nd = strtotime ("-$delta min",$nd);
      }
    }
    return date("d/m/Y H:i",$nd);
  }


  /**
   * return the today date and time with european format DD/MM/YYYY HH:MM
   * @param int $hourdelta to have the current date more or less hour  (-1 means one hour before, 1 one hour after)
   * @param bool $second if true format DD/MM/YYYY HH:MM
   * @return string DD/MM/YYYY HH:MM
   */
  public static  function getTimeDate($hourdelta=0,$second=false) {
    $delta = abs(intval($hourdelta));
    if ($second) $format="d/m/Y H:i:s";
    else $format="d/m/Y H:i";
    if ($hourdelta > 0) {
      return date($format,strtotime ("+$delta hour"));
    } else if ($hourdelta < 0) {
      return date($format,strtotime ("-$delta hour"));
    }
    return date($format);
  }

  /**
   * return value of an attribute for the document referenced
   * @param int document identificator
   * @param string attribute identificator
   * @param string def default return value
   */
  final public function getDocValue($docid, $attrid,$def="") {
    if (intval($docid) > 0) {
      $doc = new_Doc($this->dbaccess, $docid);
      if ($doc->isAlive()) {
	return $doc->getRValue($attrid,$def);
      }
    }
    return "";
  }

  /**
   * return value of an property for the document referenced
   * @param int document identificator
   * @param string  property identificator
   */
  final public function getDocProp($docid, $propid) {
    if (intval($docid) > 0) {
      $doc = new_Doc($this->dbaccess, $docid);
      if ($doc->isAlive()) {
	return $doc->$propid;
      }
    }
    return "";
  }
  /**
   * return the user last name 
   * @param bool $withfirst if true compose first below last name
   * @return string
   */
  public static function getUserName($withfirst=false) {
    global $action;
    if ($withfirst) return $action->user->firstname." ".$action->user->lastname;
    return $action->user->lastname; 
  }

  

  /**
   * return the personn doc id conform to firstname & lastname of the user
   * @return int
   */
  public static function userDocId() {
    global $action;

    
    return $action->user->fid;
     
  }
  /**
   * alias for @see Doc:userDocId
   * @return int
   */
  public static function getUserId() {
    return Doc::userDocId();
  }

  /**
   * return system user id
   * @return int
   */
  public static function getWhatUserId() {
    global $action;    
    return $action->user->id;
  }


  /**
   * return a specific attribute of the current user document
   * @return int
   */
  final public function getMyAttribute($idattr) {
    $mydoc=new_Doc($this->dbaccess,$this->getUserId());

    return $mydoc->getValue($idattr);
  }



  final public function UpdateVaultIndex() {
    $dvi = new DocVaultIndex($this->dbaccess);
    $err = $dvi->DeleteDoc($this->id);
    $fa=$this->GetFileAttributes();
    $tvid=array();
    foreach ($fa as $aid=>$oattr) {
      if ($oattr->inArray()) {
	$ta=$this->getTValue($aid);
      } else {
	$ta=array($this->getValue($aid));	  
      }
      foreach ($ta as $k=>$v) {
	$vid="";
	if (ereg ("(.*)\|(.*)", $v, $reg)) {
	  $vid=$reg[2];
	  $tvid[$vid]=$vid;
	}
      }      	
    }
    
    foreach ($tvid as $k=>$vid) {
	  $dvi->docid = $this->id;
	  $dvi->vaultid = $vid;
	  $dvi->Add();	
    }
  }

}

?>
