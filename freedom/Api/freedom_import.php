<?php
/**
 * importation of documents
 *
 * @author Anakeen 2002
 * @version $Id: freedom_import.php,v 1.4 2003/08/18 08:08:23 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WSH
 */
 /**
 */


global $appl,$action;

include_once("FDL/import_file.php");

if (GetHttpVars("htmlmode") == "Y") {
  // mode HTML
  $appl->Set("FREEDOM",	     $core);

  $action->Set("FREEDOM_IMPORT",$appl);


  print ($action->execute());
} else {
  // mode TEXT
  $appl->Set("FDL",	     $core);
  $action->Set("",$appl);
  
  add_import_file($action, 
    		    GetHttpVars("file"));
  
}

    

?>