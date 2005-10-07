var $faddbook_card = "USERCARD:FADDBOOKSOC_CARD:T";
var $faddbook_resume = "USERCARD:FADDBOOKSOC_RESUME:T";

function faddbooksoc_card($target="finfo",$ulink=true,$abstract="Y") {
  // list of attributes displayed directly in layout
  $ta=array("si_logo","si_society","si_town","si_mail","si_phone","si_mobile","si_fax","si_web","si_addr","si_cedex","si_country","si_postcode");
  

  $this->viewdefaultcard($target,$ulink,$abstract);
  $la=$this->getAttributes();
  $to=array();
  foreach ($la as $k=>$v) {
    $va=$this->getValue($v->id);
    if (($va) && (! in_array($v->id,$ta))){
      if (($v->isInAbstract) && (($v->mvisibility == "R") || ($v->mvisibility == "W"))) {
	$to[]=array("lothers"=>$v->labelText,
		    "vothers"=>$this->getHtmlValue($v,$va,$target,$ulink));
      }
    }
  }

  $logo=$this->getValue("si_logo");
  if ($logo) {
    $this->lay->set("logo",$this->getHtmlAttrValue("si_logo"));
    $this->lay->set("wlogo","70");
  } else {
    $this->lay->set("logo",$this->getIcon());
    $this->lay->set("wlogo","48");
  }
  $this->lay->setBlockData("OTHERS",$to);
  $this->lay->set("HasOthers",(count($to)>0));
  $this->lay->set("HasLogo",($logo!=""));
  
}