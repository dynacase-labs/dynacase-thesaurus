<?php
/**
 * Import CSV
 *
 * @author Anakeen 2004
 * @version $Id: generic_importcsv.php,v 1.11 2004/05/13 16:17:14 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */





include_once("FDL/Class.Dir.php");
include_once("FDL/import_file.php");
include_once("FDL/modcard.php");
include_once("GENERIC/generic_util.php"); 


/**
 * View a document
 * @param Action &$action current action
 * @global policy Http var : add|update|keep police case of similar document
 * @global category Http var : 
 * @global analyze Http var : "Y" if just analyze
 * @global key1 Http var : primary key for double
 * @global key2 Http var : secondary key for double
 * @global classid Http var : document family to import
 * @global colorder Http var : array to describe CSV column attributes
 * @global file Http var : path to import file (only with wsh)
 * @global  Http var : 
 */
function generic_importcsv(&$action) {
  // -----------------------------------
  global $_FILES;
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
 
  // Get all the params     
  $policy = GetHttpVars("policy","update"); 
  $category = GetHttpVars("category"); 
  $analyze = (GetHttpVars("analyze","N")=="Y"); // just analyze
  $key1 = GetHttpVars("key1","title"); // primary key for double
  $key2 = GetHttpVars("key2",""); // secondary key for double
  $classid = GetHttpVars("classid",getDefFam($action)); // document family to import
  $tcolorder = GetHttpVars("colorder"); // column order
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  if (ini_get("max_execution_time") < 180) ini_set("max_execution_time",180); // 3 minutes


  $ddoc=createDoc($dbaccess,$classid);
  setPostVars($ddoc); // memorize default import values

  
  
  if (isset($_FILES["vcardfile"]))    
    {
      // importation 
      $vcardfile = $_FILES["vcardfile"]["tmp_name"];
      
    } else {      
      $vcardfile = GetHttpVars("file"); 
    }

  $fdoc = fopen($vcardfile,"r");
  if (! $fdoc) $action->exitError(_("no csv import file specified"));
  $dir = new Doc($dbaccess, getDefFld($action));

  if ($analyze) $action->lay->set("importresult",_("import analysis result"));
  else $action->lay->set("importresult",_("import results"));

  $tvalue=array();



  $line=0;
  while ($data = fgetcsv ($fdoc, 1000, ";")) {
    
    $line++;
    $num = count ($data);
    if ($num < 1) continue;
    switch ($data[0]) {
	
    case "DOC":
      $cr[$line] =csvAddDoc($dbaccess, $data,getDefFld($action),
			    $analyze,'',$policy, array($key1,$key2),
			    $ddoc->getValues(),$tcolorder);
      if ($cr[$line]["err"]!="") {
      } else {
	
	if ($cr[$line]["id"] > 0) {
	  print_r2($category);
	  // add in each selected folder
	  if (is_array($category)) {

		
	    foreach($category as $k=>$v) {
		  
	      $catg = new Doc($dbaccess, $v);
	      $err=$catg->AddFile($cr[$line]["id"]);
	      $cr[$line]["err"].=$err;
	      if ($err == "") $cr[$line]["msg"].=sprintf(_("Add it in %s folder"),$catg->title);
	    }
	  }
	}

	    

      }
      break;  
    case "ORDER":
      $cr[$line]=array("err"=>"",
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
      if (is_numeric($data[1]))   $fromid = $data[1];
      else $fromid = getFamIdFromName($dbaccess,$data[1]);
      if ($fromid == $classid)   {
	$tcolorder=getOrder($data);
	$cr[$line]["msg"]=sprintf(_("new column order %s"),implode(";",$tcolorder));
      }
 
      
      break;
    }
  }
       

  fclose ($fdoc);
  foreach ($cr as $k=>$v) {
    $cr[$k]["taction"]=_($v["action"]); // translate action
    $cr[$k]["order"]=$k; // translate action
    $cr[$k]["svalues"]="";

    foreach ($v["values"] as $ka=>$va) {
      $cr[$k]["svalues"].= "<LI>[$ka:$va]</LI>"; // 
    }
  }

  $action->lay->SetBlockData("ADDEDDOC",$cr);
  $nbdoc=count(array_filter($cr,"isdoc2"));
  $action->lay->Set("nbdoc","$nbdoc");
    
}
function isdoc2($var) {
  return (($var["action"]=="added") ||  ($var["action"]=="updated"));
}


?>
