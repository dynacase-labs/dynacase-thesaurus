<?php
/**
 * edit file
 *
 * @author Anakeen 2008
 * @version $Id: maker_fileedit.php,v 1.1 2008/04/18 15:18:43 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage MAKER
 */
 /**
 */


include_once("FDL/prototree.php");

function maker_fileedit(&$action) {
  
  $project = GetHttpVars("project");
  $file=GetHttpVars("file");
  $projectdir=$action->getParam('MAKER_PROJECTDIR');
  $filename=$projectdir."/$project/".$file;
  if (is_file($filename)) {
    $action->lay->set("content",file_get_contents($filename));
  } else {
    $action->lay->set("content",sprintf(_("no file %s"),$filename));
  }

  $action->lay->set("file",str_replace(array(".","/"),array("",""),$file));
  
}