<html>

<head>
<style>
html {background-color:#eeeeee}
  body, table, select {
    background-color:#FFF1E8;
    font-family:Tahoma,Arial,Helvetica,sans-serif;
    font-size:9px;
  margin : 0px;
  margin-top : 30px;
  padding : 0px;
  }
  .root {
    border-style: groove;
    border-color: orange; 
     /* border-width: 3px; */
    border-width: 0px;
  }


 .inputzone {
    background-color:white;
    border-style: groove;
    border-color: orange; 
    border-width: 3px;
    margin : 10px;
    padding : 10px;
 }

.event {
     color:blue;
     background-color:white;
     border : 1px solid blue;
     overflow : hidden;
}

.default {
    background-color: white;
    /*  border : 1px solid black; */
    border : 1px dotted black;
    overflow : hidden;
  }
</style>

<?php
$rwhat = '/what/WHAT/Layout/';
echo '
<script type="text/javascript" src="'.$rwhat.'geometry.js"></script>
<script type="text/javascript" src="'.$rwhat.'DHTMLapi.js"></script>
<script type="text/javascript" src="'.$rwhat.'AnchorPosition.js"></script>
<script type="text/javascript" src="'.$rmcal.'xmldom.js"></script>
<script type="text/javascript" src="'.$rmcal.'/test/mcallib.js"></script>
<script type="text/javascript" src="'.$rmcal.'/test/mcalmenu.js"></script>
<script type="text/javascript" src="'.$rmcal.'/test/mcalendar.js"></script>
';
?>

</head>
<body>
<div id="calendarRoot" style="top:0px; left:0px; width:95%; height:90%; position:absolute"></div>
<!-- div id="calendarRoot2" style="top:10px; left:450px; width:400px; height:400px; position:absolute"></div -->

<script type="text/javascript">


    function mhandler(event, cal, evid) {
      var ts = '';
      for (var ia=0; ia<arguments.length; ia++) {
	ts += arguments[ia]+' ';
      }
      alert(ts);
    }

var menu = [
    { id:'newevent', label:'Nouveau RV', desc:'Nouveau rendez-vous, heure courante', status:2, type:1,
      icon:'mcalendar-new.gif', onmouse:'', amode:2, aevent:0, 
      atarget:'', ascript:'mhandler', aevent:0 },
    { id:'newevent', label:'RV toute la journée', desc:'Nouveau rendez-vous, toute la journée', status:2, type:1,
      icon:'mcalendar-new.gif', onmouse:'', amode:2, aevent:0, 
      atarget:'', ascript:'mhandler', aevent:0 },
    ];

var sm = [ 
    { id:'getevents', request:'mcalendar-rep.php?ts=%TS%&te=%TE%&' },
    { id:'geteventsdiff', request:'mcalendar-rep.php?ts=%TS%&te=%TE%&lr=%LR%' },
    { id:'eventresume', request:'mcalendar_resume.php?id=%EVID%' },
    { id:'eventcard', request:'mcalendar_detail.php?id=%EVID%' },
    ];

    var cal = new MCalendar('calendarRoot', sm, menu);

    cal.CalDaysCount = 7;
    cal.CalHoursPerDay = 10;

    cal.dayBaseCss = 'day';
    cal.dayCss = [ 'day0', 'day1' ];
    cal.dayCurrentCss = 'dayc';
    cal.dayWeekEndCss = [ 'daywe0', 'daywe1' ];
    cal.daynhCss = 'daynh';
    cal.dayTitleCss = 'dayh';
    cal.Display();
</script>

<div id="inputzone" style="position:absolute; border:1px solid orange; padding:2px; display:none; z-index:1000; ">
<input size="30" id="evtitle" type="text" value=""  onkeypress="return cal.createNewEvent(event);">
</div>


</body>
</html>
