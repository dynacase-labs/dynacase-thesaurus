include_once('WGCAL/Lib.wTools.php');
include_once('FDL/Class.Doc.php');

function wgcal_vcalmode(&$action) 
{
  $vcalmode = GetHttpVars("vcalmode", 0);
  $vcalgroups = GetHttpVars("vcalgroups", "");

  $user = new Doc($action->getParam("FREEDOM_DB"), $action->user->fid);
  $user->setValue("us_wgcal_vcalgrpmode", $vcalmode);
  
  $user->deleteValue("us_wgcal_vcalgrpid");
  if ($vcalgroups!="") {
    $tgv = array();
    $tg = explode("|",  $vcalgroups);
    foreach ($tg as $k => $v) {
      if ($v!="") $tgv[] = $v;
    }
    if (count($tgv)>0) $user->setValue("us_wgcal_vcalgrpid", $tgv);
  }
  $user->Modify();
}

?>
