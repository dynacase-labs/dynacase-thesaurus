/*
 **
 **
 **
*/
/*
 * Global mode 
 */
var ROMode = false;

function swstate(s) {
  if (s==1) return 0;
  else return 1;
}


function ShowDate(ts) {
  var d = new Date((parseInt(ts) * 1000));
  t = d.getHours()+':'+d.getMinutes()+' '+d.getDate()+'/'+parseInt(d.getMonth()+1)+'/'+d.getFullYear();
  return 'TS : '+ts+' : '+t;
}

function ComputeTime(id) {

  document.getElementById('tUstart').style.display = 'none';
  document.getElementById('tUend').style.display = 'none';

  var textTime = document.getElementById('T'+id);
  var daysTime = document.getElementById('D'+id);
  var hourTime = document.getElementById('H'+id);
  htval = hourTime.options[hourTime.selectedIndex].value;
  var minuTime = document.getElementById('M'+id);
  mtval = minuTime.options[minuTime.selectedIndex].value;
  var fullTime = document.getElementById('F'+id);

  ftime = parseInt(daysTime.value) + (parseInt(htval) * 3600) + (parseInt(mtval) * 60);
  fullTime.value = ftime;
 
  CheckIfUpdate(id, true);

  return;
}

function CheckIfUpdate(id, dalert) {

  var start = document.getElementById('Fstart');
  var end = document.getElementById('Fend');
  if (id=='start') idn = 'end';
  else idn = 'start';
  var textTime = document.getElementById('T'+idn);
  var daysTime = document.getElementById('D'+idn);
  var hourTime = document.getElementById('H'+idn);
  var minuTime = document.getElementById('M'+idn);
  var fullTime = document.getElementById('F'+idn);

  if (parseInt(start.value)<=parseInt(end.value)) return true;

  if (id=='start') uTime = parseInt(start.value) + 3600;
  else uTime = parseInt(end.value) - 3600;
    
  var d = new Date(uTime*1000);

  minuTime.value = d.getMinutes();
  hourTime.value = d.getHours();
  fullTime.value = uTime;
  var ctd = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0, 0);
  daysTime.value = d.getSeconds();
  textTime.innerHTML = Calendar._DN[ctd.getDay()]+' '+ctd.getDate()+' '+Calendar._SMN[ctd.getMonth()]+' '+ctd.getFullYear();
  
  if (dalert) {
    var malert = document.getElementById('tU'+idn);
    malert.style.display='';
  }
}

function ChangeAlarm() {
  if (ROMode) return;
  chk = document.getElementById('AlarmCheck');
  alrm = document.getElementById('AlarmVis');
  chk.checked =  (chk.checked ? "" : "checked" );
  if (chk.checked) alrm.style.visibility = 'visible';
  else alrm.style.visibility = 'hidden';
}



function ChangeNoHour() {

  nohour = document.getElementById('nohour');
  allday = document.getElementById('allday');
  tallday = document.getElementById('tallday');
  hstart = document.getElementById('start_hour');
  hend1 = document.getElementById('end_hour1');
  hend2 = document.getElementById('end_hour2');
  hend3 = document.getElementById('end_hour3');

  if (nohour.checked) {
    allday.checked = false;
    tallday.style.visibility = 'hidden';
    hend1.style.visibility = 'hidden';
    hend2.style.visibility = 'hidden';
    hend3.style.visibility = 'hidden';
    hstart.style.visibility = 'hidden';
  } else {
    tallday.style.visibility = 'visible';
    hend1.style.visibility = 'visible';
    hend2.style.visibility = 'visible';
    hend3.style.visibility = 'visible';
    hstart.style.visibility = 'visible';
  }
  return;
}
     
function SetSelectedItem(from, to) {
   var f = document.getElementById(from);
   var to = document.getElementById(to);
   for (i=0; i<f.options.length; i++) {
    if (f.options[i].selected) to.value = f.options[i].value;
   }
}

function ChangeAllDay() {

  nohour = document.getElementById('nohour');
  tnohour = document.getElementById('tnohour');
  allday = document.getElementById('allday');
  hstart = document.getElementById('start_hour');
  hend1 = document.getElementById('end_hour1');
  hend2 = document.getElementById('end_hour2');
  hend3 = document.getElementById('end_hour3');

  if (allday.checked) {
    nohour.checked = false;
    tnohour.style.visibility = 'hidden';
    hend1.style.visibility = 'hidden';
    hend2.style.visibility = 'hidden';
    hend3.style.visibility = 'hidden';
    hstart.style.visibility = 'hidden';
  } else {
    tnohour.style.visibility = 'visible';
    hend1.style.visibility = 'visible';
    hend2.style.visibility = 'visible';
    hend3.style.visibility = 'visible';
    hstart.style.visibility = 'visible';
  }
  return;
}
     

function SwitchZone(view) {

  var zones = new Array ( 'evmainzone', 'evrepeatzone');

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
  var vress = document.getElementById('attlist');
  var vdispo = document.getElementById('viewplan');
  var vdelall = document.getElementById('delall');

  var showtab = false;

  for (idx in attendeesList) {
    if (attendeesList[idx]!=-1) {
      showtab = true;
      if (attendeesList[idx][4] == 0) {
        attendeesList[idx][4] = 1;
        with (document.getElementById('trsample')) {
	  nTr = cloneNode(true);
	  style.display = 'none';
        }
        nTr.id = 'tr'+attendeesList[idx][0];
        mynodereplacestr(nTr, '%RID%', attendeesList[idx][0]);
        mynodereplacestr(nTr, '%RICON%', attendeesList[idx][2]);
        mynodereplacestr(nTr, '%RDESCR%', attendeesList[idx][1]);
        mynodereplacestr(nTr, '%RSTATE%', attendeesList[idx][5]);
	nTr.style.display = '';
        tab.appendChild(nTr);
        capp = document.getElementById('cp'+attendeesList[idx][0]);
        capp.style.backgroundColor = attendeesList[idx][6];
      }
    }
  }
  if (showtab) {
    vress.style.display = '';
    if (!ROMode) {
      vdispo.style.display = '';
      vdelall.style.display = '';
    }
    document.getElementById('vnatt').style.display = '';
    document.getElementById('spall').style.visibility = 'visible';
  }  else {
    vress.style.display = 'none';
    vdispo.style.display = 'none';
    vdelall.style.display = 'none';
    document.getElementById('vnatt').style.display = 'none';
    document.getElementById('spall').style.visibility = 'hidden';
  }
  return; 
}

function getAttendeeIdx(aid) {
  var idx = -1;
  for (i=0; i<attendeesList.length; i++) {
    if (attendeesList[i]!=-1 && attendeesList[i][0] == aid) idx = i;
  } 
  return idx;
}
      
function SetModeRo(b) { ROMode = b; }

function addRessource(rid, rtitle, ricon, rstate, rsLabel, rsColor) {
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
  attendeesList[idx][5] = rsLabel;
  attendeesList[idx][6] = rsColor;
  refreshAttendees();
}

function  deleteAttendee(aid) {
  for (i=(attendeesList.length-1); i>=0; i--) {
    if (aid==-1 || aid == attendeesList[i][0]) {
      eltA = document.getElementById('tr'+ attendeesList[i][0]);
      if (!eltA) return;
      eltA.parentNode.deleteRow(eltA.sectionRowIndex);
      attendeesList[i] = -1;
    }
  }
  showt = false;
  for (i=0; i<attendeesList.length; i++) {
    if (attendeesList[i] != -1) showt = true;
  }
  var vress = document.getElementById('attlist');
  var vdispo = document.getElementById('viewplan');
  var vdelall = document.getElementById('delall');
  if (showt) {
    vress.style.display = '';
    vdispo.style.display = '';
    vdelall.style.display = '';
    document.getElementById('vnatt').style.display = '';
    document.getElementById('spall').style.visibility = 'visible';
  } else {
    vress.style.display = 'none';
    vdispo.style.display = 'none';
    vdelall.style.display = 'none';
    document.getElementById('withMe').checked = true;
    document.getElementById('spall').style.visibility = 'hidden';
    document.getElementById('vnatt').style.display = 'none';
  }
}


var attpicker;
function attkillwins() {
  if (attpicker != null) attpicker.close();
}

function saveEvent() {
  var fs = document.getElementById('editevent');
  var ti = document.getElementById('rvtitle');
  if (ti.value=='') {
    ti.style.background = 'red';
    document.getElementById('errTitle').style.display='';
    return false;
  }
  if (EventSelectAll(fs)) { 
    fs.submit();
    document.getElementById('wgcal_conflict').style.display='';
//     self.close();
  }
  return false;
}

function cancelEvent(text) {
  ok = confirm(text); 
  if (ok) self.close();
}

function deleteEvent(text) {
  ok = confirm(text); 
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


function EventSelectAll(f) {

  var list = document.getElementById('listexcldate');
  var excdate = document.getElementById('excludedate');
  var n = "";
  for (i=(list.options.length-1); i>=0; i--) {
    list.options[i].selected = true;
    sep = (n==''?'':'|');
    n += sep + list.options[i].value;
  }
  excdate.value = n;
  
  alist = document.getElementById('attendees');
  nlist = '';
  me  = document.getElementById('withMe');
  for (att=0; att<attendeesList.length; att++) {
    if (attendeesList[att]==-1) continue;
    sep = (nlist==''?'':'|');
    nlist = nlist+sep+attendeesList[att][0];
  }
  if (nlist=='' && !me.checked) {
    document.getElementById('errAtt').style.display = '';
    return false;
  }
  alist.value = nlist;
  return true;
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
    if (attendeesList[att]==-1) continue;
    rll += attendeesList[att][0] + '|';
  }
  rl.value = rll;
  
  var td = new Date(rvs*1000);
  js.value = cal_to_jd( "CE", td.getFullYear(), td.getMonth()+1, td.getDate(), td.getHours(), td.getMinutes(), td.getSeconds() );
  je.value = parseFloat(js.value) + 14.0;
  f.submit();
}

function clickB2(idb) {
  var eb = document.getElementById(idb);
  if (!eb) return false;
  eb.checked = (eb.checked ? "" : "checked" );
  return true;
}
function clickB(idb) {
  if (!ROMode) {
    return clickB2(idb);
  }
  return false;
}

function ShowHideStatus() {
  if (ROMode) return;
  evch = document.getElementById('withMe');
  evs = document.getElementById('spall');
  evch.checked = (evch.checked ? "" : "checked" );
  if (evch.checked) evs.style.visibility = 'visible';
  else evs.style.visibility = 'hidden';
}
  
function setStatus(st, cst) {
  evst = document.getElementById('evstatus');
  evst.value = status;
  document.getElementById('spall').style.border = '1px solid '+cst;
}

function ViewGroup(ev,st) {
  var ge = document.getElementById('grp'+ev.id);
  if (!ge) return;
  ge.style.display = (st?"":"none"); 
  if (st) {
    ww = getFrameWidth();
    wh = getFrameHeight();
    ge.style.position = 'absolute';
    ge.style.width = 'auto';
    ge.style.height = 'auto';
    ge.style.left = (ww/2)+'px';
    ge.style.top = (wh/2)+'px';
  }
  return;
}
