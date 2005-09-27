/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Method.Agenda.php,v 1.1 2005/09/27 15:29:35 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage
 */

// delegate partial / full
// 

function SetDefaultAccess() {

  $this->disableEditControl();
  $this->SetProfil($this->id);
  $this->SetControl();
  $this->AddControl(2, "view");
  $this->AddControl(2, "execute");
  $this->AddControl(2, "invite");
  $err = $this->Modify();
  if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
  $this->enableEditControl();
}

function ComputeAccess() {

  $this->disableEditControl();
  $this->RemoveControl();

  // Owner visibility
  foreach ($this->acls as $ka => $va) {
    $this->AddControl($this->getValue("owner"), $va);
  }
    
  // Delegate visibility
  $dgusers = $this->getTValue("agd_dwid");
  $dgmode  = $this->getTValue("agd_dmode");
  foreach ($dgusers as $ku => $vu) {
    if ($dgmode[$ku] == 1) {
      foreach ($this->acls as $ka => $va) $this->AddControl($vu, $va);
    } else {
      $this->AddControl($vu, "pdelegate");
      $this->AddControl($vu, "view");
      $this->AddControl($vu, "execute");
      $this->AddControl($vu, "invite");
    }
  }
    
  // Group visibility on calendar
  $tgr = $this->getTValue("agd_vgroupwid");
  $tgrmode = $this->getTValue("agd_vgrouprw");
  if ($this->getValue("agd_vgroupmode")==1 && count($tgr)>0) {
    foreach ($tgr as $kg => $vg) {
      $this->AddControl($vg, "view");
      $this->AddControl($vg, "execute");
      if ($tgrmode[$kg]==1) {
	$this->AddControl($vg, "invite");
      }
    }
  } else {
    $this->AddControl(2, "view");
    $this->AddControl(2, "execute");
    $this->AddControl(2, "invite");
  }
  $err = $this->Modify();
  if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
  $this->enableEditControl();    
}

public $lsupacl = array( "invite", "delegate", "pdelegate"); # _("invite") _("delegate") _("pdelegate")

function complete() {
   $ka = POS_WF;
   foreach ($this->lsupacl as $k=>$v) {
      $this->dacls[$v] = array( "pos"=>$ka, "description" =>_($v));
      $this->acls[]=$v;
      $ka++;
   }
}

