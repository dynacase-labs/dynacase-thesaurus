<?php
/**
 * Folder document definition
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Dir.php,v 1.61 2007/08/01 14:08:55 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */


include_once("FDL/Class.PDir.php");
include_once("FDL/Class.QueryDir.php");

/**
 * default folder to place document
 */
define ("UNCLASS_FLD",10); 

/**
 * Folder document Class
 *
 */
Class Dir extends PDir
{
  
  var $defDoctype='D';

  function Dir($dbaccess='', $id='',$res='',$dbid=0) {
    parent::__construct($dbaccess, $id, $res, $dbid);
    if ($this->fromid == "") $this->fromid= FAM_DIR;
  }


  // get the home and basket folder
  function GetHome() {
    global $action;
    
    include_once("FDL/freedom_util.php");
    include_once("FDL/Lib.Dir.php");
    $rq=getChildDoc($this->dbaccess,0,0,1,array("owner = -". $this->userid),
		    $this->userid,"LIST","DIR");
    
 
    if (count($rq) > 0)      $home = $rq[0];
    else {
      $home = createDoc($this->dbaccess,"DIR");

      if (! $home) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),getFamIdFromName($dbaccess,"DIR")));

      $home->owner = -$this->userid;
      include_once("Class.User.php");
      $user = new User("", $this->userid);
      $home->title = $user->firstname." ".$user->lastname;
      $home->setTitle($home ->title);
      $home->icon = 'fldhome.gif';
      $home->name = 'FLDHOME_'.$this->getWhatUserId();
      $home->Add();

      $privlocked = createDoc($this->dbaccess,"SEARCH");
      if (! $privlocked) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),getFamIdFromName($dbaccess,"SEARCH")));

      $privlocked->title=(_("locked document of ").$home ->title);
      $privlocked->Add();
      $privlocked->AddQuery("select * from doc where (doctype!='Z') and".
			    " (locked=".$this->userid.") ");
      $home -> AddFile($privlocked->id); 

    }

      // add basket in home
      
    if (getParam("FREEDOM_IDBASKET") == "") {

      $bas = createDoc($this->dbaccess,"BASKET");
      if (! $bas) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),getFamIdFromName($dbaccess,"BASKET")));

      $query = new QueryDb($this->dbaccess, "_BASKET");
      $query->AddQuery("owner = ". $this->userid);
      $rq = $query->Query();
      if ($query->nb == 0) {
	$bas->setvalue("ba_title",_("Document basket"));
	$bas->setvalue("ba_desc",sprintf(_("basket of %s"),$home ->title));
	$home->name = 'FLDBASKET_'+$this->getWhatUserId();
	$bas->Add();
	$home->AddFile($bas->id); 
	$basid=$bas->id;
      } else {
	$basid=$rq[0]->id;
      }
      global $action;
      $action->parent->param->Set("FREEDOM_IDBASKET",$basid,PARAM_USER.$this->userid,
				  $action->parent->GetIdFromName("FREEDOM"));
      $action->parent->session->close();

    }
      
    return $home;
  }
    
  /**
   * clear containt of this folder
   *
   * @return string error message, if no error empty string
   */
  function Clear() {
    if ($this->isLocked(true)) return sprintf(_("folder is locked. Cannot containt modification"));
    // need this privilege
    $err = $this->Control("modify");
    if ($err!= "") return $err;

    $err=$this->exec_query("delete from fld where dirid=".$this->initid);
    $this->updateFldRelations();
    return $err;

  }


  /**
   * virtual method use before insert document in folder
   * @param int $docid document identificator to insert
   * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
   * @return string error message if not empty the insert will be aborted
   */
  function preInsertDoc($docid,$multiple=false) { }  

  /**
   * virtual method use after insert document in folder
   * @param int $docid document identificator to insert
   * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
   * @return string error message 
   */
  function postInsertDoc($docid,$multiple=false) { }
  /**
   * virtual method use after insert multiple document in this folder
   * must be redefined to optimize algorithm
   * @param array $tdocid array of document identificator to insert
   * @return string error message 
   */
  function postMInsertDoc($tdocid) {
    foreach ($tdocid as $docid) {
      $this->postInsertDoc($docid);
    }
  }

  /**
   * virtual method use after unlink document in folder
   * @param int $docid document identificator to unlink
   * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
   * @return string error message if not empty the insert will be aborted
   */
  function preUnlinkDoc($docid,$multiple=false) { }  

  /**
   * virtual method use after unlink document in folder
   * @param int $docid document identificator to unlink
   * @param bool $multiple flag to indicate if the insertion is a part of grouped insertion
   * @return string error message 
   */
  function postUnlinkDoc($docid,$multiple=false) { }

  

  /**
   * Test if current user can add or delete document in this folder
   *
   * @return string error message, if no error empty string
   */
  function canModify() {
     if ($this->isLocked(true)) return sprintf(_("folder is locked. Cannot containt modification"));
    
    // need this privilege
    $err = $this->Control("modify");
    return $err;
  }

  /**
   * add a document reference in this folder
   *
   * if mode is latest the user always see latest revision 
   * if mode is static the user see the revision which has been inserted
   * @param int $docid document ident for the insertion
   * @param string $mode latest|static 
   * @param bool $noprepost if true if the virtuals methods {@link preInsertDoc()} and {@link postInsertDoc()} are not called
   * @return string error message, if no error empty string
   */
  function AddFile($docid, $mode="latest",$noprepost=false) {     
    $err=$this->canModify();
    if ($err!= "") return $err;

    $doc= new_Doc($this->dbaccess, $docid);
    $qf = new QueryDir($this->dbaccess);
    switch ($mode) {
    case "static":
      $qf->qtype='F'; // fixed document
      $qf->childid=$docid; // initial doc
      break;
    case "latest":
    default:
      if (! $doc->isAffected()) return sprintf(_("Cannot add in %s folder, doc id (%d) unknown"), $this->title, $docid);
      $qf->qtype='S'; // single user query
      $qf->childid=$doc->initid; // initial doc    
      break;
    }  
    $qf->dirid=$this->initid; // the reference folder is the initial id
    $qf->query="";
    if (! $qf->Exists()) {

      // use pre virtual method
      if (!$noprepost) $err=$this->preInsertDoc($docid);
      if ($err!= "") return $err;    

      // verify if doc family is autorized   
      if (! $this->isAuthorized($doc->fromid)) return sprintf(_("Cannot add %s in %s folder, restriction set to add this kind of document"),  $doc->title ,$this->title);

    


      $err = $qf->Add();
      if ($err == "") {
	AddLogMsg(sprintf(_("Add %s in %s folder"), $doc->title, $this->title));

	// add default folder privilege to the doc
	if ($doc->profid == 0) { // only if no privilege yet
	
	  switch ($doc->defProfFamId) {
	  case FAM_ACCESSDOC:
	    $profid=$this->getValue("FLD_PDOCID",0);
	    if ($profid > 0) {
	      $doc->setProfil($profid);
	      $doc->modify();
	    }
	    break;
	  case FAM_ACCESSDIR:
	    $profid=$this->getValue("FLD_PDIRID",0);
	    if ($profid > 0) {
	      $doc->setProfil($profid);
	      // copy default privilege if not set
	      if ($doc->getValue("FLD_PDIRID") == "") {
		$doc->setValue("FLD_PDIRID", $this->getValue("FLD_PDIRID"));
		$doc->setValue("FLD_PDIR", $this->getValue("FLD_PDIR"));
	      }
	      if ($doc->getValue("FLD_PDOCID") == "") {
		$doc->setValue("FLD_PDOCID", $this->getValue("FLD_PDOCID"));
		$doc->setValue("FLD_PDOC", $this->getValue("FLD_PDOC"));
	      }
	      $doc->modify();
	    }
	    break;

	  }
	}
      }
      if ($doc->prelid == "") {
	$doc->prelid=$this->initid;
	$doc->modify(true,array("prelid"),true);
      }

      if ($err == "") {
	$this->updateFldRelations();
	// use post virtual method
	if (!$noprepost) $err=$this->postInsertDoc($docid,false);
      }
    }
    return $err;
  }
  // --------------------------------------------------------------------
  /**
   * insert multiple document reference in this folder
   *
   * if mode is latest the user always see latest revision 
   * if mode is static the user see the revision which has been inserted
   * @param array doc array document  for the insertion
   * @param string $mode latest|static 
   * @return string error message, if no error empty string
   */
  function InsertMDoc($tdocs, $mode="latest",$noprepost=false) {
    
    $err=$this->canModify();
    if ($err!= "") return $err;
    $tAddeddocids=array();

    // verify if doc family is autorized

    $qf = new QueryDir($this->dbaccess);
    foreach ($tdocs as $k=>$tdoc) {

      if (! $this->isAuthorized($tdoc["fromid"])) {
	$err.= "\n".sprintf(_("Cannot add %s in %s folder, restriction set to add this kind of document"),  $tdoc["title"] ,$this->title);
      } else {
	switch ($mode) {
	case "static":

	  $qf->qtype='F'; // fixed document
	  $docid=$tdoc["id"];
	  $qf->childid=$tdoc["id"]; // initial doc
	  break;
	case "latest":
	default:
      
	  $qf->qtype='S'; // single user query
	  $docid=$tdoc["initid"];
	  $qf->childid=$tdoc["initid"]; // initial doc
    
	  break;
	}  


	$qf->id="";
	$qf->dirid=$this->initid; // the reference folder is the initial id
	$qf->query="";
	// use post virtual method
	if (!$noprepost) $err=$this->preInsertDoc($tdoc["initid"],true);

	if ($err == "") {
	  $err = $qf->Add();
	  if ($err == "") {
	    AddLogMsg(sprintf(_("Add %s in %s folder"), $tdoc["title"], $this->title));
	  
	    $tAddeddocids[]=$docid;;
	    // use post virtual method
	    //	    if (!$noprepost) $err=$this->postInsertDoc($tdoc["initid"],true);
	  }
	}
      }
    }


    // use post virtual method
    if (!$noprepost){
      $this->updateFldRelations();
      $err.=$this->postMInsertDoc($tAddeddocids);
    }

    return $err;
  }

  /**
   * insert multiple static document reference in this folder
   * be carreful : not verify restriction folders
   * to be use when many include (verification constraint must ne set before by caller)
   *
   * @param array $tdocids identificator documents  for the insertion  
   * @return string error message, if no error empty string
   */
  function QuickInsertMSDocId($tdocids) {
    
    $err=$this->canModify();
    if ($err!= "") return $err;
    $qf = new QueryDir($this->dbaccess);
    $qf->qtype='S'; // single user query
    $qf->dirid=$this->initid; // the reference folder is the initial id
    $qf->query="";
    foreach ($tdocids as $k=>$docid) {
      $tcopy[$docid]["childid"]=$docid;     
    }

    $err=$qf->Adds($tcopy,true);
    $this->updateFldRelations();
    
    return $err;
  }
  /**
   * insert all static document which are included in $docid in this folder
   * be carreful : not verify restriction folders
   * to be use when many include (verification constraint must ne set before by caller)
   *
   * @param int $docid identificator document  for the insertion  (must be initial id)
   * @return string error message, if no error empty string
   */
  function insertFolder($docid) {
    if (!is_numeric($docid)) return sprintf(_("Dir::insertFolder identificator [%s] must be numeric"),$docid);
    if ($this->isLocked(true)) return sprintf(_("folder is locked. Cannot containt modification"));
    // need this privilege
    $err = $this->Control("modify");
    if ($err!= "") return $err;
    

    $err=$this->exec_Query(sprintf("insert INTO fld (select %d,query,childid,qtype from fld where dirid=%d);",$this->initid,$docid));
    
    
    $this->updateFldRelations();
    return $err;
  }
  // --------------------------------------------------------------------
  function getQids($docid) {
    // return array of queries id includes in a directory
    // --------------------------------------------------------------------
      
    $tableid = array();
  
    $doc = new_Doc($this->dbaccess, $docid);
    $query = new QueryDb($this->dbaccess,"QueryDir");
    $query -> AddQuery("dirid=".$this->id);
    $query -> AddQuery("((childid=$docid) and (qtype='F')) OR ((childid={$doc->initid}) and (qtype='S'))");
    $tableq=$query->Query();
  
    if ($query->nb > 0)
      {
	while(list($k,$v) = each($tableq)) 
	  {
	    $tableid[$k] = $v->id;
	  }
	unset ($tableq);
      }
  
  
    return($tableid);
  }
  // --------------------------------------------------------------------

  /**
   * delete a document reference in this folder
   *
   * @param int $docid document ident for the deletion
   * @param bool $noprepost if true then the virtuals methods {@link preUnlinkDoc()} and {@link postUnlinkDoc()} are not called
   * @return string error message, if no error empty string
   */
  function DelFile($docid,$noprepost=false ) {         
    $err=$this->canModify();
    if ($err!= "") return $err;

    // use pre virtual method
    if (!$noprepost)    $err=$this->preUnlinkDoc($docid);
    if ($err!= "") return $err;
   
    $doc = new_Doc($this->dbaccess,$docid);
    $docid=$doc->initid;
    
    //if (count($qids) == 0) $err = sprintf(_("cannot delete link : link not found for doc %d in folder %d"),$docid, $this->initid);
    if ($err != "") return $err;

    // search original query
    $qf = new QueryDir($this->dbaccess, array($this->initid,$docid));
    if (!($qf->isAffected())) $err = sprintf(_("cannot delete link : initial query not found for doc %d in folder %d"),$docid, $this->initid);
  
    if ($err != "") return $err;

    if ($qf->qtype == "M") $err = sprintf(_("cannot delete link for doc %d in folder %d : the document comes from a user query. Delete initial query if you want delete this document"),$docid, $this->initid);
  
    if ($err != "") return $err;
    $qf->Delete();

    
    if ($doc->prelid == $this->initid) {
      $doc->prelid="";
      $doc->modify(true,array("prelid"),true);
    }


    AddLogMsg(sprintf(_("Delete %d in %s folder"), $docid, $this->title));

    // use post virtual method
    if (!$noprepost) {
      $this->updateFldRelations();
      $err=$this->postUnlinkDoc($docid);
    }
  
    return $err;
  }
  // --------------------------------------------------------------------


  /**
   * return families that can be use in insertion
   * @param int $classid : restrict for same usefor families
   */
  function getAuthorizedFamilies($classid=0) {
    
    if (! isset($this->authfam)) {
      $tfamid = $this->getTValue("FLD_FAMIDS");
      $tfam   = $this->getTValue("FLD_FAM");
    
      

      $allbut=$this->getValue("FLD_ALLBUT");
     
      if (($allbut != "1") && ((count($tfamid) == 0) || ((count($tfamid) == 1) && ($tfamid[0]==0)))) {
	$this->norestrict=true;
	return;
      }

      $this->norestrict=false;;
      $tclassdoc=array();
      if ($allbut != "1") {
	include_once("FDL/Lib.Dir.php");
	$tallfam = GetClassesDoc($this->dbaccess, $this->userid,$classid,"TABLE");


	while (list($k,$cdoc)= each ($tallfam)) {
	  $tclassdoc[$cdoc["id"]]=$cdoc;	 
	  //	  $tclassdoc += $this->GetChildFam($cdoc["id"]);	  
	}
	// suppress undesirable families

	reset($tfamid);
	while (list($k,$famid)= each ($tfamid)) {
	  $tnofam = $this->GetChildFam(intval($famid));
	  
	  unset($tclassdoc[intval($famid)]);
	  foreach ($tnofam as $ka=>$va) {
	    unset($tclassdoc[intval($ka)]);
	    
	  }
	}
      } else {
	//add families
	while (list($k,$famid)= each ($tfamid)) {
	  $tfdoc=getTDoc($this->dbaccess,$famid);
	  if ($tfdoc && controlTdoc($tfdoc,'icreate'))  $tclassdoc[intval($famid)]=array("id"=> intval($famid),
									       "title"=>$tfam[$k]);
	  $tclassdoc += $this->GetChildFam(intval($famid));
	}
      
      }
      $this->authfam=$tclassdoc;
    }
    $this->kauthfam = array_keys($this->authfam);
    return $this->authfam;
  }  

  /**
   * return families that can be use in insertion
   * @param int $classid : restrict for same usefor families
   */
  public function isAuthorized($classid) {
    
    if (! isset($this->norestrict)) {
      $this->getAuthorizedFamilies();

    }
    if ($this->norestrict) return true;

    if (isset($this->authfam[$classid])) return true;
    
    return false;
  }  
  /**
   * return document includes in folder
   * @param bool $controlview if false all document are returned else only visible for current user  document are return
   * @param array $filter to add list sql filter for selected document
   * @param int $famid family identificator to restrict search 
   * @param string $qtype type os result TABLE|LIST|ITEM
   * @return array array of document array
   */
  public function getContent($controlview=true,$filter=array(),$famid="",$qtype="TABLE",$trash="") {
    include_once("FDL/Lib.Dir.php");
    if ($controlview) $uid=$this->userid;
    else $uid=1;
    $tdoc = getChildDoc($this->dbaccess, $this->initid ,0,"ALL", $filter, $uid, $qtype,$famid="",false, "title",true,$trash);
    return $tdoc;    
  }

  /**
   * update folder relations
   */
  public function updateFldRelations() {
    if ($this->doctype=='T') return;
    include_once("FDL/Class.DocRel.php");
    $nattr = $this->GetNormalAttributes();
    $or=new DocRel($this->dbaccess);
    $or->sinitid=$this->initid;
    $or->resetRelations("folder");
    $q=new QueryDb($this->dbaccess,"QueryDir");
    $tv=$q->Query(0,0,"TABLE","select childid from fld where dirid=".$this->initid." and qtype='S'");
    if (is_array($tv)) {
    foreach ($tv as $tq) {
      $val=$tq["childid"];
      $t=getTDoc($this->dbaccess,$val);
      if ($t) {
	  $or->cinitid=$t["initid"];
	  $or->ctitle=$t["title"];
	  $or->cicon=$t["icon"];
	  $or->stitle=$this->title;
	  $or->sicon=$this->icon;
	  $or->type="folder";
	  $or->doctype=$this->doctype;
	  $or->Add();
	}
    }
  }
  }

  /**
   * return number of item in the static folder
   * @param boll $onlyprimary set to true if you wnat only document linked by primary relation
   * @return int -1 if it is not a static folder
   */
  public function count($onlyprimary=false) {
    if ($onlyprimary) {
      $tdoc = $this->getPrimaryChild();
      if ($tdoc) return count($tdoc);      
    } else {
      $q = new QueryDb($this->dbaccess,"QueryDir");  
      $tv=$q->Query(0,0,"TABLE","select childid from fld where dirid=".$this->initid." and qtype='S'");
      if (is_array($tv)) return count($tv);
    }
    return -1;
  }

  
  /**
   * get  document which primary relation is this folder
   * 
   *
   * @return array of doc  (array document)
   */
  public function getPrimaryChild() {
    $filter[]="prelid=".$this->initid;
    return $this->getContent(true,$filter);
  }
  
  /**
   * delete all document which primary relation is the folder (recurively)
   * different of {@see Clear()}
   * all document are put in the trash (zombie mode)
   * @return array of possible errors. Empty array means no errors
   */
  public function deleteItems() {
    $filter[]="prelid=".$this->initid;
    $lpdoc= $this->getContent(false,$filter,"","ITEM");

    $terr=array();
    while ($doc=getNextDoc($this->dbaccess,$lpdoc)) {
      $coulddelete=true;
      if ($doc->doctype=='D') {
	$terr=array_merge($terr,$doc->deleteItems());
	foreach ($terr as $id=>$err) {
	  if ($err != "") $coulddelete=false;
	}
      }
      if ($coulddelete) $terr[$doc->id]=$doc->delete();
    }
    return $terr;        
  }

  /**
   * copy (clone) all documents which primary relation is the folder (recurively)
   * the others documents are just linked
   * all document are put in $indirid folder id 
   * @param int $indirid the folder where put the copies
   * @return array of possible errors. Empty array means no errors
   */
  public function copyItems($indirid) {
    $filter=array();
    $lpdoc= $this->getContent(false,$filter,"","ITEM");

    $terr=array();
    $fld=new_doc($this->dbaccess,$indirid);
    if ($fld->doctype=='D') {
      $err=$fld->control("modify");
      if ($err=="") {
	while ($doc=getNextDoc($this->dbaccess,$lpdoc)) {
	  if ($doc->prelid == $this->initid) {
	    // copy
	    $copy=$doc->copy();
	    if (is_object($copy)) {
	      $fld->addFile($copy->initid);
	    

	      if ($doc->doctype=='D') {
		$terr=array_merge($terr,$doc->copyItems($copy->id));
		
	      }
	    }
	  } else {
	    // link
	    $fld->addFile($doc->initid);
	  }
	  
	  
	}
      }
    }
    return $terr;        
  }

  /**
   * delete the folder and its containt
   * different of {@see Clear()}
   * all document are put in the trash (zombie mode)
   * @return string error message, if no error empty string
   */
  public function deleteRecursive() {
    $err=$this->predocdelete(); // test before try recursive deletion
    if ($err != "") return $err;
    $coulddelete=true;
    $terr=$this->deleteItems();
    $err="";
    foreach ($terr as $id=>$err1) {
      if ($err1 != "") {
	$coulddelete=false;
	$err.="\n$err1";
      }
    }
    if ($coulddelete) $err=$this->delete();
    return $err;
  }

  /**
   * restore all document which primary relation is the folder (recurively)
   * 
   * 
   * @return int -1 if it is not a static folder
   */
  function reviveItems() {
    $filter[]="prelid=".$this->initid;
    $lpdoc= $this->getContent(true,$filter,"","ITEM","only");
    $terr=array();
    while ($doc=getNextDoc($this->dbaccess,$lpdoc)) {
      if ($doc->defDoctype=='D') $terr=array_merge($terr,$doc->reviveItems());
      $terr[$doc->id]=$doc->revive();
    }
    return $terr;        
  }


}

?>