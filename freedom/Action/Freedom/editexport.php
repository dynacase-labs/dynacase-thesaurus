<?php
/**
 * Export edition
 *
 * @author Anakeen 2007
 * @version $Id: editexport.php,v 1.1 2007/03/16 17:53:37 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

include_once("FDL/Class.Doc.php");

function editexport(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
 

  $action->lay->Set("dirid",$docid);

  $doc= new_Doc($dbaccess,$docid);
}

?>