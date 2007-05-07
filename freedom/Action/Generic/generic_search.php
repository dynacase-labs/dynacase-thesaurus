<?php
/**
 * Generic searches
 *
 * @author Anakeen 2000 
 * @version $Id: generic_search.php,v 1.33 2007/05/07 09:56:47 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/Class.DocSearch.php");
include_once("FDL/freedom_util.php");  
include_once("GENERIC/generic_util.php");  





function generic_search(&$action) {
   
  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $catgid=GetHttpVars("catg", getDefFld($action)); // primary folder/search where search
  $dirid=GetHttpVars("dirid", getDefFld($action)); // temporary subsearch
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $famid = getDefFam($action);
  $action->parent->param->Set("GENE_LATESTTXTSEARCH",
			      setUkey($action,$famid,$keyword),PARAM_USER.$action->user->id,
			      $action->parent->id);
  
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


    $sdoc = createDoc($dbaccess,5,false); //new DocSearch($dbaccess);
    $sdoc->doctype = 'T';// it is a temporary document (will be delete after)
    $sdoc->title = sprintf(_("search %s"),$keyword);
    if ($catgid > 0) {
      if ($doc->id == getDefFld($action)) $sdoc->title = sprintf(_("search  contains %s in all state"),$keyword );
      else $sdoc->title = sprintf(_("search contains %s in %s"),$keyword,$doc->getTitle() );
    }
    $sdoc->Add();
  
    $searchquery="";
    $sdirid = 0;
    if ($doc->defDoctype == 'S') { // case of search in search doc
      $sdirid = $doc->id;
    } else { // case of search in folder
      if ($doc->id != getDefFld($action))
	$sdirid = $dirid;

    }



    $full=true;


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

    redirect($action,GetHttpVars("app"),"GENERIC_LIST$pds&famid=$famid&dirid=".$sdoc->id."&catg=$catgid");
  } else {
    redirect($action,GetHttpVars("app"),"GENERIC_LIST$pds&famid=$famid&dirid=".$catgid."&catg=$catgid");
    
  }
  
  
}



?>