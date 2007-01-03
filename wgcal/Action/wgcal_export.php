<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_export.php,v 1.1 2007/01/03 18:18:02 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");
include_once('WHAT/Lib.Common.php');

function wgcal_export(&$action) { 

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $explode = true;
  $filter = array();
  setHttpVar("ress", $action->user->fid);
  $today = strftime("%Y%m%d", time());
  $edate = GetHttpVars("exportStart", time());
  $sd = strftime("%Y-%m-%d 00:00:00", $edate);
  $ed = "2038-12-31 23:59:59";
 
  $edoc = array();
  $evt = wGetEvents($sd, $ed, $explode, $filter); 
  if (count($evt) > 0) {
    $tdir = createTmpDoc($dbaccess, "DIR");
    $tdir->title = "agenda-exp-$today";
    $tdir->Add();
    foreach ($evt as $ke=> $ve) {
      
      if (!isset($edoc[$ve["evt_idinitiator"]])) {
	$edoc[$ve["evt_idinitiator"]] = true;
	$tdir->AddFile($ve["evt_idinitiator"]);
      }

    }
    $tdir->Modify();
   
    // Export tmp dir content
    include("FDL/exportfld.php");
    exportfld($action, $tdir->id);

  } else {
    echo "pas de rv";
  }
}