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

// Array to save rv event 
var Event = new Array();
var EventCount = -1;

var EvTObject = new Array();
var EvTObjectCount = -1;

var AltIsFixed = 'Float';
var AltCoord = new Object();



function SetAltCoord(alt) {
  
  delta = 50;
  
  ww = getFrameWidth();
  wh = getFrameHeight();
  var h = getObjectHeight(alt);
  var w = getObjectWidth(alt);
  
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
var evd = new Date();
   var tinfo = new Object();
   evd.setTime((ts*1000));
   tinfo.day = parseInt(evd.getDay());
   tinfo.hours = parseInt(evd.getHours());
   tinfo.minutes = parseInt(evd.getMinutes());
   return tinfo;
}

// --------------------------------------------------------
function GetCoordFromDate(ts) {
   
  var evt = new Object();
  var tinf = GetTimeInfoFromTs(ts);

  // X coordinate (day position)
  day = tinf.day;
  if (day==0) day = 7;
  evt.x = (Wday * (day - 1)) + Xs;

  // Y coordinate (hour positionning)
  yp = (( 1 + ((tinf.hours-(Ystart)) * YDivCount)) * YDivMinute ) + tinf.minutes;
  evt.y = (yp * PixelByMinute) + Ys;

  return evt;
}


// --------------------------------------------------------
function WGCalRefreshAll() {
  for (i=0; i<=EventCount; i++) {
    WGCalDisplayEvent(i, false);
  }
}
  
// --------------------------------------------------------
function WGCalCleanAllFullView() {
  for (id=0; id<XDays; id++) {
    for (i=0; i<Days[id].ev.length; i++) {
      evtc = document.getElementById('evtc'+Days[id].ev[i].id);
      evtc.style.display = 'none';
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
  if (TimerOnElt!='') {
    WGCalCleanAllFullView();
    evtc = document.getElementById(TimerOnElt);
    evtc.style.position = 'absolute';
    evtc.style.width = 'auto';
    evtc.style.zIndex = 1001;
    evtc.style.display = '';
    SetAltCoord(evtc);
    evtc.style.left = AltCoord.x+'px';
    evtc.style.top = AltCoord.y+'px';
  }
  ResetSetTimerOnMO();
}

function WGCalEvOnMouseOver(ev, id) {
  TimerMouseX = getX(ev);
  TimerMouseY = getY(ev);
  SetTimerOnMO('evtc'+id);
}

function WGCalEvOnMouseOut(ev, id) {
  ResetSetTimerOnMO();
  WGCalCleanAllFullView();
}
 

// --------------------------------------------------------
function WGCalSetDate(calendar)
{  
  var y = calendar.date.getFullYear();
  var m = calendar.date.getMonth();     // integer, 0..11
  var d = calendar.date.getDate();
  var w = calendar.date.getWeekNumber();
  var ts = calendar.date.print("%s");
  
  if (calendar.dateClicked) {
    usetparam("WGCAL_U_CALCURDATE", ts, 'wgcal_calendar', '[CORE_STANDURL]&app=WGCAL&action=WGCAL_CALENDAR');
  }
}


// --------------------------------------------------------
function getX(e) { 
  var posx = 0; 
  if (!e) var e = window.event;
  if (e.pageX) posx = e.pageX;
  else if (e.clientX) posx = e.clientX + document.body.scrollLeft;
  return posx;
}

// --------------------------------------------------------
function getY(e) { 
  var posy = 0; 
  if (!e) var e = window.event;
  if (e.pageY)  posy = e.pageY;
  else if (e.clientY)  posy = e.clientY + document.body.scrollTop;
  return posy;
}

// --------------------------------------------------------
function ClickCalendarCell(urlroot, nh,times,timee) {
  subwindow(400, 650, 'EditEvent', urlroot+'&app=WGCAL&action=WGCAL_EDITEVENT&evt=-1&nh='+nh+'&ts='+times+'&te='+timee);
}

// --------------------------------------------------------
function OverCalendarCell(ev, elt, lref, cref) {
  WGCalCleanAllFullView();
  elt.className = 'WGCAL_PeriodSelected'; //WGCAL_DayLineOver';
  document.getElementById(lref).className = 'WGCAL_PeriodSelected';
  document.getElementById(cref).className = 'WGCAL_PeriodSelected';
}

// --------------------------------------------------------
function OutCalendarCell(ev, elt, lref, cref, cclass, hourclass, dayclass) {
  elt.className = cclass;
  document.getElementById(lref).className = dayclass;
  document.getElementById(cref).className = hourclass;
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
//   DrawRect(os.x,os.y,w,(hr-gamma),'yellow');
//     DrawRect(Xs,Ys,Wzone,Hzone,'yellow');
}

// --------------------------------------------------------
function WGCalChangeClass(event, id, refclass, nclass)
{
  var elt = document.getElementById(id);
  if (!elt) return;
  if (elt.className!=refclass) elt.className = nclass;
}

// --------------------------------------------------------
function WGCalAddEvent(evtid, dstart, dend) 
{
  if (dstart<Days[0].start) dstart = Days[0].start;
  e = (Days[XDays-1].start + (24*3600)) - 1;
//alert('XDays = '+XDays+' dend>e='+(dend>e?"T":"F"));
  if (dend>e) dend = e;

  istart = GetTimeInfoFromTs(dstart);
  iend   = GetTimeInfoFromTs(dend);

  istart.day--;
  istart.day = (istart.day<0 ? 0 : istart.day);
  istart.day = (istart.day>=XDays ? (XDays-1) : istart.day);

  iend.day--;
  iend.day = (iend.day<0 ? 6 : iend.day);
  iend.day = (iend.day>=XDays ? (XDays-1) : iend.day);

  clone = (istart.day!=iend.day ? true : false);
  for (id=istart.day; id<=iend.day; id++) {
    WGCalAddDaylyEvent(evtid, dstart, dend, istart.day, iend.day, id, clone);
  }
  return;
}
  
// --------------------------------------------------------
function WGCalAddDaylyEvent(evtid, dstart, dend, daystart, dayend, day, clone) 
{
  var dd = new Date();
  var evt = new Array();

  var cEv = Days[day].ev.length;
  Days[day].ev[cEv] = new Object;

  evt.id = evtid;
  if (dend<dstart) {
    t = dend;
    dend = dstart;
    dstart = t;
  }
  evt.start = dstart + (dd.getTimezoneOffset() * 60);
  evt.end = dend + (dd.getTimezoneOffset() * 60);

  if (evt.end==evt.start) {
    evt.start = Days[day].vstart - (YDivMinute * 60);
    evt.end = Days[day].vstart;
  } else {
    if (evt.start<Days[day].vstart || ((day==dayend) && clone) ) {
      evt.start = Days[day].vstart;
    }
    if (evt.end>Days[day].vend || ((day>daystart && day<dayend) && clone)) {
      evt.end = Days[day].vend;  
    }
  }

  pstart   = GetCoordFromDate(evt.start);
  pend     = GetCoordFromDate(evt.end);
  dstartt  = GetCoordFromDate(Days[day].vstart);
  dendt    = GetCoordFromDate(Days[day].vend);

  evt.x = pstart.x;
  evt.y = pstart.y;
  evt.h = pend.y - pstart.y;
  evt.w = Wday;
  evt.s = -1;
  evt.pos = 1;
  evt.displayed = false;
  evt.clone = clone; // Clone number
  Days[day].ev[cEv] = evt;

  return;
}
  

function WGCalSortEventInDay( e1, e2) {
  if (e1.y == e2.y) return e2.h - e1.h;
  return e1.y - e2.y;
}



function WGCalComputeDayLine(day) {

  evs = Days[day].ev.sort(WGCalSortEventInDay);
  var r = new Array();

  if (evs.length==0) return;

  // compute ranges
  for (i=0; i<evs.length; i++) {
    ir = -1;
    y = evs[i].y;
    h = y + evs[i].h;
    for (irv=0; irv<r.length && ir==-1; irv++) {
      if (y>=r[irv].ymin && h<=[irv].ymax) ir=irv;
    }
    if (ir==-1) {
      irx = r.length;
      r[irx] = new Object();
      r[irx].count = 1;
      r[irx].ymin = y;
      r[irx].ymax = h;
      r[irx].evs = new Array();
    }
  }
  // for each range search evs are into
  for (i=0; i<evs.length; i++) {
    y = evs[i].y;
    h = y + evs[i].h;
    for (irv=0; irv<r.length && ir==-1; irv++) {
      if (Intersect(y, h, r[irv].ymin, r[irv].ymax)) {
	r[irv].evs[r[irv].evs.length] = i;
      }
    }
  }
//   for (irv=0; irv<r.length && ir==-1; irv++) {
//     t='['+irv+']';
//     for (e=0; e<r[irv].evs.length; e++) t += r[irv].evs[e]+' ';
//     DrawRect(400+irv*10,r[irv].ymin,50,(r[irv].ymax-r[irv].ymin),'red',t);
//   }

     
  s = '';
  for (i=0; i<evs.length; i++) {
    c = 0;
    y = evs[i].y;
    h = y + evs[i].h;
    evs[i].pos = -1;
    evs[i].count = -1;
    for (irv=0; irv<r.length; irv++) {
      f = false;
      for (e=0; e<r[irv].evs.length && !f; e++) {
// 	if (r[irv].ymin==y && r[irv].ymax==h) continue;
	if (r[irv].evs[e] == i) {
	  if (evs[i].count==-1 || r[irv].evs.length>evs[i].count) {
	    f = true;
	    evs[i].count = r[irv].evs.length;
	    evs[i].pos = e;
	  }
	}
      }
    }
    s += WGCalPrintAnEvent(evs[i])+'\n';
  }
  //alert(s);

  return evs;    

}
  



function WGCalComputeDay(day) {
  
  dEvent = Days[day].ev.sort(WGCalSortEventInDay);
  
  for (i=0; i<dEvent.length; i++) {
    for (j=i; j<dEvent.length; j++) {
      if (i==j) continue;
      if (Intersect( dEvent[i].y, (dEvent[i].y + dEvent[i].h), dEvent[j].y, (dEvent[j].y + dEvent[j].h))) {
	dEvent[j].pos = dEvent[i].pos + 1;
      }
    }
  }
  return dEvent;
}
      
    
    

function Intersect(asy,aey,bsy,bey) {
  st = false;
  if ( (bsy>=asy && bsy<=aey) || (bey>=asy && bey<=aey)) return true;
  if ( (asy>=bsy && asy<=bey) || (aey>=bsy && aey<=bey)) return true;
  return st;
}

function WGCalPrintAnEvent(ev) {
  s = '';
  s += ' -- Ev['+ev.id+'] (x1,y1),(x2,y2)=('+ ev.x + ',' + ev.y + '),(' + parseInt(ev.x+ev.w) + ',' + parseInt(ev.y+ev.w) + ') pos='+ev.pos+ ' count='+ev.count;
  return s;

}
function WGCalPrintAllEvents() {
  for (id=0; id<XDays; id++) {
    s = 'Day '+id+'\n';
    for (i=0; i<Days[id].ev.length; i++) {
      s += WGCalPrintAnEvent(Days[id].ev[i]) + '\n';
    }
    alert(s)
  }
}

      

function WGCalDisplayAllEvents() {

  var  root = document.getElementById(Root);
  
  foot = 1;
  head = 3;
  cWidth = Wday - (2*head);

//     WGCalPrintAllEvents();    
  for (id=0; id<XDays; id++) {

//     dEvent = WGCalComputeDay(id);

    dEvent = WGCalComputeDayLine(id);
    dEvent = Days[id].ev.sort(WGCalSortEventInDay);
    
    for (iev=0; iev<dEvent.length; iev++) {

      cEv = dEvent[iev];
      if (cEv.clone==0) {
	eE = document.getElementById('evt'+cEv.id); // Event container
	eH = document.getElementById('evth'+cEv.id); // Header
	eF = document.getElementById('evtf'+cEv.id); // Footer
	eA = document.getElementById('evta'+cEv.id); // Abstract
	eC = document.getElementById('evtc'+cEv.id); // Comment
	content = cEv.h - foot - head;
	root.appendChild(eC);
	eH.style.height = head+"px";
	eF.style.height = foot+"px";
	eA.style.height = content+"px";
      } else {
	etmp = document.getElementById('evt'+cEv.id); 
	eE = etmp.cloneNode(true);
      }
      eE.style.top = cEv.y+"px";
//       div = (cEv.count == 1 ? 1 : cEv.count - 1);
      div = cEv.count;
     xw = Math.round(cWidth/div);
      shift = xw * cEv.pos;
      eE.style.left = parseInt(cEv.x + head + shift) + "px";
      eE.style.width = xw+"px";
      eE.style.height = cEv.h+"px";
      eE.style.position = 'absolute';
      eE.style.display = '';
	
      root.appendChild(eE);
    }
  }
  return;
}
