<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Lib.Attr.php,v 1.26 2003/10/28 16:31:23 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Lib.Attr.php,v 1.26 2003/10/28 16:31:23 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Lib.Attr.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------
include_once('FDL/Class.Doc.php');

function AttrToPhp($dbaccess, $tdoc) {
  global $action;
 
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
      $phpAdoc->Set("include","include_once(\"FDLGEN/Class.Doc".$tdoc["fromid"].".php\");");
    } else {
      $phpAdoc->Set("GEN","GEN");
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
      switch ($v->type) {
      case "menu": // menu
	if ($v->visibility != "H")
	  $tmenu[] = array("attrid"=>strtolower($v->id),
			   "label"=>str_replace("\"","\\\"",$v->labeltext),
			   "order"=>intval($v->ordered),
			   "link"=>str_replace("\"","\\\"",$v->link),
			   "precond"=>$v->phpfunc);
	break;
      case "frame": // frame
	$tfield[] = array("attrid"=>strtolower($v->id),
			  "visibility"=>$v->visibility,
			  "label"=>str_replace("\"","\\\"",$v->labeltext));
	break;
	
      default: // normal
	
    
	if (ereg("([a-z]+)\(\"(.*)\"\)",$v->type, $reg)) {
	  $atype=$reg[1];
	  $aformat=$reg[2];
	} else {
	  $atype=$v->type;
	  $aformat="";
	}
	if (ereg("([a-z]+)list",$atype, $reg)) {
	  $atype=$reg[1];
	  $repeat="true";
	  
	} else {
	  $repeat="false";
	}
	// create code for calculated attributes
	if (substr($v->phpfunc,0,2)=="::") {
	  if (ereg("::([^\(]+)\(([^\)]*)\)[:]{0,1}(.*)",$v->phpfunc, $reg)) {
	    $iattr = explode(",",$reg[2]);
	    $tiattr=array();
	    while(list($ka,$va) = each($iattr))   {
	      $tiattr[]= array("niarg"=>trim($va));
	    }
	    
	    $phpAdoc->SetBlockData("biattr".$v->id, $tiattr);
	    $tcattr[]=array("method"=>$reg[1],
			    "biattr"=>"biattr".$v->id,
			    "rarg"=>($reg[3]=="")?$v->id:trim($reg[3]),
			    "niargs"=>implode(",",$iattr));
	  }
	}
	

	// complete attributes characteristics
	$tnormal[$v->id] = array("attrid"=>strtolower($v->id),
				 "label"=>str_replace("\"","\\\"",$v->labeltext),
				 "type"=>$atype,
				 "format"=>str_replace("\"","\\\"",$aformat),
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
				 "phpfunc"=>str_replace(", ",",",$v->phpfunc));
	 
	if ($v->type != "array")  $tattr[$v->id] = array("attrid"=>strtolower($v->id));	 
	if (($repeat=="true") || ($tnormal[$v->frameid]["type"]=="array")) {
	  $attrids[$v->id] = strtolower($v->id)." text";  // for the moment all repeat are text
	} else {
	  switch($atype) {
	  case double:
	  case money:
	    $attrids[$v->id] = strtolower($v->id)." float8";  
	    break;
	  case integer:
	    $attrids[$v->id] = strtolower($v->id)." int4";  
	    break;
	  case date:
	    $attrids[$v->id] = strtolower($v->id)." date";  
	    break;
	  case time:
	    $attrids[$v->id] = strtolower($v->id)." time";  
	    break;
	  default: 
	    $attrids[$v->id] = strtolower($v->id)." text";    
	  }
	}

	
      }
    }	 


    $phpAdoc->Set("sattr", implode(",",$attrids));
    $phpAdoc->SetBlockData("MATTR", $tmenu);
    $phpAdoc->SetBlockData("FATTR", $tfield);
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
  if (isset($tdoc["methods"]) && ($tdoc["methods"] != "")) {

    $filename=GetParam("CORE_PUBDIR")."/FDL/".$tdoc["methods"];
    $fd = fopen ($filename, "rb");
    $contents = fread ($fd, filesize ($filename));
    fclose ($fd);
    $phpAdoc->Set("METHODS",str_replace(array( "<?php\n","\n?>"),"",$contents)  );
  } else $phpAdoc->Set("METHODS","");
  

  return $phpAdoc->gen();
    
}


function PgUpdateFamilly($dbaccess, $docid) {

  $msg="";
  $doc = new Doc($dbaccess);
  $err = $doc->exec_query("SELECT * FROM pg_class where relname='doc".$docid."';");
  if ($doc->numrows() == 0) {
    $msg .= "Create table doc".$docid."\n";
    // create postgres table if new familly
    $cdoc = createDoc($dbaccess, $docid);
    
    $cdoc->exec_query($cdoc->sqltcreate,1);
    // step by step
    $cdoc->Create();




  } else {
      


    $row = $doc->fetch_array(0,PGSQL_ASSOC);
    $relid= $row["relfilenode"]; // pg id of the table
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
    include_once("FDLGEN/Class.$classname.php");
    $cdoc = new $classname($dbaccess);
      
    $oattr = $cdoc->GetNormalAttributes();
    reset($oattr);

    while (list($ka,$attr) = each($oattr)) {
      if ($attr->type=="array") continue; // don't use column for container
      if ($attr->docid == $docid) { // modify my field not inherited fields

	if (! in_array($attr->id, $pgatt)) {
	  $msg .= "add field {$attr->id} in table doc".$docid."\n";
	  
	  if ($attr->repeat)  $sqltype = strtolower($v->id)." text";  // for the moment all repeat are text
	  else {
	    switch($attr->type) {
	    case double:
	    case money:
	      $sqltype = strtolower($v->id)." float8";  
	      break;
	    case integer:
	      $sqltype = strtolower($v->id)." int4";  
	      break;
	    case date:
	      $sqltype = strtolower($v->id)." date";  
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
  return $msg;
}


function createDocFile($dbaccess, $tdoc) {
  

  $pubdir = GetParam("CORE_PUBDIR");
  $dfile = "$pubdir/FDLGEN/Class.Doc".$tdoc["id"].".php";

  $fphp=fopen($dfile,"w");
  if ($fphp) {
      fwrite($fphp,AttrtoPhp($dbaccess,$tdoc));
      fclose($fphp);
      @chmod ($dfile, 0666);  // write for nobody
  }
}


function activateTrigger($dbaccess, $docid) {
    $cdoc = createDoc($dbaccess, $docid);

    $msg=$cdoc->exec_query($cdoc->sqltcreate,1);
  //  print $cdoc->sqltcreate;
    $sqlcmds = explode(";",$cdoc->SqlTrigger());
    $cdoc = new Doc($dbaccess, $docid);
  //  print $cdoc->SqlTrigger();
    while (list($k,$sqlquery)=each($sqlcmds)) {
      if ($sqlquery != "") $msg=$cdoc->exec_query($sqlquery,1);
    }
}


// refresh PHP Class & Postgres Table Definition
function refreshPhpPgDoc($dbaccess, $docid) {
  
  $query = new QueryDb($dbaccess,"DocFam");
  $query ->AddQuery("doctype='C'");  
  $query->AddQuery("id=$docid");
  $table1 = $query->Query(0,0,"TABLE");

  if ($query->nb > 0)	{
    $v=$table1[0];
    createDocFile($dbaccess,$v);
    $msg=PgUpdateFamilly($dbaccess, $v["id"]);
    //------------------------------
    // see if workflow
   

    AddLogMsg($msg);

    // -----------------------------
    // activate trigger by trigger
    activateTrigger($dbaccess, $docid);

  }
  
}
?>
