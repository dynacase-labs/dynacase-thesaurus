<?php
/**
 * Edition of virtual document
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_iedit2.php,v 1.5 2005/03/07 16:41:09 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.WDoc.php");
include_once("Class.QueryDb.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");
include_once("VAULT/Class.VaultFile.php");


// -----------------------------------
function freedom_iedit2(&$action) {
  // -----------------------------------
  global $action;


  // Get All Parameters
  $xml = GetHttpVars("xml");
 
  $famid = GetHttpVars("famid");
  //printf($famid);
  $type_attr=GetHttpVars("type_attr");
  $action->lay->Set("type_attr",$type_attr);

  $mod=GetHttpVars("mod");
  $action->lay->Set("mod",$mod);


  $attrid=GetHttpVars("attrid");
  $action->lay->Set("attrid",$attrid);

  $action->lay->Set("xml_initial",$xml);

  $temp=base64_decode(trim($xml));
  $entete="<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>";
  $xml=$entete;
  $xml.=$temp;
	


 
  $famid = GetHttpVars("famid");
 
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $idoc= createDoc($dbaccess,$famid,false);///new doc
 



  $idoc=fromxml($xml,$idoc);
  $idoc->doctype='T';
  $idoc->Add();
  SetHttpVar("id",$idoc->id);
  $idoc->SetTitle($idoc->title);

  $action->lay->Set("docid",$idoc->id);
  $action->lay->Set("TITLE",$idoc->title);
  $action->lay->Set("STITLE",addslashes($idoc->title));
  $action->lay->Set("iconsrc", $idoc->geticon()); 
  $action->lay->Set("famid", $famid);

  // $xml_initial=addslashes(htmlentities($xml));


    

}
?>
