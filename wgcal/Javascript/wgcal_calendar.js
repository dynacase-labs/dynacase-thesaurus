
function WGCalResetSizes() {
//  alert("resize");
//  WGCalComputeCoord();
//  WGCalRefreshAll();
}
// --------------------------------------------------------
var Days = new Array();

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

var AltIsFixed = 'Float';
var AltCoord = new Object();

var  P_DURATION = 10;
var  P_DEB = 100;
var  P_FIN = 1;



function SetAltCoord(alt) {
  
  var delta = 50;
  
  var ww = getFrameWidth();
  var wh = getFrameHeight();
  var h = getObjectHeight(alt);
  var w = getObjectWidth(alt);

//   alert('(ww,wh)=('+ww+','+wh+') \n object='+alt+'(h,w)=('+h+','+w+')');
  
  var recompute = false;

  if (AltIsFixed!='Float') {
    switch(AltIsFixed) {
    case 'RightTop':
      AltCoord.x = ww/2 + delta;
      AltCoord.y = delta;
      break;
    case 'RightBottom':
      AltCoord.x = ww/2 + delta;
      AltCoord.y = wh/2 + delta;
      break;
    case 'LeftBottom':
      AltCoord.x = delta;
      AltCoord.y = wh/2 + delta;
      break;
    default:
      AltCoord.x = delta;
      AltCoord.y = delta;
    }
  }
  if (   (TimerMouseX>AltCoord.x && TimerMouseX<(AltCoord.x+w+delta)) 
      && (TimerMouseY>AltCoord.y && TimerMouseY<(AltCoord.y+h+delta)) ) recompute = true;
  if (AltIsFixed=='Float' || recompute) {
    if ((TimerMouseX + w + 30) > ww) AltCoord.x = TimerMouseX - 15 - w;
    else AltCoord.x = TimerMouseX + 15;
    if ((TimerMouseY + h + 30)> wh) AltCoord.y = TimerMouseY - 15 - h;
    else AltCoord.y = TimerMouseY + 15;
  }
  return;
}

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
  for (id=0; id<XDays; id++) {
    for (i=0; i<Days[id].ev.length; i++) {
      if (Days[id].view) {
	evtc = document.getElementById('evtc'+Days[id].ev[i].n);
	if (evtc) evtc.style.display = 'none';
      }
    }
  }
}
  

// --------------------------------------------------------
var TimerOnElt = '';
var TimerID = -1;
var TimerMouseX = 10;
var TimerMouseY = 10;
var AltTimerValue = 500;
function  SetTimerOnMO(elt) {
  if (TimerOnElt == elt) return;
  ResetSetTimerOnMO();
  TimerOnElt = elt;
  TimerID = self.setTimeout("ShowEvInfos()", AltTimerValue);
}
function  ResetSetTimerOnMO() {
  if (TimerID!=-1) clearTimeout(TimerID);
  TimerID = -1;
  TimerOnElt = '';
}

function ShowEvInfos() {
  var ww = getFrameWidth();
  if (TimerOnElt!='') {
    WGCalCleanAllFullView();
    evtc = document.getElementById(TimerOnElt);
    if (evtc) {
      evtc.style.position = 'absolute';
      evtc.style.zIndex = 1001;
      evtc.style.display = 'block'; 
      SetAltCoord(evtc);
      evtc.style.left = AltCoord.x+'px';
      evtc.style.top = AltCoord.y+'px';
      var w = getObjectWidth(evtc);
    }
  }
  ResetSetTimerOnMO();
}

function WGCalEvOnMouseOver(ev, id) {
  TimerMouseX = getX(ev);
  TimerMouseY = getY(ev);
  SetTimerOnMO('evtc'+id);
}

function WGCalEvOnMouseOut(ev, id) {
  evtc = document.getElementById('evtc'+id);
  evtc.style.display ='none';
  ResetSetTimerOnMO();
  WGCalCleanAllFullView();
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
function ClickCalendarCell(nh,times,timee) {
  closeMenu('calpopup');
  subwindow(400, 700, 'EditEvent', UrlRoot+'&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT&id=0&nh='+nh+'&ts='+times+'&te='+timee);
}

// --------------------------------------------------------
function OverCalendarCell(ev, elt, lref, cref) {
  closeMenu('calpopup');
  WGCalCleanAllFullView();
  elt.className = 'WGCAL_PeriodSelected'; //WGCAL_DayLineOver';
  if (document.getElementById(lref)) document.getElementById(lref).className = 'WGCAL_PeriodSelected';
  if (document.getElementById(cref)) document.getElementById(cref).className = 'WGCAL_PeriodSelected';
}

// --------------------------------------------------------
function OutCalendarCell(ev, elt, lref, cref, cclass, hourclass, dayclass) {
  elt.className = cclass;
  if (document.getElementById(lref)) document.getElementById(lref).className = dayclass;
  if (document.getElementById(cref)) document.getElementById(cref).className = hourclass;
}

// --------------------------------------------------------
function WGCalViewInit(idstart, idend, xdiv, ydiv, ystart, ydivc, ydmin) {
  IdStart     = idstart;
  IdEnd       = idend;	
  XDays       = parseInt(xdiv);
  Ystart      = parseInt(ystart);
  Ydivision   = parseInt(ydiv);
  YDivCount   = parseInt(ydivc);
  YDivMinute  = parseInt(ydmin);
  WGCalComputeCoord();
}



// --------------------------------------------------------
function DrawRect(x,y,w,h,c,t) {
  //text = '(x,y,w,h)=('+x+','+y+','+w+','+h+')';
  text = t;
  nText = document.createElement('div');
  content = document.createTextNode(text);
  nText.appendChild(content);
  nText.style.position = 'absolute';
  nText.style.background = c;
  nText.style.left = x+"px";
  nText.style.top = y+"px";
  nText.style.width = w+"px";
  nText.style.height = h+"px";
  nText.style.border = '1px solid black';
  document.getElementById(Root).appendChild(nText);
}

// --------------------------------------------------------
function WGCalComputeCoord() {

  var gamma = 0; //0.25;

  // compute area coord left/top (Xs,Ys) right/bottom (Xe,Ye)
  var os = getAnchorPosition(IdStart);
  var hr = getObjectHeight(document.getElementById(IdStart));
  var wref = getObjectWidth(document.getElementById(IdStart));
  var oe = getAnchorPosition(IdEnd);
  var w = getObjectWidth(document.getElementById(IdEnd));
  var h = getObjectHeight(document.getElementById(IdEnd));
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
  var ida=0;
  var incr=0;
  for (ida=0; ida<Days.length; ida++) {
    if (Days[ida].view) {
      Days[ida].cpos = (Wday * incr) + Xs;
      incr++;
    }
  }      

//   DrawRect(os.x,os.y,w,(hr-gamma),'yellow');
//     DrawRect(Xs,Ys,Wzone,Hzone,'yellow','(x,y,w,h)=('+Xs+','+Ys+','+Wzone+','+Hzone+')');
}

// --------------------------------------------------------
function WGCalChangeClass(event, id, refclass, nclass)
{
  var elt = document.getElementById(id);
  if (!elt) return;
  if (elt.className!=refclass) elt.className = nclass;
}




// --------------------------------------------------------
function WGCalIntersect(asy,aey,bsy,bey) {
  var IsInt = false;
  if ((bsy>asy && bsy<aey)) IsInt = true;
  if ((bey>asy && bey<aey)) IsInt = true;
  if (bsy==asy && bey==aey) IsInt = true;
     //alert('a(s,e) b(s,e) = a('+asy+','+aey+')  b('+bsy+','+bey+') Intersect='+IsInt); 
  return IsInt;
}


// --------------------------------------------------------
function WGCalAddEvent(n, tstart, tend, tdeb) 
{
  var evt = new Object;
  var id;
  var cEv;
  var dd = new Date();
//   var Tz =  dd.getTimezoneOffset() * 60;
//    var Tz = -3600;
  var Tz = 0;
  var tstart;
  var tend;
  var id;
  var dstart;
  var dend;
  var vstart;
  var vend;
  var weight;
  var mdays;

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
      Days[id].ev[cEv].n = n;
      Days[id].ev[cEv].curday = id;
      Days[id].ev[cEv].dstart = dstart;
      Days[id].ev[cEv].dend = dend;
      Days[id].ev[cEv].vstart = vstart;
      Days[id].ev[cEv].vend = vend;
      Days[id].ev[cEv].weight = ((vend - vstart) * P_DURATION) - (vstart  * P_DEB);
      Days[id].ev[cEv].mdays = mdays;
      Days[id].ev[cEv].base = -1;
      Days[id].ev[cEv].col = 0;
      Days[id].ev[cEv].ncol = 0;
    }
  }
}

  
function WGCalDisplayAllEvents() {
  var iday; 
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

  var  root = document.getElementById(Root);
  
  foot = 0;
  head = 3;
  cWidth = Wday - (2*head);
  
  startX = Days[cEv.curday].cpos; 
  startY = GetYForTime(cEv.vstart);
  endY = GetYForTime(cEv.vend);

  h = endY - startY;
  if (!cEv.mdays) {
    eE = document.getElementById('evt'+cEv.n); // Event abstract container
    eC = document.getElementById('evtc'+cEv.n); // Card
    if (eC) root.appendChild(eC);
  } else {
    etmp = document.getElementById('evt'+cEv.n); 
    eE = etmp.cloneNode(true);
  }
  eE.style.top = startY+"px";
  // Compute width
  xw = cWidth * cEv.rwidth;
  // Compute X coord
  delta = cWidth / ncol;
  eE.style.left = startX + head + ((cEv.col-1) * delta) + "px";
  eE.style.width = (xw-2)+"px";
  eE.style.height = (h-2)+"px";
  eE.style.position = 'absolute';
  eE.style.display = 'block';

  root.appendChild(eE);
  return;
}
