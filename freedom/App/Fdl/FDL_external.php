<?php

include_once("FDL/Class.Dir.php");
include_once("FDL/Lib.Dir.php");




function vault_filename($th, $fileid) {


  if (ereg ("(.*)\|(.*)", $fileid, $reg)) {	 
    // reg[1] is mime type
      $vf = new VaultFile($th->dbaccess, "FREEDOM");
    if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
    else $fname=sprintf(_("file %d"),$th->initid);
  } else {
    $fname=sprintf(_("file %d"),$th->initid);
  }

  return array($fname);
}


function gettitle($dbaccess, $docid) {

  $doc=new Doc($dbaccess, $docid);
  if ($doc->isAffected())  return array($doc->title);
  return array(" "," "); // suppress
}

// liste de personnes
function lmail( $dbaccess, $name) {     

  global $action;
  //comlist(A,D,PRO_COM):PRO_COM,PRO_IDCOM
  

  

  $filter=array();
  if ($name != "") {
    $filter[]="title ~* '.*$name.*'";
  }



  $tinter = getChildDoc($dbaccess, 0,0,100, $filter,$action->user->id,"LIST",120);
  
  $tr = array();

  while(list($k,$v) = each($tinter)) {
            
    $mail = $v->getValue("US_MAIL");
    if ($mail != "")  $tr[] = array($v->title ,$v->title." <$mail>");
    
  }
  return $tr;  
}


// liste des sociétés
function lfamilly($dbaccess, $famid, $name, $catgid=0, $filter=array()) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  $catgid= 0;
  


  if ($name != "") {
    $filter[]="title ~* '.*$name.*'";
  }


  $tinter = getChildDoc($dbaccess, $catgid,0,100, $filter,$action->user->id,"TABLE",$famid);
  
  $tr = array();


  while(list($k,$v) = each($tinter)) {
            
    $tr[] = array($v["title"] ,
		  $v["id"],$v["title"]);
    
  }
  return $tr;
  
}


// liste des profils
function lprofil($dbaccess, $name) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  $catgid= 0;
  
  
  return lfamilly($dbaccess, 3, $name, $catgid);
  
}

?>
