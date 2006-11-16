<?php
/**
 * Generation of PHP Document classes
 *
 * @author Anakeen 2000 
 * @version $Id: Lib.Attr.php,v 1.62 2006/11/16 16:42:37 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once('FDL/Class.Doc.php');
/**
 * Generate Class.Docxxx.php files
 *
 * @param string $dbaccess database specification
 * @param array $tdoc array of family definition
 */
function AttrToPhp($dbaccess, $tdoc) {
  global $action;
 
  $GEN=getGen($dbaccess);
  $phpAdoc = new Layout("FDL/Layout/Class.Doc.xml",$action);


  
  if ($tdoc["classname"] == "") { // default classname
    if ($tdoc["fromid"] == 0)  $tdoc["classname"]="DocFile";
    else    $tdoc["classname"]="Doc".$tdoc["fromid"];
  }


  $phpAdoc->Set("docid", $tdoc["id"]);
  $phpAdoc->Set("include","");
  $phpAdoc->Set("GEN","");
  if ($tdoc["fromid"] == 0) {
    $phpAdoc->Set("DocParent", $tdoc["classname"]);
    $phpAdoc->Set("AParent", "ADoc");
    $phpAdoc->Set("fromid", "");
    $phpAdoc->Set("pinit", "DocCtrl");

  }  else  {
    $phpAdoc->Set("fromid", $tdoc["fromid"]);
    if ($tdoc["classname"] != "Doc".$tdoc["fromid"] ) {
      $phpAdoc->Set("DocParent", $tdoc["classname"]);
      $phpAdoc->Set("pinit", $tdoc["classname"]);
      $phpAdoc->Set("include","include_once(\"FDL$GEN/Class.Doc".$tdoc["fromid"].".php\");");
    } else {
      $phpAdoc->Set("GEN",$GEN);
      $phpAdoc->Set("DocParent", "Doc".$tdoc["fromid"]);
      if ($tdoc["usefor"] == "W") $phpAdoc->Set("pinit", "WDoc"); // special init for workflow
      else $phpAdoc->Set("pinit", "DocCtrl");
    }
    $phpAdoc->Set("AParent", "ADoc".$tdoc["fromid"]);
  }

   $phpAdoc->Set("title", $tdoc["title"]);

  $query = new QueryDb($dbaccess,"DocAttr");
  $query->AddQuery("docid=".$tdoc["id"]);
  $query->order_by="ordered";

  $table1 = $query->Query();

  $phpAdoc->Set("sattr","");
    
  if ($query->nb > 0)	{

    $tmenu=array();
    $tfield=array();
    $tnormal=array();
    $tattr=array();
    $attrids=array();
    $tcattr=array();
    while(list($k,$v) = each($table1))   {
      if ($v->visibility == "F") $v->type="frame"; // old notation compliant
      if ($v->visibility == "M") $v->type="menu"; // old notation compliant
      $v->phpfunc=str_replace("\"","\\\"",$v->phpfunc);
      switch ($v->type) {
      case "menu": // menu
	if (substr($v->link,0,2)=="::") {
	  if (ereg("::([^\(]+)\(([^\)]*)\)",$v->link, $reg)) {
	    /*
	    $iattr = explode(",",$reg[2]);
	    $iattr2 = $iattr;
	    $tiattr=array();
	    while(list($ka,$va) = each($iattr))   {
	      $tiattr[]= array("niarg"=>trim($va));
	      if ($va[0] == "'") unset($iattr2[$ka]); // not really attribute
	      
	      }*/
	    $method=$reg[1];
	    $v->link="%S%app=FDL&action=FDL_METHOD&id=%I%&method=$method";	    
	  }
	
	}
	  $tmenu[strtolower($v->id)] = array("attrid"=>strtolower($v->id),
					     "label"=>str_replace("\"","\\\"",$v->labeltext),
					     "order"=>intval($v->ordered),
					     "link"=>str_replace("\"","\\\"",$v->link),
					     "visibility"=>$v->visibility,
					     "options"=>str_replace("\"","\\\"",$v->options),
					     "precond"=>$v->phpfunc);
	break;
      case "tab": 
      case "frame": // frame
	$tfield[strtolower($v->id)] = array("attrid"=>strtolower($v->id),
					    "visibility"=>$v->visibility,
					    "label"=>str_replace("\"","\\\"",$v->labeltext),
					    "usefor"=>$v->usefor,
					    "type"=>$v->type,
					    "frame"=>($v->frameid=="")?"FIELD_HIDDENS":strtolower($v->frameid));
	break;
      case "action": // action
	$taction[strtolower($v->id)] = array("attrid"=>strtolower($v->id),
					    "visibility"=>$v->visibility,
					    "label"=>str_replace("\"","\\\"",$v->labeltext),
					     "order"=>intval($v->ordered),
					     "options"=>str_replace("\"","\\\"",$v->options),
					     "wapplication"=>$v->phpfile,
					     "waction"=>$v->phpfunc,
					     "precond"=>$v->phpconstraint);
	break;
	
      default: // normal
	
	if (ereg("\[([a-z=0-9]+)\](.*)",$v->phpfunc, $reg)) {
	  $v->phpfunc=$reg[2];
	  $funcformat=$reg[1];
	} else {	  
	  $funcformat="";
	}
    
	if (ereg("([a-z]+)\(\"(.*)\"\)",$v->type, $reg)) {
	  $atype=$reg[1];
	  $aformat=$reg[2];
	  if ($atype=="idoc") {
	    if (! is_numeric($aformat)) $aformat=getFamIdFromName($dbaccess,$aformat);
	  }
	} else {
	  $atype=$v->type;
	  $aformat="";
	}
	if (ereg("([a-z]+)list",$atype, $reg)) {
	  $atype=$reg[1];
	  $repeat="true";
	  
	} else {
	  if ($tnormal[strtolower($v->frameid)]["type"]=="array") $repeat="true";
	  else $repeat="false";
	}
	// create code for calculated attributes
	if (substr($v->phpfunc,0,2)=="::") {
	  if (ereg("::([^\(]+)\(([^\)]*)\)[:]{0,1}(.*)",$v->phpfunc, $reg)) {
	    $iattr = explode(",",$reg[2]);
	    $iattr2 = $iattr;
	    $tiattr=array();
	    while(list($ka,$va) = each($iattr))   {
	      $tiattr[]= array("niarg"=>trim($va));
	      if ($va[0] == "'") unset($iattr2[$ka]); // not really attribute
	      
	    }
	    
	    $phpAdoc->SetBlockData("biattr".$v->id, $tiattr);
	    $tcattr[]=array("method"=>$reg[1],
			    "biattr"=>"biattr".$v->id,
			    "rarg"=>($reg[3]=="")?$v->id:trim($reg[3]),
			    "niargs"=>implode(",",$iattr2));
	  }
	}
	


	// complete attributes characteristics
	$v->id=chop(strtolower($v->id));
	$tnormal[($v->id)] = array("attrid"=>($v->id),
				   "label"=>str_replace("\"","\\\"",$v->labeltext),
				   "type"=>$atype,
				   "format"=>str_replace("\"","\\\"",$aformat),
				   "eformat"=>str_replace("\"","\\\"",$funcformat),
				   "options"=>str_replace("\"","\\\"",$v->options),
				   "order"=>intval($v->ordered),
				   "link"=>str_replace("\"","\\\"",$v->link),
				   "visibility"=>$v->visibility,
				   "needed"=>($v->needed=="Y")?"true":"false",
				   "title"=>($v->title=="Y")?"true":"false",
				   "repeat"=>$repeat,
				   "abstract"=>($v->abstract=="Y")?"true":"false",
				   "frame"=>($v->frameid=="")?"FIELD_HIDDENS":strtolower($v->frameid),
				   "elink"=>$v->elink,
				   "phpfile"=>$v->phpfile,
				   "phpfunc"=>str_replace(", ",",",str_replace(", |",",  |",$v->phpfunc)),
				   "phpconstraint"=>$v->phpconstraint,
				   "usefor"=>$v->usefor);
	 
	if (($atype != "array") && ($v->usefor!="Q")) {
	if ($atype != "array")  $tattr[$v->id] = array("attrid"=>($v->id));	 
	if (($repeat=="true") || ($tnormal[$v->frameid]["type"]=="array")) {
	  $attrids[$v->id] = ($v->id)." text";  // for the moment all repeat are text
	} else {
	  switch($atype) {
	  case double:
	  case money:
	    $attrids[$v->id] = ($v->id)." float8";  
	    break;
	  case int:
	  case integer:
	    $attrids[$v->id] = ($v->id)." int4";  
	    break;
	  case date:
	    $attrids[$v->id] = ($v->id)." date";  
	    break;
	  case timestamp:
	    $attrids[$v->id] = ($v->id)." timestamp with time zone";  
	    break;
	  case time:
	    $attrids[$v->id] = ($v->id)." time";  
	    break;
	  default: 
	    $attrids[$v->id] = ($v->id)." text";    
	  }
	}

	}
      }
    }	 


    $phpAdoc->Set("sattr", implode(",",$attrids));
    $phpAdoc->SetBlockData("MATTR", $tmenu);
    $phpAdoc->SetBlockData("FATTR", $tfield);
    $phpAdoc->SetBlockData("AATTR", $taction);
    $phpAdoc->SetBlockData("NATTR", $tnormal);
    $phpAdoc->SetBlockData("ATTRFIELD2", $tattr);
    reset( $tattr);
    $phpAdoc->SetBlockData("ATTRFIELD", $tattr);

    $phpAdoc->SetBlockData("ACALC", $tcattr);

    
      
  }      


  if ($tdoc["name"] != "") { // create name alias classes
    $phpAdoc->SetBlockData("CLASSALIAS", array(array("zou")));
    $phpAdoc->Set("docName",$tdoc["name"]);
  }

  //----------------------------------
  // Add specials methods
  $cmethod=""; // method file which is use as inherit virtual class
  if (isset($tdoc["methods"]) && ($tdoc["methods"] != "")) {
    $tfmethods=explode("\n",$tdoc["methods"]);
    $contents="";
    foreach ($tfmethods as $fmethods) {
      if ($fmethods[0]=="*") {
	$cmethod=substr($fmethods,1);
	$filename=GetParam("CORE_PUBDIR")."/FDL/".$cmethod;
	$fd = fopen ($filename, "rb");
	$contents2 = fread ($fd, filesize ($filename)); // only one
	fclose ($fd);
      } else {
	$filename=GetParam("CORE_PUBDIR")."/FDL/".$fmethods;
	$fd = fopen ($filename, "rb");
	$contents .= fread ($fd, filesize ($filename));
	fclose ($fd);
      }
    }
    $phpAdoc->Set("METHODS",str_replace(array( "<?php\n","\n?>"),"",$contents)  );
  } else $phpAdoc->Set("METHODS","");

  $phpAdoc->Set("DocParent1",$phpAdoc->Get("DocParent"));
  if ($cmethod != "") {
    $phpAdoc->Set("METHODS2",str_replace(array( "<?php\n","\n?>"),"",$contents2)  );
    $phpAdoc->SetBlockData("INDIRECT", array(array("zou")));
    $phpAdoc->Set("docNameIndirect","Doc".$tdoc["id"]."__");
    $phpAdoc->Set("RedirectDocParent",$phpAdoc->Get("DocParent"));
    $phpAdoc->Set("DocParent",$phpAdoc->Get("docNameIndirect"));
    
  }
  return $phpAdoc->gen();
    
}

function PgUpdateFamilly($dbaccess, $docid) {

  $msg="";
  $GEN=getGen($dbaccess);
  $doc = new_Doc($dbaccess);
  $err = $doc->exec_query("SELECT oid FROM pg_class where relname='doc".$docid."';");
  if ($doc->numrows() == 0) {
    $msg .= "Create table doc".$docid."\n";
    // create postgres table if new familly
    $cdoc = createDoc($dbaccess, $docid);
    
    $cdoc->exec_query($cdoc->sqltcreate,1);
    // step by step
    $cdoc->Create();

  } else {      
    $row = $doc->fetch_array(0,PGSQL_ASSOC);
    $relid= $row["oid"]; // pg id of the table
    $sqlquery="select attname FROM pg_attribute where attrelid=$relid;";  
    $doc->exec_query($sqlquery,1); // search existed attribute of the table
      
    $nbidx = $doc->numrows();
    $pgatt = array();
    for ($c=0; $c < $nbidx; $c++) {
      $row = $doc->fetch_array($c,PGSQL_ASSOC);      
      $pgatt[$row["attname"]]=$row["attname"];
	
    }
    // -----------------------------
    // add column attribute
    $classname="Doc".$docid;
    include_once("FDL$GEN/Class.$classname.php");
    $cdoc = new $classname($dbaccess);
      
    $qattr = new QueryDb($dbaccess,"DocAttr");
    $qattr->AddQuery("docid=".$docid);
    $qattr->AddQuery("type != 'menu'");
    $qattr->AddQuery("type != 'frame'");
    $qattr->AddQuery("type != 'tab'");
    $qattr->AddQuery("type != 'action'");
    //$qattr->AddQuery("type != 'array'");
    $qattr->AddQuery("visibility != 'M'");
    $qattr->AddQuery("visibility != 'F'");
    $qattr->AddQuery("usefor != 'Q' or usefor is null");

    $oattr=$qattr->Query();
    if ($qattr->nb > 0) {
      foreach($oattr as $ka => $attr) {	
	$tattr[strtolower($attr->id)]=$attr;
      }
      foreach($tattr as $ka => $attr) {
	if ($attr->type == "array") continue; // skip array but must be in table to search element in arrays
	$attr->id=chop($attr->id);
	if ($attr->type=="array") continue; // don't use column for container
	if ($attr->docid == $docid) { // modify my field not inherited fields

	  if (! in_array($attr->id, $pgatt)) {
	    $msg .= "add field {$attr->id} in table doc".$docid."\n";

	    if (($attr->repeat) || ($tattr[$attr->frameid]->type=="array")) { 
		
		$sqltype = strtolower($v->id)." text";  // for the moment all repeat are text
	    } else {
	      switch($attr->type) {
	      case double:
	      case money:
		$sqltype = strtolower($v->id)." float8";  
		break;
	      case int:
	      case integer:
		$sqltype = strtolower($v->id)." int4";  
		break;
	      case date:
		$sqltype = strtolower($v->id)." date"; 
		break;
	      case timestamp:
		$sqltype = strtolower($v->id)." timestamp with time zone"; 
		break;
	      case time:
		$sqltype = strtolower($v->id)." time";  
		break;
	      default: 
		$sqltype = strtolower($v->id)." text";    
	      }
	    }
	    $sqlquery="ALTER TABLE doc".$docid." ADD COLUMN {$attr->id} $sqltype;";
	    $doc->exec_query($sqlquery,1); // add new field
	  }
	}
      }
    }

    $ncdoc=new_Doc($dbaccess,$docid);
    if (isset($ncdoc->attributes->fromids)&& (in_array(2,$ncdoc->attributes->fromids)) && ($ncdoc->usefor=="N")) {
      // its a folder
      $ncdoc->usefor="F";
      print "\nchange usefor to F\n";
      $ncdoc->modify();
    }

  }
  return $msg;
}


function createDocFile($dbaccess, $tdoc) {
  

  $GEN=getGen($dbaccess);
  $pubdir = GetParam("CORE_PUBDIR");
  $dfile = "$pubdir/FDL$GEN/Class.Doc".$tdoc["id"].".php";

  $fphp=fopen($dfile,"w");
  if ($fphp) {    
    $err=fwrite($fphp,AttrtoPhp($dbaccess,$tdoc));
    if ($err === false) print_r2("cannot access $dfile");
    fclose($fphp);
    @chmod ($dfile, 0666);  // write for nobody
  } else {
    print_r2("cannot access $dfile");
  }

  return $dfile;
}


function activateTrigger($dbaccess, $docid) {
  $cdoc = createDoc($dbaccess, $docid);
    $msg=$cdoc->exec_query($cdoc->sqltcreate,1);
  //  print $cdoc->sqltcreate;
    $sqlcmds = explode(";",$cdoc->SqlTrigger());

    //$cdoc = new_Doc($dbaccess, $docid);
    //  print $cdoc->SqlTrigger();
    while (list($k,$sqlquery)=each($sqlcmds)) {
      if ($sqlquery != "") $msg=$cdoc->exec_query($sqlquery,1);
    }
}
function setSqlIndex($dbaccess, $docid) {
  $cdoc = createDoc($dbaccess, $docid);
  $indexes=$cdoc->GetSqlIndex();
  if ($indexes)  $msg=$cdoc->exec_query($indexes);  
}


// refresh PHP Class & Postgres Table Definition
function refreshPhpPgDoc($dbaccess, $docid) {
  
  $query = new QueryDb($dbaccess,"DocFam");
  $query->AddQuery("doctype='C'");  
  $query->AddQuery("id=$docid");
  $table1 = $query->Query(0,0,"TABLE");

  if ($query->nb > 0)	{
    $v=$table1[0];
    $df=createDocFile($dbaccess,$v);

    $msg=PgUpdateFamilly($dbaccess, $v["id"]);
    //------------------------------
    // see if workflow
   

    AddLogMsg($msg);

    // -----------------------------
    // activate trigger by trigger
    activateTrigger($dbaccess, $docid);
    setSqlIndex($dbaccess, $docid);
  }
  
}
?>
