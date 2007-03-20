<?php
/**
 * Export edition
 *
 * @author Anakeen 2007
 * @version $Id: editexport.php,v 1.2 2007/03/20 09:44:40 eric Exp $
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
 
  $doc= new_Doc($dbaccess,$docid);

  $action->lay->Set("dirid",$docid);
  $action->lay->Set("title",$doc->title);

}

?>