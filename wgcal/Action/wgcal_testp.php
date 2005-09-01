<?php
include_once('FDL/Class.Doc.php');
include_once('WGCAL/Lib.wTools.php');

function wgcal_testp(&$action) {


  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  wSearchUserCal(12, array(1094));
  wSearchUserCal(12);



//   $docid = GetHttpVars("id",0);
//   if ($docid == 0 ) return;

//   $d = new_Doc($dbaccess, $docid);

//   $attr = $d->getValues();

// //   print_r2($attr);
//   foreach ($d->dacls as $k => $v) echo "[".$k."] => ".$v["pos"]." (".$v["description"].")<br>";


//   $m = $d->UnsetControl();
//   $action->log->info("UnsetControl(): [$m]");

//   $m = $d->SetProfil($d->id);
//   echo "SetProfil():$m<br>";


//   $m =  $d->SetControl(true);
//   $action->log->info("SetControl(): [$m]");
//   $m =  $d->AddControl(2, 'view');
//   $action->log->info("AddControl(2,view): [$m]");
//    $m =  $d->AddControl(11, 'edit');
//   $action->log->info("AddControl(11,edit): [$m]");

//   $m = $d->Modify();
//   echo "Modify():$m<br>";


 return;

}