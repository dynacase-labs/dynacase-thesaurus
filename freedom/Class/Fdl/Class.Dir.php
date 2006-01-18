<?php
/**
 * Folder document definition
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Dir.php,v 1.43 2006/01/18 10:24:49 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
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

      $home ->owner = -$this->userid;
      include_once("Class.User.php");
      $user = new User("", $this->userid);
      $home ->title = $user->firstname." ".$user->lastname;
      $home->setTitle($home ->title);
      $home ->icon = 'fldhome.gif';
      $home -> Add(); 

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
	$bas->title=sprintf(_("basket of %s"),$home ->title);
	$bas->Add();
	$home -> AddFile($bas->id); 
	$basid=$bas->id;
      } else {
	$basid=$rq[0]->id;
      }
      global $action;
      $action->parent->param->Set("FREEDOM_IDBASKET",$basid,PARAM_USER.$this->userid,
				  $action->parent->GetIdFromName("FREEDOM"));
    }
      
    return $home;
  }
    
  /**
   * clear containt of this folder
   *
   * @return string error message, if no error empty string
   */
  function Clear() {
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
    // need this privilege
    $err = $this->Control("modify");
    if ($err!= "") return $err;

    // use pre virtual method
    if (!$noprepost) $err=$this->preInsertDoc($docid);
    if ($err!= "") return $err;    

    // verify if doc family is autorized
    $doc= new_Doc($this->dbaccess, $docid);
    

    if (! $this->isAuthorized($doc->fromid)) return sprintf(_("Cannot add %s in %s folder, restriction set to add this kind of document"),  $doc->title ,$this->title);

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

    if ($err == "") {
      $this->updateFldRelations();
      // use post virtual method
      if (!$noprepost) $err=$this->postInsertDoc($docid,false);
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
    
    // need this privilege
    $err = $this->Control("modify");
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
    
    // need this privilege
    $err = $this->Control("modify");
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
    


    // need this privilege
    $err = $this->Control("modify");
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
	  $tclassdoc[intval($famid)]=array("id"=> intval($famid),
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
  function isAuthorized($classid) {
    
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
   * @return array array of document array
   */
  function getContent($controlview=true,$filter=array(),$famid="") {
    include_once("FDL/Lib.Dir.php");
    if ($controlview) $uid=$this->userid;
    else $uid=1;
    $tdoc = getChildDoc($this->dbaccess, $this->initid ,0,"ALL", $filter, $uid, "TABLE",$famid="");
    return $tdoc;    
  }

  /**
   * update folder relations
   */
  function updateFldRelations() {
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
	  $or->Add();
	}
    }
  }
  }
}

?>