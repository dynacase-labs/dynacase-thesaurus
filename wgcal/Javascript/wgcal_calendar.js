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
    usetparam(-1, "WGCAL_U_CALCURDATE", ts, 'wgcal_calendar', UrlRoot+'app=WGCAL&action=WGCAL_CALENDAR');
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
   EventInEdition = { id:0, idp:0, idowner:-1, titleowner:'',
		      title:'', hmode:nh, start:times, end:timee, 
		      category:0, note:'', location:'', 
		      confidentiality:eltId('defvis').value, 
		      rcolor:parent.wgcal_toolbar.calCurrentEdit.color, eventjs:null };
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

//   var wroot = '100%';
  var wroot = parseInt(document.body.clientWidth);
  eltId(Root).style.width=wroot; 
  eltId('week').style.width=wroot; 
  eltId('headscreen').style.width=wroot; 
  if (eltId('agtitle')) eltId('agtitle').style.width=wroot-4; 
//   if (eltId('wgcalmenu')) eltId('wgcalmenu').style.width=wroot; 

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
function fcalGetEvtCardName(ie) {   
  if (fcalEvents[ie]) return 'evtc'+fcalEvents[ie].idp;
  return '__unknown'+ie;
}

function fcalRemoveEvent() {
  var msgd = "";
  for (var iev=fcalEvents.length-1; iev>=0; iev--) {
    var icard = eltId(fcalGetEvtCardName(iev));
    if (icard && icard.parentNode) icard.parentNode.removeChild(icard);
    for (var iocc=fcalEvents[iev].occur; iocc>0; iocc--) {
      var iabs = eltId(fcalGetEvtRName(iev,iocc));
      if (iabs && iabs.parentNode) iabs.parentNode.removeChild(iabs);
    }
    fcalEvents[iev].occur = 0;
  }
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

  var  root = eltId(Root);

  var bwidth = (isIE?4:8);
  
  foot = 0;
  head = 0;
  // cWidth = Wday - (2*head);
  var cWidth =  parseInt(getObjectWidth(eltId('D'+cEv.curday+'H0'))) - (2*head);
  var xt = getAnchorPosition('D'+cEv.curday+'H0');
  startX = parseInt(xt.x) + 1; 
  startY = parseInt(GetYForTime(cEv.vstart)) + 1;
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
    style.padding = '0px';
    style.backgroundColor = 'red';
  }
  with (eE2) {
    style.top = style.left = 0;
    style.position = 'relative';
    style.display = 'block';
    style.width = parseInt(getObjectWidth(eE))-bwidth;
    style.height = parseInt(getObjectHeight(eE))-bwidth;
    style.margin = '0px';
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
  var  root = eltId(Root);
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
    if (fcalEvents[ie].menu && fcalEvents[ie].menu!='') {
      fcalAddEvent(nev, 'contextmenu', function foo(event) { fcalCancelEvDisplay(event, ie);
                                                             fcalOpenMenuEvent(event, ie, nev);
                                                             return false; } );
    }
    if (fcalEvents[ie].edit) {
      fcalAddEvent(nev, 'click', function foo(event) { fcalFastEditEvent(event, ie); return; } );
    }
    if (fcalEvents[ie].icons.length>0) {
      for (var iic=0; iic<fcalEvents[ie].icons.length; iic++) {
	inhtml += '<img src="'+fcalEvents[ie].icons[iic]+'" witdh="9px">';
      }
    }

//     inhtml += '&nbsp;['+ie+'] '+ fcalEvents[ie].title;
    inhtml += '&nbsp;'+ fcalEvents[ie].title;
    innerHTML = inhtml;
//     style.opacity = '0.6';
//     style.filter = 'alpha(opacity=60)';
    style.backgroundColor = fcalEvents[ie].bgColor;

    var tcol = getHSL(style.backgroundColor);
    tcol[1] = (tcol[1]+128) % 256;
    tcol[2] = (tcol[2]+128) % 256;
    var trgb = HSL2RGB(tcol[0], tcol[1], tcol[2]);
    for (i=0;i<3;i++) {
      if (trgb[i]>15)  trgb[i]=trgb[i].toString(16);
      else trgb[i]='0'+trgb[i].toString(16);
    }
    style.color = '#'+trgb.join('');
   

//     style.color = fcalEvents[ie].fgColor;
    style.borderWidth = '3px';
    style.borderStyle = 'solid';
    style.borderColor = fcalEvents[ie].topColor+' '+fcalEvents[ie].rightColor+' '+fcalEvents[ie].bottomColor+' '+fcalEvents[ie].leftColor;    
    style.display = 'block';
    style.position = 'absolute';
  }
  root.appendChild(rnev);

  return true;
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

function fcalReloadEvents(ev) {
   showWaitServerMessage(ev, 'Loading interface');
   fcalInitEvents();
   fcalShowEvents();
   hideWaitServerMessage();
}

function fcalStartEvDisplay(ev,ie) {
  ev || (ev = window.event);
  var srcel = (ev.target) ? ev.target : ev.srcElement;
  if (evDisplayed==ie) return;
  fcalResetTempo(ev);
  evDisplayed = ie;
  posM.x = getX(ev);
  posM.y = getY(ev);
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
function fcalGetCalEvent(ev, ie) {
  evLoaded[ie] = true;
  if (window.XMLHttpRequest) rq = new XMLHttpRequest();
  else if (window.ActiveXObject) rq = new ActiveXObject("Microsoft.XMLHTTP");
  else alert('pas de XMLHttpRequest');
  if (rq) {
    rq.onreadystatechange = function foo() { addCalEvContent(ev, ie) };
    var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_VIEWEVENT&id="+fcalEvents[ie].idp;
    rq.open("GET", urlsend, true);
    rq.send('');
  }
}


function addCalEvContent(ev, ie) {
  if (rq.readyState == 4) {
    if (rq.responseText && rq.status==200) {
      if (!eltId(fcalGetEvtCardName(ie))) return;
      var eid = eltId(fcalGetEvtCardName(ie));
      eid.innerHTML = rq.responseText;
      if (evDisplayed!=ie) return;
      hideWaitServerMessage();
      fcalSetEvCardPosition(ie,posM.x,posM.y, 20);
    }
  }
  return;
}

  
function initCalEvent(ie) {
  var eid = fcalGetEvtCardName(ie);
  if (eltId(eid)) return;

  showWaitServerMessage(false, 'Loading event');
  var ref = eltId(Root);
  var nev = document.createElement('div');
  with (nev) {
    id = fcalGetEvtCardName(ie);
    name = fcalGetEvtCardName(ie);
    innerHTML = '';
  }
  ref.appendChild(nev);
  fcalGetCalEvent(false, ie);
  return;  
}

function fcalShowCalEvent() {
  if (evLoaded[evDisplayed] || evDisplayed<0) return;
  evLoaded[evDisplayed] = true;
  initCalEvent(evDisplayed);
  fcalSetEvCardPosition(evDisplayed,posM.x,posM.y, 20);
}


function fcalSetEvCardPosition(evid, x, y, shift) {
  computeDivPosition(fcalGetEvtCardName(evid),x,y, shift, 0.5);
  return true;
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
  showWaitServerMessage(ev, 'Saving event.');
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
  urlsend += "&id="+EventInEdition.idp;
  urlsend += "&oi="+EventInEdition.idowner;
  urlsend += "&ti="+feTitle;
  var hmode = 0;
  if (eltId('nohour') && eltId('nohour').checked) hmode = 1;
  if (eltId('allday') && eltId('allday').checked) hmode = 2;
  urlsend += "&nh="+hmode;
  urlsend += "&ts="+eltId('s_start').value;
  urlsend += "&te="+eltId('s_end').value;
  urlsend += "&ca="+cat;
  urlsend += "&co="+conf;
  urlsend += "&lo="+loc;
  urlsend += "&no="+note;
  
  var rq;
  if (window.XMLHttpRequest) rq = new XMLHttpRequest();
  else rq = new ActiveXObject("Microsoft.XMLHTTP");
  rq.open("POST", urlsend, false);
  rq.send('');
  eval(rq.responseText);
  hideWaitServerMessage();
  if (fcalStatus.code==-1) {
    alert('Server error ['+fcalStatus.code+'] : '+fcalStatus.text);
    return false;
  }
  if (inCalendar) {
    fcalInsertTmpEvent(ev, _fcalTmpEvents);
  } else {
    document.location.reload(false);
  }
  flogDisplayMsg('I');
  return;
} 

function fcalInsertTmpEvent(ev, tEv) {
  var mm = '';
  for (var iie=0; iie<tEv.length; iie++) {
    for (var itt=fcalEvents.length-1; itt>=0; itt--) {
      if (fcalEvents[itt].id==tEv[iie].id) fcalEvents.splice(itt, 1);
    }
  }
  for (var iie=0; iie<tEv.length; iie++) {
     fcalEvents[fcalEvents.length] = tEv[iie];
  }
  fastEditReset();
  fcalReloadEvents(ev);
}

function fcalGetRgFromIdP(idp) {
  var rt = new Array();
  for (var ir=0; ir<fcalEvents.length; ir++) {
    if (fcalEvents[ir].idp==idp) rt[rt.length] = ir;
  }
  return rt;
}
function fcalGetRgFromId(id) {
  var rt = new Array();
  for (var ir=0; ir<fcalEvents.length; ir++) {
    if (fcalEvents[ir].id==id) rt[rt.length] = ir;
  }
  return rt;
}

function fcalDeleteEvent(event,idp) {
  var url = UrlRoot+'&app=WGCAL&action=WGCAL_DELETEEVENT&id='+idp;
  var res = fcalSendRequest(url, false, false);
  if (res.status!=200) {
    alert('Server error on request ['+url+']\n - Status='+res.status+'\n - Return '+res.content); 
  } else {
    if (inCalendar) {
      if (fcalEvents) {
	for (var iie=fcalEvents.length-1; iie>=0; iie--) {
	  if (fcalEvents[iie].idp==idp) fcalEvents.splice(iie,1);
	}
	fcalReloadEvents();
      }
      flogDisplayMsg('I');
    } else {
      //     document.location.href = document.location.href;
      document.location.reload(false);
    }
  }
  return true; 
}

function fcalDeleteEventOcc(event,idp,occ) {
  var url = UrlRoot+'&app=WGCAL&action=WGCAL_DELOCCUR&id='+idp+'&evocc='+occ;
  var res = fcalSendRequest(url, false, false);
  if (res.status!=200) {
    alert('Server error on request ['+url+']\n - Status='+res.status+'\n - Return '+res.content); 
  } else {
    if (inCalendar) {  
      eval(res.content);
      if (_fcalTmpEvents) fcalInsertTmpEvent(event, _fcalTmpEvents);
      flogDisplayMsg('I');
    } else {
      document.location.reload(false);
    }
  }
  return true; 
}

function fcalSetEventState(event,idp,state,reloadcal) {
  var owner = 0;
  if (inCalendar) owner = parent.wgcal_toolbar.calCurrentEdit.id;

  var url = UrlRoot+'&app=WGCAL&action=WGCAL_SETEVENTSTATE&id='+idp+'&ow='+owner+'&st='+state;
  var res = fcalSendRequest(url, false, false);
  if (res.status!=200) {
    alert('Server error on request ['+url+']\n - Status='+res.status+'\n - Return '+res.content); 
  } else {
    if (inCalendar) {  
      eval(res.content);
      if (_fcalTmpEvents.length>0) fcalInsertTmpEvent(event,_fcalTmpEvents);
      else {
 	for (var iie=fcalEvents.length-1; iie>=0; iie--) {
	  if (fcalEvents[iie].idp==idp) fcalEvents.splice(iie,1);
	}
  	fcalReloadEvents();
      }
      flogDisplayMsg('I');
    } else {
      document.location.reload(false);
      if (reloadcal) parent.wgcal_calendar.document.location.reload(false);
    }
  }
  return true; 
}


function fastEditOpenFullEdit(ev) {
  showWaitServerMessage(ev, 'Start full edition mode...');
  if (fastEditChangeAlert()) {
    subwindow(400, 700, 'EditEvent', UrlRoot+'&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT&id='+EventInEdition.idp);
    fastEditReset();
  }
  hideWaitServerMessage();
}

var EventInEdition;
function fastEditReset() {
  EventInEdition = { rg:-1, id:0, idp:0, idowner:-1, titleowner:'',
			      title:'', hmode:0, start:0, end:0, 
			      category:0, note:'', location:'', 
			      confidentiality:eltId('defvis').value, rcolor:parent.wgcal_toolbar.calCurrentEdit.color, eventjs:null};
  eltId('fe_title').value ='';
  eltId('fe_location').value ='';
  eltId('fe_note').value = '';
  eltId('fe_categories').options[0].selected = true;
  eltId('fe_confidentiality').options[eltId('defvis').value].selected = true;
  fastEditCanSave(false);
  eltId('fastedit').style.display = 'none';
  eltId('nohour').checked = '';
  eltId('allday').checked = '';
  eltId('s_start').value = 0;
  eltId('s_end').value = 0;
  fcalSetOpacity(document.getElementById(Root), 100);
  datehourChanged = false;
}
  

var  datehourChanged = false;
function fastEditContentChanged() {
  if (EventInEdition) {
    var title = eltId('fe_title').value;
    var loc = eltId('fe_location').value;
    var note = eltId('fe_note').value;
    var scat = eltId('fe_categories');
    var cat = 0;
    for (var i=0; i<scat.options.length; i++) { if (scat.options[i].selected) cat = scat.options[i].value; }
    var sconf = eltId('fe_confidentiality');
    var conf = 0;
    for (var i=0; i<sconf.options.length; i++) { if (sconf.options[i].selected) conf = sconf.options[i].value; }
    if (title != EventInEdition.title) return true;
    if (cat != EventInEdition.category) return true;
    if (note != EventInEdition.note) return true;
    if (loc != EventInEdition.location) return true;
    if (conf != EventInEdition.confidentiality) return true;

    var hmode = 0;
    if (eltId('nohour').checked) hmode=1;
    if (eltId('allday').checked) hmode=2;
    if (hmode!=EventInEdition.hmode) return true;

    if (datehourChanged) return true;
  }
  return false;
}
  
function fastEditChangeAlert() {
  if (!fastEditContentChanged()) return true;
  var ca = confirm('Abandon des modifications en cours ?');
  if (ca) fastEditReset();
  return ca;
} 

function fastEditCanSave(yn) {
  if (yn) {
    eltId('btnSave').style.visibility = 'visible';
    eltId('btnCConf').style.visibility = 'visible';
  } else {
    eltId('btnSave').style.visibility = 'hidden';
    eltId('btnCConf').style.visibility = 'hidden';
  }
}
function fastEditChange(o) {
  fastEditCanSave(eltId('fe_title').value!=''?true:false);
  return true;
}

function fastEditFSave(event, o) {
  var evt = (evt) ? evt : ((event) ? event : null );
  var cc = (evt.keyCode) ? evt.keyCode : evt.charCode;
  if (cc==13) {
    fastEditSave(event);
    return false;
  }
  if (eltId('fe_title').value!='') fastEditCanSave(true);
  return true;
}

function fastEditCancel() {
  if (!fastEditChangeAlert()) return;
  fastEditReset();
  return true;
}


    
function fastEditCheckConflict(ev) {
  showWaitServerMessage(ev, 'Checking for conflict');
  ev || (ev = window.event);
  var ress="";
  if (EventInEdition.eventjs) {
    for (var io=0; io<EventInEdition.eventjs.calev_attid.length; io++) {
      ress += (ress==''?'':'|')+EventInEdition.eventjs.calev_attid[io];
    }
  } else {
    ress = parent.wgcal_toolbar.calCurrentEdit.id;
  }
  
  var ts = parseInt(eltId('s_start').value) + 60;
  var te = parseInt(eltId('s_end').value) - 60;
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_GVIEW&stda=1&rvfs_pexc="+EventInEdition.idp+"&rvfs_ts="+ts+"&rvfs_te="+te+"&rvfs_ress="+ress;
  var rq;
  posM.x = getX(ev);
  posM.y = getY(ev);

  var res = fcalSendRequest(urlsend, false, false);
  if (res.status==200) {
    eltId('conflictcontent').innerHTML = res.content;
  } else {
    alert('Oooops>'+res.content);
  }
  hideWaitServerMessage();
  computeDivPosition('conflict', posM.x, posM.y, -50);
  return;
}


function fcalFastEditEvent(ev, ie) {
  var evt = (evt) ? evt : ((ev) ? ev : null );
  var idp = fcalEvents[ie].idp;
  if (evt.ctrlKey) {
    showWaitServerMessage(ev, 'Start full edition mode...');
    subwindow(400, 700, 'EditEvent', UrlRoot+'&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT&id='+idp);
    hideWaitServerMessage();
  } else {
    var ii = 0;
    var dv = fcalGetJSDoc(ev,idp) ;
    if (!dv) return;

    // compute owner color
    var rcol = '';
    for (var ir=0; ir<parent.wgcal_toolbar.calRessources.length && rcol=='' ; ir++) {
      if (parent.wgcal_toolbar.calRessources[ir].id==dv.calev_ownerid) rcol = parent.wgcal_toolbar.calRessources[ir].color;
    }
    rcol = (rcol==''?'white':rcol);
       
    EventInEdition = { rg:ie, id:fcalEvents[ie].id, idp:fcalEvents[ie].idp, 
		       idowner:dv.calev_ownerid, titleowner:dv.calev_owner,
		       title:dv.calev_evtitle, hmode:dv.calev_timetype, 
		       occstart:fcalEvents[ie].start, occend:fcalEvents[ie].end, start:dv.tsstart, end:dv.tsend, 
		       category:dv.calev_category, note:dv.calev_evnote, location:dv.calev_location, 
		       confidentiality:dv.calev_visibility, rcolor:rcol, 
		       eventjs:dv };
    return fastEditInit(ev, true);
  }
  return;
}

function fastEditInit(ev, init) {
  if (!init && !fastEditChangeAlert()) return;
  
  fcalSetOpacity(document.getElementById(Root), 50);
  var fedit = eltId('fastedit');


  if (EventInEdition.idowner==-1) {
    EventInEdition.idowner = parent.wgcal_toolbar.calCurrentEdit.id;
    EventInEdition.titleowner = parent.wgcal_toolbar.calCurrentEdit.title;
    EventInEdition.rcolor = parent.wgcal_toolbar.calCurrentEdit.color;
  }    
  
  fcalInitDatesTimes(EventInEdition.hmode, 
		     EventInEdition.start, 
		     EventInEdition.end);
  
  eltId('fastedit').style.backgroundColor = EventInEdition.rcolor;
  eltId('agendaowner').innerHTML = EventInEdition.titleowner;
  
  eltId('fe_title').value = EventInEdition.title;
  eltId('fe_location').value = EventInEdition.location;
  eltId('fe_note').value = EventInEdition.note;

  var scat = eltId('fe_categories');
  for (var i=0; i<scat.options.length; i++) {
    if (scat.options[i].value == EventInEdition.category) scat.options[i].selected = true;
  }

  var scat = eltId('fe_confidentiality');
  for (var i=0; i<scat.options.length; i++) {
    if (scat.options[i].value == EventInEdition.confidentiality) scat.options[i].selected = true;
  }

  posM.x = getX(ev);
  posM.y = getY(ev);
  computeDivPosition('fastedit', posM.x, posM.y, 0);

  eltId('fe_title').focus();
  fastEditCanSave((eltId('fe_title').value!=''?true:false));
}

function fcalOpenMenuEvent(ev, ie, nev) {
  ev || (ev = window.event);
  var srcel = (ev.target) ? ev.target : ev.srcElement;
  if (!fcalEvents[ie].menu) {
    alert("pas de menu pour "+ie);
    return;
  }
  stopPropagation(ev);
  var urlmenu = fcalEvents[ie].menu+"&ctx=FULL&occ="+fcalEvents[ie].start;
  posM.x = getX(ev);
  posM.y = getY(ev);
  viewmenu(ev,urlmenu); 
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



// time and date


function fcalComputeDateFromStart() {

  var o_stime = parseInt(document.getElementById('js_start').value);
  var od_stime = new Date();
  od_stime.setTime(o_stime);

  var o_etime = parseInt(document.getElementById('js_end').value);
  var od_etime = new Date();
  od_etime.setTime(o_etime);

  var tdiff = (o_etime - o_stime);

  // Compute new start time
  var tsS = parseInt(document.getElementById('s_start').value) * 1000;
  var hts = new Date();
  hts.setTime(tsS);
  var Hstart = parseInt(document.getElementById('h_start').options[document.getElementById('h_start').selectedIndex].value);
  var Mstart = parseInt(document.getElementById('m_start').options[document.getElementById('m_start').selectedIndex].value);
  var nS = new Date(hts.getFullYear(), hts.getMonth(), hts.getDate(), Hstart, Mstart, 0, 0);
  var nE = new Date();
  nE.setTime(nS.getTime() + tdiff);


  // Updating all fields....
  fcalSetTime('start', nS, false);
  fcalSetTime('end', nE, false);

  datehourChanged = true;
}


function fcalComputeDateFromEnd() {

  var o_stime = parseInt(document.getElementById('js_start').value);
  var od_stime = new Date();
  od_stime.setTime(o_stime);

  var o_etime = parseInt(document.getElementById('js_end').value);
  var od_etime = new Date();
  od_etime.setTime(o_etime);


  // Compute new old time
  var tsE = parseInt(document.getElementById('s_end').value) * 1000;
  var hte = new Date();
  hte.setTime(tsE);
  var Hend = parseInt(document.getElementById('h_end').options[document.getElementById('h_end').selectedIndex].value);
  var Mend = parseInt(document.getElementById('m_end').options[document.getElementById('m_end').selectedIndex].value);
  var nE = new Date(hte.getFullYear(), hte.getMonth(), hte.getDate(), Hend, Mend, 0, 0);

  if (nE.getTime()<=od_stime.getTime()) {
    alert('La date demandée est antérieure à celle de début');
    nE.setTime(od_etime.getTime());
  }

  fcalSetTime('end', nE, false);
  datehourChanged = true;
  return;
}

function fcalSetTime(startend, oTime, full) {
  eltId('js_'+startend).value = oTime.getTime();
  eltId('s_'+startend).value = (oTime.getTime() / 1000);
  eltId('t_'+startend).innerHTML = oTime.print('%a %d %b %Y');
  if (startend=='end')  { 
    eltId('h_'+startend).selectedIndex = oTime.getHours();
    fcalUpdateMinutes(startend, oTime.getMinutes());
  }
  if (startend=='start' && full)  { 
    eltId('h_'+startend).selectedIndex = oTime.getHours();
    fcalUpdateMinutes(startend, oTime.getMinutes());
  }
}
 
function fcalUpdateMinutes(startend, min) {
  var init = -1;
  var sb = document.getElementById('m_'+startend);
  for (var ib=(sb.options.length-1); ib>=0 && init==-1; ib--) {
    if (parseInt(min)>=parseInt(sb.options[ib].value)) init=ib;
  }
  sb.selectedIndex = init;
}

function fcalAlldayClicked(event) {
  var o = eltId('allday');
  o.checked = (o.checked ? "" : "checked");
  if (o && o.checked) fcalAllday(true);
  else fcalAllday(false);
  return true;
}
function fcalAllday(s) {
  var showhide = [ 'start_hour', 'end_hour1', 'end_hour2', 'end_hour3', 'nohour_span' ];
  var vis = 'visible';
  if (s) vis = 'hidden';
  for (var i=0; i<showhide.length; i++) {
    var ot = eltId(showhide[i]);
    if (ot) ot.style.visibility = vis;
  }
  return true;
}
  


function fcalNohourClicked(event) {
  var o = eltId('nohour');
  o.checked = (o.checked ? "" : "checked");
  if (o && o.checked) fcalNohour(true);
  else fcalNohour(false);
  return true;
}
function fcalNohour(s) {
  var showhide = [ 'start_hour', 'end_hour1', 'end_hour2', 'end_hour3', 'allday_span' ];
  var vis = 'visible';
  if (s) vis = 'hidden';
  for (var i=0; i<showhide.length; i++) {
    var ot = eltId(showhide[i]);
    if (ot) ot.style.visibility = vis;
  }
  return true;
}

function fcalInitTimeO(second) {
  var otime = new Date();
  var tzd = otime.getTimezoneOffset()*60*1000;
  otime.setTime( (second + otime.getTimezoneOffset()*60) * 1000);
  return otime;
}

function fcalInitDatesTimes(nh, start, end) {
  var stime = fcalInitTimeO(start);
  fcalSetTime('start', stime, true);
  var etime = new Date();
  etime = fcalInitTimeO(end);
  fcalSetTime('end', etime, true);
  eltId('nohour').checked = '';
  eltId('allday').checked = '';
    fcalNohour(false);
    fcalAllday(false);
  if (nh==1) {
    fcalNohour(true);
    eltId('nohour').checked = 'checked';
  } else if (nh==2) {
    fcalAllday(true);
    eltId('allday').checked = 'checked';
  } 

  return true;
}


function displayWeekEnd(show) {
  var st = (show=='yes'?true:false);
  for (var id=0; id<Days.length; id++) {
    if (Days.isWE) Days.view = st;
  }
  displayInit();
}
