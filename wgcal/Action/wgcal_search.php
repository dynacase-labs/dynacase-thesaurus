<?
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_search.php,v 1.1 2005/02/01 15:12:33 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("FDL/modcard.php");

function wgcal_search(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $sphrase = GetHttpVars("searchphrase", "");
  $sdstart = GetHttpVars("searchdstart", "");
  $sdend   = GetHttpVars("searchdend", "");
  $sress   = GetHttpVars("searchressource", "");

  $rvfam = getFamIdFromName($dbaccess, "CALEVENT");

  $ndoc = createDoc($dbaccess, getFamIdFromName($dbaccess, "SEARCH"));
  if (! $ndoc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
  if ($keyword != "") $ndoc->title=_("event search ");
  
  $ndoc->doctype='T';
  $ndoc->setValue("se_key", $sphrase);
  $ndoc->setValue("se_latest", "yes");
  $ndoc->setValue("se_famid", $rvfam);
  $err = $ndoc->Add();
  if ($err != "")  $action->ExitError($err);

  SetHttpVar("id", $ndoc->id);
  $err = modcard($action, $ndocid); // ndocid change if new doc
  redirect($action, "FREEDOM", "FREEDOM_VIEW&viewone=$viewone&dirid=".$ndoc->id);
  
}