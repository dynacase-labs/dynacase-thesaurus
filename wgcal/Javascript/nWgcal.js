
var iTempo = 0;
function fcalDisplayInitTempo() {
  if (iTempo!=0) clearTimeout(iTempo);
  iTempo = self.setTimeout("displayInit()", 500);
}
 


var evDisplayed = -1;
var evLoaded = new Array();

// Mouse pos
var posM = { x:0, y:0 };
var Tempo = 200;
var TempoId = -1;

function fcalReloadEvents() {
  showWaitServerMessage('Loading interface');
  fcalInitEvents();
  fcalShowEvents();
  hideWaitServerMessage();
}

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
  evDisplayed = -1;
}

function fcalCancelEvDisplay(pid) {
  hideCalEvent(pid);
  evDisplayed = -1;
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
	hideWaitServerMessage();
      }
    }
  }
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_VIEWEVENT&id="+pid;
  rq.open("GET", urlsend, true);
  rq.send(null);
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

  if (!document.getElementById(o)) {
    alert('Element '+o+' not found');
    return;
  }
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
  if (document.getElementById(eid)) return;

  showWaitServerMessage('Loading event');
  var ref = document.getElementById('root');
  var nev = document.createElement('div');
  ref.appendChild(nev);
  with (nev) {
    id = 'EVTC'+pid;
    name = 'EVTC'+pid;
    innerHTML = '';
    computeDivPosition('EVTC'+pid,20);
  }
  fcalGetCalEvent(pid);
  return;  
}

function fcalShowCalEvent() {
  if (evLoaded[evDisplayed] || evDisplayed<0) return;
  evLoaded[evDisplayed] = true;
  initCalEvent(evDisplayed);
  computeDivPosition('EVTC'+evDisplayed,20);
}

function hideCalEvent(pid) {
  if (document.getElementById('EVTC'+pid)) {
    evLoaded[pid] = false;
    document.getElementById('EVTC'+pid).style.display = 'none';
    hideWaitServerMessage();
  }
}


function modifyEvent(pid) {
  alert('pid='+pid);
}

function fastEditSave(ev) {

  posM.x = getX(ev);
  posM.y = getY(ev);
  showWaitServerMessage('Saving event.');
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
  
  var rq;
  try {
    rq = new XMLHttpRequest();
  } catch (e) {
    rq = new ActiveXObject("Msxml2.XMLHTTP");
  }
  rq.open("POST", urlsend, false);
  rq.send(null);
  eval(rq.responseText);
  hideWaitServerMessage();
  if (fcalStatus.code==-1) {
    alert('Server error ['+fcalStatus.code+'] : '+fcalStatus.text);
    return false;
  }
  if (document.EventInEdition && document.EventInEdition.rg>=0) 
    Events[document.EventInEdition.rg] = newEvent;
  else
    Events[Events.length] = newEvent;
  fcalReloadEvents();
  fastEditReset();
  return;
} 

function fastEditOpenFullEdit() {
  if (fastEditChangeAlert()) {
    subwindow(400, 700, 'EditEvent', UrlRoot+'&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT&id='+document.EventInEdition.idp);
    fastEditReset();
  }
}

function fastEditReset() {
  document.EventInEdition = { rg:-1, id:-1, idp:-1, idowner:-1, titleowner:'',
			      title:'', hmode:0, start:0, end:0, 
			      category:0, note:'', location:'', 
			      confidentiality:0 };
  document.getElementById('fe_title').value ='';
  document.getElementById('fe_location').value ='';
  document.getElementById('fe_note').value = '';
  document.getElementById('fe_categories').options[0].selected = true;
  document.getElementById('fe_confidentiality').options[0].selected = true;
  document.getElementById('btnSave').style.display = 'none';
  document.getElementById('fastedit').style.display = 'none';
  fcalSetOpacity(document.getElementById('root'), 100);
}
  

function fastEditContentChanged() {
  if (document.EventInEdition) {
    var title = document.getElementById('fe_title').value;
    var loc = document.getElementById('fe_location').value;
    var note = document.getElementById('fe_note').value;
    var scat = document.getElementById('fe_categories');
    var cat = 0;
    for (var i=0; i<scat.options.length; i++) { if (scat.options[i].selected) cat = scat.options[i].value; }
    var sconf = document.getElementById('fe_confidentiality');
    var conf = 0;
    for (var i=0; i<sconf.options.length; i++) { if (sconf.options[i].selected) conf = sconf.options[i].value; }
    if (title != document.EventInEdition.title) return true;
    if (cat != document.EventInEdition.category) return true;
    if (note != document.EventInEdition.note) return true;
    if (loc != document.EventInEdition.location) return true;
    if (conf != document.EventInEdition.confidentiality) return true;
  }
  return false;
}
  
function fastEditChangeAlert() {
  if (!fastEditContentChanged()) return true;
  var ca = confirm('Abandon des modifications en cours ?');
  if (ca) fastEditReset();
  return ca;
} 

function fastEditChange(o) {
  if (document.getElementById('fe_title').value!='') document.getElementById('btnSave').style.display = '';
  else document.getElementById('btnSave').style.display = 'none';
  return true;
}

function fastEditFSave(event, o) {
  var evt = (evt) ? evt : ((event) ? event : null );
  var cc = (evt.keyCode) ? evt.keyCode : evt.charCode;
  if (cc==13) {
    fastEditSave(event);
    return false;
  }
  if (document.getElementById('fe_title').value!='') document.getElementById('btnSave').style.display = '';
  return true;
}

function fastEditCancel() {
  if (!fastEditChangeAlert()) return;
  fastEditReset();
  return true;
}


function  fcalGetJSDoc(id) {
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_DOCGETVALUES&id="+id;
  var rq;
  showWaitServerMessage('Loading event');
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
  hideWaitServerMessage();
} 


function fcalFastEditEvent(ev, id) {
  var evt = (evt) ? evt : ((ev) ? ev : null );
  if (evt.ctrlKey) {
    subwindow(400, 700, 'EditEvent', UrlRoot+'&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT&id='+id);
  } else {
    var ii = 0;
    var dv = fcalGetJSDoc(id) ;
    if (!dv) return;
    for (iev=0; iev<Events.length && ii==0; iev++) {
      if (Events[iev].idp == id) ii = iev;
    }
    document.EventInEdition = { rg:ii, id:Events[ii].id, idp:Events[ii].idp, idowner:dv.calev_ownerid, titleowner:dv.calev_owner,
				title:dv.title, hmode:dv.calev_timetype, start:Events[ii].start, end:Events[ii].end, 
				category:dv.calev_category, note:dv.calev_evnote, location:dv.calev_location, 
				confidentiality:dv.calev_visibility };
    return fastEditInit(ev, true);
  }
  return;
}

function fastEditInit(ev, init) {
  if (!init && !fastEditChangeAlert()) return;
  
  fcalSetOpacity(document.getElementById('root'), 50);

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

  var scat = document.getElementById('fe_categories');
  for (var i=0; i<scat.options.length; i++) {
    if (scat.options[i].value == document.EventInEdition.category) scat.options[i].selected = true;
  }

  var scat = document.getElementById('fe_confidentiality');
  for (var i=0; i<scat.options.length; i++) {
    if (scat.options[i].value == document.EventInEdition.confidentiality) scat.options[i].selected = true;
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


function fcalOpenMenuEvent(event, id, idp) {
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_GETMENU&id="+idp;
  var rq;
  showWaitServerMessage('Loading menu');
  try {
    rq = new XMLHttpRequest();
  } catch (e) {
    rq = new ActiveXObject("Msxml2.XMLHTTP");
  }
  rq.id = id;
  rq.open("GET", urlsend, false);
  rq.send(null);
  hideWaitServerMessage();
  eval(rq.responseText);
  if (fcalStatus.code<0) {
    alert('Server error ['+fcalStatus.code+'] : '+fcalStatus.text);
    return false;
  } else {
    var mn = new MCalMenu('m'+id, pmenu, pmstyle);
    mn.attachToElt( id, 0, 0, true, 'contextmenu', 'fcalActivateMenu', idp);
    return true;
  }
}

function fcalActivateMenu(event, mode, type, action, target, hmode, hparam) {
  alert(hparam[0]);
}

function showWaitServerMessage(msg) {
  var ws = document.getElementById('waitmessage'); 
  if (msg) document.getElementById('wmsgtext').innerHTML = msg;
  computeDivPosition('waitmessage',0);
}

function hideWaitServerMessage() {
  var ws = document.getElementById('waitmessage'); 
  ws.style.display = 'none';
}

