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
  var d = new Date();
  d.setTime((parseInt(ts) * 1000));
  t = d.getUTCHours()+':'+d.getUTCMinutes()+' '+d.getUTCDate()+'/'+parseInt(d.getUTCMonth()+1)+'/'+d.getUTCFullYear();
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

  oldtime = fullTime.value;
  ftime = parseInt(daysTime.value) + (parseInt(htval) * 3600) + (parseInt(mtval) * 60);
  fullTime.value = ftime;
 
  CheckIfUpdate(id, true);

  return;
}


function showtimes() {
  var start = document.getElementById('Fstart').value;
  var end = document.getElementById('Fend').value;
  alert('start='+start+' end='+end);
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

  if (dalert) {
    var malert = document.getElementById('tUtime');
    malert.style.display='';
  }
  return false;

//   var d = new Date();
//   if (id=='start') {
//     uTime = parseInt(start.value);
//     d.setTime((uTime+3600)*1000);
//   } else {
//     uTime = parseInt(end.value);
//     d.setTime((uTime-3600)*1000);
//   }
    

//   minuTime.value = d.getUTCMinutes();
//   hourTime.value = d.getUTCHours();
//   fullTime.value = d.getTime();
//   var ctd = new Date(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate(), 0, 0, 0, 0);
//   daysTime.value = d.getUTCSeconds();
//   textTime.innerHTML = Calendar._DN[ctd.getUTCDay()]+' '+ctd.getUTCDate()+' '+Calendar._SMN[ctd.getUTCMonth()]+' '+ctd.getUTCFullYear();
  
//   if (dalert) {
//     var malert = document.getElementById('tU'+idn);
//     malert.style.display='';
//   }
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

  var nohour = document.getElementById('nohour');
  var allday = document.getElementById('allday');
  var tallday = document.getElementById('tallday');
  var hstart = document.getElementById('start_hour');
  var hend1 = document.getElementById('end_hour1');
  var hend2 = document.getElementById('end_hour2');
  var hend3 = document.getElementById('end_hour3');

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
     if (f.options[i].selected) {
       to.value = f.options[i].value;
     }
   }
   if (from=='rvcalendar') {
     if (to.value>0) HideShowAtt(false);
     else HideShowAtt(true);
   }
}

function HideShowAtt(show) {
  if (!show) {
    deleteAttendee(-1);
    document.getElementById('fullattendees').style.display = 'none';
  } else {
    document.getElementById('fullattendees').style.display = '';
    showt = false;
    for (i=0; i<attendeesList.length; i++) {
      if (attendeesList[i].id != -1) showt = true;
    }
    if (showt) {
      document.getElementById('tabress').style.display = '';
      document.getElementById('viewplan').style.display = '';
      document.getElementById('delall').style.display = '';
    }
  }
}

function ChangeAllDay() {

  var nohour = document.getElementById('nohour');
  var tnohour = document.getElementById('tnohour');
  var allday = document.getElementById('allday');
  var hstart = document.getElementById('start_hour');
  var hend1 = document.getElementById('end_hour1');
  var hend2 = document.getElementById('end_hour2');
  var hend3 = document.getElementById('end_hour3');

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

  var zones = new Array ( 'evattendees', 'evrepeatzone');

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
  var vress = document.getElementById('tabress');
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
  }  else {
    vress.style.display = 'none';
    vdispo.style.display = 'none';
    vdelall.style.display = 'none';
    document.getElementById('vnatt').style.display = 'none';
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
  var idx = attendeesList.length;
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
  var i = 0;

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
  var vress = document.getElementById('tabress');
  var viewplan = document.getElementById('viewplan');
  var delall = document.getElementById('delall');
 if (showt) {
    vress.style.display = '';
    viewplan.style.display = '';
    delall.style.display = '';
  } else {
    vress.style.display = 'none';
    viewplan.style.display = 'none';
    delall.style.display = 'none';
    document.getElementById('withMe').checked = true;
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
//     showtimes()
    if (CheckIfUpdate('start', true)) fs.submit();
  }
  return false;
}

function GetTitle(evt) {
  evt = (evt) ? evt : ((event) ? event : null );
  var cc = (evt.keyCode) ? evt.keyCode : evt.charCode;
  var ftitle = document.getElementById('rvtitle');
  if ((cc == 13)  && (ftitle.value != "")) {
    saveEvent();
    return false;
  }
  return true;
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

  var jdate = new Date();
  jdate.setTime(ndate*1000);
  var y = jdate.getUTCFullYear();
  var m = jdate.getUTCMonth();     // integer, 0..11
  var d = jdate.getUTCDate();
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
  
  var td = new Date();
  td.setTime(rvs*1000);
  js = cal_to_jd( "CE", td.getFullYear(), td.getMonth()+1, td.getDate(), td.getHours(), td.getMinutes(), td.getSeconds() );
  je = parseFloat(js) + 14.0;
//   alert('ViewDispo '+url+'&jdstart='+js+'&jdend='+je+'&idres='+rll);
  subwindow(300, 700, 'ViewDispo', url+'&jdstart='+js+'&jdend='+je+'&idres='+rll);
}

function clickB(idb) {
  var eb = document.getElementById(idb);
  if (!eb) return false;
  eb.checked = (eb.checked ? "" : "checked" );
  return true;
}

function ShowHideStatus() {
  if (ROMode) return;
  evch = document.getElementById('withMe');
  evch.checked = (evch.checked ? "" : "checked" );
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
  for (i=0; i<tress.length; i++) {
    addRessource(tress[i][0], tress[i][1], tress[i][2], '0', 'nouveau', 'red', true);
  }
}

var classSelected = 'WGCRessSelected';
var classUnSelected = 'WGCRessDefault';

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


function ViewRessourceHelper(url) {
  var pst = document.getElementById('_addatt'); 
  if (pst.style.display=='none') {
    parent.wgcal_addatt.location.href=url; 
    pst.style.display  = ''; 
  } else {
    pst.style.display  = 'none';
  }		
}
  

function SearchIUser(evt) {
  evt = (evt) ? evt : ((event) ? event : null );
  var cc = (evt.keyCode) ? evt.keyCode : evt.charCode;
  var nitem = document.getElementById('stmptext');
  if ((cc == 13)  && (nitem.value != "")) {
    var fvl = document.getElementById('fgetiuser');
    var val = document.getElementById('iusertext');
    val.value = nitem.value;
    fvl.submit();
    nitem.value = '';
    return false;
  }
  return true;
}
  
