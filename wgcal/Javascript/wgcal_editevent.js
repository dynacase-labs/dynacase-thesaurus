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
      default:
	capp.style.background = 'orange';
      }
    }
  }
  return; 
}

function getAttendeeIdx(aid) {
  var idx = -1;
  for (i=0; i<attendeesList.length && idx==-1; i++) {
    if (attendeesList[i][0] == aid) idx = i;
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
  idx = getAttendeeIdx(aid);
  if (idx == -1) return;
  eltA = document.getElementById('tr'+aid);
  if (!eltA) return;
  eltA.parentNode.deleteRow(eltA.sectionRowIndex);
  attendeesList[idx] = -1;
}


var attpicker;
function attkillwins() {
  if (attpicker != null) attpicker.close();
}

function saveEvent() {
  document.getElementById('editevent').submit();
  //displayEvent(document.getElementById('eventid').value;	
  //self.close();
}

function cancelEvent() {
  ok = confirm('[TEXT: close whithout saving]'); 
  if (ok) self.close();
}

