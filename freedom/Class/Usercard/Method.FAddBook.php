
var $faddbook_card = "USERCARD:FADDBOOK_CARD";
var $faddbook_resume = "USERCARD:FADDBOOK_RESUME";

function faddbook_resume()
{

  global $action;

  $imgu = "";
  $img = $this->getValue("us_photo");
  if ($img=="") {
    $this->lay->set("hasPhoto", false);
  } else {
    $this->lay->set("hasPhoto", true);
    $imgu = $this->GetHtmlValue($this->getAttribute("us_photo"), $img);
    $this->lay->set("photo", $imgu);
  }

  $civ = $this->getValue("us_civility");
  $this->lay->set("hasCiv", ($civ!="" ? true : false));
  $this->lay->set("civilite", $civ);

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

function faddbook_card() 
{

}
