<?php
/**
 * Functions used for edition help of USER, GROUP & SOCIETY Family
 *
 * @author Anakeen 2003
 * @version $Id: USERCARD_external.php,v 1.6 2003/08/18 15:47:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */

include_once("FDL/Class.Dir.php");
include_once("FDL/Lib.Dir.php");
include_once("EXTERNALS/fdl.php");





/**
 * society list
 *
 * the SOCIETY documents and the SITE documents wich doesn't have society father
 * @param string $dbaccess database specification
 * @param string $name string filter on the title
 * @return array/string*3 array of (title, identifier, title)
 * see lfamilly()
 */
function lsociety($dbaccess, $name) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  $dirid= 0;
  

  $societies =  lfamilly($dbaccess, 124, $name, $dirid, array("fromid=124"));



  $societies +=  lfamilly($dbaccess, 126, $name, $dirid, array("si_idsoc isnull"));
  
  return $societies;
}


/**
 * site list
 *
 * all the SITE documents
 * @param string $dbaccess database specification
 * @param string $name string filter on the title
 * @return array/string*3 array of (title, identifier, title)
 * see lfamilly()
 */
function lsite($dbaccess, $name) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,

  $dirid= 0;
  

  return lfamilly($dbaccess, 124, $name, $dirid);
  
}

// liste des société
function laddrsoc($dbaccess, $idc) {
  //'laddrsoc(D,US_IDSOCIETY):US_SOCADDR,US_WORKADDR,US_WORKTOWN,US_WORKPOSTALCODE,US_WORKWEB,US_CEDEX,US_COUNTRY


  $doc = new Doc($dbaccess, $idc);

  if ($doc->isAffected()) {
    $tr[] = array("adresse société",
		  "yes",
		  $doc->getValue("SI_ADDR"),
		  $doc->getValue("SI_TOWN"),
		  $doc->getValue("SI_POSTCODE"),
		  $doc->getValue("SI_WEB"),
		  $doc->getValue("SI_CEDEX"),
		  $doc->getValue("SI_COUNTRY"));
  }
  
  $tr[] = array("adresse propre",
		  " ",
		  "?",
		  "?",
		  "?",
		  "?",
		  "?",
		  "?");
  
  return $tr;
  
}

// liste des personnes d'une société
function lpersonnesociety( $dbaccess, $idsociety, $name="" ) {  

  // 'lpersonnesociety(D,CMF_IDSFUR,CMF_PFUR):CMF_IDPFUR,CMF_PFUR,CMF_AFUR,CMF_MFUR,CMF_TFUR,CMF_FFUR,CMF_SFUR,CMF_IDSFUR


  $filter=array();

  if ($idsociety > 0)  $filter[]="us_idsociety = '$idsociety'";
  
  if ($name != "")     $filter[]="title ~* '$name'";
  


  $tinter = getChildDoc($dbaccess, 0 ,0,100, $filter,$action->user->id, "TABLE",
			getFamIdFromName($dbaccess,"USER"));


  
  $tr = array();


  while(list($k,$v) = each($tinter)) {
            
    $sidfur= setv($v,"us_idsociety");
    
    $sfur= setv($v,"us_society");
    $afur= setv($v,"us_workaddr")."\n".setv($v,"us_workpostalcode")." ".setv($v,"us_worktown")." ".setv($v,"us_workcedex");
    if (setv($v,"us_country") != "") $afur.="\n".setv($v,"us_country");
    $tfur= setv($v,"us_phone");
    $ffur= setv($v,"us_fax");
    $mfur= setv($v,"us_mail");

    $tr[] = array($v["title"] ,$v["id"],$v["title"], $afur, $mfur, $tfur,$ffur, $sfur, $sidfur);
    
  }
  return $tr;
  
}


// identification société
function gsociety($dbaccess, $idc) {     
  //gsociety(D,US_IDSOCIETY):US_SOCIETY
  $doc = new Doc($dbaccess, $idc);
  $cl = array($doc->title);

  return ($cl);
  }


// get enum list from society document
function enumscatg() {
  $dbaccess=getParam("FREEDOM_DB");
  $soc = new Doc($dbaccess, 124);

  if ($soc->isAffected()) {
    $a = $soc->getAttribute("si_catg");
    return $a->phpfunc;
  }
  return "";
}

function members($dbaccess, $groupid, $name="") {
  $doc  = new Doc($dbaccess, $groupid);
  $tmembers= $doc->getTvalue("GRP_USER");
  $tmembersid= $doc->getTvalue("GRP_IDUSER");

  $tr = array();
  while(list($k,$v)=each($tmembersid)) {
    if (($name == "") || (eregi($name,$tmembers[$k])))
      $tr[] = array($tmembers[$k] ,
		    $v,$tmembers[$k]);
    
  }
  return $tr;  

}


?>
