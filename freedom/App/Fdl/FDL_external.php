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
  return array("?"," "); // suppress
}

// liste de personnes
function lmail( $dbaccess, $name) {     

  global $action;
  //comlist(A,D,PRO_COM):PRO_COM,PRO_IDCOM
  

  

  $filter=array();
  if ($name != "") {
    $filter[]="title ~* '.*$name.*'";
  }

  $famid=getFamIdFromName($dbaccess,"USER");

  $tinter = getChildDoc($dbaccess, 0,0,100, $filter,$action->user->id,"LIST",$famid);
  
  $tr = array();

  while(list($k,$v) = each($tinter)) {
            
    $mail = $v->getValue("US_MAIL");
    if ($mail != "")  $tr[] = array($v->title ,$v->title." <$mail>");
    
  }
  return $tr;  
}

// liste des familles
function lfamilies($dbaccess) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  

  $tinter = GetClassesDoc($dbaccess, $action->user->id);
  
  $tr = array();


  while(list($k,$v) = each($tinter)) {
            
    $tr[] = array($v->title ,
		  $v->id,$v->title);
    
  }
  return $tr;  
}


// liste des documents par familles
function lfamilly($dbaccess, $famid, $name, $dirid=0, $filter=array()) {
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
		  $v["id"],$v["title"]);
    
  }
  return $tr;
  
}



// liste des documents par catégories
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
// liste 
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
  //$filter[]="mskfamid='$maskfamid'"; // when workflow will have attribut to say the compatible families
  return lfamilly($dbaccess, "MASK", $name, 0, $filter);
  
}

// liste des attributs d'une famille
function getDocAttr($dbaccess, $famid, $name="") {
  return getSortAttr($dbaccess, $famid, $name);
}

// liste des attributs triable d'une famille
function getSortAttr($dbaccess, $famid, $name="") {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  
  $doc = createDoc($dbaccess, $famid);
  // internal attributes
  $ti = array("title" => _("doctitle"),
	      "revdate" => _("revdate"),
	      "revision" => _("revision"),
	      "state" => _("state"));
  
  $tr = array();
  while(list($k,$v) = each($ti)) {
   
      $tr[] = array($v , $k,$v);
    
  }

  $tinter = $doc->GetSortAttributes();
  

  while(list($k,$v) = each($tinter)) {
    if (($name == "") ||    (eregi("$name", $v->labelText , $reg)))
      $tr[] = array($v->labelText ,
		    $v->id,$v->labelText);
    
  }
  return $tr;  
}
?>
