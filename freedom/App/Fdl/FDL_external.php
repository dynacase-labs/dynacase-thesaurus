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
?>
