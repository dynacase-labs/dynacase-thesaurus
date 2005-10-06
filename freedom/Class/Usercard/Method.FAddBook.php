
var $faddbook_card = "USERCARD:FADDBOOK_CARD:T";
var $faddbook_resume = "USERCARD:FADDBOOK_RESUME:S";

function faddbook_resume($target="finfo",$ulink=true,$abstract="Y") {

  $imgu = "";
  $img = $this->getValue("us_photo");
  if ($img=="") {
    $this->lay->set("hasPhoto", false);
  } else {
    $this->lay->set("hasPhoto", true);
    $imgu = $this->GetHtmlValue($this->getAttribute("us_photo"), $img);
    $this->lay->set("photo", $imgu);
  }

  $this->lay->set("nom", $this->getValue("us_lname"));
  $this->lay->set("prenom", $this->getValue("us_fname"));

  $soc = $this->getValue("us_society");
  $this->lay->set("hasSoc", ($soc!="" ? true : false));
  $this->lay->set("societe", $soc);

  $mail = $this->getValue("us_mail");
  $this->lay->set("hasMail", ($mail!="" ? true : false));
  $this->lay->set("addmail", $mail);

  $mob = $this->getValue("us_mobile");
  $this->lay->set("nomob", $mob);
  $this->lay->set("hasMob", ($mob!="" ? true : false));

  $tel = $this->getValue("us_phone");
  $this->lay->set("notel", $tel);
  $this->lay->set("hasTel", ($tel!="" ? true : false));

  $sky = $this->getValue("us_skypeid");
  $this->lay->set("skypeid", $sky);
  $this->lay->set("hasSky", ($sky!="" ? true : false));

  $msn = $this->getValue("us_msnid");
  $this->lay->set("msnid", $msn);
  $this->lay->set("hasMsn", ($msn!="" ? true : false));

  
  return;
   
}


function faddbook_card($target="finfo",$ulink=true,$abstract="Y") {
  // list of attributes displayed directly in layout
  $ta=array("us_workweb","us_photo","us_lname","us_fname","us_society","us_civility","us_mail","us_phone","us_mobile","us_fax","us_intphone","us_workaddr","us_workcedex","us_country","us_workpostalcode","us_worktown");
  

  $this->viewdefaultcard($target,$ulink,$abstract);
  $la=$this->getAttributes();
  $to=array();
  foreach ($la as $k=>$v) {
    $va=$this->getValue($v->id);
    if (($va) && (! in_array($v->id,$ta))){
      if (($v->mvisibility == "R") || ($v->mvisibility == "W")) {
	$to[]=array("lothers"=>$v->labelText,
		    "vothers"=>$this->getHtmlValue($v,$va,$target,$ulink));
      }
    }
  }
  $this->lay->setBlockData("OTHERS",$to);
  
}