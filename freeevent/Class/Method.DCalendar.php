<?php

var $cviews=array("FREEEVENT:PLANNER");
function getEvents($d1="",$d2="") {

  return $this->getContent();
}


function planner($target="finfo",$ulink=true,$abstract="Y") {
  include_once("FREEEVENT/Lib.DCalendar.php");
  include_once("FDL/Lib.Color.php");
  global $action;

  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/jdate.js");
  $action->parent->AddCssRef($action->GetParam("CORE_PUBURL")."/FREEEVENT/Layout/planner.css");
  $tevt=$this->getEvents();
  $byres= (getHttpVars("byres","N")=="Y");
  $mb=microtime();
 
  // window time interval
  $hwstart=getHttpVars("wstart");
  if ($hwstart) {
    $wstart=FrenchDateToJD($hwstart);
  } else $wstart=getHttpVars("jdstart"); 
  
  $hwend=getHttpVars("wend");
  if ($hwend) {
    $wend=FrenchDateToJD($hwend);
  } else $wend=getHttpVars("jdend");

  if (!$wstart) {
    $isoperiode=getHttpVars("isoperiod"); 
    if ($isoperiode) {
      if (ereg("([0-9]+)-([0-9]+)",$isoperiode,$reg)) {
	// month period
	$wstart=FrenchDateToJD(sprintf("01/%02d/%04d",$reg[2],$reg[1]));
	$wend=FrenchDateToJD(sprintf("01/%02d/%04d",$reg[2]+1,$reg[1]));
      } elseif (ereg("([0-9]+)",$isoperiode,$reg)) {
	// year period
	$wstart=FrenchDateToJD(sprintf("01/01/%04d",$reg[1]));
	$wend=FrenchDateToJD(sprintf("01/01/%04d",$reg[1]+1));
      }
    }
  }

    print "<br>wstart:$wstart:".jd2cal($wstart);
   print "<br>wend:$wend:".jd2cal($wend);
  

  $mstart=5000000; // vers 9999
  $mend=0;
  if ($wstart) {
    $mstart=$wstart;
    $mstart=floor($mstart+0.5)-0.5; // begin at 00:00
  }
  if ($wend) {
    $mend=$wend;
    $mend=floor($mend)+0.5; // end at 00:00
  } 
  
  foreach ($tevt as $k=>$v) {

    $mdate1=FrenchDateToJD(getv($v,"evt_begdate"));
    $mdate2=FrenchDateToJD(getv($v,"evt_enddate"));
    if ($wstart) {
      if (($mdate2<$mstart) || ($mdate1>$wend)) {
	unset($tevt[$k]);       
      } else {  
	$tevt[$k]["m1"]=max($mdate1,$mstart);
	$tevt[$k]["m2"]=min($mdate2,$mend);
      } 
    } else {
      if ($mstart > $mdate1) $mstart=$mdate1;
      $tevt[$k]["m1"]=$mdate1;
      if ($mdate2 > $mend) $mend=$mdate2;
      $tevt[$k]["m2"]=$mdate2;
      
    }
    
  }
  uasort($tevt,"cmpevtm1");
  $ridx=0;
  $delta=$mend-$mstart;
  $sub=0;
  $idc=0;
//   print "delta=$delta";
//   print " - <B>".microtime_diff(microtime(),$mb)."</B> ";
  foreach ($tevt as $k=>$v) {
    $tr=$this->_val2array(getv($v,"evt_idres"));
    $x=floor(100*($v["m1"]-$mstart)/$delta);
    $w=floor(100*($v["m2"]-$v["m1"])/$delta);
    foreach ($tr as $ki=>$ir) {
      if (! isset($colorredid[$ir])) $colorredid[$ir]=$idc++;
      $RN[$ir][]=array("w"=>sprintf("%d",($w<1)?1:$w),
		       "absx"=>$v["m1"],
		       "absw"=>$v["m2"]-$v["m1"],
		       "line"=>$k,
		       "subline"=>$byres?$colorredid[$ir]:$sub,
		       //"subline"=>$colorredid[$ir],
		       "divid"=>"div$k$ki",
		       "idx"=>$sub,
		       "evticon"=>$this->getIcon($v["icon"]),
		       "rid"=>getv($v,"evt_idinitiator"),
		       "eid"=>getv($v,"id"),
		       "divtitle"=>((($v["m2"]-$v["m1"])>0)?'':_("DATE ERROR")).$v["title"],
		       "bartitle"=>sprintf("%s - %s",
					   substr(getv($v,"evt_begdate"),0,10),
					   substr(getv($v,"evt_enddate"),0,10)));
      $SX[$ir]+=$w;
      $sub++;
      $tres[$ir]=array("BAR"=>"bar$ir",
		       "res"=>getv($v,"evt_res"));
      
    }
    
  }
  
  $dcol=360/count($tres);
  foreach ($tres as $k=>$v) {    
    
    //  $rn=1;
    $col=HSL2RGB($colorredid[$k]*$dcol,1,0.8);
    foreach ($RN[$k] as $kn=>$vn) $RN[$k][$kn]["color"]=$col;
    $tres[$k]["rescolor"]=$col;
    $this->lay->setBlockData("bar$k",$RN[$k]);
  }
  $this->lay->setBlockData("RES",$tres);



    if (!$wstart) {
      $mstart=floor($mstart)-0.5; // begin at 00:00
      $mend=floor($mend)+0.5; // end at 00:00
    }

  $this->lay->set("begdate",jd2cal($mstart));
  $this->lay->set("enddate",jd2cal($mend));
  $this->lay->set("mstart",$mstart);
  $this->lay->set("mend",$mend);
  $this->lay->set("id",$this->id);
  $this->lay->set("vid",GetHttpVars("vid"));

  //  print "<HR>". print " - <B>".microtime_diff(microtime(),$mb)."</B>";
  // print "<hr>";

}

?>