<?php
/**
 * Export Vault Files
 *
 * @author Anakeen 2000 
 * @version $Id: exportfile.php,v 1.15 2007/04/12 12:00:19 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("VAULT/Class.VaultFile.php");

// --------------------------------------------------------------------
function exportfile(&$action) 
// --------------------------------------------------------------------
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("docid",0);
  $attrid = GetHttpVars("attrid",0);
  $vaultid = GetHttpVars("vaultid",0);
  $index = GetHttpVars("index");
  //  $imgheight = GetHttpVars("height");
  $imgwidth = GetHttpVars("width");
  $inline = (GetHttpVars("inline")=="yes");

  $isControled=false;

  if ($vaultid == 0) {

    $doc= new_Doc($dbaccess,$docid);
    // ADD CONTROL ACCESS HERE
    $err = $doc->control("view");
    if ($err != "") $action->exiterror($err);
    $isControled=true;;
    $ovalue = $doc->getValue($attrid);

    if (($index !== "") && ($index >= 0)) {
      $tvalue = explode("\n",$ovalue);
      $ovalue= $tvalue[$index];
    }
    
    if ($ovalue == "") $action->exiterror(_("no file referenced"));
    
    ereg ("(.*)\|(.*)", $ovalue, $reg);
    $vaultid= $reg[2];
    $mimetype=$reg[1];
  } else {
    $mimetype = "";
  }

  DownloadVault($action, $vaultid, $isControled, $mimetype,$imgwidth,$inline);

    
  exit;
    
  
    
}


  // --------------------------------------------------------------------
function exportfirstfile(&$action) 
  // --------------------------------------------------------------------
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("docid",0);


    $doc= new_Doc($dbaccess,$docid);
  // ADD CONTROL ACCESS HERE
    $err = $doc->control("view");
    if ($err != "") $action->exiterror($err);

  $isControled=true;
  $attr = $doc->GetFirstFileAttributes();

  $ovalue = $doc->getValue($attr->id);

    
  if ($ovalue == "") $action->exiterror(_("no file referenced"));
    
  ereg ("(.*)\|(.*)", $ovalue, $reg);
  $vaultid= $reg[2];
  $mimetype=$reg[1];

  
  DownloadVault($action, $vaultid, $isControled, $mimetype);
        
  
    
}


  // --------------------------------------------------------------------
function DownloadVault(&$action, $vaultid, $isControled, $mimetype="",$width="",$inline=false,$cache=true) {
  // --------------------------------------------------------------------
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $vf = newFreeVaultFile($dbaccess);

  if ($vf -> Retrieve ($vaultid, $info) != "") {    
      Http_DownloadFile("FREEDOM/Images/doc.gif", "unknow", "image/gif");
  } else
    {
      //Header("Location: $url");
      if ($isControled || ( $info->public_access)) {
	if (($mimetype != "image/jpeg") || ($width == 0)) {
	  Http_DownloadFile($info->path, $info->name, $mimetype,$inline,$cache);
	} else {
	  $filename=$info->path; 
	  $name=$info->name;
	  header("Content-Disposition: form-data;filename=$name");   
	  //	  header("Cache-Control: private, max-age=3600"); // use cache client (one hour) for speed optimsation
	  // header("Expires: ".gmdate ("D, d M Y H:i:s T\n",time()+3600));  // for mozilla
	  // header("Pragma: "); // HTTP 1.0
	  header('Content-type: image/jpeg');

	  $mb=microtime();
	  // Calcul des nouvelles dimensions
	  list($owidth, $oheight) = getimagesize($filename);
	  $newwidth = $width;
	  $newheight = $oheight * ($width/$owidth);

	  // chargement
	  $thumb = imagecreatetruecolor($newwidth, $newheight);
	  $source = imagecreatefromjpeg($filename);

	  // Redimensionnement
	  imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $owidth, $oheight);

	  // Affichage
	  imagejpeg($thumb);
	  exit;
	  
	}
	if (! $info->public_access)   AddlogMsg(sprintf(_("%s has be sended"),$info->name));
      } else {
	$action->exiterror(_("file must be controlled : read permission needed"));
      }
    }

  exit;
}

?>
