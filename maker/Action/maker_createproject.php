<?php
/**
 * Create new project 
 *
 * @author Anakeen 2008
 * @version $Id: maker_createproject.php,v 1.1 2008/04/02 11:44:39 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage MAKER
 */
 /**
 */

/**
 * Project maker
 * @param Action &$action current action
 */
function maker_createproject(&$action) {
  
  $id = GetHttpVars("identificator");
  $label = GetHttpVars("label");
  $description = GetHttpVars("description");
  
  $err=createproject($action,$id,$label,$description);
  if ($err) $action->exitError($err);

  print $gen;
  
  }



function createproject(&$action,$id,$label,$desc) {
  $dir=$action->getParam("MAKER_PROJECTDIR");

  if (! is_dir($dir)) {
    return sprintf(_("Main project directory [%s] no exists. Need to create it"), $dir);
  }

  
  $lproject= new Layout(getLayoutFile("MAKER","project.xml"));
  $lproject->setEncoding("utf-8");
  $lproject->set("id",$id);
  $lproject->set("label",$label);
  $lproject->set("descr",nl2br($desc));
  $gen=$lproject->gen();
  
  $pdir=$dir."/$id";
  if (is_dir($pdir)) return sprintf(_("Project directory [%s] already exists. Cannot create new one"), $pdir);

  $err=mkdir($pdir);  
  if ($err===false) return sprintf(_("cannot create directory %s"),$pdir);
  $projectfile=$pdir."/project.xml";
  $err=file_put_contents($projectfile,$lproject->gen());
  if ($err===false) return sprintf(_("cannot write file %s"),$projectfile);

}
?>