/*
 **
 **
 **
*/

function swstate(s) {
  if (s==1) return 0;
  else return 1;
}

function UpdateEndTime() {
  var stime = document.getElementById('rvstart');
  var etime = document.getElementById('rvend');
  var letime = document.getElementById('Trvend');
  if (etime.value<stime.value) {
    et = stime.value*1; 
    etime.value = et + 3600;
    d = new Date((etime.value * 1000));//Samedi 29 Jan 2005
    letime.innerHTML = Calendar._DN[d.getDay()]+' '+d.getDate()+' '+Calendar._SMN[d.getMonth()]+' '+d.getFullYear();
  }
}

function ChangeAlarm(state) {
  chk = document.getElementById('AlarmCheck');
  alrm = document.getElementById('AlarmVis');
  if (state!=-1) chk.checked = (state==0?false:true);
  if (chk.checked) alrm.style.display = '';
  else alrm.style.display = 'none';
}



function ChangeNoHour(init, cstate) {

  nohour = document.getElementById('nohour');
  allday = document.getElementById('allday');
  tallday = document.getElementById('tallday');
  hstart = document.getElementById('start_hour');
  hend = document.getElementById('end_hour');

  if (!init) cstate =  (nohour.checked ? 1 : 0 );
  if (cstate == 1) {
    nohour.checked = true; 
    allday.checked = false;
    tallday.style.visibility = 'hidden';
    hend.style.visibility = 'hidden';
    hstart.style.visibility = 'hidden';
  } else {
    nohour.checked = false;
    tallday.style.visibility = 'visible';
    hend.style.visibility = 'visible';
    hstart.style.visibility = 'visible';
  }
  return;
}
     
function ChangeAllDay(init, cstate) {

  nohour = document.getElementById('nohour');
  tnohour = document.getElementById('tnohour');
  allday = document.getElementById('allday');
  hstart = document.getElementById('start_hour');
  hend = document.getElementById('end_hour');

  if (!init) cstate =  (allday.checked ? 1 : 0 );
  if (cstate == 1) {
    allday.checked = true; 
    nohour.checked = false;
    tnohour.style.visibility = 'hidden';
    hend.style.visibility = 'hidden';
    hstart.style.visibility = 'hidden';
  } else {
    allday.checked = false;
    tnohour.style.visibility = 'visible';
    hend.style.visibility = 'visible';
    hstart.style.visibility = 'visible';
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
      if (attendeesList[idx][3] == 1) scolor = 'green';
      else if (attendeesList[idx][3] == 2) scolor = 'red';
      else if (attendeesList[idx][3] == 3) scolor = 'orange';
      else scolor = 'yellow';
      nTr.style.display = '';
      tab.appendChild(nTr);
      capp = document.getElementById('cp'+attendeesList[idx][0]);
      capp.style.backgroundColor = scolor;
   }
  }
  return; 
}

function getAttendeeIdx(aid) {
  var idx = -1;
  for (i=0; i<attendeesList.length; i++) {
    if (attendeesList[i][0] == aid) idx = i;
  } 
  return idx;
}
      
function addRessource(rid, rtitle, ricon, rstate) {
  if (getAttendeeIdx(rid)!=-1) {
    return;
  }
  idx = attendeesList.length;
  attendeesList[idx] = new Array();
  attendeesList[idx][0] = rid;
  attendeesList[idx][1] = rtitle;
  attendeesList[idx][2] = ricon;
  attendeesList[idx][3] = rstate; /* confirmation status */
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
  EventSetElement('rvstatus','evstatus');
  EventSetElement('rvcalendar','evcalendar');
  EventSetElement('rvconfid', 'evconfidentiality');
  fs.submit();
  fs.reset();
  self.close();
}

function cancelEvent() {
  ok = confirm('[TEXT: save before closing]'); 
  if (ok) saveEvent();
  self.close();
}

function deleteEvent() {
  ok = confirm('[TEXT: delete this event]'); 
  if (!ok) return;
  var fs = document.getElementById('deleteevent');
  fs.submit();
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

  if (checkone==2) document.getElementById('d_rweekday').style.display = '';
  if (checkone==3) document.getElementById('d_rmonth').style.display = '';

}

function EventSetElement(arrElt, uElt) {
  evst = document.getElementById(uElt);
  evst.value = -1;
  aevst = document.getElementById(arrElt);
  for (ist=0; ist<aevst.length; ist++) {
    if (aevst[ist].selected)  evst.value = ist;
  }
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


function viewattdispo() {

  var f = document.getElementById('viewdispo');
  var rl = document.getElementById('idres');
  var rvs = document.getElementById('rvstart').value;
  var js = document.getElementById('jdstart');
  var rve = document.getElementById('rvend').value;
  var je = document.getElementById('jdend');

  rll = "";
  for (att=0; att<attendeesList.length; att++) {
    if (attendeesList[att][0]==-1) continue;
    rll += attendeesList[att][0] + '|';
  }
  rl.value = rll;
  
  var td = new Date(rvs*1000);
  js.value = cal_to_jd( "CE", td.getFullYear(), td.getMonth(), td.getDate(), td.getHours(), td.getMinutes(), td.getSeconds() );
  var te = new Date(rve*1000);
  je.value = cal_to_jd( "CE", te.getFullYear(), te.getMonth(), te.getDate(), te.getHours(), te.getMinutes(), te.getSeconds() );
  alert(' sdate=['+js.value+']');
  alert(' edate=['+je.value+']');
  f.submit();
}
