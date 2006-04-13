var evDisplayed = 0;
var evLoaded = new Array();

// Mouse pos
var posM = { x:0, y:0 };
var Tempo = 200;
var TempoId = -1;

function fcalStartEvDisplay(ev, pid) {
  if (evDisplayed==pid) return;
  fcalResetTempo();
  posM.x = getX(ev);
  posM.y = getY(ev);
  evDisplayed = pid;
  TempoId = self.setTimeout("fcalShowCalEvent()", AltTimerValue);
  return;
}

function fcalResetTempo() {
  if (TempoId>-1) clearTimeout(TempoId);
  TempoId = -1;
  evDisplayed = 0;
}

function fcalCancelEvDisplay(pid) {
  hideCalEvent(pid);
  evDisplayed = 0;
  posM.x = 0;
  posM.y = 0;
}



function fcalGetCalEvent(pid) {

  evLoaded[pid] = true;
  var rq;
  try {
    rq = new XMLHttpRequest();
  } catch (e) {
    rq = new ActiveXObject("Msxml2.XMLHTTP");
  }
  rq.evId = pid;
  rq.onreadystatechange =  function() {
    if (rq.readyState == 4) {
      if (rq.responseText && rq.status==200) {
	addCalEvContent(rq.evId, rq.responseText);
      }
    }
  }
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_VIEWEVENT&id="+pid;
  rq.open("GET", urlsend, true);
  rq.send(null);
}

var iif = 0;
function calInfo(t) {
  if (document.getElementById('textinfo')) {
    iif++;
    var nev = document.createElement('div');
    nev.innerHTML = '['+iif+'] '+t;
    document.getElementById('textinfo').appendChild(nev);
  }
}

function addCalEvContent(pid, html) {
  if (!document.getElementById('EVTC'+pid)) return;
  var eid = document.getElementById('EVTC'+pid);
  eid.innerHTML = html;
  if (evDisplayed!=pid) return;
  computeDivPosition('EVTC'+pid,20);
  return;
}

function computeDivPosition(o,delta) {

  if (!document.getElementById(o)) return;
  var eid = document.getElementById(o);

  eid.style.display = 'block';
  eid.style.position = 'absolute';

  var ww = getFrameWidth();
  var wh = getFrameHeight();
  var h = getObjectHeight(eid);
  var w = getObjectWidth(eid);
  var w1 = posM.x;
  var w2 = ww - posM.x;
  var h1 = posM.y;
  var h2 = wh - posM.y;
  
  var xp = yp = 0;
   if (w < (w2+delta)) xp =  posM.x + delta;
  else if (w < (w1+delta)) xp = posM.x - delta - w;
  else xp = delta;

  if (h < (h2+delta)) yp = posM.y + delta;
  else if (h < (h1+delta)) yp = posM.y - delta - h;
  else yp = delta;

  eid.style.left = parseInt(xp)+'px';
  eid.style.top = parseInt(yp)+'px';

  return;
}
 
  
function initCalEvent(pid) {
  var eid = 'EVTC'+pid;
  if (document.getElementById(eid)) {
    return;
  }
  var ref = document.getElementById('root');
  var nev = document.createElement('div');
  ref.appendChild(nev);
  with (nev) {
    id = 'EVTC'+pid;
    name = 'EVTC'+pid;
    innerHTML = document.getElementById('waitmessage').innerHTML; 
    computeDivPosition('EVTC'+pid,20);
  }
  fcalGetCalEvent(pid);
  return;  
}

function fcalShowCalEvent() {
  if (evLoaded[evDisplayed]) return;
  evLoaded[evDisplayed] = true;
  initCalEvent(evDisplayed);
  computeDivPosition('EVTC'+evDisplayed,20);
}

function hideCalEvent(pid) {
  if (document.getElementById('EVTC'+pid)) {
    evLoaded[pid] = false;
    document.getElementById('EVTC'+pid).style.display = 'none';
  }
}


function modifyEvent(pid) {
  alert('pid='+pid);
}

  var feChange = false;
var feHour = 0; // No hour / alla day ? 
var feStart = 0; // Start time
var feEnd = 0;   // End time
var fePid = 0;   // Event producter Id
var feId = 0;    // Event Id


function fastEditSave() {

  var feTitle = document.getElementById('fe_title').value;
  var loc = document.getElementById('fe_location').value;
  var note = document.getElementById('fe_note').value;
  var scat = document.getElementById('fe_categories');
  var cat = 0;
  for (var i=0; i<scat.options.length; i++) { if (scat.options[i].selected) cat = scat.options[i].value; }
  var sconf = document.getElementById('fe_confidentiality');
  var conf = 0;
  for (var i=0; i<sconf.options.length; i++) { if (sconf.options[i].selected) conf = sconf.options[i].value; }
  
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_SAVEEVENT";
  urlsend += "&id="+document.EventInEdition.idp;
  urlsend += "&oi="+document.EventInEdition.idowner;
  urlsend += "&ti="+feTitle;
  urlsend += "&nh="+document.EventInEdition.hmode;
  urlsend += "&ts="+document.EventInEdition.start;
  urlsend += "&te="+document.EventInEdition.end;
  urlsend += "&ca="+cat;
  urlsend += "&co="+conf;
  urlsend += "&lo="+loc;
  urlsend += "&no="+note;
  
  calInfo(urlsend);
  var rq;
  try {
    rq = new XMLHttpRequest();
  } catch (e) {
    rq = new ActiveXObject("Msxml2.XMLHTTP");
  }
  rq.open("POST", urlsend, false);
  rq.send(null);
  eval(rq.responseText);
  if (fcalStatus.code==-1) {
    alert('Server error ['+fcalStatus.code+'] : '+fcalStatus.text);
    return false;
  }
  fastEditReset();
  return;
} 

function fastEditOpenFullEdit() {
  
  editevent(document.getElementById('fe_title').value,'',feHour,feStart,feEnd);
  fastEditReset();
 }

function fastEditReset() {
  document.EventInEdition = { id:-1, idp:-1, idowner:-1, titleowner:'',
			      title:'', hmode:0, start:0, end:0, 
			      category:0, note:'', location:'', 
			      confidentiality:0 };
  document.getElementById('btnSave').style.display = 'none';
  var fedit = document.getElementById('fastedit');
  fedit.style.display = 'none';
}
  
  
function fastEditChangeAlert() {
  if (!feChange) return true;
  var ca = confirm('Abandon des modifications en cours ?');
  if (ca) fastEditReset();
  return ca;
} 

function fastEditChange(o) {
  feChange = true;
  if (o.id=='fe_title' && document.getElementById('fe_title').value!='') {
    document.getElementById('btnSave').style.display = '';
  }
  if (document.getElementById('fe_title').value=='') document.getElementById('btnSave').style.display = 'none';
  
}

function fastEditCancel() {
  if (!fastEditChangeAlert()) return;
  var fedit = document.getElementById('fastedit');
  fedit.style.display = 'none';
}


function  fcalGetJSDoc(id) {
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_DOCGETVALUES&id="+id;
  var rq;
  try {
    rq = new XMLHttpRequest();
  } catch (e) {
    rq = new ActiveXObject("Msxml2.XMLHTTP");
  }
  rq.id = id;
  rq.open("GET", urlsend, false);
  rq.send(null);
  eval(rq.responseText);
  if (fcalStatus.code==-1) {
    alert('Server error ['+fcalStatus.code+'] : '+fcalStatus.text);
    return false;
  } else {
    return docValues;
  }
} 


function fcalFastEditEvent(ev, id) {
  var ii = 0;
  var dv = fcalGetJSDoc(id) ;
  if (!dv) return;
  for (iev=0; iev<Events.length && ii==0; iev++) {
    if (Events[iev].id == id) ii = iev;
  }
  document.EventInEdition = { id:Events[ii].id, idp:Events[ii].idp, idowner:dv.calev_ownerid, titleowner:dv.calev_owner,
			      title:dv.title, hmode:dv.calev_timetype, start:Events[ii].start, end:Events[ii].end, 
			      category:dv.calev_category, note:dv.calev_evnote, location:dv.calev_location, 
			      confidentiality:dv.calev_visibility };
  return fastEditInit(ev);
}

function fastEditInit(ev) {
  if (!fastEditChangeAlert()) return;

  if (document.EventInEdition.idowner==-1) {
    document.EventInEdition.idowner = parent.wgcal_toolbar.calCurrentEdit.id;
    document.EventInEdition.titleowner = parent.wgcal_toolbar.calCurrentEdit.title;
  }    
  document.getElementById('agendaowner').innerHTML = document.EventInEdition.titleowner;
  
  var fedit = document.getElementById('fastedit');
  
  document.getElementById('fe_allday').style.display = 'none'; 
  document.getElementById('fe_nohour').style.display = 'none'; 
  document.getElementById('fe_title').value = document.EventInEdition.title;
  document.getElementById('fe_location').value = document.EventInEdition.location;
  document.getElementById('fe_note').value = document.EventInEdition.note;

  if (document.EventInEdition.category>0) {
    var scat = document.getElementById('fe_categories');
    for (var i=0; i<scat.options.length; i++) {
      if (scat.options[i].value == document.EventInEdition.category) scat.options[i].selected = true;
    }
  }

  if (document.EventInEdition.confidentiality>0) {
    var scat = document.getElementById('fe_confidentiality');
    for (var i=0; i<scat.options.length; i++) {
      if (scat.options[i].value == document.EventInEdition.confidentiality) scat.options[i].selected = true;
    }
  }

  var textdate = '';
  var ds = new Date();
  ds.setTime((document.EventInEdition.start*1000) + (ds.getTimezoneOffset()*60*1000));
  textdate = ds.print('%a %d %b %Y');
  switch (document.EventInEdition.hmode) {
  case 2: 
    document.getElementById('fe_allday').style.display = 'block'; 
    break;
  case 1: 
    document.getElementById('fe_nohour').style.display = 'block'; 
    break;
  default:
    var de = new Date();
    de.setTime((document.EventInEdition.end*1000) + (de.getTimezoneOffset()*60*1000));
    textdate = ds.print('%a %d %b %Y, %H:%M - ');
    if (ds.print('%a %d %b')!=de.print('%a %d %b')) textdate+=de.print('%a %d %b %Y, %H:%M');
    else textdate+=de.print('%H:%M');
  }
  document.getElementById('fe_date').innerHTML = textdate;
  
  posM.x = getX(ev);
  posM.y = getY(ev);
  computeDivPosition('fastedit', -20);

  document.getElementById('fe_title').focus();
  if (document.getElementById('fe_title').value!='') document.getElementById('btnSave').style.display = '';
}


function fcalOpenMenuEvent(event, ie) {
  alert('Open menu '+ie);
}



