<?php
/**
 * Search come from mozilla seach engine
 *
 * @author Anakeen 2005 
 * @version $Id: freedomsearch.php,v 1.4 2007/04/26 12:23:44 eric Exp $
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
  $pspell_link = pspell_new("fr","","","iso8859-1",PSPELL_FAST);

  if (!pspell_check($pspell_link, $keyword)) {
    $suggestions = pspell_suggest($pspell_link, $keyword);
    $sug=$suggestions[0];
    //foreach ($suggestions as $k=>$suggestion) {  echo "$k : $suggestion\n";  }
    $keyword="$keyword|$sug";
  }

  $location="index.php?sole=Y&&app=FREEDOM&action=SEARCH&famid=$famid&latest=$latest&fromdir=$fromdir&sensitive=$sensitive&viewone=$viewone&keyword=$keyword";
  Header("Location: $location");
}
?>
