<?php
/**
 * Import documents
 *
 * @author Anakeen 2000 
 * @version $Id: import_file.php,v 1.61 2004/03/01 08:49:57 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.DocFam.php");
include_once("FDL/Class.DocSearch.php");
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.QueryDir.php");
include_once("FDL/Lib.Attr.php");

function add_import_file(&$action, $fimport="") {
  // -----------------------------------
  global $HTTP_POST_FILES;
  $gerr=""; // general errors

  ini_set("max_execution_time", 300);
  $dirid = GetHttpVars("dirid",10); // directory to place imported doc 
  $analyze = (GetHttpVars("analyze","N")=="Y"); // just analyze

  $nbdoc=0; // number of imported document
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (isset($HTTP_POST_FILES["file"]))    
    {
      $fdoc = fopen($HTTP_POST_FILES["file"]['tmp_name'],"r");
    } else {
      if ($fimport != "")      $fdoc = fopen($fimport,"r");
      else $fdoc = fopen(GetHttpVars("file"),"r");
    }

  if (! $fdoc) $action->exitError(_("no import file specified"));
  $nline=0;
  while ($data = fgetcsv ($fdoc, 2000, ";")) {
    $nline++;
    $num = count ($data);
    if ($num < 1) continue;
    switch ($data[0]) {
      // -----------------------------------
    case "BEGIN":
      $err="";	
      // search from name or from id
      if ($data[3]=="") $doc=new DocFam($dbaccess,getFamIdFromName($dbaccess,$data[5]) );
      else $doc=new DocFam($dbaccess, $data[3]);

     
      if ($analyze) continue;
      if (! $doc->isAffected())  {
	//$doc = createDoc($dbaccess, $data[1]);
	$doc  =new DocFam($dbaccess);
	if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
	if (isset($data[3]) && ($data[3] > 0)) $doc->id= $data[3]; // static id
	if (is_numeric($data[1]))   $doc->fromid = $data[1];
	else $doc->fromid = getFamIdFromName($dbaccess,$data[1]);
	$err = $doc->Add();
	$msg=sprintf(_("create %s family"),$data[2]);
	AddImportLog($action,$msg);
      }
      
      if (is_numeric($data[1]))   $doc->fromid = $data[1];
      else $doc->fromid = getFamIdFromName($dbaccess,$data[1]);

      $doc->title =  $data[2];  
     
      
      if (isset($data[4])) $doc->classname = $data[4]; // new classname for familly

      if (isset($data[5])) $doc->name = $data[5]; // internal name


    if ($err != "") $gerr="\nBEGIN line $nline:".$err;

	  
    break;
    // -----------------------------------
    case "END":

      // add messages
      $msg=sprintf(_("modify %s family"),$doc->title);
      AddImportLog($action,$msg);
      
      if ($analyze) {
	$nbdoc++;
	continue;
      }
      $action->log->debug("add ");
      if (($num > 3) && ($data[3] != "")) $doc->doctype = "S";


      $doc->modify();

      if (isset($data[2])) {
	if  ($data[2] > 0) { // dirid
	  $dir = new Doc($dbaccess, $data[2]);
	  if ($dir->isAlive())	  $dir->AddFile($doc->id);
	} else if ($data[2] ==  0) {
	  $dir = new Doc($dbaccess, $dirid);
	  if ((method_exists($dir,"AddFile")) &&
	      ($dir->isAlive()))	$dir->AddFile($doc->id);
	}
      }

      
      if (  $doc->doctype=="C") {

	global $tFamIdName;
	$msg=refreshPhpPgDoc($dbaccess, $doc->id);
	if (isset($tFamIdName))	$tFamIdName[$doc->name]=$doc->id; // refresh getFamIdFromName for multiple family import
      }
      
      $nbdoc++;

      if (isset($famdoc)) $famdoc->modify(); // has default values
    break;
    // -----------------------------------
    case "DOC":
      $ndoc=csvAddDoc($action,$dbaccess, $data, $dirid);
      $nbdoc++;
    break;    
    // -----------------------------------
    case "SEARCH":

    if  ($data[1] > 0) {
      $doc = new Doc($dbaccess, $data[1]);
      if (! $doc -> isAffected()) {
	$doc = createDoc($dbaccess,5);
	$doc->id= $data[1]; // static id
	$err = $doc->Add();
      }
    } else {
      $doc = createDoc($dbaccess,5);
      $err = $doc->Add();
    }
    if (($err != "") && ($doc->id > 0)) { // case only modify
      if ($doc -> Select($doc->id)) $err = "";
    }
    if ($err != "") $gerr="\nSEARCH: line $nline:".$err;
    
    // update title in finish
    $doc->title =  $data[3];
    $doc->modify();

    if (($data[4] != "")) { // specific search 
	$err = $doc->AddQuery($data[4]);
	if ($err != "") $gerr="\nSEARCH: line $nline:".$err;
      }

    if ($data[2] > 0) { // dirid
      $dir = new Doc($dbaccess, $data[2]);
      $dir->AddFile($doc->id);
    } else if ($data[2] ==  0) {
      $dir = new Doc($dbaccess, $dirid);
      $dir->AddFile($doc->id);
    }
    $nbdoc++;
    break;
    // -----------------------------------
    case "TYPE":
      $doc->doctype =  $data[1];
    break;
    // -----------------------------------
    case "ICON":
      if ($doc->icon == "")
	if (! $analyze) $doc->changeIcon($data[1]);
    break;
    // -----------------------------------
    case "DFLDID":
      $doc->dfldid =  $data[1];
    break;
    // -----------------------------------
    case "WID":
      $doc->wid =  $data[1];
    break;
    // -----------------------------------
    case "SCHAR":
      $doc->schar =  $data[1];
    break;
    // -----------------------------------
    case "METHOD":

      if ($data[1][0]=="+") {
	if ($doc->methods == "") {
	  $doc->methods =  substr($data[1],1);
	} else {
	  $doc->methods .= "\n".substr($data[1],1);
	  // not twice
	  $tmeth = explode("\n",$doc->methods);
	  $tmeth=array_unique($tmeth);
	  $doc->methods =  implode("\n",$tmeth);
	}
      } else  $doc->methods =  $data[1];
      
      
    break;
    // -----------------------------------
    case "USEFORPROF":     
      $doc->usefor =  "P";
    break;
    // -----------------------------------
    case "USEFOR":     
      $doc->usefor =   $data[1];
    break;
    // -----------------------------------
    case "CPROFID":     
      $doc->cprofid =  $data[1];
    break;
    // -----------------------------------
    case "PROFID":     
      $doc->setProfil($data[1]);// change profile
    break;
    case "DEFAULT":     
      //   $famdoc=$doc->getFamDoc();
      $doc->setDefValue($data[1],str_replace('\n',"\n",$data[2]));
      $msg=sprintf("add default value %s %s",$data[1],$data[2]);
      AddImportLog($action,$msg);
    break;
    // -----------------------------------
    case "ATTR":
    case "PARAM":
      if     ($num < 13) print "Error in line $nline: $num cols < 14<BR>";

      if ($analyze) continue;
      $oattr=new DocAttr($dbaccess, array($doc->id,strtolower($data[1])));
     
      if ($data[0]=="PARAM") $oattr->usefor='Q'; // parameters
      
      $oattr->docid = $doc->id;
      $oattr->id = strtolower($data[1]);
      $oattr->frameid = strtolower($data[2]);
      $oattr->labeltext=$data[3];

      if (! $oattr->isAffected()) { 
	// don't change config by admin
	$oattr->title = ($data[4] == "Y")?"Y":"N";
	$oattr->abstract = ($data[5] == "Y")?"Y":"N";
      }
      if (($data[6] != "enum")  &&
	  ($data[6] != "enumlist") || 
	  ($oattr->phpfunc == "")) $oattr->phpfunc = $data[12]; // don(t modify  enum possibilities
      $oattr->type = $data[6];

      $oattr->ordered = $data[7];
      $oattr->visibility = ($oattr->type=="frame")&&($data[8]!="H")&&($data[8]!="R")?"F":$data[8];
      $oattr->needed =  ($data[9]=="Y")?"Y":"N";
      $oattr->link = $data[10];
      $oattr->phpfile = $data[11];
      if (isset($data[13])) $oattr->elink = $data[13];
      if (isset($data[14])) $oattr->phpconstraint = $data[14];
	  
      if ($oattr->isAffected()) $err =$oattr ->Modify();
      else    $err = $oattr ->Add();
      //    if ($err != "") $err = $oattr ->Modify();
    if ($err != "") $gerr="\nATTR line $nline:".$err;
    $msg=sprintf(_("update %s attribute"),$data[1]);
    AddImportLog($action,$msg);
    break;
	  
    }

	  
  }
      
  fclose ($fdoc);


  if (isset($HTTP_POST_FILES["file"])) {
    if ($gerr != "") $action->exitError($gerr);

  } else {
    print $gerr;
  }
    
  return $nbdoc;
}

function csvAddDoc(&$action,$dbaccess, $data, $dirid=10) {
  $analyze = (GetHttpVars("analyze","N")=="Y"); // just analyze
  $wdouble = (GetHttpVars("double","N")=="Y"); // with double title document

  // like : DOC;120;...
  $err="";
  $doc = createDoc($dbaccess, $data[1]);
  if (! $doc) return;

  $msg =""; // information message
  $doc->fromid = $data[1];
  if  ($data[2] > 0) $doc->id= $data[2]; // static id
  if ( (intval($doc->id) == 0) || (! $doc -> Select($doc->id))) {
    if (! $analyze) {
      $err = $doc->Add();
      $msg .= $err . sprintf(_("add id [%d] "),$doc->id); 
    } else {
      $msg .=  sprintf(_("add [%s] "),implode('-',$data)); 
    }
  } else {
    $msg .= $err . sprintf(_("update id [%d] "),$doc->id);
    
  }
  
    
  if ($err != "") {
    global $nline, $gerr;
    $gerr="\nline $nline:".$err;
    return false;
  }
  $lattr = $doc->GetImportAttributes();



  $iattr = 4; // begin in 5th column
  reset($lattr);
  while (list($k, $attr) = each ($lattr)) {

    if (isset($data[$iattr]) &&  ($data[$iattr] != "")) {
      $dv = str_replace('\n',"\n",$data[$iattr]);
      $doc->setValue($attr->id, $dv);
    }
    $iattr++;
  }
  if (! $analyze) {
    // update title in finish
    $doc->refresh(); // compute read attribute
    if (! $wdouble) {
      // test if same doc in database
      $doc->RefreshTitle();
      $lsdoc = $doc->GetDocWithSameTitle();

      if (count($lsdoc) > 0) {
	$msg .= $err.sprintf(_("double title %s <B>ignored</B> "),$doc->title); 
	$doc->delete(true); // really delete it's not a really doc yet
	$doc=false; 
      } else {
	// no double title found
	$err=$doc->PostModify(); // compute read attribute
	if ($err=="") $doc->modify();
      }
    } else {
      // with double title
      $err=$doc->PostModify(); // compute read attribute
      if ($err=="") $doc->modify();
    }
  }

  if ($doc) {

    $msg .= $doc->title;
    if ($data[3] > 0) { // dirid
      $dir = new Doc($dbaccess, $data[3]);
      if ($dir->isAffected()) {
	if (! $analyze) $dir->AddFile($doc->id);
	$msg .= $err." ".sprintf(_("and add in %s folder "),$dir->title); 
      }
    } else if ($data[3] ==  0) {
      if ($dirid > 0) {
	$dir = new Doc($dbaccess, $dirid);
	if ($dir->isAlive() && method_exists($dir,"AddFile")) {
	  if (! $analyze) $dir->AddFile($doc->id);
	  $msg .= $err." ".sprintf(_("and add  in %s folder "),$dir->title); 
	}
      }
    }
  }
  if (isset($action->lay)) {
    AddImportLog($action,$msg);

  } else {
    print $msg."\n";
  }


  return $doc;
}

function AddImportLog(&$action, $msg) {
  if ($action->lay) {
    $tmsg = $action->lay->GetBlockData("MSG");
    $tmsg[] = array("msg"=>$msg);
    $action->lay->SetBlockData("MSG",$tmsg);
  } else {
    print "\n$msg";
  }
}
?>
