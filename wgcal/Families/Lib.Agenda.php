<?php


function MonAgenda() 
{
  include_once("FDL/Class.Dir.php");
  global $action;
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $fid = GetHttpVars("fid", $action->user->fid);

  $rq=getChildDoc($dbaccess,0,0,1,array("(agd_oid = ".$fid." and agd_omain = 1)"), 1, "LIST", "AGENDA");
  if (count($rq)>0)  $cal = $rq[0];
  else  $cal = false;

  if (!$cal) {

    $user = new_Doc($dbaccess, $fid);
    if ($user->IsAffected()) {

      $cal = createDoc($dbaccess,"AGENDA");

      $mycalendar = _("public calendar");
      $cal->owner =  $user->id;
      $cal->setValue("agd_oname", ucwords(strtolower($user->getValue("title"))));
      $cal->setTitle(ucwords(strtolower($user->getValue("title")))." [".$mycalendar."]");
      $cal->setValue("agd_oid", $user->id);
      $cal->setValue("agd_owid", $user->getValue("us_whatid"));
      $cal->setValue("agd_omain", 1);

      $cal->setValue("se_famid", getFamIdFromName($dbaccess, "EVENT_FROM_CAL"));
      $cal->setValue("se_ols", array( "and", "and"));
      $cal->setValue("se_attrids", array( "evt_idres", "evt_frominitiatorid"));
      $cal->setValue("se_funcs", array( '~y', '~*' ));
      $cal->setValue("se_keys", array( $fid, getFamIdFromName($dbaccess, "CALEVENT") ));

      $cal->Add();
      $cal->PostModify();
      $cal->Modify();

      $cal->ComputeAccess();

      $rq=getChildDoc($dbaccess, 0, 0, 1, array("owner = -". $user->getValue("us_whatid")), $user->getValue("us_whatid"), "LIST", "DIR");      
      if (count($rq)>0) {
	$rq[0]->AddFile($cal->id);
      }
    }
  }
    
  return $cal;
}  

/*
 * 
 */
function  getUserPublicAgenda($fid=-1, $t=true) {
  $fid = ($fid!=-1 ? $fid : $action->user->fid);
  $tc = getUserAgenda($fid, true, "", $t);
  return $tc[0];
} 

/*
 * 
 */
function  getUserAgenda($fid=-1, $public=true, $namefilter="", $t=true) 
{
  global $action;
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fid = ($fid!=-1 ? $fid : $action->user->fid);  
  if ($namefilter != "") $filter[] = "title ~* '".$namefilter."'";
  $filter[] = "(agd_oid = ".$fid." and agd_omain = ".$public.")";
  $public = ($public ? 1 : 0 );
  $rq=getChildDoc($dbaccess, 0, 0, 1, $filter, $action->user->id, ($t?"TABLE":"LIST"), "AGENDA");
  if (count($rq)>0) return $rq;
  return false;
}


function myDelegation($fid=-1) {
  global $action;
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fid = ($fid!=-1 ? $fid : $action->user->fid);  
  $filter[]="( agd_dfid ~ '\\\y(".$fid.")\\\y' ) and ( agd_omain=1 )";
  $dcal = GetChildDoc($dbaccess, 0, 0, "ALL", $filter, 1, "TABLE", "AGENDA");
  return $dcal;
}