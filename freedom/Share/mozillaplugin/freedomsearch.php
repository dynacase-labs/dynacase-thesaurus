<?php
include_once("Lib.Http.php");
$famid = GetHttpVars("famid", 0);
$latest = GetHttpVars("latest", true);
$fromdir = GetHttpVars("fromdir", false);
$sensitive = GetHttpVars("sensitive", false);
$viewone = GetHttpVars("viewone", "N");
$keyword=GetHttpVars("keyword", "");
if ($keyword=="") {
  echo "<h4>You have to enter a keyword !";
} else {
  $location="index.php?sole=Y&&app=FREEDOM&action=SEARCH&famid=$famid&latest=$latest&fromdir=$fromdir&sensitive=$sensitive&viewone=$viewone&keyword=$keyword";
  Header("Location: $location");
}
?>
