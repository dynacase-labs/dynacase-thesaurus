<?php
/**
 * Generic searches
 *
 * @author Anakeen 2000 
 * @version $Id: generic_search.php,v 1.35 2007/07/30 16:05:27 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/Class.DocSearch.php");
include_once("FDL/freedom_util.php");  
include_once("GENERIC/generic_util.php");  




/**
 * Search a document by keyword
 * @param Action &$action current action
 * @global keyword Http var : keyword to search
 * @global catg Http var : primary folder/search where search
 * @global dirid Http var : secondary search for sub searches
 * @global mode Http var : (REGEXP|FULL)  search mode regular expression or full text
 */
function generic_search(&$action) {
   
  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $catgid=GetHttpVars("catg", getDefFld($action)); // primary folder/search where search
  $dirid=GetHttpVars("dirid", getDefFld($action)); // temporary subsearch
  $mode=GetHttpVars("mode");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $famid = getDefFam($action);
  $action->parent->param->Set("GENE_LATESTTXTSEARCH",
			      setUkey($action,$famid,$keyword),PARAM_USER.$action->user->id,
			      $action->parent->id);

  setSearchMode($action,$famid,$mode);


  if ($keyword) {
    if ($keyword[0]!=">") {
      $dirid=$catgid; 
      $doc = new_Doc($dbaccess, $dirid);
      $pds=$doc->urlWhatEncodeSpec("");
    } else {  // search sub searches   
      $keyword=substr($keyword,1);
      $catg = new_Doc($dbaccess,$catgid );
      $pds=$catg->urlWhatEncodeSpec("");
      $doc = new_Doc($dbaccess, $dirid);
    }
    $searchquery="";
    $sdirid = 0;
    if ($doc->defDoctype == 'S') { // case of search in search doc
      $sdirid = $doc->id;
    } else { // case of search in folder
      if ($doc->id != getDefFld($action))
	$sdirid = $dirid;

    }

    $sdoc = createTmpDoc($dbaccess,5); //new DocSearch($dbaccess);
    $sdoc->title = sprintf(_("search %s"),$keyword);
    if ($sdirid > 0) {
      if ($doc->id == getDefFld($action)) $sdoc->title = sprintf(_("search  contains %s in all state"),$keyword );
      else $sdoc->title = sprintf(_("search contains %s in %s"),$keyword,$doc->getTitle() );
    }
    $sdoc->Add();
  
    

    //    AddwarningMsg( "[dirid:$dirid][catg:$catgid][sdirid:$sdirid]");


    $full=($mode=="FULL");


    $only=(getInherit($action,$famid)=="N");

  
    $sqlfilter=$sdoc->getSqlGeneralFilters($keyword,"yes",false,$full);
    if ($full) {
      //if ($famid > 0) $sqlfilter[]="fromid=".intval($famid); // here function to retrieve descendants
    }

    $query=getSqlSearchDoc($dbaccess, 
			   $sdirid,  
			   ($only)?-($famid):$famid, 
			   $sqlfilter,false,true,"",false);

    $sdoc-> AddQuery($query);

    redirect($action,GetHttpVars("app"),"GENERIC_LIST$pds&mode=$mode&famid=$famid&dirid=".$sdoc->id."&catg=$catgid");
  } else {
    redirect($action,GetHttpVars("app"),"GENERIC_LIST$pds&mode=$mode&famid=$famid&dirid=".$catgid."&catg=$catgid");
    
  }
  
  
}



?>