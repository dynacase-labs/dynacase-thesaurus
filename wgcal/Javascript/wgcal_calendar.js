var  Root = 'root';
var  IdStart = 0;
var  IdEnd   = 0;
var  XDays = 0;
var  Ydivision = 0;
var  YDivCount = 0;
var  YDivMinute = 0;
var  Ystart = 0;

var  PixelByMinute = 0;
var  PixelBySecond = 0;

var Wzone = 0;
var Hzone = 0;
var Hhdiv  = 0;
var Wday  = 0;
var Wevt = 0;
var Wshift = 0;

// Coordinate of the top-left corner for calendar display
var Xs = 0;
var Ys = 0;
// Coordinate of the bottom-right corner for calendar display
var Xe = 0;
var Ye = 0;

var AltCoord = new Object();

var  P_DURATION = 10;
var  P_DEB = 100;
var  P_FIN = 1;



// --------------------------------------------------------
function GetTimeInfoFromTs(ts) {
  var evd = new Date((ts*1000));
   var tinfo = new Object();
   var tinfo = new Object();
   tinfo.day = parseInt(evd.getUTCDay());
   tinfo.hours = parseInt(evd.getUTCHours());
   tinfo.minutes = parseInt(evd.getUTCMinutes());
   return tinfo;
}

// --------------------------------------------------------
function GetYForTime(ts) {
  var tinf = GetTimeInfoFromTs(ts);
  yp = (( 1 + ((tinf.hours-(Ystart)) * YDivCount)) * YDivMinute ) + tinf.minutes;
  return (yp * PixelByMinute) + Ys;
}


  
// --------------------------------------------------------
function WGCalCleanAllFullView() {
  for (var io=0; io<fcalEvents.length; io++) {
    evtc = fcalGetEvtCardName(io);
    if (eltId(evtc)) eltId(evtc).style.display = 'none';
  }
}
  

// --------------------------------------------------------
function WGCalSetDate(cc)
{ 
  if (cc.dateClicked) {
    var ts = cc.date.print("%s");
    usetparam(-1, "WGCAL_U_CALCURDATE", ts, 'wgcal_calendar', '[CORE_STANDURL]&app=WGCAL&action=WGCAL_CALENDAR');
  }
}

// ----------------------------------------------------------

var  EvSelected = -1;
var  EvCurDay = 0;

function SetCurrentEvent(id, cd) {
    EvSelected = id;
    EvCurDay = cd;
}
  


// --------------------------------------------------------
function ClickCalendarCell(event, nh,times,timee) {
  var evt = (evt) ? evt : ((event) ? event : null );
  closeMenu('calpopup');
  if (!evt.ctrlKey) {
   if (!fastEditChangeAlert()) return;
   document.EventInEdition = { id:-1, idp:-1, idowner:-1, titleowner:'',
			       title:'', hmode:nh, start:times, end:timee, 
			       category:0, note:'', location:'', 
			       confidentiality:0, rcolor:parent.wgcal_toolbar.calCurrentEdit.color};
   fastEditInit(event, true);
  }
  if (evt.ctrlKey) {
    subwindow(400, 700, 'EditEvent', UrlRoot+'&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT&id=0&nh='+nh+'&ts='+times+'&te='+timee);
  }
}

// --------------------------------------------------------
function OverCalendarCell(ev, elt, lref, cref) {
  closeMenu('calpopup');
  WGCalCleanAllFullView();
  elt.className = 'WGCAL_PeriodSelected'; 
  if (eltId(lref)) eltId(lref).className = 'WGCAL_PeriodSelected';
  if (eltId(cref)) eltId(cref).className = 'WGCAL_PeriodSelected';
}

// --------------------------------------------------------
function OutCalendarCell(ev, elt, lref, cref, cclass, hourclass, dayclass) {
  elt.className = cclass;
  if (eltId(lref)) eltId(lref).className = dayclass;
  if (eltId(cref)) eltId(cref).className = hourclass;
}

// --------------------------------------------------------
function fcalInitView(idstart, idend, xdiv, ydiv, ystart, ydivc, ydmin) {
  IdStart     = idstart;
  IdEnd       = idend;	
  XDays       = parseInt(xdiv);
  Ystart      = parseInt(ystart);
  Ydivision   = parseInt(ydiv);
  YDivCount   = parseInt(ydivc);
  YDivMinute  = parseInt(ydmin);
  fcalComputeCoord();
}




// --------------------------------------------------------
function fcalComputeCoord() {

  var gamma = 0; //0.25;
  var ida=0;

  // compute area coord left/top (Xs,Ys) right/bottom (Xe,Ye)
  var os = getAnchorPosition(IdStart);
  var hr = getObjectHeight(eltId(IdStart));
  var wref = getObjectWidth(eltId(IdStart));
  var oe = getAnchorPosition(IdEnd);
  var w = getObjectWidth(eltId(IdEnd));
  var h = getObjectHeight(eltId(IdEnd));

  var wroot = '100%';
  eltId(Root).style.width=wroot; 
  eltId('headscreen').style.width=wroot; 
  if (eltId('wgcalmenu')) eltId('wgcalmenu').style.width=wroot; 

  Xs = os.x;
  Ys = os.y;
  Xe = oe.x + w;
  Ye = oe.y + h;

  Hzone = Ye-Ys;
  Hhdiv = Hzone / Ydivision;
  Wzone = Xe-Xs;
  Wday = wref;
  Wevt = Wday;
  AltCoord.x = 15;
  AltCoord.y = Hzone - 15;
 
  PixelByMinute = (hr - gamma) / YDivMinute;

  // Set day col position
  var xt;
  var incr=0;
  for (ida=0; ida<Days.length; ida++) {
    if (Days[ida].view) {
      xt = getAnchorPosition('D'+ida+'H0');
      Days[ida].cpos = xt.x; //(Wday * incr) + Xs;
      incr++;
    }
  }
}

// --------------------------------------------------------
function WGCalChangeClass(event, id, refclass, nclass)
{
  var elt = eltId(id);
  if (!elt) return;
  if (elt.className!=refclass) elt.className = nclass;
}




// --------------------------------------------------------
function WGCalIntersect(asy,aey,bsy,bey) {
  var IsInt = false;
  if ((bsy>asy && bsy<aey)) IsInt = true;
  if ((bey>asy && bey<aey)) IsInt = true;
  if (bsy==asy && bey==aey) IsInt = true;
  return IsInt;
}


// --------------------------------------------------------
function WGCalAddEvent(nev) 
{
  var evt = new Object;
  var id;
  var cEv;
  var dd = new Date();
  var Tz = 0;
  var id;
  var dstart;
  var dend;
  var vstart;
  var vend;
  var weight;
  var mdays;

  var tstart = fcalEvents[nev].start;
  var tend  = fcalEvents[nev].end;

  if (tend<tstart) {
    t = tend;
    tend = tstart;
    tstart = t;
  }

  dstart = Math.floor((tstart - Days[0].start)/ (24*3600));
  dstart= (dstart<0 ? 0 : (dstart>=XDays ? (XDays-1) : dstart) );
  dstart = (dstart>=XDays ? (XDays-1) : dstart);
  dend = Math.floor((tend - Days[0].start) / (24*3600));
  dend = (dend<dstart ? dstart : (dend>=XDays ? (XDays-1) : dend) );
  mdays = (dstart!=dend ? true : false);
  for (id=dstart ; id<=dend; id++) {
    if (Days[id].view) {
      vstart = tstart + Tz;
      vend   = tend + Tz;
      
      if (tend==tstart) {
	vstart = Days[id].vstart;
	vend   = Days[id].vstart + (YDivMinute * 60);
      } else {
	
	// Heure de début antérieure à l'heure de début de la journée....
	if (tstart <= parseInt(Days[id].vstart - (YDivMinute * 60 / YDivCount))) {
	  vstart = parseInt(Days[id].vstart);
	  
	  // Heure de début postérieure à l'heure de fin de la journée....
	} else if (tstart>=(Days[id].vend)) {
	  vstart = Days[id].vend - (YDivMinute * 60);
	  
	} else {
	  vstart += 0;
	}
	
	
	// Heure de fin supérieure à la fin de la journée....
	if (tend>=Days[id].vend) {
	  vend = Days[id].vend;
	  
	  // Heure de fin antérieur au début de la journée....
	} else if (tend<=Days[id].vstart) {
	  vend = Days[id].vstart + (YDivMinute * 60);
	  
	} else {
	  vend += 0;
	}
      }
      
      // Add event
      cEv = Days[id].ev.length; 
      Days[id].ev[cEv] = new Object();
      Days[id].ev[cEv].n = nev;
      Days[id].ev[cEv].tstart = tstart;
      Days[id].ev[cEv].tend  = tend;
      Days[id].ev[cEv].curday = id;
      Days[id].ev[cEv].dstart = dstart;
      Days[id].ev[cEv].dend = dend;
      Days[id].ev[cEv].vstart = vstart;
      Days[id].ev[cEv].vend = vend;
      Days[id].ev[cEv].weight = ((vend - vstart) * P_DURATION) - (vstart  * P_DEB);
      Days[id].ev[cEv].mdays = mdays;
      Days[id].ev[cEv].occ = 0;
      Days[id].ev[cEv].base = -1;
      Days[id].ev[cEv].col = 0;
      Days[id].ev[cEv].ncol = 0;
    }
  }
}

function fcalGetEvtRName(ie,occ) {   return 'evt'+ie+'_'+occ; }
function fcalGetEvtSubRName(ie,occ) {   return '_'+fcalGetEvtRName(ie,occ); }
function fcalGetEvtCardName(ie) {   return 'evtc'+ie; }

function fcalRemoveEvent() {
  var r = document.getElementById('fcalDatas');
  if (r) {
    var iln = r.childNodes.length - 1;
    for (var io=iln; io>=0; io--) {
      r.removeChild(r.childNodes[io]);
    }
  }      
  for (iev=0; iev<fcalEvents.length; iev++) fcalEvents[iev].occur = 0;
  return;
}

function fcalInitEvents() {
  var iev;
  for (iday=0; iday<XDays; iday++) {
    if (Days[iday].view) Days[iday].ev = new Array();
  }
  fcalRemoveEvent();
  for (iev=0; iev<fcalEvents.length; iev++) {
    WGCalAddEvent(iev);
  }
}

function fcalShowEvents() {
  var iday; 
  for (iday=0; iday<XDays; iday++) {
    if (Days[iday].view) WGCalDisplayDailyEvents(Days[iday].ev);
  }
}
  
function WGCalDisplayAllEvents() {
  var iday; 
  var iev;
  for (iev=0; iev<fcalEvents.length; iev++) {
    WGCalAddEvent(iev);
  }
  for (iday=0; iday<XDays; iday++) {
    if (Days[iday].view) WGCalDisplayDailyEvents(Days[iday].ev);
  }
}


function WGCalDisplayDailyEvents(dEv) {

  var s = '';
  var it;
  
  // Order day events according the weight
  var evts = new Array();
  evts = dEv.sort(WGCalSortByWeight);

  // Compute column to place events
  col = 0;
  base = 0;
  s = '';
  for (ie=0; ie<evts.length; ie++) {
    if (ie==0) {
      col = 1; 
      base = evts[ie].vstart;
    } else {
      iprev = -1;
      for (ic=(ie-1); ic>=0 && iprev==-1; ic--) {
	if (WGCalIntersect(evts[ic].vstart, evts[ic].vend, evts[ie].vstart, evts[ie].vend)) {
	  iprev = ic;
	}
      }
      if (iprev!=-1) {
	col = evts[iprev].col + 1;
	base = evts[iprev].base;
      } else {
	col = 1;
	base = evts[ie].vstart;
      }
    }
    evts[ie].base = base;
    evts[ie].col = col;
    evts[ie].rwidth = 1;
  }
  
  // Optimize event width according column count
  s='';
  for (it=0; it<evts.length; it++) {
    ncol = WGCalGetColForBase(evts, evts[it].base);
    haveR = WGCalGetRightEvForBase(it, evts, evts[it].base);
     if (haveR) {
       evts[it].rwidth = 1 / ncol;
     } else {
       evts[it].rwidth = (ncol - (evts[it].col - 1) ) / ncol;
     }
    WGCalDisplayEvent(evts[it], ncol);
  }
}

function WGCalEmptyColBase(evts, base, ic) {
  var ix;
  for (ix=0; ix<evts.length; ix++) {
    if (evts[ix].base==base && WGCalIntersect(evts[ic].vstart, evts[ic].vend, evts[ix].vstart, evts[ix].vend)) return false;
  }
  return true;
}

function WGCalCountColsForBase(icur, iprev, evts,  b) {
  var icol = 0;
  var ix;
  for (ix=0; ix<iprev; ix++) {
    if (evts[ix].base==b && WGCalIntersect(evts[icur].vstart, evts[icur].vend, evts[ix].vstart, evts[ix].vend)) icol++;
  }
  return icol;
}

function WGCalGetColForBase(evts, b) {
  var ncol = 0;
  var ix;
  for (ix=0; ix<evts.length; ix++) {
    if (evts[ix].base==b) ncol++;
  }
  return ncol;
}

function WGCalGetRightEvForBase(first, evts, b) {
  var ix;
  if (first+1>=evts.length) return false;
  for (ix=first+1; ix<evts.length; ix++) {
    if (evts[ix].base==b && WGCalIntersect(evts[first].vstart, evts[first].vend, evts[ix].vstart, evts[ix].vend)) return true;
  }
  return false;
}

function WGCalSortByWeight(e1, e2) {
  return e2.weight - e1.weight;
}

function WGCalDisplayEvent(cEv, ncol) {

  var  root = eltId('fcalDatas');
  
  foot = 0;
  head = 3;
  cWidth = Wday - (2*head);
  
  startX = Days[cEv.curday].cpos; 
  startY = GetYForTime(cEv.vstart);
  endY = GetYForTime(cEv.vend);

  h = endY - startY;
  var eE;
  var eE2;
  var etmp;
  if (!cEv.mdays || fcalEvents[cEv.n].occur==0) {
    fcalCreateEvent(cEv.n, false);
    var ename = fcalGetEvtRName(cEv.n,0);
    var esname = fcalGetEvtSubRName(cEv.n,0);
    fcalEvents[cEv.n].occur++;
  } else {
    fcalCreateEvent(cEv.n, true);
    var ename = fcalGetEvtRName(cEv.n,fcalEvents[cEv.n].occur);
    var esname = fcalGetEvtSubRName(cEv.n,fcalEvents[cEv.n].occur);
    fcalEvents[cEv.n].occur++;    
  }
  eE = eltId(ename);   // Event abstract container
  eE2 = eltId(esname); // Event abstract 
  // Compute width and X coord
  xw = cWidth * cEv.rwidth;
  delta = cWidth / ncol;
  with (eE) {
    style.top = startY+"px";
    style.left = startX + head + ((cEv.col-1) * delta) + "px";
    style.width = (xw-2>0?xw-2:6)+"px";
    style.height = (h-2>0?h-2:6)+"px";
    style.position = 'absolute';
    style.display = 'block';
  }
  with (eE2) {
    style.position = 'absolute';
    style.display = 'block';
    style.width = (xw-2-6>0?xw-2-6:6)+"px";
    style.height = (h-2-6>0?h-2-6:6)+"px";
  }
  
  return;
}


function fcalDisplayEvent(ie) {
  
  if (!fcalExistEvent(ie)) fcalCreateEvent(ie, false);
  
}

function fcalExistEvent(ie) {
  if (eltId(fcalGetEvtRName(ie,0))) return true;
  return false;
}

function fcalCreateEvent(ie,isclone) {
  if (fcalExistEvent(ie) && !isclone) return true;
  var  root = eltId('fcalDatas');
  var rnev; // System element  (no content)
  var nev;  // User content

  if (!isclone) {
    rnev = document.createElement('div');
  } else {
    var etmp = eltId(fcalGetEvtRName(ie,0));
    if (!etmp) { alert('Can\'t clone S Elem'+fcalGetEvtName(ie,0)); return; };
    rnev = etmp.cloneNode(true);
    var iis = rnev.childNodes.length - 1;
    for (var ii=iis; ii>=0; ii--) rnev.removeChild(rnev.childNodes[ii]);
  }
  rnev.setAttribute('id', fcalGetEvtRName(ie,fcalEvents[ie].occur));
  rnev.setAttribute('name', fcalGetEvtRName(ie,fcalEvents[ie].occur));
  rnev.style.overflow = 'hidden';
  rnev.className = 'wEvResume';
   
  if (!isclone) {
    nev = document.createElement('div');
  } else {
    var etmp = eltId(fcalGetEvtSubRName(ie,0));
    if (!etmp) { alert('Can\'t clone U Elem for '+fcalGetEvtSubRName(ie,0)); return; };
    nev = etmp.cloneNode(true);
  }
  rnev.appendChild(nev);

  var inhtml = '';
  with (nev) { 
    setAttribute('id', fcalGetEvtSubRName(ie,fcalEvents[ie].occur));
    setAttribute('name', fcalGetEvtSubRName(ie,fcalEvents[ie].occur));
    if (fcalEvents[ie].display) {
      fcalAddEvent(nev, 'mouseover', function foo(event) { fcalStartEvDisplay(event, ie) } );
      fcalAddEvent(nev, 'mouseout', function foo(event) { fcalCancelEvDisplay(event, ie) } );
      fcalAddEvent(nev, 'contextmenu', function foo(event) { fcalOpenMenuEvent(event, ie); return false; } );
    } else {
      fcalAddEvent(nev, 'mouseover',  function foo(event) { 
	                                       event || (event = window.event);
	                                       var srcel = (event.target) ? event.target : event.srcElement;
                                               fcalSetOpacity(srcel, 100);
                                               return;
      });
      fcalAddEvent(nev, 'mouseout',  function foo(event) { 
	                                       event || (event = window.event);
	                                       var srcel = (event.target) ? event.target : event.srcElement;
					       fcalSetOpacity(srcel, 60);
                                               return;
      });
    }      
    if (fcalEvents[ie].edit) {
      fcalAddEvent(nev, 'click', function foo(event) { fcalFastEditEvent(event, ie); return; } );
    }
    if (fcalEvents[ie].icons.length>0) {
      for (var iic=0; iic<fcalEvents[ie].icons.length; iic++) {
	inhtml += '<img src="'+fcalEvents[ie].icons[iic]+'" witdh="9px">';
      }
    }
//     inhtml += '&nbsp;['+ie+']'+ fcalEvents[ie].title;
    inhtml += '&nbsp;'+ fcalEvents[ie].title;
    innerHTML = inhtml;
    style.backgroundColor = fcalEvents[ie].bgColor;
    style.color = fcalEvents[ie].fgColor;
    style.borderWidth = '3px';
    style.borderStyle = 'solid';
    style.opacity = '0.6';
    style.filter = 'alpha(opacity=60)';
    style.borderColor = fcalEvents[ie].topColor+' '+fcalEvents[ie].rightColor+' '+fcalEvents[ie].bottomColor+' '+fcalEvents[ie].leftColor;    
    style.display = 'block';
    style.position = 'absolute';
  }
  root.appendChild(rnev);

  return true;
}
 
function fcalSetOpacity(o, value) {
	o.style.opacity = value/100;
	o.style.filter = 'alpha(opacity=' + value + ')';
}


// Events to display
var fcalEvents = new Array();

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

function fcalStartEvDisplay(ev,ie) {
  ev || (ev = window.event);
  var srcel = (ev.target) ? ev.target : ev.srcElement;
  if (evDisplayed==ie) return;
  fcalResetTempo(ev);
  posM.x = getX(ev);
  posM.y = getY(ev);
  evDisplayed = ie;
  TempoId = self.setTimeout("fcalShowCalEvent()", AltTimerValue);
  return;
}

function fcalResetTempo(ev) {
  ev || (ev = window.event);
  var srcel = (ev.target) ? ev.target : ev.srcElement;
  if (TempoId>-1) clearTimeout(TempoId);
  TempoId = -1;
  evDisplayed = -1;
  fcalSetOpacity(srcel, 100); 
  return;
}

function fcalCancelEvDisplay(ev, ie) {
  ev || (ev = window.event);
  var srcel = (ev.target) ? ev.target : ev.srcElement;
  var pid = fcalEvents[ie].idp;
  hideCalEvent(ie);
  evDisplayed = -1;
  posM.x = 0;
  posM.y = 0;
  fcalSetOpacity(srcel, 60); 
}


var rq;
function fcalGetCalEvent(ie) {
  evLoaded[ie] = true;
  if (window.XMLHttpRequest) rq = new XMLHttpRequest();
  else if (window.ActiveXObject) rq = new ActiveXObject("Microsoft.XMLHTTP");
  else alert('pas de XMLHttpRequest');
  if (rq) {
    rq.onreadystatechange = function foo() { addCalEvContent(ie) };
    var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_VIEWEVENT&id="+fcalEvents[ie].idp;
    rq.open("GET", urlsend, true);
    rq.send(null);
  }
}


function addCalEvContent(ie) {
  if (rq.readyState == 4) {
    if (rq.responseText && rq.status==200) {
      if (!eltId(fcalGetEvtCardName(ie))) return;
      var eid = eltId(fcalGetEvtCardName(ie));
      eid.innerHTML = rq.responseText;
      if (evDisplayed!=ie) return;
      hideWaitServerMessage();
      computeDivPosition(fcalGetEvtCardName(ie),20);
    }
  }
  return;
}

function computeDivPosition(o,delta) {

  if (!eltId(o)) {
    alert('Element '+o+' not found');
    return;
  }
  var eid = eltId(o);

  eid.style.visibility = 'hidden';
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
  eid.style.visibility = 'visible';

  return;
}
 
  
function initCalEvent(ie) {
  var eid = fcalGetEvtCardName(ie);
  if (eltId(eid)) return;

  showWaitServerMessage('Loading event');
  var ref = eltId('fcalDatas');
  var nev = document.createElement('div');
  with (nev) {
    id = fcalGetEvtCardName(ie);
    name = fcalGetEvtCardName(ie);
    innerHTML = '';
  }
  ref.appendChild(nev);
  fcalGetCalEvent(ie);
  return;  
}

function fcalShowCalEvent() {
  if (evLoaded[evDisplayed] || evDisplayed<0) return;
  evLoaded[evDisplayed] = true;
  initCalEvent(evDisplayed);
  computeDivPosition(fcalGetEvtCardName(evDisplayed),20);
}

function hideCalEvent(ie) {
  if (eltId(fcalGetEvtCardName(ie))) {
    evLoaded[ie] = false;
    eltId(fcalGetEvtCardName(ie)).style.display = 'none';
    hideWaitServerMessage();
  }
}


function modifyEvent(pid) {
  alert('pid='+pid);
}

function fastEditSave(ev) {
  fcalSetOpacity(document.getElementById('fastedit'), 50);
  posM.x = getX(ev);
  posM.y = getY(ev);
  showWaitServerMessage('Saving event.');
  var feTitle = eltId('fe_title').value;
  var loc = eltId('fe_location').value;
  var note = eltId('fe_note').value;
  var scat = eltId('fe_categories');
  var cat = 0;
  for (var i=0; i<scat.options.length; i++) { if (scat.options[i].selected) cat = scat.options[i].value; }
  var sconf = eltId('fe_confidentiality');
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
  if (window.XMLHttpRequest) rq = new XMLHttpRequest();
  else rq = new ActiveXObject("Microsoft.XMLHTTP");
  rq.open("POST", urlsend, false);
  rq.send(null);
  eval(rq.responseText);
  hideWaitServerMessage();
  if (fcalStatus.code==-1) {
    alert('Server error ['+fcalStatus.code+'] : '+fcalStatus.text);
    return false;
  }
  if (document.EventInEdition && document.EventInEdition.rg>=0) {
    fcalEvents[document.EventInEdition.rg] = newEvent;
  } else {
    fcalEvents[fcalEvents.length] = newEvent;
  }
  fastEditReset();
  fcalReloadEvents();
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
			      confidentiality:0, rcolor:parent.wgcal_toolbar.calCurrentEdit.color};
  eltId('fe_title').value ='';
  eltId('fe_location').value ='';
  eltId('fe_note').value = '';
  eltId('fe_categories').options[0].selected = true;
  eltId('fe_confidentiality').options[0].selected = true;
  eltId('btnSave').style.display = 'none';
  eltId('fastedit').style.display = 'none';
  fcalSetOpacity(document.getElementById(Root), 100);
}
  

function fastEditContentChanged() {
  if (document.EventInEdition) {
    var title = eltId('fe_title').value;
    var loc = eltId('fe_location').value;
    var note = eltId('fe_note').value;
    var scat = eltId('fe_categories');
    var cat = 0;
    for (var i=0; i<scat.options.length; i++) { if (scat.options[i].selected) cat = scat.options[i].value; }
    var sconf = eltId('fe_confidentiality');
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
  if (eltId('fe_title').value!='') eltId('btnSave').style.display = '';
  else eltId('btnSave').style.display = 'none';
  return true;
}

function fastEditFSave(event, o) {
  var evt = (evt) ? evt : ((event) ? event : null );
  var cc = (evt.keyCode) ? evt.keyCode : evt.charCode;
  if (cc==13) {
    fastEditSave(event);
    return false;
  }
  if (eltId('fe_title').value!='') eltId('btnSave').style.display = '';
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
  if (window.XMLHttpRequest) rq = new XMLHttpRequest();
  else rq = new ActiveXObject("Microsoft.XMLHTTP");
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


function fcalFastEditEvent(ev, ie) {
  var evt = (evt) ? evt : ((ev) ? ev : null );
  var idp = fcalEvents[ie].idp;
  if (evt.ctrlKey) {
    subwindow(400, 700, 'EditEvent', UrlRoot+'&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT&id='+idp);
  } else {
    var ii = 0;
    var dv = fcalGetJSDoc(idp) ;
    if (!dv) return;
    document.EventInEdition = { rg:ie, id:fcalEvents[ie].id, idp:fcalEvents[ie].idp, idowner:dv.calev_ownerid, titleowner:dv.calev_owner,
				title:dv.title, hmode:dv.calev_timetype, start:fcalEvents[ie].start, end:fcalEvents[ie].end, 
				category:dv.calev_category, note:dv.calev_evnote, location:dv.calev_location, 
				confidentiality:dv.calev_visibility, rcolor:fcalEvents[ie].bgColor };
    return fastEditInit(ev, true);
  }
  return;
}

function fastEditInit(ev, init) {
  if (!init && !fastEditChangeAlert()) return;
  
  fcalSetOpacity(document.getElementById(Root), 50);
  var fedit = eltId('fastedit');

  if (document.EventInEdition.idowner==-1) {
    document.EventInEdition.idowner = parent.wgcal_toolbar.calCurrentEdit.id;
    document.EventInEdition.titleowner = parent.wgcal_toolbar.calCurrentEdit.title;
  }    
  eltId('fastedit').style.backgroundColor = document.EventInEdition.rcolor;
  eltId('agendaowner').innerHTML = document.EventInEdition.titleowner;
  
  eltId('fe_allday').style.display = 'none'; 
  eltId('fe_nohour').style.display = 'none'; 
  eltId('fe_title').value = document.EventInEdition.title;
  eltId('fe_location').value = document.EventInEdition.location;
  eltId('fe_note').value = document.EventInEdition.note;

  var scat = eltId('fe_categories');
  for (var i=0; i<scat.options.length; i++) {
    if (scat.options[i].value == document.EventInEdition.category) scat.options[i].selected = true;
  }

  var scat = eltId('fe_confidentiality');
  for (var i=0; i<scat.options.length; i++) {
    if (scat.options[i].value == document.EventInEdition.confidentiality) scat.options[i].selected = true;
  }

  var textdate = '';
  var ds = new Date();
  ds.setTime((document.EventInEdition.start*1000) + (ds.getTimezoneOffset()*60*1000));
  textdate = ds.print('%a %d %b %Y');
  switch (document.EventInEdition.hmode) {
  case 2: 
    eltId('fe_allday').style.display = 'block'; 
    break;
  case 1: 
    eltId('fe_nohour').style.display = 'block'; 
    break;
  default:
    var de = new Date();
    de.setTime((document.EventInEdition.end*1000) + (de.getTimezoneOffset()*60*1000));
    textdate = ds.print('%a %d %b %Y, %H:%M - ');
    if (ds.print('%a %d %b')!=de.print('%a %d %b')) textdate+=de.print('%a %d %b %Y, %H:%M');
    else textdate+=de.print('%H:%M');
  }
  eltId('fe_date').innerHTML = textdate;
  
  posM.x = getX(ev);
  posM.y = getY(ev);
  computeDivPosition('fastedit', -20);

  eltId('fe_title').focus();
  if (eltId('fe_title').value!='') eltId('btnSave').style.display = '';
}


function fcalOpenMenuEvent(ev, ie) {
  ev || (ev = window.event);
  var srcel = (ev.target) ? ev.target : ev.srcElement;
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_GETMENU&id="+fcalEvents[ie].idp;
  var rq;
  showWaitServerMessage('Loading menu');
  if (window.XMLHttpRequest) rq = new XMLHttpRequest();
  else rq = new ActiveXObject("Microsoft.XMLHTTP");
  rq.ie = ie;
  rq.open("GET", urlsend, false);
  rq.send(null);
  hideWaitServerMessage();
  eval(rq.responseText);
  if (fcalStatus.code<0) {
    alert('Server error ['+fcalStatus.code+'] : '+fcalStatus.text);
    return false;
  } else {
    var mn = new MCalMenu('mevt'+ie, pmenu, pmstyle);
    mn.attachToElt(srcel.id , 0, 0, true, 'contextmenu', 'fcalActivateMenu', fcalEvents[ie].idp);
    return false;
  }
}

function fcalActivateMenu(event, mode, type, action, target, hmode, hparam) {
  alert(hparam[0]);
}

function showWaitServerMessage(msg) {
  fcalSetOpacity(eltId(Root), 40);
  var ws = eltId('waitmessage'); 
  if (msg) eltId('wmsgtext').innerHTML = msg;
  computeDivPosition('waitmessage',10);
}

function hideWaitServerMessage() {
  var ws = eltId('waitmessage'); 
  ws.style.display = 'none';
  fcalSetOpacity(eltId(Root), 100);
}

function eltId(eltid) {
  if (document.getElementById(eltid)) return document.getElementById(eltid);
//   alert('ID '+eltid+' not found '); 
  return false;
}

function fcalAddEvent(o, e, f) {
  if (o.addEventListener){ o.addEventListener(e,f,true); return true;   }
  else if (o.attachEvent) { return o.attachEvent("on"+e,f); }
  else { return false; }
}
function fcalDelEvent(o,e,f){
        if (o.removeEventListener){ o.removeEventListener(e,f,true); return true; }
        else if (o.detachEvent){ return o.detachEvent("on"+e,f); }
        else { return false; }
}

function fcalCancelEvent(e) {
  if (!e) e = window.event;
  if (e.stopPropagation) e.stopPropagation();
  else e.cancelBubble = true;
}

