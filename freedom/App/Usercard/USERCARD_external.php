<?php

include_once("FDL/Class.Dir.php");
include_once("FDL/Lib.Dir.php");
include_once("EXTERNALS/fdl.php");




// liste des sociétés
function lsociety($dbaccess, $name) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  $dirid= 0;
  

  $societies =  lfamilly($dbaccess, 124, $name, $dirid, array("fromid=124"));



  $societies +=  lfamilly($dbaccess, 126, $name, $dirid, array("si_idsoc isnull"));
  
  return $societies;
}


// liste des sociétés
function lsite($dbaccess, $name) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,

  $dirid= 0;
  

  return lfamilly($dbaccess, 124, $name, $dirid);
  
}

// liste des société
function laddrsoc($dbaccess, $idc) {
  //'laddrsoc(D,US_IDSOCIETY):US_SOCADDR,US_WORKADDR,US_WORKTOWN,US_WORKPOSTALCODE,US_WORKWEB,US_CEDEX


  $doc = new Doc($dbaccess, $idc);

  if ($doc->isAffected()) {
    $tr[] = array("adresse société",
		  "yes",
		  $doc->getValue("SI_ADDR"),
		  $doc->getValue("SI_TOWN"),
		  $doc->getValue("SI_POSTCODE"),
		  $doc->getValue("SI_WEB"),
		  $doc->getValue("SI_CEDEX"));
  }
  
    $tr[] = array("adresse propre",
		  " ",
		  "?",
		  "?",
		  "?",
		  "?",
		  "?");
  
  return $tr;
  
}


// identification société
function gsociety($dbaccess, $idc) {     
  //gsociety(D,US_IDSOCIETY):US_SOCIETY
  $doc = new Doc($dbaccess, $idc);
  $cl = array($doc->title);

  return ($cl);
  }

// identification société
function gaddrsociety($dbaccess, $idc, $sameaddr) {     
  //gaddrsociety(D,US_IDSOCIETY,US_SOCADDR):US_WORKADDR,US_WORKTOWN,US_WORKPOSTALCODE,US_WORKWEB

  $doc = new Doc($dbaccess, $idc);
  $cl = array(
	      $doc->getValue("SI_ADDR"," "),
	      $doc->getValue("SI_TOWN"," "),
	      $doc->getValue("SI_POSTCODE"," "),
	      $doc->getValue("SI_WEB"," "));

  return ($cl);
  }
?>
