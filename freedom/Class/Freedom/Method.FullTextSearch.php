<?php
/**
 * Attribute Document Object Definition
 *
 * @author Anakeen 2002
 * @version $Id: Method.FullTextSearch.php,v 1.1 2004/10/14 14:15:35 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */

/**
 * Attribute Document Class
 *
 */
var $defaultedit= "FREEDOM:EDITFTSEARCH";

function fileNameToId($name) {
  $ifile = basename($name);
  $te = explode(".", $ifile);
  return $te[0];
}


function editftsearch() {
  global $action;

  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/edittable.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/editdsearch.js");

  $this->lay->set("selfam","");
  $tclassdoc=GetClassesDoc($this->dbaccess, $action->user->id);
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"] = $cdoc->initid;
    $selectclass[$k]["classname"] = $cdoc->title;
    if ($cdoc->initid == $this->se_famid) {
      $selectclass[$k]["selected"]="selected";
      $this->lay->set("selfam",$this->se_famid);
    } else {
      $selectclass[$k]["selected"]="";
    }
  }
  $this->lay->SetBlockData("SELECTCLASS", $selectclass);
  $this->setFamidInLayout();
  $this->editattr();

}


function GetFullTextResultDocs ($dbaccess, 
				$dirid, 
				$start="0", $slice="ALL", $sqlfilters=array(), 
				$userid=1, 
				$qtype="LIST", $fromid="",$distinct=false, $orderby="title",$latest=true) {
  global $action;
  $tdocs = array();
  $fulltextsearch = new Doc($dbaccess, $dirid);

  $s_rcount = $action->GetParam("FREEDOM_FULLTEXT_RESULT", 100); 

  $keyword=$fulltextsearch->se_key;
  $title=GetHttpVars("title", _("new search ").'['.$keyword).']'; // title of the search
  $latest=$fulltextsearch->se_latest;
  $famid=$fulltextsearch->se_famid;

  switch (strtoupper($fulltextsearch->fts_where)) 
    {
    case "BEGIN": $s_mode = UDM_MATCH_BEGIN; break;
    case "ENDING": $s_mode = UDM_MATCH_END; break;
    case "ENTIRE": $s_mode = UDM_MATCH_WORD; break;
    default: $s_mode = UDM_MATCH_SUBSTR; 
    }

  switch (strtoupper($fulltextsearch->fts_logical)) 
    {
    case "ALL": $s_match = UDM_MODE_ALL; break;
    case "PHRASE": $s_match = UDM_MODE_PHRASE; break;
    default: $s_match = UDM_MODE_ANY; 
    }
  
  $tcount = 0;
  $rcount = 0;
  $found  = false;
  $s = new FTSMnoGoSearch();
  if (!$s) $action->AddWarningMsg(_("can't connect mnogosearch db"));
  else {
    
    $resultfiles = $s->FTSearch($keyword, $s_mode, $s_match, $s_rcount );
    if ($s->found && $s->rcount>0) {
      $idoc = 0;
      while (list($k, $v) = each($resultfiles)) {
	$ndoc = new Doc($dbaccess, $this->fileNameToId($v["name"]));
	if ($ndoc->famid == $famid || $famid == 0) {
	  $tdocs[$idoc] = $ndoc;
	  $idoc++;
	}
      }
    }
    $tdocs[$idoc] = new Doc($dbaccess, 1012);
    $idoc++;
    $s->Close();
  }
  return $tdocs;
}

function isStaticSql() { 
  return true; 
}


function PostModify() {
  $this->setValue("SE_SQLSELECT", "select * from doc32 where false");
  $this->AddQuery($this->getQuery());
}

?>
