<?php
/**
 * Export Document from Folder
 *
 * @author Anakeen 2003
 * @version $Id: exportfld.php,v 1.19 2005/08/16 07:46:11 eric Exp $
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
  $fld = new_Doc($dbaccess, $fldid);
  if ($famid=="") $famid=GetHttpVars("famid");
  $tdoc = getChildDoc($dbaccess, $fldid,"0","ALL",array(),$action->user->id,"TABLE",$famid);
    usort($tdoc,"orderbyfromid");

  $foutname = uniqid("/tmp/exportfld").".csv";
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

    while (list($k,$zdoc)= each ($tdoc)) {
      $doc->Affect($zdoc,true);

      if ($prevfromid != $doc->fromid) {
	$adoc = $doc->getFamDoc();
	if ($adoc->name != "") $fromname=$adoc->name;
	else $fromname=$adoc->id;;
	$lattr=$adoc->GetExportAttributes();
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
      fputs($fout,"DOC;".$fromname.";".$name.";".$fldid.";");
      // write values
      while (list($ka,$attr)= each ($lattr)) {
      
	$value= $doc->getValue($attr->id);
	// invert HTML entities
	$value = preg_replace("/(\&[a-zA-Z0-9\#]+;)/es", "strtr('\\1',\$trans)", $value);
 
	// invert HTML entities which ascii code like &#232;

	$value = preg_replace("/\&#([0-9]+);/es", "chr('\\1')", $value);

	
	fputs($fout,str_replace(array("\n",";","\r"),
				array("\\n"," - ",""),
				$value) .";");
     
      }
      fputs($fout,"\n");

      if ($wprof && ($doc->profid == $doc->id)) {
	// import its profile
	$doc = new_Doc($dbaccess,$doc->id); // needed to have special acls
	$q= new QueryDb($dbaccess,"DocPerm");
	$q->AddQuery("docid=".$doc->profid);
	$acls=$q->Query(0,0,"TABLE");
	
	$tpu=array();
	$tpa=array();
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
  Http_DownloadFile($foutname, "$fname.csv", "text/csv");
  unlink($foutname);

  exit;
}

function orderbyfromid($a, $b) {
  
    if ($a["fromid"] == $b["fromid"]) return 0;
    if ($a["fromid"] > $b["fromid"]) return 1;
  
  return -1;
}


?>
