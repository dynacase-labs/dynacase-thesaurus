<?php
/**
 * Image document
 *
 * @author Anakeen 2000 
 * @version $Id: Method.Forum.php,v 1.4 2007/10/13 10:20:10 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

var $defaultview= "FDL:FORUM_VIEW:T";

function getEntryId() {
  $dids = $this->getTValue("forum_d_id");
  $max = 0;
  foreach ($dids as $k => $v) $max = ($v > $max ? $v : $max );
  $max++;
  return $max;
}


function forum_view() {
  global $action;

  setHttpVar("fid", $this->id);

  $action->parent->AddCssRef("FDL:forum.css", true);
  $action->parent->AddJsRef("FDL:forum.js", true);
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDC/Layout/inserthtml.js");

  $entries = $this->getentries();
  foreach ($entries as $k => $v) {
    if ($v["prev"]==-1) {
      $el[] = array("fid"=>$this->id, "eid"=>$v["id"]);
    }
  }
  $this->lay->setBlockData("entry_list", $el);
  $this->lay->set("title", $this->getTitle());
  $this->lay->set("closed", false);
  $this->lay->set("docid", $this->getValue("forum_docid"));
  return;
}

function getentries() {

  $elist = array();
  
  $docid     = $this->getValue("forum_docid"); 
  $t_id     = $this->getTValue("forum_d_id"); 
  $t_lid    = $this->getTValue("forum_d_link");
  $t_userid = $this->getTValue("forum_d_userid");
  $t_user   = $this->getTValue("forum_d_user");
  $t_mail   = $this->getTValue("forum_d_usermail");
  $t_text   = $this->getTValue("forum_d_text");
  $t_flag   = $this->getTValue("forum_d_flag");
  $t_date   = $this->getTValue("forum_d_date");

  $fclosed = false;

  foreach ($t_id as $k => $v) {
    
    $next = array();
    $prev = -1;

    foreach ($t_id as $ki => $vi) {
      if ($t_lid[$ki] == $v) $next[] = $vi;
      if ($vi == $t_lid[$k]) $prev = $vi;
    }

    $elist[$v] = array( "id" => $v,
			"docid" => $docid,
			"next" => $next,
			"prev" => $prev,
			"who" => $t_user[$k], // ." [eid:".$v."|link:".$t_lid[$k]."]",
			"mail" => $t_mail[$k],
			"havemail" => ($t_mail[$k]=="" ? false : true ),
			"content" => $t_text[$k],
			"date" => $t_date[$k],
			"flag" => $t_flag[$k],
			"havenext" => (count($next)==0 ? false : true),
			"closed" => $fclosed,
		    );

  }
  return $elist;
}


?>
