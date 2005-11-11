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

  .day {
     background-color:white;
     font-size:9px;
     position : relative;
     border-style:  outset;
     border-color: #FFF2DB; 
     border-width: 1px;
  }

  .dayh {
     text-align:center;
     font-weight: bold;
     background-color:#F1D998;
     border-style:  outset;
     border-color: #FFF2DB;
     border-width: 1px;
  }

 .day0 { background-color:  #F8F1FB; }
 .day1 { background-color:  #F8F1FB; }
 .dayc { background-color:  #F2FFDB; }
 .daynh { background-color: #EAE9C1; }
 .daywe0 { background-color: #edeff7; }
 .daywe1 { background-color: #eff1f9; }

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
<script type="text/javascript" src="/test/jslib/geometry.js"></script>
<script type="text/javascript" src="/test/jslib/DHTMLapi.js"></script>
<script type="text/javascript" src="/test/jslib/AnchorPosition.js"></script>

<script type="text/javascript" src="/test/jslib/jsXMLParser/xmldom.js"></script>

<script type="text/javascript" src="/test/mcallib.js"></script>
<script type="text/javascript" src="/test/mcalmenu.js"></script>
<script type="text/javascript" src="/test/mcalendar.js"></script>
</head>
<body>
<div id="calendarRoot" style="top:0px; left:0px; width:90%; height:90%; position:absolute"></div>
<!-- div id="calendarRoot2" style="top:10px; left:450px; width:400px; height:400px; position:absolute"></div -->

<script type="text/javascript">


    function mhandler(event, cal, evid) {
      var ts = '';
      for (var ia=0; ia<arguments.length; ia++) {
	ts += arguments[ia]+' ';
      }
      alert(ts);
    }


    var sm = [ 'mcalendar-rep.php?ts=%TS%&te=%TE%', 'mcalendar_detail.php?id=%EVID%' ];
    var cal = new MCalendar('calendarRoot', sm);

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
