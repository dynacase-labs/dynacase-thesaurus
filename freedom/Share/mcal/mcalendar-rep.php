<?php

global $_GET;
$startp = $_GET["ts"];
$endp = $_GET["te"];
 
// sleep(2);

$events = array( 100 =>array( "time" => $startp + (8*3600), "dura" => 3600 ),
                 101 =>array( "time" => $startp + (34*3600), "dura" => 1800 ),
                 1011 =>array( "time" => $startp + (34*3600) + 1800, "dura" => 1800 ),
                 1012 =>array( "time" => $startp + (48*3600), "dura" => (24*3600)-1 ),
                 102 =>array( "time" => $startp + (110*3600), "dura" => 5400),
                 1022 =>array( "time" => $startp + (110*3600), "dura" => 3600),
                 1021 =>array( "time" => $startp + (110*3600), "dura" => 0),
 		 103 =>array( "time" => $startp + (130*3600), "dura" => 26*3600),
// 		 200 =>array( "time" => $startp + (8*3600), "dura" => 3600 ),
//                  201 =>array( "time" => $startp + (34*3600), "dura" => 1800 ),
//                  2011 =>array( "time" => $startp + (34*3600) + 1800, "dura" => 1800 ),
//                  2012 =>array( "time" => $startp + (48*3600), "dura" => (24*3600)-1 ),
//                  202 =>array( "time" => $startp + (110*3600), "dura" => 5400),
//                  2021 =>array( "time" => $startp + (110*3600), "dura" => 0),
// 		 203 =>array( "time" => $startp + (130*3600), "dura" => 26*3600),
// 		 300 =>array( "time" => $startp + (8*3600), "dura" => 3600 ),
//                  301 =>array( "time" => $startp + (34*3600), "dura" => 1800 ),
//                  3011 =>array( "time" => $startp + (34*3600) + 1800, "dura" => 1800 ),
//                  3012 =>array( "time" => $startp + (48*3600), "dura" => (24*3600)-1 ),
//                  302 =>array( "time" => $startp + (110*3600), "dura" => 5400),
//                  3021 =>array( "time" => $startp + (110*3600), "dura" => 0),
// 		 303 =>array( "time" => $startp + (130*3600), "dura" => 26*3600),
// 		 400 =>array( "time" => $startp + (8*3600), "dura" => 3600 ),
//                  401 =>array( "time" => $startp + (34*3600), "dura" => 1800 ),
//                  4011 =>array( "time" => $startp + (34*3600) + 1800, "dura" => 1800 ),
//                  4012 =>array( "time" => $startp + (48*3600), "dura" => (24*3600)-1 ),
//                  402 =>array( "time" => $startp + (110*3600), "dura" => 5400),
//                  4021 =>array( "time" => $startp + (110*3600), "dura" => 0),
// 		 403 =>array( "time" => $startp + (130*3600), "dura" => 26*3600)
		 );
header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';

echo '<eventdesc>';

echo '<menu id="evt_menu">';
echo '<style font="Arial,Helvetica,sans-serif" size="9" fgcolor="#000081" bgcolor="#E9E3FF" afgcolor="" abgcolor="#C2C5F9" tfgcolor="white" tbgcolor="#000081" />';
echo '  <item id="evt_menu_title" status="2" type="0">';
echo '    <label>Menu evenement</label>';
echo '    <description>Menu evenement</description>';
echo '  </item>';
echo '  <item id="evt_menu_read" status="2" type="1" icon="defico.png">';
echo '    <label>Afficher</label>';
echo '    <description>Afficher la description complete</description>';
echo '    <action aid="evt_menu_read" amode="0" aevent="0" atarget="event_show" ascript="mcalendar_detail.php?id=%EID%" />';
echo '   </item>';
echo '  <item id="evt_menu_test" status="2" type="1">';
echo '    <label>Test parser</label>';
echo '    <description>Menu permettant de tester le parser</description>';
echo '    <action aid="evt_menu_read" amode="2" aevent="0" atarget="" ascript="alert(\'test parser %EID%\');" />';
echo '   </item>';
echo '  <item id="evt_menu_test2" status="2" type="1">';
echo '    <label>Free</label>';
echo '    <description>Visiter le site de Free</description>';
echo '    <action aid="evt_menu_read" amode="1" aevent="0" atarget="free" ascript="http://www.free.fr" />';
echo '   </item>';
echo '</menu>';

foreach ($events as $k => $v) {
  echo '<event id="'.$k.'" rid="evt'.$k.'" cid="evc'.$k.'" dmode="1" time="'.$v["time"].'" duration="'.$v["dura"].'">';
  echo '<menuref id="evt_menu" use="1,1" />';
  echo '<title>'.getTitle($k).'</title>';
  echo '<content>'.getContent($k,$v).'</content>';
//   echo '<menu>';

//   // <item>
//   // default -> 1 action activated on mouse over 2 action activated on click
//   // target  =  "_self" an "WxH" window sized
//   // type = Title | Separator | JScript | Action | 
//    echo '<item default="0" type="Action" label="Accepter" target="fhidden" action="http://....wgcal_accept.php&amp;id=1223"></item>';
//    echo '<item default="0" type="Action" label="Refuser" target="fhidden" action="http://....wgcal_refuse.php&amp;id=1223"></item>';
//    echo '<item default="0" type="Separator" label="" target="" action=""></item>';
//    echo '<item default="1" type="Action" label="Afficher" target="300x150" action="http://....wgcal_view.php&amp;id=1223"></item>';
//    echo '<item default="0" type="Action" label="Editer" target="300x150" action="http://....wgcal_edit.php&amp;id=1223"></item>';
//    echo '<item default="0" type="Action" label="Supprimer" target="fhidden" action="http://....wgcal_delete.php&amp;id=1223"></item>';
//    echo '<item default="0" type="Separator" label="" target="" action=""></item>';
//    echo '<item default="0" type="Action" label="Historique" target="400x200" action="http://....wgcal_histo.php&amp;id=1223"></item>';
  
//   echo '</menu>';
  echo '</event>';
}
echo '</eventdesc>';
return;

function getTitle($x) {
  return "Event number $x";
}
function getContent($x,$v) {
  $r = '<styleinfo>';
  $r .= '<style id="background-color" val="yellow"/>';
  $r .= '<style id="color" val="blue"/>';
  $r .= '<style id="border" val="1px solid blue"/>';
  $r .= '</styleinfo>';
  $r .= '<chtml>';
  $r .= '<img style="vertical-align:middle; width:14" src="defico.png"/>';
  $r .= '<img style="vertical-align:middle; width:14" src="defico.png"/>';
  $r .= '<img style="vertical-align:middle; width:14" src="defico.png"/>';
  $r .= '<span style="font-weight:bold; vertical-align:middle">'.getTitle($x).'</span>';
  $r .= '<div>'.strftime("%d/%m/%y %H:%M",$v["time"]).' - '.strftime("%d/%m/%y %H:%M",($v["time"] + $v["dura"])).'</div>';
  $r .= '</chtml>';
  return $r;
}
?>
