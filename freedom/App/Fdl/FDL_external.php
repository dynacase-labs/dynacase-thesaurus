<?php

include_once("FDL/Class.Dir.php");




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

  return array($doc->title);
}

// liste de personnes
function lmail( $dbaccess, $name) {     

  global $action;
  //comlist(A,D,PRO_COM):PRO_COM,PRO_IDCOM
  

  

  $filter=array();
  if ($name != "") {
    $filter[]="doc.title ~* '.*$name.*'";
  }
  $filter[]="doc.classname = 'DocUser'";


  $tinter = getChildDoc($dbaccess, 0,0,100, $filter,$action->user->id);
  
  $tr = array();

  while(list($k,$v) = each($tinter)) {
            
    $mail = $v->getValue("US_MAIL");
    if ($mail != "")  $tr[] = array($v->title ,$v->title." <$mail>");
    
  }
  return $tr;  
}


// liste des sociétés
function lfamilly($dbaccess, $famid, $name, $catgid=0) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  $catgid= 0;
  
  $filter=array();
  $filter[]="doc.fromid=$famid";
  if ($name != "") {
    $filter[]="doc.title ~* '.*$name.*'";
  }
  $filter[]="doc.doctype='F'";

  $tinter = getChildDoc($dbaccess, $catgid,0,"ALL", $filter,$action->user->id,"TABLE");
  
  $tr = array();

  $tr[] = array(_("unreferenced")," "," " );
  while(list($k,$v) = each($tinter)) {
            
    $tr[] = array($v["title"] ,
		  $v["id"],$v["title"]);
    
  }
  return $tr;
  
}

?>
