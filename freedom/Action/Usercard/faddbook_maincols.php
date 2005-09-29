<?php
include_once("FDL/Lib.Dir.php");


function faddbook_maincols(&$action) {

  global $_GET,$_POST,$ZONE_ARGS;
  $dbaccess = $action->getParam("FREEDOM_DB");

  $reset = GetHttpVars("resetcols", 0);

  $ncols = array();
  if ($reset!=1) {
    foreach ($_POST as $k => $v) {
      if (substr($k,0,11)!="faddb_cols_") continue;
      $id = substr($k, 13);
      $vie = substr($k, 11, 1);
      $ncols[$id][$vie] = ($v=="on"?1:0);
    }
  }

  // Get default visibilty => Abstract view from freedom
  $dfam = createDoc($dbaccess, "USER",  false);
  $fattr = $dfam->GetAttributes();
  $cols = array();
  foreach ($fattr as $k => $v) {
    if ($v->type!="menu" && $v->type!="frame" && $v->visibility!="H" && $v->visibility!="O" && $v->visibility!="I") {
      $cols[$v->id] = array( "l"=>($v->isInAbstract==1?1:0) , "r"=>($v->isInAbstract==1?1:0) ,
                       "order" => $v->ordered, "label" => $v->labelText);
    }
  }

  if (count($ncols)>0 || $reset==1) { // Modified state

    $allcol = array();
    foreach ($cols as $k => $v) {
      if ($reset!=1) $cols[$k]["l"] = $cols[$k]["r"] = 0;
      if (isset($ncols[$k])) {
	if ($ncols[$k]["l"]!="") $cols[$k]["l"] = $ncols[$k]["l"];
	if ($ncols[$k]["r"]!="") $cols[$k]["r"] = $ncols[$k]["r"];
      }
      $allcol[] = $k."%".$cols[$k]["l"]."%".$cols[$k]["r"];
    }
    $scol = implode("|", $allcol);
    $action->parent->param->set("FADDBOOK_MAINCOLS", $scol, PARAM_USER.$action->user->id, $action->parent->id);
    
  } else { // User initial state
    
    $pc = $action->getParam("FADDBOOK_MAINCOLS", "");
    if ($pc!="") {
      $tccols = explode("|",  $pc);
      foreach ($tccols as $k => $v) {
	if ($v=="") continue;
	$x = explode("%",$v);
	if (isset($cols[$x[0]])) {
	  if (isset($cols[$x[0]]["l"])) $cols[$x[0]]["l"] = $x[1];
	  if (isset($cols[$x[0]]["r"])) $cols[$x[0]]["r"] = $x[2];
	}
      }
    }
  }

  foreach ($cols as $k => $v) {
    $vcols[] = array( "id" => $k,
		      "label" => $v["label"],
		      "pos" => $v["order"],
		      "r_view" => ($v["r"] == 1 ? "checked" : ""),
		      "l_view" => ($v["l"] == 1 ? "checked" : "")
		      );
  }
  $action->lay->setBlockData("Columns", $vcols);

}

?>
