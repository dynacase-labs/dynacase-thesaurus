function WGCalResetSizes() {
//  alert("resize");
//  WGCalComputeCoord();
//  WGCalRefreshAll();
}

var  Root = 'root';
var  IdStart = 0;
var  IdEnd   = 0;
var  Xdivision = 0;
var  Ydivision = 0;
var  Ystart = 0;

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

// -----------------------------------------------------"private"---
function GetPxForMin() {
  return (Hzone / ((Ydivision)*60));  
}


// --------------------------------------------------------
function GetTimeInfoFromTs(ts) {
   var evd = new Date();
   var tinfo = new Object();
   evd.setTime((ts*1000));
   tinfo.day = evd.getDay();
   tinfo.hours = evd.getHours();
   tinfo.minutes = evd.getMinutes();
   return tinfo;
}

// --------------------------------------------------------
function GetCoordFromDate(ts) {
   
  var evt = new Object();
  var tinf = GetTimeInfoFromTs(ts);
  day = tinf.day;
  evt.x = (Wday * (day - 1)) + Xs;
  hmin = ((tinf.hours-Ystart) * 60) + tinf.minutes;
  ypx = GetPxForMin();
  evt.y = (ypx * hmin) + Ys;

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
  for (i=0; i<=EventCount; i++) {
    evtc = document.getElementById('evtc'+Event[i][0]);
    evtc.style.display = 'none';
  }
}
  


// --------------------------------------------------------
function WGCalEvOnMouseOver(ev, id) {
  evt  = document.getElementById('evt'+id);
  evt.style.zIndex = 1000;
  WGCalCleanAllFullView();
  evtc = document.getElementById('evtc'+id);
  x = getX(ev);
  y = getY(ev);
  evtc.style.left = (x+15)+'px';
  evtc.style.top = (y+5)+'px';
  evtc.style.position = 'absolute';
  evtc.style.zIndex = 1001;
  evtc.style.display = '';
}

// --------------------------------------------------------
function WGCalEvOnMouseOut(ev, id) {
  evt  = document.getElementById('evt'+id);
  evt.style.zIndex = 0;
  WGCalCleanAllFullView();
}
 

// --------------------------------------------------------
function WGCalSetDate(calendar)
{  
  var ff = document.getElementById('fdatesel');
  
  var y = calendar.date.getFullYear();
  var m = calendar.date.getMonth();     // integer, 0..11
  var d = calendar.date.getDate();
  var w = calendar.date.getWeekNumber();
  var ts = calendar.date.print("%s");
  
  if (calendar.dateClicked) {
    usetparam("WGCAL_U_CALCURDATE", ts);
    ff.submit();
  }
}


// --------------------------------------------------------
function getElementGeo(e) {
  var geo = new Object();
  geo.x = geo.y = geo.w = geo.h = -1;
  geo.w = e.offsetWidth;
  geo.h = e.offsetHeight;
  geo.x = e.offsetLeft;
  geo.y = e.offsetTop;
  return geo;
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
function AddEvent(urlroot,time) {
  subwindow(300, 500, 'EditEvent'+time, urlroot+'&app=WGCAL&action=WGCAL_EDITEVENT&time='+time);	
}

// --------------------------------------------------------
function ClickCalendarCell(urlroot, nh,times,timee) {
  subwindow(300, 500, 'EditEvent', urlroot+'&app=WGCAL&action=WGCAL_EDITEVENT&evt=-1&nh='+nh+'&ts='+times+'&te='+timee);
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
function WGCalViewInit(idstart, idend, xdiv, ydiv, ystart) {
  IdStart   = idstart;
  IdEnd     = idend;	
  Xdivision = xdiv;
  Ydivision = ydiv;
  Ystart    = ystart;
  WGCalComputeCoord();
}

// --------------------------------------------------------
function WGCalComputeCoord() {
  // compute area coord left/top (Xs,Ys) right/bottom (Xe,Ye)
  var os = getAnchorPosition(IdStart);
  var oe = getAnchorPosition(IdEnd);
  var w = getObjectWidth(document.getElementById(IdEnd));
  var h = getObjectHeight(document.getElementById(IdEnd));
  Xs = os.x;
  Ys = os.y;
  Xe = oe.x + w;
  Ye = oe.y + h;

  Hzone = Ye-Ys;
  Hhdiv = Math.round(Hzone / Ydivision)
  Wzone = Xe-Xs;
  Wday = Math.round(Wzone / Xdivision);
  Wevt = Wday - 4;
  Wshift = Math.round(Wday / 5);
 
  text = ' W zone = '+Wzone+' W day = '+Wday+' W evt = '+Wevt+' Wshift = '+Wshift;  
  nText = document.createElement('div');
  content = document.createTextNode(text);
  nText.appendChild(content);
  nText.style.position = 'absolute';
  nText.style.background = 'yellow';
  nText.style.left = Xs+"px";
  nText.style.top = Ys+"px";
  nText.style.width = Wzone+"px";
  nText.style.height = Hzone+"px";
  nText.style.border = '1px outset #afafaf';
  //document.getElementById(Root).appendChild(nText);
}

// --------------------------------------------------------
var Days = new Array();
function PosToDate(ev) {
  var posd = new Object();
  x = getX(ev);
  y = getY(ev);
  posd.day = Math.round(x/Wday);
  y = Math.round(y/Ydivision);
  sdate = EvTs2String(Days[posd.day-1]);
  alert('Date = '+sdate+' Ydivision='+Ydivision);
  return pdate;
}
// --------------------------------------------------------
function WGCalChangeClass(event, id, refclass, nclass)
{
  var elt = document.getElementById(id);
  if (!elt) return;
  if (elt.className!=refclass) elt.className = nclass;
}


// --------------------------------------------------------
function EvTs2String(ts) {
   var d = new Date();
   d.setTime((ts*1000));
   var ms = [ 'Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jui', 'Jui', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec' ];
   return d.getDay()+'.'+ms[d.getMonth()]+'.'+d.getFullYear()+' '+d.getHours()+':'+d.getMinutes();
}

// --------------------------------------------------------
function GetCurCell(ev) {
  x = getX(ev);
  y = getY(ev);
  cell = new Object();
  cell.x = cell.y = cell.width = cell.height = 0;
  d = Math.round(x/Wday) - 1;
  cell.x = Xs + (d * Wday);
  cell.width = Wday;
  h = Math.round(y / Hhdiv) - 1;
  cell.y = Ys + (h * Hhdiv);
  cell.height = Hhdiv;
  return cell;
}

// --------------------------------------------------------
function AddNewEvent(ev, c) {

  var cell = GetCurCell(ev);
  var geo = getElementGeo(c);

  x = getX(ev);
  y = getY(ev);
  nText = document.createElement('input');
  nText.id = 'test';
  nText.style.left = x+"px";
  nText.style.top = y+"px";
  nText.style.background = 'yellow';
  nText.style.border = '1px solid red';
  nText.style.position = 'absolute';
  document.getElementById(Root).appendChild(nText);
  document.getElementById('test').focus();

//   content = document.createElement('input');
//   nText.style.position = 'absolute';
//   content.style.width = cell.width+"px";
//   content.style.height = cell.height+"px";
//   content.style.border = '0px solid';
//   content.style.background = 'yellow';
//   nText.appendChild(content);
//   nText.style.position = 'absolute';
//   nText.style.left = cell.x+"px";
//   nText.style.top = cell.y+"px";
//   nText.style.width = cell.width+"px";
//   nText.style.height = cell.height+"px"; 
//   nText.style.background = 'yellow';
//   nText.style.border = '1px outset #afafaf';
//   nText.style.overflow = '';
//   document.getElementById(Root).appendChild(nText);

}

// --------------------------------------------------------
function WGCalAddEvent(evtid, dstart, dend) {
  EventCount++;
  Event[EventCount] = new Array();
  for (i=0; i<arguments.length; i++) {
    Event[EventCount][i] = arguments[i];
  }
  WGCalDisplayEvent(EventCount, true);
}
  
// --------------------------------------------------------
function WGCalDisplayEvent(iev, newEvent) {

  var evo = new Object();
  var dd = new Date();

  id     = Event[iev][0];
  dstart = Event[iev][1] + (dd.getTimezoneOffset() * 60);
  dend   = Event[iev][2] + (dd.getTimezoneOffset() * 60);
  if (dend<dstart) {
    t = dend;
    dend = dstart;
    dstart = t;
  }
  var tinfo = GetTimeInfoFromTs(dstart);
  root = document.getElementById(Root);
  evtElt = document.getElementById('evt'+id);
  evtHeadElt = document.getElementById('evth'+id);
  evtFootElt = document.getElementById('evtf'+id);
  evtAbstractElt = document.getElementById('evta'+id);
  evtcElt = document.getElementById('evtc'+id);

  pstart = GetCoordFromDate(dstart);
  pend   = GetCoordFromDate(dend);
  dstartt  = GetCoordFromDate(Days[tinfo.day-1].vstart);
  dendt  = GetCoordFromDate(Days[tinfo.day-1].vend);

  rw = Math.round(Wday/8);
  wi = Wday - 4;

  evo.x = Math.round(pstart.x);

  evo.y = Math.round(pstart.y) + Ys;
  evo.h = Math.round(pend.y - pstart.y);
  if (evo.y<dstartt.y) {
    evo.y = dstartt.y;
    evo.h = Math.round(pend.y - dstartt.y + (GetPxForMin()*60));
  }
  if ((evo.h+evo.y)>dendt.y) evo.h = dendt.y - pstart.y;

  evo.w = Math.round(wi);
  evo.s = 0;


  shift = WGCalComputeShift(evo);
  evo.x = evo.x + (shift*rw);
  evo.y = evo.y;
  evo.h = evo.h;
  evo.w = evo.w - (shift*rw);
  evo.s = shift;

  EvTObjectCount++;
  EvTObject[EvTObjectCount] = evo;

  foot = 1;
  head = 5;
  content = evo.h - foot - head;
	
  evtHeadElt.style.height = head+"px";
  evtFootElt.style.height = foot+"px";
  evtAbstractElt.style.height = content+"px";


  evtElt.style.top = evo.y+"px";
  evtElt.style.left = evo.x+"px";
  evtElt.style.width = evo.w+"px";
  evtElt.style.height = evo.h+"px";
  evtElt.style.position = 'absolute';
  evtElt.style.display = '';

  root.appendChild(evtElt);
  root.appendChild(evtcElt);
}

function PtInRect(rx1, ry1, rx2, ry2, px, py) {
  s = "OUT";
  st = false;
  if (px>=rx1 && px<=rx2 && py>=ry1 && py<=ry2) {
    s = 'IN';
    st = true;
  }
  //alert('Rect[ ('+rx1+','+ry1+') ('+rx2+','+ry2+') ] Pt ('+px+','+py+') => '+s);
  return st;
}

function WGCalComputeShift(evo) {
  var dsh = 0;
  for (i=0; i<=EvTObjectCount; i++) {
    var xs = EvTObject[i].x;
    var ys = EvTObject[i].y;
    var xe = EvTObject[i].x + EvTObject[i].w;
    var ye = EvTObject[i].y + EvTObject[i].h;
     if (PtInRect(xs,ys,xe,ye,evo.x,evo.y) || PtInRect(xs,ys,xe,ye,(evo.x+evo.w),(evo.y+evo.h))) {
	dsh++;
    }
  }
  return dsh;
}

function WGCalPrintObjects() {
  s = 'Event graphic object : \n';
  for (i=0; i<=EvTObjectCount; i++) {
    s += 'o['+i+'] x='+EvTObject[i].x+' y='+EvTObject[i].y+' h='+EvTObject[i].h+' w='+EvTObject[i].w+' s='+EvTObject[i].s+'\n';
  }
  //alert(s);
}
