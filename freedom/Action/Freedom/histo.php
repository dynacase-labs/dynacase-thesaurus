<?php
/**
 * Historique view
 *
 * @author Anakeen 2000 
 * @version $Id: histo.php,v 1.6 2004/01/14 14:22:29 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once("FDL/Class.Doc.php");
function histo(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);

  
  $doc= new Doc($dbaccess,$docid);
  $action->lay->Set("title",$doc->title);
  
}

?>
