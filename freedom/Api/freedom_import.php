<?php
/**
 * importation of documents
 *
 * @author Anakeen 2002
 * @version $Id: freedom_import.php,v 1.7 2006/08/15 13:56:10 eric Exp $
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
  $appl=new Application();
  $appl->Set("FREEDOM",	     $core);

  $action->Set("FREEDOM_IMPORT",$appl);


  print ($action->execute());
} else {
  // mode TEXT
  $appl=new Application();
  $appl->Set("FDL",	     $core);
  $action->Set("",$appl);

  $filename=GetHttpVars("file");
 
  add_import_file($action, $filename );
    
  
}

    

?>