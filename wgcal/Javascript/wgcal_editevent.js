/*
 **
 **
 **
*/

function swstate(s) {
  if (s==1) return 0;
  else return 1;
}

function ChangeAlarm(state) {
  chk = document.getElementById('AlarmCheck');
  alrm = document.getElementById('AlarmVis');
 if (state!=-1) chk.checked = (state==0?false:true);
  if (chk.checked) alrm.style.display = '';
  else alrm.style.display = 'none';
}



var NoHourState = 0;
function ChangeNoHour(state) {
  chk = document.getElementById('nohour');
  chko = document.getElementById('allday');
  hstart = document.getElementById('start_hour');
  hend = document.getElementById('end_hour');
  if (state==1) {
    chk.checked = true;
    chko.checked = false;
    hend.style.display = 'none';
    hstart.style.display = 'none';
    NoHourState = 1;
  } else {
    chk.checked = false;
    hend.style.display = '';
    hstart.style.display = '';
    NoHourState = 0;
  }
  return;
}
     
var  AllDayState= 0;
function ChangeAllDay(state) {
  chk = document.getElementById('allday');
  chko = document.getElementById('nohour');
  hstart = document.getElementById('start_hour');
  hend = document.getElementById('end_hour');
  if (state==1) {
    chk.checked = true;
    chko.checked = false;
    hend.style.display = 'none';
    hstart.style.display = 'none';
    AllDayState = 1;
  } else {
    chk.checked = false;
    hend.style.display = '';
    hstart.style.display = '';
    AllDayState = 0;
  }
  return;
}

function SwitchZone(view) {

  var zones = new Array ( 'evmainzone', 'evrepeatzone', 'evattendeeszone');

  for (zx in zones) {
    z = zones[zx];
    document.getElementById(z).style.display = 'none';
    document.getElementById('but'+z).className = 'WGCalZoneDefault';
    if (z == view) {
       document.getElementById(view).style.display = '';
       document.getElementById('but'+view).className = 'WGCalZoneSelected';
   }
  }

}


// Attendees management --------------------------------------------

var attendeesList = new Array();


function refreshAttendees() {

  var nTr;
  var tab = document.getElementById('tabress');

  for (idx in attendeesList) {
    if (attendeesList[idx][0] != -1 &&  attendeesList[idx][4]==0) {
      attendeesList[idx][4] = 1;
      with (document.getElementById('trsample')) {
	style.display = '';
	nTr = cloneNode(true);
	style.display = 'none';
      }
      nTr.id = 'tr'+attendeesList[idx][0];
      mynodereplacestr(nTr, '%RID%', attendeesList[idx][0]);
      mynodereplacestr(nTr, '%RICON%', attendeesList[idx][2]);
      mynodereplacestr(nTr, '%RDESCR%', attendeesList[idx][1]);
      mynodereplacestr(nTr, '%RSTATE%', attendeesList[idx][3]);
      nTr.style.display = '';
      tab.appendChild(nTr);
      capp = document.getElementById('cp'+attendeesList[idx][0]);
      switch (attendeesList[idx][3]) {
      case 1: /* approved */
	capp.style.background = 'green';
	break;
      case 2: /* refused */
	capp.style.background = 'red';
	break;
      case 3:
	capp.style.background = 'orange';
	break;
      default:
	capp.style.background = 'yellow';
      }
    }
  }
  return; 
}

function getAttendeeIdx(aid) {
  var idx = -1;
  for (i=0; i<attendeesList.length; i++) {
    if (attendeesList[i][0] == aid) idx = ix;
  }
  return idx;
}
      
function addRessource(rid, rtitle, ricon) {
  if (getAttendeeIdx(rid)!=-1) return;
  idx = attendeesList.length;
  attendeesList[idx] = new Array();
  attendeesList[idx][0] = rid;
  attendeesList[idx][1] = rtitle;
  attendeesList[idx][2] = ricon;
  attendeesList[idx][3] = 0; /* confirmation status */
  attendeesList[idx][4] = 0; /* displayed status */
  refreshAttendees();
}

function  deleteAttendee(aid) {
  for (i=(attendeesList.length-1); i>=0; i--) {
    if (aid == attendeesList[i][0]) {
      eltA = document.getElementById('tr'+aid);
      if (!eltA) return;
      eltA.parentNode.deleteRow(eltA.sectionRowIndex);
      attendeesList[i] = -1;
    }
  }
}


var attpicker;
function attkillwins() {
  if (attpicker != null) attpicker.close();
}

function saveEvent() {
   var fs = document.getElementById('editevent');
   EventSelectAll(fs);
   fs.submit();
   fs.reset();
  //displayEvent(document.getElementById('eventid').value;	
  //self.close();
}

function cancelEvent() {
  ok = confirm('[TEXT: save before closins]'); 
  if (ok) saveEvent();
  self.close();
}

var ExcludeDate = 0;

function addExclDate() {

  var list = document.getElementById('listexcldate');
  var nOpt = new Option();
  var ndate = document.getElementById('nexcldate').value;

  var jdate = new Date(ndate*1000);
  var y = jdate.getFullYear();
  var m = jdate.getMonth();     // integer, 0..11
  var d = jdate.getDate();
  var ts = calendar.date.print("%s");

  nOpt.id = 'exdate'+ExcludeDate;
  nOpt.value = ndate;
  nOpt.text  = calendar.date.print("%A %d %B %Y");
  i = list.options.length;
  list.options[i] = nOpt;
  ExcludeDate++;
}

function delExclDate() {
  var list = document.getElementById('listexcldate');
  for (i=(list.options.length-1); i>=0; i--) {
    if (list.options[i].selected) list.options[i] = null;
  }
}



function ViewElement(eCheck, eDisplay) {
  chk = document.getElementById(eCheck);
  zon = document.getElementById(eDisplay);
  if (chk.checked == true) {
    zon.style.display = '';
  } else {
    zon.style.display = 'none';
  }
}

function everyInfo() {
  var checkone = -1;
  evr = document.getElementsByName('repeattype');
  for (i=0; i<evr.length; i++) {
    if (evr[i].checked) checkone = i;
  }

  document.getElementById('d_rweekday').style.display = 'none';
  document.getElementById('d_rmonth').style.display = 'none';

  if (checkone==1) document.getElementById('d_rweekday').style.display = '';
  if (checkone==2) document.getElementById('d_rmonth').style.display = '';

}


function EventSelectAll(f) {

  var list = document.getElementById('listexcldate');
  for (i=(list.options.length-1); i>=0; i--) {
    list.options[i].selected = true;
  }

  alist = document.getElementById('attendees');
  for (att=0; att<attendeesList.length; att++) {
    if (attendeesList[att][0]==-1) continue;
    var nOpt   = new Option();
    nOpt.id    = 'natt'+attendeesList[att][0];
    nOpt.value = attendeesList[att][0];
    nOpt.text  = attendeesList[att][0]+'::'+attendeesList[att][1];
    i = alist.options.length;
    alist.options[i] = nOpt;
  }
  for (i=(alist.options.length-1); i>=0; i--) {
    alist.options[i].selected = true;
  }

}
