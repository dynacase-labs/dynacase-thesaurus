<?php
/**
 * Functions used for edition help
 *
 * @author Anakeen 2003
 * @version $Id: FDL_external.php,v 1.43 2006/07/27 16:17:05 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */

include_once("FDL/Class.Dir.php");
include_once("FDL/Lib.Dir.php");




function vault_filename($th, $fileid) {


  if (ereg ("(.*)\|(.*)", $fileid, $reg)) {	 
    // reg[1] is mime type
      $vf = newFreeVaultFile($th->dbaccess);
    if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
    else $fname=sprintf(_("file %d"),$th->initid);
  } else {
    $fname=sprintf(_("file %d"),$th->initid);
  }

  return array($fname);
}


/**
 * Functions used for edition help
 *
 * @param string $dbaccess database specification
 * @param int $docid identificator document 
 * @return array first item : the title
 */
function gettitle($dbaccess, $docid) {

  $doc=new_Doc($dbaccess, $docid);
  if ($doc->isAffected())  return array($doc->title);
  return array("?"," "); // suppress
}

/**
 * link enum definition from other def
 */
function linkenum($famid,$attrid) {
  
  $dbaccess=getParam("FREEDOM_DB");
  if (! is_numeric($famid)) $famid=getFamIdFromName($dbaccess,$famid);
  $soc = new_Doc($dbaccess, $famid);
  if ($soc->isAffected()) {
    $a = $soc->getAttribute($attrid);
    return $a->phpfunc;
  }
  return "";
}
// liste de personnes
function lmail( $dbaccess, $name) {     

  global $action;
  

  $filter=array();
  if ($name != "") {
    $filter[]="(title ~* '$name') or (us_mail ~* '$name')";
  }

  $filter[] = "us_mail is not null";
  $famid=getFamIdFromName($dbaccess,"USER");

  $tinter = getChildDoc($dbaccess, 0,0,100, $filter,$action->user->id,"TABLE",$famid);
  
  $tr = array();

  while(list($k,$v) = each($tinter)) {
            
    $mail = getv($v,"us_mail");
    $usw=getv($v,"us_whatid");
    $uid="";
    if ($usw > 0) {
      $uid=$v["id"];
      $type="link"; 
    } else {
      $type="plain";
      $uid=" ";
    }
    $tr[] = array($v["title"] ,$v["title"]." <$mail>",$uid,$type);
    
  }
  return $tr;  
}

// liste des familles
function lfamilies($dbaccess, $name='',$subfam="") {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  
  if ($subfam=="") {
    $tinter = GetClassesDoc($dbaccess, $action->user->id,0,"TABLE");
  } else {
    if (! is_numeric($subfam)) $subfam=getFamIdFromName($dbaccess,$subfam);
    $cdoc = new_Doc($dbaccess,$subfam);
    $tinter = $cdoc->GetChildFam();
    $tinter[]=get_object_vars($cdoc);
  }
  
  $tr = array();


  while(list($k,$v) = each($tinter)) {
            
    if (($name == "") || (eregi("$name", $v["title"] , $reg))) {

      $tr[] = array($v["title"] ,
		    $v["id"],$v["title"]);
    
    }
  }
  return $tr;  
}


// liste des documents par familles
/**
 * list of documents of a same family
 *
 * @param string $dbaccess database specification
 * @param string $famid family identifier (if 0 any family). It can be internal name
 * @param string $name string filter on the title
 * @param int $dirid identifier of folder for restriction to a folder tree (deprecated)
 * @param array $filter additionnals SQL filters
 * @return array/string*3 array of (title, identifier, title)
 */
function lfamilly($dbaccess, $famid, $name="", $dirid=0, $filter=array(),$idid="id") {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  

  if (! is_numeric($famid)) {
    $famid=getFamIdFromName($dbaccess,$famid);
  }


  if ($name != "") {
    $filter[]="title ~* '.*$name.*'";
  }


  $tinter = getChildDoc($dbaccess, $dirid,0,200, $filter,$action->user->id,"TABLE",$famid);
  
  $tr = array();


  while(list($k,$v) = each($tinter)) {
            
    $tr[] = array($v["title"] ,
		  $v[$idid],$v["title"]);
    
  }
  return $tr;
  
}

// alias name
function lfamily($dbaccess, $famid, $name="", $dirid=0, $filter=array(),$idid="id") {
  return lfamilly($dbaccess, $famid, $name, $dirid, $filter,$idid);
}


/**
 * list of documents of a same family and which are in the $kid category
 *
 * @param string $dbaccess database specification
 * @param string $famname family internal name
 * @param string $aid enum attribute identifier 
 * @param string $kid enum key to search
 * @param string $name string filter on the title
 * @param array $filter additionnals SQL filters
 * @return array/string*3 array of (title, identifier, title)
 */
function lkfamily($dbaccess, $famname, $aid, 
		  $kid, $name, $filter=array()) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  
  if ($name != "") {
    $filter[]="title ~* '.*$name.*'";
  }

  $tinter = getKindDoc($dbaccess, $famname, $aid,$kid,$name,$filter);    
  
  $tr = array();


  while(list($k,$v) = each($tinter)) {
            
    $tr[] = array($v["title"] ,
		  $v["id"],$v["title"]);
    
  }
  return $tr;
  
}
/**
 * return list of string for multiple static choice
 *
 * @param string $val filter value - can be empty => see all choices
 * @param string $enum possible choices like 'the first|the second|the last'
 * @return array/string*2 array of (enum, enum)
 */
function lenum($val, $enum) {
  // $enum like 'a|b|c'
 

  $tenum=explode("|",$enum);

  $tr=array();

  while(list($k,$v) = each($tenum)) {
            
    if (($val == "") || (eregi("$val", $v , $reg)))
      $tr[] = array($v , $v);
    
  }
  return $tr;
  
}

// liste des profils
function lprofil($dbaccess, $name) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  $dirid= 0;
  
  
  return lfamilly($dbaccess, 3, $name, $dirid);
  
}

// liste des masque
function lmask($dbaccess, $name, $maskfamid="") {

  $filter=array();
  if ($maskfamid > 0) {
    $mdoc = new_Doc($dbaccess,$maskfamid);
    $chdoc=$mdoc->GetFromDoc();
    $filter[]=GetSqlCond($chdoc,"msk_famid");
    //    $filter[]="msk_famid='$maskfamid'"; // when workflow will have attribut to say the compatible families
  }
  return lfamilly($dbaccess, "MASK", $name, 0, $filter);
  
}

/**
 * search list not filters
 */
function lsearches($dbaccess, $name) {

  $filter=array("fromid=5 or fromid=16");
  return lfamilly($dbaccess, "SEARCH", $name, 0, $filter);
  
}
// liste des zones possibles
// $tview VCONS|VEDIT
function lzone_($dbaccess, $tview, $famid ="") {
  $tz=array();

  $filter=array();
  if ($famid > 0) {
    $fdoc = new_Doc($dbaccess,$famid);
    $cdoc=createDoc($dbaccess,$famid,false);
    if ($tview == "VEDIT") $tz=$cdoc->eviews;
    else $tz=$cdoc->cviews;
    $oz=lzone_($dbaccess, $tview, $fdoc->fromid);
    $tz = array_merge($oz,$tz);
    
  } else {
    $fdoc = new_Doc($dbaccess);
    if ($tview == "VEDIT") $tz=$fdoc->eviews;
    else $tz=$fdoc->cviews;
  }
  

  return $tz;
  
}

function lzone($dbaccess, $tview, $famid ="") {
  $tz=lzone_($dbaccess, $tview, $famid);
  $tz=array_unique($tz);
  foreach ($tz as $v) {
    $tr[]=array($v,$v);
  }
  

  return $tr;
  
}

function lview($tidview, $tlview) {

  foreach ($tidview as $k=>$v) {
    $tr[]=array($tlview[$k],$v,$tlview[$k]);
  }
  

  return $tr;
  
}

// liste des attributs d'une famille
function getDocAttr($dbaccess, $famid, $name="") {
  return getSortAttr($dbaccess, $famid, $name,false);
}

// liste des attributs triable d'une famille
function getSortAttr($dbaccess, $famid, $name="",$sort=true) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  
  $doc = createDoc($dbaccess, $famid,false);
  // internal attributes
  $ti = array("title" => _("doctitle"),
	      "revdate" => _("revdate"),
	      "revision" => _("revision"),
	      "state" => _("state"));
  
  $tr = array();
  while(list($k,$v) = each($ti)) {
   
      $tr[] = array($v , $k,$v);
    
  }

  if ($sort)  $tinter = $doc->GetSortAttributes();
  else $tinter = $doc->GetNormalAttributes();
  

  while(list($k,$v) = each($tinter)) {
    if (($name == "") ||    (eregi("$name", $v->labelText , $reg)))
      $tr[] = array($v->labelText ,
		    $v->id,$v->labelText);
    
  }
  return $tr;  
}


function laction($dbaccess, $famid, $name,$type) {
  $filter=array();
  $filter[]="act_type='$type'";
  return lfamilly($dbaccess, $famid,$name,0,$filter);
}

/**
 * return list of what application
 */
function lapplications($n="") {
  $q=new QueryDb("","Application");

  $tr = array();
  if ($n != "") $q->AddQuery("name ~* '$n'");
  $la=$q->Query(0,0,"TABLE");
  foreach ($la as $k=>$v) {
     $tr[] = array($v["name"].":".$v["short_name"],
		    $v["name"]);
  }
  return $tr;
}
/**
 * return list of what action for one application
 */
function lactions($app,$n="") {
  $tr = array();
  $q=new QueryDb("","Application"); 
  $q->AddQuery("name = '$app'");
  $la=$q->Query(0,0,"TABLE");
  if ($q->nb == 1) {
    $appid=$la[0]["id"];
    if ($appid > 0) {
      $q=new QueryDb("","Action");
      $q->AddQuery("id_application = $appid");      
      if ($n != "") $q->AddQuery("name ~* '$n'");
      $la=$q->Query(0,0,"TABLE");
      foreach ($la as $k=>$v) {
	$tr[] = array($v["name"].":"._($v["short_name"]),
		      $v["name"]);
      }
    }
  }
  return $tr;
}
?>
