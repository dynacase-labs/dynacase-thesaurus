<?php

var $defaultedit= "FDL:FDL_PUBEDIT";
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
function fdl_pubedit() {
  $this->editattr();
  $udoc=createDoc($this->dbaccess,"USER",false);
  $listattr = $udoc->GetNormalAttributes();  
  foreach($listattr as $k=>$v) {
    $tatt[$k]=array("aid"=>"[".strtoupper($k)."]",
		    "alabel"=>$v->labelText);
    
  }
  $listattr = $udoc->GetFileAttributes();  
  foreach($listattr as $k=>$v) {
    if ($v->type=="image") {
      $tatt[$k]=array("aid"=>"<img src=\"[".strtoupper($k)."]\" />",
		      "alabel"=>$v->labelText);
    } else {
      $tatt[$k]=array("aid"=>"<a href=\"[".strtoupper($k)."]\">".$v->labelText."</a>",
		      "alabel"=>$v->labelText);
      
    }
    
  }
  $this->lay->setBlockData("ATTR",$tatt);
  

}
?>