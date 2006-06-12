<?php
ini_set("memory_limit","80M");  
// --------------------------------------
// first part event proprties construction

function calprop(&$trv,&$tuf) {
  $fdoc=fopen("migr1.db","r");

  $head= (fgetcsv ($fdoc, 5000, "|"));
  $head=array_map("trim",$head);
  //print_r($head);

  $none= (fgetcsv ($fdoc, 5000, "|"));
  $n=0;
  while ($data = fgetcsv ($fdoc, 5000, "|")) {
    if (trim($data[0])=='$') {
      $n++;
      foreach ($data as $k=>$v) {
	$tcal[$n][$head[$k]]=trim($v);
      }
      $ok=$k;
      //     if (trim($v)=="") $ok--;
     
    } else {
      foreach ($data as $k=>$v) {
	if ($k==0) {
	  $tcal[$n][$head[$k+$ok]].="\\n".trim($v);
	} else {
	  $tcal[$n][$head[$k+$ok]]=trim($v);
	}
      }
      $ok+=$k;
      // print_r2($tcal[$n]);
      // if ($tcal[$n]["cal_id"]==2295){
      //       print "[$ok]";
      //       print_r($data);
      //       print_r($tcal[$n]);
      //     }
    
    }
  }
  fclose($fdoc);
  //print_r2($tcal);

  
  //print_r($tuf);
  $trv=array();
  foreach ($tcal as $k=>$v) {
    $kc=$v["cal_id"];
    $trv[$kc]["calev_ownerid"]=$tuf[$v["wid"]];
    $trv[$kc]["calev_owner"]=$v["name"];
    $trv[$kc]["calev_evtitle"]=str_replace(";"," - ",$v["cal_name"]);
    $trv[$kc]["calev_evnote"]=str_replace(";"," - ",$v["cal_description"]);
    $trv[$kc]["calev_visibility"]=($v["cal_access"]=="P")?"0":"1";
    $trv[$kc]["calev_evstatus"]="2";
    $trv[$kc]["calev_evalarm"]="0"; //$v["cal_remind"];
    $trv[$kc]["calev_evalarmtime"]=$v["cal_data"];
    $sd=$v["cal_date"];
    $y=substr($sd,0,4);
    $m=substr($sd,4,2);
    $d=substr($sd,6,2);
    $st=$v["cal_time"];
    $du=$v["cal_duration"];

    if (($du > 0)&&($st>0)) {
      $trv[$kc]["calev_timetype"]="0";
      $st=$st/100;
      $h=floor($st/100);
      $mi=$st-($h*100);
      $sdate=sprintf("%04d-%02d-%02d %02d:%02d",$y,$m, $d,$h,$mi);
      $h+=floor($du/60);
      $mi+=($du-(floor($du/60))*60);
       if ($mi >= 60) {
 	$h += floor($mi/60);
 	$mi = ($mi % 60);
       }
      $send=sprintf("%04d-%02d-%02d %02d:%02d",$y,$m, $d,$h,$mi);

    } else if (($du <= 0)&&($st>0)) {
      $trv[$kc]["calev_timetype"]="0";
      $st=$st/100;
      $h=floor($st/100);
      $mi=$st-($h*100);
      $sdate=sprintf("%04d-%02d-%02d %02d:%02d",$y,$m, $d,$h,$mi);
      $h+=floor($du/60);
      $mi+=30;
      if ($mi >= 60) {
        $h += floor($mi/60);
        $mi = ($mi % 60);
      }
      $send=sprintf("%04d-%02d-%02d %02d:%02d",$y,$m, $d,$h,$mi);

    } else {
      if ($st < 0) $trv[$kc]["calev_timetype"]="2";
      else $trv[$kc]["calev_timetype"]="1";
      $sdate=sprintf("%04d-%02d-%02d 00:00",$y,$m, $d);
      $send=$sdate;
  //     if ($st < 0) print "$kc --)[$sdate] [$send] $du -$st- [ $sd $st\n";
//       else print "$kc)[$sdate] [$send] $du -$st- [ $sd $st\n";
    }
   
    //print "$kc)$sdate $send $du [ $sd $st\n";
    $trv[$kc]["calev_start"]=$sdate;
    $trv[$kc]["calev_end"]=$send;
    $trv[$kc]["calev_evcalendarid"]="-1";
    $trv[$kc]["calev_evcalendar"]="Calendrier public";
    $trv[$kc]["calev_repeatmode"]="0";
    $trv[$kc]["calev_repeatuntil"]="0";
    $trv[$kc]["calev_repeatuntildate"]="";
    $trv[$kc]["calev_repeatweekday"]="";


  }
  unset($tcal);
}
// --------------------------------------
// second part event  attendees
function calattendees(&$trv,&$tuf) {
  $fdoc=fopen("migr2.db","r");

  $head= (fgetcsv ($fdoc, 5000, "|"));
  $head=array_map("trim",$head);
  //print_r($head);

  $none= (fgetcsv ($fdoc, 5000, "|"));
  $n=0;
  while ($data = fgetcsv ($fdoc, 5000, "|")) { 
    $n++;
    foreach ($data as $k=>$v) {
      $tatt[$n][$head[$k]]=trim($v);
    }     
  }  
  fclose($fdoc);
  $tattid=array();
  foreach ($tatt as $k=>$v) {
    $i=$v["cal_id"];
    if (isset($trv[$i])) {
      $tattid[$i]["calev_attid"][]=$tuf[$v["wid"]];
      $tattid[$i]["calev_atttitle"][]=$v["name"];
      switch ($v["cal_status"]) {
      case 'A':
	$state="2";
	break;
      case 'W':
	$state="1";
	break;
      case 'D': // not used
	$state="3";
	break;
      case 'R':
	$state="3";
	break;
      default:
	$state="0";
      }
      $tattid[$i]["calev_attstate"][]=$state;
      $tattid[$i]["calev_attgroup"][]="-1";
    }
  }
  unset($tatt);
  foreach ($tattid as $k=>$v) {
    $trv[$k]["calev_attid"]=implode("\\n",$v["calev_attid"]);
    $trv[$k]["calev_atttitle"]=implode("\\n",$v["calev_atttitle"]);
    $trv[$k]["calev_attstate"]=implode("\\n",$v["calev_attstate"]);
    $trv[$k]["calev_attgroup"]=implode("\\n",$v["calev_attgroup"]);
  }

  //print_r($tatt);
}
// third part event  attendees
function calrepeats(&$trv) {

$fdoc=fopen("migr3.db","r");

$head= (fgetcsv ($fdoc, 5000, "|"));
$head=array_map("trim",$head);


$none= (fgetcsv ($fdoc, 5000, "|"));
$n=0;
while ($data = fgetcsv ($fdoc, 5000, "|")) {
  $n++;
  foreach ($data as $k=>$v) {
    $tatt[$n][$head[$k]]=trim($v);
  }
}
fclose($fdoc);
$tattid=array();
foreach ($tatt as $k=>$v) {
  $i=$v["cal_id"];
 
  if (isset($trv[$i])) {

   switch ($v["cal_type"]) {
    case 'daily':
      $state="1";
      break;
    case 'weekly':
      $state="2";
      break;
    default:
      $state="0";
    }
    $trv[$i]["calev_repeatmode"]=$state;
    if ($v["cal_end"] != "") {
      $trv[$i]["calev_repeatuntil"]="1";
      $sd=$v["cal_end"] ;
      $y=substr($sd,0,4);
      $m=substr($sd,4,2);
      $d=substr($sd,6,2);

      $trv[$i]["calev_repeatuntildate"]=sprintf("%04d-%02d-%02d",$y,$m,$d);

    } else {
      $trv[$i]["calev_repeatuntil"]="0";
      $trv[$i]["calev_repeatuntildate"]="";

    }

    if ($v["cal_days"] != "nnnnnnn") {
      $rdays=array();
      for ($k=0;$k<strlen($v["cal_days"]); $k++) {
	if ($v["cal_days"][$k]=='y') $rdays[]=($k+6)%7;
      }
      $trv[$i]["calev_repeatweekday"]=implode("\\n",$rdays);
    } 

   
  
  } 
  //print $i;    print_r($trv[$i]);
} 
}

function getUsers(&$tuf) {
  global $action;
  $tu=$action->user->GetUserList("TABLE");
  //print_r($tu);
  foreach ($tu as $k=>$v) {
    $tuf[$v["id"]]=$v["fid"];
  }
}
$trv=array();
   
getUsers($tuf);
   
calprop($trv,$tuf);
   

   
calrepeats($trv);
//print "\n".memory_get_usage();
calattendees($trv,$tuf);

print "ORDER;CALEVENT;;;";
print "calev_ownerid;calev_owner;calev_evtitle;calev_evnote;calev_visibility;calev_evstatus;calev_evalarm;calev_evalarmtime;calev_timetype;calev_start;calev_end;calev_evcalendarid;calev_evcalendar;calev_repeatmode;calev_repeatuntil;calev_repeatuntildate;calev_repeatweekday;calev_attid;calev_atttitle;calev_attstate;calev_attgroup\n";
foreach ($trv as $k=>$v) {
  print "DOC;CALEVENT;wcal$k;;";
  print implode(";",$v);
  print "\n";
  
}

?>
