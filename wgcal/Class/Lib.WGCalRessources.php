<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Lib.WGCalRessources.php,v 1.1 2004/11/26 18:05:35 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function WgcalRessSearch(&$action) {

  $tcress = GetHttpVars("rclass", array("USER"));
  $sfilter = GetHttpVars("fclass", "");
  
  if ($sfilter == "") return;
  
  $filter = array("title ~* '".$sfilter."'");
  foreach ($tcress as $kc => $vc )     {
    $fid = getFamIdFromName($dbaccess,$vc);
    $tdoc = getChildDoc( $dbaccess, 0, "0", "ALL", $filter, $action->user->id, "TABLE", $fid);
  }

}