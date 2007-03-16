<?php
/**
 * Export Document from Folder
 *
 * @author Anakeen 2003
 * @version $Id: exportfld.php,v 1.22 2007/03/16 17:53:37 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.DocAttr.php");
include_once("VAULT/Class.VaultFile.php");

// --------------------------------------------------------------------
function exportfld(&$action, $aflid="0", $famid="") 
// --------------------------------------------------------------------
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fldid = GetHttpVars("id",$aflid);
  $wprof = (GetHttpVars("wprof","N")=="Y"); // with profil
  $wfile = (GetHttpVars("wfile","N")=="Y"); // with files
  $fld = new_Doc($dbaccess, $fldid);
  if ($famid=="") $famid=GetHttpVars("famid");
  $tdoc = getChildDoc($dbaccess, $fldid,"0","ALL",array(),$action->user->id,"TABLE",$famid);
  usort($tdoc,"orderbyfromid");

  $efldid=($fld->doctype=='D')?$fld->initid:"-";

  if ($wfile) {
    $foutdir=uniqid("/var/tmp/exportfld")."/";
    if (! mkdir($foutdir)) exit();
    
    $foutname = $foutdir."fdl.csv";
  } else {
    $foutname = uniqid("/var/tmp/exportfld").".csv";
  }
  $fout = fopen($foutname,"w");

  while (list($k,$doc)= each ($tdoc)) {        
    $docids[]=$doc->id;
  }

  if (isset($docids)) {

    // to invert HTML entities
    $trans = get_html_translation_table (HTML_ENTITIES);
    $trans = array_flip ($trans);
    

    
    $doc = createDoc($dbaccess,0);

    // compose the csv file
    $prevfromid = -1;
    reset($tdoc);

    $ef=array(); //   files to export
    while (list($k,$zdoc)= each ($tdoc)) {
      $doc->Affect($zdoc,true);

      if ($prevfromid != $doc->fromid) {
	$adoc = $doc->getFamDoc();
	if ($adoc->name != "") $fromname=$adoc->name;
	else $fromname=$adoc->id;;
	$lattr=$adoc->GetExportAttributes($wfile);
	fputs($fout,"//FAM;".$adoc->title."(".$fromname.");<specid>;<fldid>;");
	foreach($lattr as $ka=>$attr) {
	  fputs($fout,str_replace(";"," - ",$attr->labelText).";");
	}
	fputs($fout,"\n");
	fputs($fout,"ORDER;".$fromname.";;;");
	foreach($lattr as $ka=>$attr) {
	  fputs($fout,$attr->id.";");
	}
	fputs($fout,"\n");
	$prevfromid = $doc->fromid;
      }
      reset($lattr);
      if ($doc->name != "") $name=$doc->name;
      else $name=$doc->id;
      fputs($fout,"DOC;".$fromname.";".$name.";".$efldid.";");
      // write values
      foreach ($lattr as $ka=>$attr) {
      
	$value= $doc->getValue($attr->id);
	// invert HTML entities
	if (($attr->type=="image") || ($attr->type=="file")) {
	  $tfiles=$doc->vault_properties($attr);
	  $tf=array();
	  foreach ($tfiles as $f) {
	    $fname=$f["vid"].'-'.$f["name"];
	    $tf[]=$fname;
	    $ef[$fname]=$f["path"];
	  }
	  $value=implode("\n",$tf);
	} else {
	$value = preg_replace("/(\&[a-zA-Z0-9\#]+;)/es", "strtr('\\1',\$trans)", $value);
 
	// invert HTML entities which ascii code like &#232;

	$value = preg_replace("/\&#([0-9]+);/es", "chr('\\1')", $value);

	}
	fputs($fout,str_replace(array("\n",";","\r"),
				array("\\n"," - ",""),
				$value) .";");
     
      }
      fputs($fout,"\n");

      if ($wprof && ($doc->profid == $doc->id)) {
	// import its profile
	$doc = new_Doc($dbaccess,$doc->id); // needed to have special acls
	$doc->acls[]="viewacl";
	$doc->acls[]="modifyacl";
	$q= new QueryDb($dbaccess,"DocPerm");
	$q->AddQuery("docid=".$doc->profid);
	$acls=$q->Query(0,0,"TABLE");
	
	$tpu=array();
	$tpa=array();
	if ($acls) {
	  foreach ($acls as $va) {
	    $up=$va["upacl"];
	    $uid=$va["userid"];

	    foreach ($doc->acls as $acl) {
	      if ($doc->ControlUp($up,$acl) == "") {
		if ($uid >= STARTIDVGROUP) {
		  $vg=new Vgroup($dbaccess,$uid);
		  $qvg=new QueryDb($dbaccess,"VGroup");
		  $qvg->AddQuery("num=$uid");
		  $tvu=$qvg->Query(0,1,"TABLE");
		  $uid=$tvu[0]["id"];
		}

		$tpu[]=$uid;
		$tpa[]=$acl;
	      }
	    }
	  }
	}
	if (count($tpu) > 0) {
	  fputs($fout,"PROFIL;".$name.";;");
	  foreach ($tpu as $ku=>$uid) {
	    fputs($fout,";".$tpa[$ku]."=".$uid);
	  }
	  fputs($fout,"\n");
	}
      }
    }
  }
  fclose($fout);

  $fname=str_replace(array(" ","'"),array("_",""),$fld->title);
  if ($wfile) {
    foreach ($ef as $dest=>$source) {
      if (!copy($source,$foutdir.$dest )) $err.=sprintf(_("cannot copy %s"),$dest);
      
    }
    if ($err) $action->addWarningMsg($err);
    system("cd $foutdir && zip fdl * > /dev/null",$ret);
    $foutname=$foutdir."fdl.zip";
    Http_DownloadFile($foutname, "$fname.zip", "application/x-zip",false,false);
    

  } else {
    Http_DownloadFile($foutname, "$fname.csv", "text/csv",false,false);
    unlink($foutname);
  }
  exit;
}

function orderbyfromid($a, $b) {
  
    if ($a["fromid"] == $b["fromid"]) return 0;
    if ($a["fromid"] > $b["fromid"]) return 1;
  
  return -1;
}


?>
