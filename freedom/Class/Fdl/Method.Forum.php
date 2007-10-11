<?php
/**
 * Image document
 *
 * @author Anakeen 2000 
 * @version $Id: Method.Forum.php,v 1.1 2007/10/11 13:39:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

  //var $defaultview= "FDL:VIEWFORUM";


function getNewId() {
  $dids = $this->getTValues("forum_d_id");
  $max = 0;
  foreach ($dids as $k => $v) $max = ($v > $max ? $v : $max );
  $max++;
  return $max;
}




?>
