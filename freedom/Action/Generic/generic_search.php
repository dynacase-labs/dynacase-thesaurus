<?php
/**
 * Generic searches
 *
 * @author Anakeen 2000 
 * @version $Id: generic_search.php,v 1.24 2005/03/03 17:14:13 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/Class.DocSearch.php");
include_once("FDL/freedom_util.php");  
include_once("GENERIC/generic_util.php");  





// -----------------------------------
function generic_search(&$action) {
  // -----------------------------------
   

  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $catgid=GetHttpVars("catg", getDefFld($action)); // folder where search
  $dirid=GetHttpVars("dirid", getDefFld($action)); // folder where search
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($keyword) $action->parent->param->Set("GENE_LATESTTXTSEARCH",
					    setUkey($action,$keyword),PARAM_USER.$action->user->id,
					    $action->parent->id);

  if ($keyword[0]!=">") {
    $dirid=$catgid; 
    $doc = new Doc($dbaccess, $dirid);
    $pds=$doc->urlWhatEncodeSpec("");
  } else {  // search sub searches   
    $keyword=substr($keyword,1);
    $catg = new Doc($dbaccess,$catgid );
    $pds=$catg->urlWhatEncodeSpec("");
    $doc = new Doc($dbaccess, $dirid);
  }


  $sdoc = createDoc($dbaccess,5,false); //new DocSearch($dbaccess);
  $sdoc->doctype = 'T';// it is a temporary document (will be delete after)
  $sdoc->title = sprintf(_("search %s"),$keyword);
  if ($doc->id == getDefFld($action)) $sdoc->title = sprintf(_("search  contains %s in all state"),$keyword );
  else $sdoc->title = sprintf(_("search contains %s in %s"),$keyword,$doc->getTitle() );

  $sdoc->Add();
  
  $searchquery="";
  $sdirid = 0;
  if ($doc->defDoctype == 'S') { // case of search in search doc
    $sdirid = $doc->id;
  } else { // case of search in folder
    if ($doc->id != getDefFld($action))
      $sdirid = $dirid;

  }

  $famid = getDefFam($action);


  $keyword= str_replace("^","£",$keyword);
  $keyword= str_replace("$","£",$keyword);
  $keyword= addslashes($keyword);

  $sqlfilter[]= "locked != -1";
  //  $sqlfilter[]= "doctype ='F'";
  //  $sqlfilter[]= "usefor != 'D'";
  if ($keyword != "") $sqlfilter[]= "values ~* '$keyword' ";

  $query=getSqlSearchDoc($dbaccess, 
			 $sdirid,  
			 $famid, 
			 $sqlfilter);

  $sdoc-> AddQuery($query);

  redirect($action,GetHttpVars("app"),"GENERIC_LIST$pds&dirid=".$sdoc->id."&catg=$catgid");
  
  
}

function setUkey(&$action, $key) {
  
  $famid=getDefFam(&$action);
  $dbaccess = $action->GetParam("FREEDOM_DB");


  $fdoc= new Doc( $dbaccess, $famid);

  $pu = $action->GetParam("GENE_LATESTTXTSEARCH");
  $tr=array();
  if ($pu) {
    // disambled parameter
    $tu = explode("|",$pu);
    
    while (list($k,$v) = each($tu)) {
      list($afamid,$uk) = explode(":",$v);
      $tr[$afamid]=$uk;
    }
  }


 
  $tr[$famid]=$key;

  // rebuild parameter
  $tu=array();
  foreach($tr as $k=>$v) {
    $tu[]="$k:$v";
  }
  return implode("|", $tu);
  
  
  
}
?>