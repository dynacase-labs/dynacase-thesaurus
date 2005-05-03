<?php

function fdl_pubsendmail($target="_self",$ulink=true,$abstract=false) {
  $this->viewdefaultcard($target,$ulink,$abstract);

  $uid=getHttpVars("uid");
  if ($uid) {
    $udoc=new Doc($this->dbaccess,$uid);
    if ($udoc->isAlive()) {
      $listattr = $udoc->GetNormalAttributes();
      $atarget=""; // must not be mail the same bacuse it is not the doc itself
      foreach($listattr as $k=>$v) {
	$value=$udoc->getValue($v->id);

	if ($value) $this->lay->Set(strtoupper($v->id),$udoc->GetHtmlValue($v,$value,$atarget,$ulink));
	else $this->lay->Set(strtoupper($v->id),false);
      }  
    }
  }
}
function fdl_pubprintone($target="_self",$ulink=true,$abstract=false) {
  return $this->fdl_pubsendmail($target,$ulink,$abstract); 
}
?>