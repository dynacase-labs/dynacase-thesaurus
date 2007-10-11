<?php
/**
 * Image document
 *
 * @author Anakeen 2000 
 * @version $Id: Method.Forum.php,v 1.2 2007/10/11 17:56:39 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

  //var $defaultview= "FDL:VIEWFORUM";


function getEntryId() {
  $dids = $this->getTValue("forum_d_id");
  $max = 0;
  foreach ($dids as $k => $v) $max = ($v > $max ? $v : $max );
  $max++;
  return $max;
}


function viewforum() {

}



?>
