<?php
/**
 * Search come from mozilla seach engine
 *
 * @author Anakeen 2005 
 * @version $Id: freedomsearch.php,v 1.5 2007/05/16 15:43:55 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */include_once("Lib.Http.php");
$famid = GetHttpVars("famid", 0);
$latest = GetHttpVars("latest", true);
$fromdir = GetHttpVars("fromdir", false);
$sensitive = GetHttpVars("sensitive", false);
$viewone = GetHttpVars("viewone", "N");
$keyword=GetHttpVars("keyword", "");
if ($keyword=="") {
  $location="index.php?sole=Y&&app=FREEDOM&action=FULLSEARCH";
  Header("Location: $location");
} else {
  

  $location="index.php?sole=Y&&app=FREEDOM&action=SEARCH&famid=$famid&latest=$latest&fromdir=$fromdir&sensitive=$sensitive&viewone=$viewone&keyword=$keyword";
  Header("Location: $location");
}
?>
