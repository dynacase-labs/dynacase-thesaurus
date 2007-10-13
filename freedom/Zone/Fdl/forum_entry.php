<?php

function forum_entry(&$action) {

  $dbaccess = GetParam("FREEDOM_DB");


  $fid = GetHttpVars("fid", "");
  $eid = GetHttpVars("eid", -1);
  if ($fid=="") $action->exitError(_("no document reference"));

  $forum = new_Doc($dbaccess, $fid);
  if (! $forum->isAffected()) $action->exitError(sprintf(_("cannot see unknow forum reference %s"),$fid));
 
  $entries = $forum->getentries();
  if ($eid==-1 || !is_array($entries[$eid])) {
    $show = false;
  } else {
    $show = true;

    $action->lay->set("who", $entries[$eid]["who"]);
    $action->lay->set("mail", $entries[$eid]["mail"]);
    $action->lay->set("havemail", $entries[$eid]["havemail"]);
    $action->lay->set("docid", $entries[$eid]["docid"]);
    $action->lay->set("date", $entries[$eid]["date"]);
    $action->lay->set("havenext", $entries[$eid]["havenext"]);
    $action->lay->set("closed", $entries[$eid]["closed"]);
    $action->lay->set("content", $entries[$eid]["content"]);
    $action->lay->set("rcount", count($entries[$eid]["next"]));
    $action->lay->set("hasresponse", (count($entries[$eid]["next"])>0?true:false));

    if (count($entries[$eid]["next"])==0) {
      $rlist = null;
    } else {
      foreach ($entries[$eid]["next"] as $kr => $vr) {
	$rlist[] = array("fid" => $fid, "eid" => $vr);
      }
    }
    $action->lay->setBlockData("forum_answer", $rlist);

  }

  $action->lay->set("fid", $fid);
  $action->lay->set("eid", $eid);
  $action->lay->set("show", $show);


}

?>