<?php
/**
 * Import documents
 *
 * @author Anakeen 2000 
 * @version $Id: import_file.php,v 1.87 2005/04/05 09:46:00 eric Exp $
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
  global $_FILES;
  $gerr=""; // general errors 

  if (intval(ini_get("max_execution_time")) < 300) ini_set("max_execution_time", 300);
  $dirid = GetHttpVars("dirid",10); // directory to place imported doc 
  $analyze = (GetHttpVars("analyze","N")=="Y"); // just analyze
  $policy = GetHttpVars("policy","update"); 

  $nbdoc=0; // number of imported document
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (isset($_FILES["file"]))    
    {
      $fdoc = fopen($_FILES["file"]['tmp_name'],"r");
    } else {
      if ($fimport != "")      $fdoc = fopen($fimport,"r");
      else $fdoc = fopen(GetHttpVars("file"),"r");
    }

  if (! $fdoc) $action->exitError(_("no import file specified"));
  $nline=0;
  while ($data = fgetcsv ($fdoc, 50000, ";")) {
    $nline++;
  // return structure
    $num = count ($data);
    if ($num < 1) continue;
    $tcr[$nline]=array("err"=>"",
	     "msg"=>"",
	     "folderid"=>0,
	     "foldername"=>"",
	     "filename"=>"",
	     "title"=>"",
	     "id"=>"",
	     "values"=>array(),
	     "familyid"=>0,
	     "familyname"=>"",
	     "action"=>" ");
    $tcr[$nline]["title"]=substr($data[0],0,10);
    switch ($data[0]) {
      // -----------------------------------
    case "BEGIN":
      $err="";	
      // search from name or from id
      if ($data[3]=="") $doc=new DocFam($dbaccess,getFamIdFromName($dbaccess,$data[5]) );
      else $doc=new DocFam($dbaccess, $data[3]);

     
      if (! $doc->isAffected())  {
	
	if (! $analyze) {
	  $doc  =new DocFam($dbaccess);
	  if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
	  if (isset($data[3]) && ($data[3] > 0)) $doc->id= $data[3]; // static id
	  if (is_numeric($data[1]))   $doc->fromid = $data[1];
	  else $doc->fromid = getFamIdFromName($dbaccess,$data[1]);
	  $err = $doc->Add();

	}
	$tcr[$nline]["msg"]=sprintf(_("create %s family"),$data[2]);
	$tcr[$nline]["action"]="added";
      } else {
	$tcr[$nline]["action"]="updated";
	$tcr[$nline]["msg"]=sprintf(_("update %s family"),$data[2]);
      }
      
      if (is_numeric($data[1]))   $doc->fromid = $data[1];
      else $doc->fromid = getFamIdFromName($dbaccess,$data[1]);

      $doc->title =  $data[2];  
     
      
      if (isset($data[4])) $doc->classname = $data[4]; // new classname for familly

      if (isset($data[5])) $doc->name = $data[5]; // internal name

      $tcr[$nline]["err"].=$err;

	  
      break;
      // -----------------------------------
    case "END":
      // add messages
      $msg=sprintf(_("modify %s family"),$doc->title);
      $tcr[$nline]["msg"]=$msg;
      
      if ($analyze) {
	$nbdoc++;
	continue;
      }
      if (($num > 3) && ($data[3] != "")) $doc->doctype = "S";


      $doc->modify();

      if (  $doc->doctype=="C") {
	global $tFamIdName;
	$msg=refreshPhpPgDoc($dbaccess, $doc->id);
	if (isset($tFamIdName))	$tFamIdName[$doc->name]=$doc->id; // refresh getFamIdFromName for multiple family import
      }
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

      
     
      
      $nbdoc++;

      if (isset($famdoc)) $famdoc->modify(); // has default values
      break;
      // -----------------------------------
    case "DOC":
      // case of specific order
      if (is_numeric($data[1]))   $fromid = $data[1];
      else $fromid = getFamIdFromName($dbaccess,$data[1]);
      if (!isset($torder[$fromid])) $torder[$fromid]=array();

      $tcr[$nline]=csvAddDoc($dbaccess, $data, $dirid,$analyze,
		    '',$policy,array("title"),array(),$tcolorder[$fromid]);
      if ($tcr[$nline]["err"]=="") $nbdoc++;
      break;    
      // -----------------------------------
    case "SEARCH":

      if  ($data[1] > 0) {
	$tcr[$nline]["id"]=$data[1];
	$doc = new Doc($dbaccess, $data[1]);
	if (! $doc -> isAffected()) {
	  $doc = createDoc($dbaccess,5);
	  if (!$analyze) {
	    $doc->id= $data[1]; // static id
	    $err = $doc->Add();
	  }
	  $tcr[$nline]["msg"]=sprintf(_("update %s search"),$data[3]);
	  $tcr[$nline]["action"]="updated";
	}
      } else {
	$doc = createDoc($dbaccess,5);
	if (!$analyze) {
	  $err = $doc->Add();
	}
	$tcr[$nline]["msg"]=sprintf(_("add %s search"),$data[3]);
	$tcr[$nline]["action"]="added";
	$tcr[$nline]["err"].=$err;
      }
      if (($err != "") && ($doc->id > 0)) { // case only modify
	if ($doc -> Select($doc->id)) $err = "";
      }
      if (!$analyze) {
	// update title in finish
	$doc->title =  $data[3];
	$err=$doc->modify();
	$tcr[$nline]["err"].=$err;

	if (($data[4] != "")) { // specific search 
	  $err = $doc->AddQuery($data[4]);
	  $tcr[$nline]["err"].=$err;
	}

	if ($data[2] > 0) { // dirid
	  $dir = new Doc($dbaccess, $data[2]);
	  $dir->AddFile($doc->id);
	} else if ($data[2] ==  0) {
	  $dir = new Doc($dbaccess, $dirid);
	  $dir->AddFile($doc->id);
	}
      }
      $nbdoc++;
      break;
      // -----------------------------------
    case "TYPE":
      $doc->doctype =  $data[1];
      $tcr[$nline]["msg"]=sprintf(_("set doctype to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "ICON":
      if ($doc->icon == "") {
	if (! $analyze) $doc->changeIcon($data[1]);
	
	$tcr[$nline]["msg"]=sprintf(_("set icon to '%s'"),$data[1]);
      } else {
	$tcr[$nline]["err"]=sprintf(_("icon already set. No update allowed"));
      }
      break;
      // -----------------------------------
    case "DFLDID":
      if ($data[1] == "auto") {
	if ($doc->dfldid == "") {
	  if (! $analyze) {
	    // create auto
	    include_once("FDL/freedom_util.php");
	    $fldid=createAutoFolder($doc);
	    $tcr[$nline]["msg"].=sprintf(_("create default folder (id [%d])\n"),$fldid);
	  }
	} else {
	  $fldid=$doc->dfldid;
	  $tcr[$nline]["err"]=sprintf(_("default folder already set. Auto ignored"));
	}
      } elseif (is_numeric($data[1]))   $fldid = $data[1];
      else $fldid =  getIdFromName($dbaccess,$data[1],2);
      $doc->dfldid =  $fldid;
      $tcr[$nline]["msg"].=sprintf(_("set default folder to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "WID":
      if (is_numeric($data[1]))   $wid = $data[1];
      else $wid =  getIdFromName($dbaccess,$data[1],20);
      $doc->wid =  $wid;
      $tcr[$nline]["msg"]=sprintf(_("set default workflow to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "CVID":
      if (is_numeric($data[1]))   $cvid = $data[1];
      else $cvid =  getIdFromName($dbaccess,$data[1],28);
      $doc->ccvid =  $cvid;
      $tcr[$nline]["msg"]=sprintf(_("set view control to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "SCHAR":
      $doc->schar =  $data[1];
      $tcr[$nline]["msg"]=sprintf(_("set special characteristics to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "METHOD":

      $s1=$data[1][0];
      if (($s1=="+")||($s1=="*")) {
	if ($s1=="*") $method=$data[1];
	else $method=substr($data[1],1);

	if ($doc->methods == "") {
	  $doc->methods =  $method;
	} else {
	  $doc->methods .= "\n$method";
	  // not twice
	  $tmeth = explode("\n",$doc->methods);
	  $tmeth=array_unique($tmeth);
	  $doc->methods =  implode("\n",$tmeth);
	}
      } else  $doc->methods =  $data[1];
      
      $tcr[$nline]["msg"]=sprintf(_("change methods to '%s'"),$doc->methods);
      
      break;
      // -----------------------------------
    case "USEFORPROF":     
      $doc->usefor =  "P";
      $tcr[$nline]["msg"]=sprintf(_("change special use to '%s'"),$doc->usefor);
      break;
      // -----------------------------------
    case "USEFOR":     
      $doc->usefor =   $data[1];
      $tcr[$nline]["msg"]=sprintf(_("change special use to '%s'"),$doc->usefor);
      break;
      // -----------------------------------
    case "TAG":           
      $doc->AddATag($data[1]);
      $tcr[$nline]["msg"]=sprintf(_("change application tag to '%s'"),$doc->atags);
      break;
      // -----------------------------------
    case "CPROFID":     
      if (is_numeric($data[1]))   $pid = $data[1];
      else $pid =  getIdFromName($dbaccess,$data[1],3);
      $doc->cprofid =  $pid;
      $tcr[$nline]["msg"]=sprintf(_("change default creation profile id  to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "PROFID":     
      if (is_numeric($data[1]))   $pid = $data[1];
      else $pid =  getIdFromName($dbaccess,$data[1],3);
      $doc->setProfil($pid);// change profile
      $tcr[$nline]["msg"]=sprintf(_("change profile id  to '%s'"),$data[1]);
      break;
    case "DEFAULT":     
      //   $famdoc=$doc->getFamDoc();
      $doc->setDefValue($data[1],str_replace('\n',"\n",$data[2]));
      $doc->setParam($data[1],str_replace('\n',"\n",$data[2]));

      $tcr[$nline]["msg"]=sprintf(_("add default value %s %s"),$data[1],$data[2]);
      break;
    case "IATTR":
      // import attribute definition from another family
      $fiid=$data[3];
      if (! is_numeric($fiid))    $fiid =  getFamIdFromName($dbaccess,$fiid);
      $fi=new Doc($dbaccess,$fiid);
      if ($fi->isAffected()) {
	$fa=$fi->getAttribute($data[1]);
	if ($fa) {
	  $oattri=new DocAttr($dbaccess, array($fiid,strtolower($data[1])));
	  $oattr=new DocAttr($dbaccess, array($doc->id,strtolower($data[1])));
	  $oattri->docid=$doc->id; 
	  $tcr[$nline]["msg"]=sprintf(_("copy attribute %s from %s"),$data[1],$data[3]);
	  if (!$analyze) {
	    if ($oattr->isAffected()) {
	      $err=$oattri->modify();
	    } else {
	      $err=$oattri->add();
	    }
	    $tcr[$nline]["err"]=$err;
	  }

	  if (($err=="") && (get_class($fa) == "fieldsetattribute")) {
	    $frameid=$fa->id;
	    // import attributes included in fieldset
	    foreach($fi->attributes->attr as $k=>$v) {
	      if (get_class($v) == "normalattribute") {
		
		if ($v->fieldSet->id == $frameid) {
		  $tcr[$nline]["msg"].="\n".sprintf(_("copy attribute %s from %s"),$v->id,$data[3]);
		  $oattri=new DocAttr($dbaccess, array($fiid,$v->id));
		  $oattr=new DocAttr($dbaccess, array($doc->id,$v->id));
		  $oattri->docid=$doc->id; 
		  if (!$analyze) {
		    if ($oattr->isAffected()) {
		      $err=$oattri->modify();
		    } else {
		      $err=$oattri->add();
		    }
		    $tcr[$nline]["err"].=$err;
		  }
		}
	      }
	    }
	  }
	}
      }
      break;
      // -----------------------------------
    case "ATTR":
    case "PARAM":
    case "OPTION":
      if     ($num < 13) $tcr[$nline]["err"]= "Error in line $nline: $num cols < 14";

      $tcr[$nline]["msg"].=sprintf(_("update %s attribute"),$data[1]);
      if ($analyze) continue;
      $oattr=new DocAttr($dbaccess, array($doc->id,strtolower($data[1])));
     
      if ($data[0]=="PARAM") $oattr->usefor='Q'; // parameters
      elseif ($data[0]=="OPTION") $oattr->usefor='O'; // options
      
      $oattr->docid = $doc->id;
      $oattr->id = trim(strtolower($data[1]));
      $oattr->frameid = trim(strtolower($data[2]));
      $oattr->labeltext=$data[3];

      if (! $oattr->isAffected()) { 
	// don't change config by admin
	$oattr->title = ($data[4] == "Y")?"Y":"N";
	$oattr->abstract = ($data[5] == "Y")?"Y":"N";
      }
      if (((($data[11]!="")&&($data[11]!="-")) || (($data[6] != "enum")  && ($data[6] != "enumlist"))) || 
	  ($oattr->phpfunc == "")) $oattr->phpfunc = $data[12]; // don(t modify  enum possibilities
      $oattr->type = $data[6];

      $oattr->ordered = $data[7];
      $oattr->visibility = ($oattr->type=="frame")&&($data[8]!="H")&&($data[8]!="R")?"F":$data[8];
      $oattr->needed =  ($data[9]=="Y")?"Y":"N";
      $oattr->link = $data[10];
      $oattr->phpfile = $data[11];
      if (isset($data[13])) $oattr->elink = $data[13];
      if (isset($data[14])) $oattr->phpconstraint = $data[14];
      if (isset($data[15])) $oattr->options = $data[15];
	  
      if ($oattr->isAffected()) $err =$oattr ->Modify();
      else    $err = $oattr ->Add();
      //    if ($err != "") $err = $oattr ->Modify();
      
      $tcr[$nline]["err"].=$err;

      break;
	
    case "ORDER":  
      if (is_numeric($data[1]))   $orfromid = $data[1];
      else $orfromid = getFamIdFromName($dbaccess,$data[1]);
      
      $tcolorder[$orfromid]=getOrder($data);
      $tcr[$nline]["msg"]=sprintf(_("new column order %s"),implode(" - ",$tcolorder[$orfromid]));
      
      break;
    case "PROFIL":  
      if (is_numeric($data[1]))   $pid = $data[1];
      else $pid =  getIdFromName($dbaccess,$data[1],3);
      
      if (! ($pid>0)) $tcr[$nline]["err"]=sprintf(_("profil id unkonow %s"),$data[1]);
      else {

	$pdoc = new Doc($dbaccess, $pid);
	if ($pdoc->isAlive()) {
	  $tcr[$nline]["msg"]=sprintf(_("change profil %s"),$data[1]);	  
	  if ($analyze) continue;
	  if ($pdoc->profid != $pid) {
	    $pdoc->setProfil($pid);
	    $pdoc->SetControl(false);
	    $pdoc->disableEditControl(); // need because new profil is not enable yet
	    $tcr[$nline]["err"]= $pdoc-> Modify();  
	  }
	  $tacls=array_slice($data, 2); 
	  foreach ($tacls as $acl) {
	    
	    if (ereg("([a-zA-Z_]+)=(.*)",$acl, $reg)) {
	      $tuid= explode(",",$reg[2]);
	      $perr="";
	      foreach ($tuid as $uid) {
		$perr.=$pdoc->AddControl($uid,$reg[1]);
	      }
	      $tcr[$nline]["err"]=$perr;
	    }
	  }
	  
	  
	} else {
	  $tcr[$nline]["err"]=sprintf(_("profil id unkonow %s"),$data[1]);
	}
      }
      
      break;
    default:
      // uninterpreted line
      unset($tcr[$nline]);
    }

	  
  }
      
  fclose ($fdoc);



    
  return $tcr;
}


/**
 * Add a document from csv import file
 * @param string $dbaccess database specification
 * @param array $data  data information conform to {@link Doc::GetImportAttributes()}
 * @param int $dirid default folder id to add new document
 * @param bool $analyze true is want just analyze import file (not really import)
 * @param string $ldir path where to search imported files
 * @param string $policy add|update|keep policy use if similar document
 * @param array $tkey attribute key to search similar documents
 * @param array $prevalues default values for new documents
 * @param array $torder array to describe CSV column attributes
 * @global double Http var : Y if want double title document
 * @return array properties of document added (or analyzed to be added)
 */
function csvAddDoc($dbaccess, $data, $dirid=10,$analyze=false,$ldir='',$policy="add",
		   $tkey=array("title"),$prevalues=array(),$torder=array()) {

  // return structure
  $tcr=array("err"=>"",
	     "msg"=>"",
	     "folderid"=>0,
	     "foldername"=>"",
	     "filename"=>"",
	     "title"=>"",
	     "id"=>"",
	     "values"=>array(),
	     "familyid"=>0,
	     "familyname"=>"",
	     "action"=>" ");
  // like : DOC;120;...
  $err="";

  if (is_numeric($data[1]))   $fromid = $data[1];
  else $fromid = getFamIdFromName($dbaccess,$data[1]);
  $doc = createDoc($dbaccess, $fromid);
  if (! $doc) return;
 

  $msg =""; // information message
  $doc->fromid = $fromid;
  $tcr["familyid"]=$doc->fromid;
  if  ($data[2] > 0) $doc->id= $data[2]; // static id
  elseif (trim($data[2]) != "") {
    if (! is_numeric(trim($data[2]))) {
      $doc->name=trim($data[2]); // logical name
      $docid=getIdFromName($dbaccess,$doc->name,$fromid);
      if ($docid > 0) $doc->id=$docid;
    }
  }

  if ( (intval($doc->id) == 0) || (! $doc -> Select($doc->id))) {
    $tcr["action"]="added";
    
  } else {
    $tcr["action"]="updated";
    $tcr["id"]=$doc->id;
    $msg .= $err . sprintf(_("update id [%d] "),$doc->id);
    
  }
  
    
  if ($err != "") {
    global $nline, $gerr;
    $gerr="\nline $nline:".$err;
    $tcr["err"]=$err;
    return false;
  }

  


  if (count($torder) == 0) {
    $lattr = $doc->GetImportAttributes();
    $torder=array_keys($lattr);
  } else {
    $lattr = $doc->GetNormalAttributes();
  }

  $iattr = 4; // begin in 5th column

  foreach ($torder as $attrid) {
    if (isset($lattr[$attrid])) {
      $attr=$lattr[$attrid];
      if (isset($data[$iattr]) &&  ($data[$iattr] != "")) {
	$dv = str_replace('\n',"\n",$data[$iattr]);
	if (($attr->type == "file") || ($attr->type == "image")) {
	  // insert file
	  $absfile="$ldir/$dv";
	  $tcr["foldername"]=$ldir;
	  $tcr["filename"]=$dv;

	  if (! $analyze) {
	    $err=AddVaultFile($dbaccess,$absfile,$analyze,$vfid);
	    if ($err != "") { 
	      $tcr["err"]=$err;
	    } else {
	      $doc->setValue($attr->id, $vfid);
	    }
	
      
	  }
	} else {
	  $doc->setValue($attr->id, $dv);
	  $tcr["values"][$attr->labelText]=$dv;
	}
      }
    }
    $iattr++;
  }
  // update title in finish
  $doc->refresh(); // compute read attribute
  if ($doc->id == "") {

    switch ($policy) {
    case "add": 
      $tcr["action"]="added"; # N_("added")
      if (! $analyze) {
	if ($doc->id == "") {
	  // insert default values
	  foreach($prevalues as $k=>$v) {
	    $doc->setValue($k,$v);
	  }
	  $err = $doc->Add(); 
	}
	$tcr["id"]=$doc->id;
	$msg .= $err . sprintf(_("add %s id [%d]  "),$doc->title,$doc->id); 
	$tcr["msg"]=sprintf(_("add %s id [%d]  "),$doc->title,$doc->id); 
      } else {
	$doc->RefreshTitle();
	$tcr["msg"]=sprintf(_("%s to be add"),$doc->title);
      }
      break;

	
    case "update": 
      // test if same doc in database
      $doc->RefreshTitle();
      $lsdoc = $doc->GetDocWithSameTitle($tkey[0],$tkey[1]);

      if (count($lsdoc) == 0) {
	$tcr["action"]="added";
	if (! $analyze) {
	  if ($doc->id == "") {
	    // insert default values
	    foreach($prevalues as $k=>$v) {
	      $doc->setValue($k,$v);
	    }
	    $err = $doc->Add(); 
	  }
	  $tcr["id"]=$doc->id;
	  $tcr["action"]="added";
	  $tcr["msg"]=sprintf(_("add id [%d] "),$doc->id); 
	} else {	    
	  $tcr["msg"]=sprintf(_("%s to be add"),$doc->title);
	}
      } elseif (count($lsdoc) == 1) {
	 
	// no double title found
	$tcr["action"]="updated";# N_("updated")
	$lsdoc[0]->transfertValuesFrom($doc);
	$doc=$lsdoc[0];
	$tcr["id"]=$doc->id;
	if (! $analyze) {
	  if (($data[2]!="") && (! is_numeric(trim($data[2]))) && ($doc->name=="")) {
	    $doc->name=$data[2];
	  }
	  $tcr["msg"]=sprintf(_("update %s [%d] "),$doc->title,$doc->id);
	} else {
	  $tcr["msg"]=sprintf(_("to be update %s [%d] "),$doc->title,$doc->id);
	  
	}
	
      } else {
	//more than one double
	$tcr["action"]="ignored";# N_("ignored")
	$tcr["err"]=sprintf(_("too many similar document %s <B>ignored</B> "),$doc->title);
	$msg .= $err.$tcr["err"];
	  
      }
    
      break;
    case "keep": 
      $doc->RefreshTitle();
      $lsdoc = $doc->GetDocWithSameTitle($tkey[0],$tkey[1]);

      if (count($lsdoc) == 0) { 
	$tcr["action"]="added";
	if (! $analyze) {
	  if ($doc->id == "") {
	    // insert default values
	    foreach($prevalues as $k=>$v) {
	      if ($doc->getValue($k)=="") $doc->setValue($k,$v);
	    }
	    $err = $doc->Add(); 
	  }
	  $tcr["id"]=$doc->id;
	  $msg .= $err . sprintf(_("add id [%d] "),$doc->id); 
	} else {	    
	  $tcr["msg"]=sprintf(_("%s to be add"),$doc->title);
	}
      } else {
	//more than one double
	$tcr["action"]="ignored";
	$tcr["err"]=sprintf(_("similar document %s found. keep similar"),$doc->title);
	$msg .= $err.$tcr["err"];
	  
      }
	
      break;
    }
  } else {
    // add special id
    if (! $doc->isAffected()) {
      $tcr["action"]="added"; 
      if (! $analyze) {
	// insert default values
	foreach($prevalues as $k=>$v) {
	  if ($doc->getValue($k)=="") $doc->setValue($k,$v);
	}
	$err = $doc->Add(); 
	
	$tcr["id"]=$doc->id;
	$msg .= $err . sprintf(_("add %s id [%d]  "),$doc->title,$doc->id); 
	$tcr["msg"]=sprintf(_("add %s id [%d]  "),$doc->title,$doc->id); 
      } else {
	$doc->RefreshTitle();
	$tcr["id"]=$doc->id;
	$tcr["msg"]=sprintf(_("%s to be add"),$doc->title);
      }
    }
  }
    
  

  $tcr["title"]=$doc->title;
  if (! $analyze) {
    

    if ($doc->isAffected()) {
      $err=$doc->Refresh(); // compute read attribute
      $err=$doc->PostModify(); // compute read attribute
      if ($err=="") $doc->modify();
      if ($err=="") $doc->AddComment(sprintf(_("updated by import")));
      $tcr["err"].=$err;

    }
  }
  //------------------
  // add in folder
  if ($err=="") {
    $msg .= $doc->title;
    if (is_numeric($data[3])) $ndirid=$data[3];
    else $ndirid=getIdFromName($dbaccess,$data[3],2);
    if ($ndirid > 0) { // dirid
      $dir = new Doc($dbaccess, $ndirid);
      if ($dir->isAffected()) {
	$tcr["folderid"]=$dir->id;
	$tcr["foldername"]=dirname($ldir)."/".$dir->title;
	if (! $analyze) $dir->AddFile($doc->id);
	$msg .= $err." ".sprintf(_("and add in %s folder "),$dir->title); 
      }
    } else if ($ndirid ==  0) {
      if ($dirid > 0) {
	$dir = new Doc($dbaccess, $dirid);
	if ($dir->isAlive() && method_exists($dir,"AddFile")) {
	  $tcr["folderid"]=$dir->id;
	  $tcr["foldername"]=dirname($ldir)."/".$dir->title;
	  if (! $analyze) $dir->AddFile($doc->id);
	  $msg .= $err." ".sprintf(_("and add in %s folder "),$dir->title); 
	}
      }
    }
  }
  
  


  return $tcr;
}

function AddImportLog( $msg) {
  global $action;
  if ($action->lay) {
    $tmsg = $action->lay->GetBlockData("MSG");
    $tmsg[] = array("msg"=>$msg);
    $action->lay->SetBlockData("MSG",$tmsg);
  } else {
    print "\n$msg";
  }
}

function getOrder($orderdata) {
  return array_map("trim", array_slice ($orderdata, 4));
}

function AddVaultFile($dbaccess,$path,$analyze,&$vid) {
  global $importedFiles;

  $path=str_replace("//","/",$path);
  // return same if already imported (case of multi links)
  if (isset($importedFiles[$path])) {
    $vid=$importedFiles[$path];
    return "";
  }

  $absfile2=str_replace('"','\\"',$path);
  // $mime=mime_content_type($absfile);
  $mime=trim(`file -ib "$absfile2"`);
  if (!$analyze) {
    $vf = newFreeVaultFile($dbaccess);
    $err=$vf->Store($path, false , $vid);
  }
  if ($err != "") {

    AddWarningMsg($err);
    return $err;
  } else {
    $importedFiles[$path]="$mime|$vid";
    $vid="$mime|$vid";
  
   
    return "";
  }
  return false;
}


?>
