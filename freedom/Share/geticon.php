<?php
/**
 * Retrieve icon file
 *
 * @author Anakeen 2002
 * @version $Id: geticon.php,v 1.4 2004/08/05 09:47:20 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

include_once("Lib.Http.php");

include_once("FDL/exportfile.php");


$vaultid = GetHttpVars("vaultid",0);
$$mimetype = GetHttpVars("$$mimetype","image");

$dbaccess = "host=localhost user=anakeen port=5432 dbname=freedom";
$vf = newFreeVaultFile($dbaccess);

  if ($vf -> Retrieve ($vaultid, $info) != "") {    
  } else
    {
      //Header("Location: $url");
      if (( $info->public_access)) {
	Http_DownloadFile($info->path, $info->name, $mimetype);
	
      } else {
	Http_DownloadFile("FREEDOM/Images/doc.gif", "unknow", "image/gif");
      }
    }

?>
