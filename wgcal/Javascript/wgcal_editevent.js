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

  for (idx=0; idx<attendeesList.length; idx++) {
    if (attendeesList[idx].id!=-1) {
      showtab = true;
      if (attendeesList[idx].status == 0) {
        attendeesList[idx].status = 1;
        with (document.getElementById('trsample')) {
	  nTr = cloneNode(true);
	  style.display = 'none';
        }
        nTr.id = 'tr'+attendeesList[idx].id;
        mynodereplacestr(nTr, '%RID%', attendeesList[idx].id);
        mynodereplacestr(nTr, '%RICON%', attendeesList[idx].icon);
        mynodereplacestr(nTr, '%RDESCR%', attendeesList[idx].title);
        mynodereplacestr(nTr, '%RSTATE%', attendeesList[idx].state);
	nTr.style.display = '';
        tab.appendChild(nTr);
	if (attendeesList[idx].select) 	  document.getElementById(attendeesList[idx].id).className = classSelected;
	else  document.getElementById(attendeesList[idx].id).className = classUnSelected;
        capp = document.getElementById('cp'+attendeesList[idx].id);
        capp.style.backgroundColor = attendeesList[idx].bgcolor;
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
//     document.getElementById('spall').style.visibility = 'visible';
  }  else {
    vress.style.display = 'none';
    vdispo.style.display = 'none';
    vdelall.style.display = 'none';
    document.getElementById('vnatt').style.display = 'none';
//     document.getElementById('spall').style.visibility = 'hidden';
  }
  return; 
}

function getAttendeeIdx(aid) {
  var idx = -1;
  for (i=0; i<attendeesList.length; i++) {
    if (attendeesList[i]!=null && attendeesList[i].id == aid) idx = i;
  } 
  return idx;
}
      
function SetModeRo(b) { ROMode = b; }

function addRessource(rid, rtitle, ricon, rstate, rsLabel, rsColor, rselect) {
  if (getAttendeeIdx(rid)!=-1) return;
  idx = attendeesList.length;
  attendeesList[idx] = new Object();
  attendeesList[idx].id = rid;
  attendeesList[idx].title = rtitle;
  attendeesList[idx].icon = ricon;
  attendeesList[idx].state = rstate; /* confirmation status */
  attendeesList[idx].status = 0; /* displayed status */
  attendeesList[idx].label = rsLabel;
  attendeesList[idx].bgcolor = rsColor;
  attendeesList[idx].select = rselect;
  refreshAttendees();
}

function  deleteAttendee(aid) {
  for (i=(attendeesList.length-1); i>=0; i--) {
    if (aid==-1 || aid == attendeesList[i].id) {
      eltA = document.getElementById('tr'+ attendeesList[i].id);
      if (!eltA) return;
      eltA.parentNode.deleteRow(eltA.sectionRowIndex);
      attendeesList[i].id = -1;
    }
  }
  showt = false;
  for (i=0; i<attendeesList.length; i++) {
    if (attendeesList[i].id != -1) showt = true;
  }
  var vress = document.getElementById('attlist');
  var vdispo = document.getElementById('viewplan');
  var vdelall = document.getElementById('delall');
  if (showt) {
    vress.style.display = '';
    vdispo.style.display = '';
    vdelall.style.display = '';
    document.getElementById('vnatt').style.display = '';
//     document.getElementById('spall').style.visibility = 'visible';
  } else {
    document.getElementById('bpress').checked = false;
    document.getElementById('press').style.background = '';
    document.getElementById('press').style.display = '';
    document.getElementById('bdress').checked = false;
    document.getElementById('dress').style.background = '';
    document.getElementById('dress').style.display = '';
    vress.style.display = 'none';
    vdispo.style.display = 'none';
    vdelall.style.display = 'none';
    document.getElementById('withMe').checked = true;
//     document.getElementById('spall').style.visibility = 'hidden';
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
  var refi = document.getElementById('editevent');
  if (ti.value=='') {
    ti.style.background = 'red';
    document.getElementById('errTitle').style.display='';
    return false;
  }
  if (EventSelectAll(fs)) { 
    fs.submit();
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
    list.options[i].select = true;
    sep = (n==''?'':'|');
    n += sep + list.options[i].value;
  }
  excdate.value = n;
  
  alist = document.getElementById('attendees');
  nlist = '';
  me  = document.getElementById('withMe');
  for (att=0; att<attendeesList.length; att++) {
    if (attendeesList[att].id==-1 || !attendeesList[att].select) continue;
    sep = (nlist==''?'':'|');
    nlist = nlist+sep+attendeesList[att].id;
  }
  if (nlist=='' && !me.checked) {
    document.getElementById('errAtt').style.display = '';
    return false;
  }
  alist.value = nlist;
  return true;
}


function viewattdispo(url) {

  var withme = document.getElementById('withMe');
  var me = document.getElementById('ownerid').value;
  var rvs = document.getElementById('Fstart').value;
  var js;
  var je;

  rll = "";
  for (att=0; att<attendeesList.length; att++) {
    if (attendeesList[att].id==-1 || !attendeesList[att].select) continue;
    if (rll!='') rll += '|';
    rll += attendeesList[att].id;
  }
  if (withme.checked) {
    if (rll!='') rll += '|';
    rll += me;
  }
  
  var td = new Date(rvs*1000);
  js = cal_to_jd( "CE", td.getFullYear(), td.getMonth()+1, td.getDate(), td.getHours(), td.getMinutes(), td.getSeconds() );
  je = parseFloat(js) + 14.0;
  alert('ViewDispo '+url+'&jdstart='+js+'&jdend='+je+'&idres='+rll);
  subwindow(300, 700, 'ViewDispo', url+'&jdstart='+js+'&jdend='+je+'&idres='+rll);
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
//   evs = document.getElementById('spall');
  evch.checked = (evch.checked ? "" : "checked" );
  if (evch.checked) evs.style.visibility = 'visible';
  else evs.style.visibility = 'hidden';
}
  
function setStatus(st, cst) {
  evst = document.getElementById('evstatus');
  evst.value = status;
//   document.getElementById('spall').style.border = '1px solid '+cst;
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


function ImportRessources(elt, tress) {
  var i;
  var ch = document.getElementById(elt);
  var bch = document.getElementById('b'+elt);
  if (bch.checked) {
    bch.checked = false;
  } else {
    ch.style.display = 'none';
    bch.checked = true;
    for (i=0; i<tress.length; i++) {
       addRessource(tress[i][0], tress[i][1], tress[i][2], '0', 'nouveau', 'red', true);
    }
  }
}

var classSelected = '';
var classUnSelected = '';
function SetRessDeco(cSel, cUSel) {
  classSelected = cSel;
  classUnSelected = cUSel;
}

function RessourceSelect(idr) {
  var idx = -1;

  idx = getAttendeeIdx(idr);
  if (idx==-1) return;
  attendeesList[idx].select = ( attendeesList[idx].select ? false : true );
  relt = document.getElementById(idr);
  if (attendeesList[idx].select) {
    document.getElementById(attendeesList[idx].id).className = classSelected;
  } else {
    document.getElementById(attendeesList[idx].id).className = classUnSelected;
  }
}


function ViewRessourceHelper(idh, url) {
  var pst = document.getElementById(idh).style.display; 
  if (pst=='') {
    document.getElementById(idh).style.display = 'none';
  } else { 
    parent.wgcal_addatt.location.href=url; 
    document.getElementById(idh).style.display  = ''; 
  }
}
  

function SearchIUser(iuser) {
  if (iuser=='') return;
  var fvl = document.getElementById('fgetiuser');
  var vl = document.getElementById('iusertext');
  vl.value = iuser;
  fvl.submit();
}
  
