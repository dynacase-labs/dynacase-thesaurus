<?php
/**
 * Retrieve icon file
 *
 * @author Anakeen 2002
 * @version $Id: geticon.php,v 1.3 2004/02/12 10:27:54 eric Exp $
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
$vf = new VaultFile($dbaccess, "FREEDOM");

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
