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

// -----------------------------------------------------"private"---
function GetPxForMin() {
  return (Hzone / (Ydivision*60));  
}

// --------------------------------------------------------
function GetCoordFromDate(ts) {
 
   var evt = new Object();
   var evd = new Date();

   evd.setTime((ts*1000));
   
   day = evd.getDay();
   evt.x = (Wday * (day - 1)) + Xs;
   hmin = ((evd.getHours()-Ystart) * 60) + evd.getMinutes();
   ypx = GetPxForMin();
   evt.y = (ypx * hmin) + Ys;
   //alert('evt(x,y)=('+evt.x+','+evt.y+')'+' day='+day+' Wday='+Wday+' Xs='+Xs);
    
   return evt;
}


// --------------------------------------------------------
function WGCalRefreshAll() {
  for (i=0; i<=EventCount; i++) {
    WGCalDisplayEvent(i, false);
  }
}
  

// --------------------------------------------------------
function WGCalEvOnMouseOver(ev, id) {
  evt  = document.getElementById('evt'+id);
  evt.style.zIndex = 1000;

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
  evtc = document.getElementById('evtc'+id);
  evt.style.zIndex = 0;
  evtc.style.display = 'none';
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
  alert(urlroot+'&app=WGCAL&action=WGCAL_EDITEVENT&evt=-1&nh='+nh+'&ts='+times+'&te='+timee);
  subwindow(300, 500, 'EditEvent', urlroot+'&app=WGCAL&action=WGCAL_EDITEVENT&evt=-1&nh='+nh+'&ts='+times+'&te='+timee);
}
// --------------------------------------------------------
function OverCalendarCell(ev, elt, lref, cref) {
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
function WGCalAddEvent(evtid, dstart, dend, rg, sz) {
  EventCount++;
  Event[EventCount] = new Array();
  for (i=0; i<arguments.length; i++) {
    Event[EventCount][i] = arguments[i];
  }
  WGCalDisplayEvent(EventCount, true);
}
  
// --------------------------------------------------------
function WGCalDisplayEvent(iev, newEvent) {

  var dd = new Date();
  id     = Event[iev][0];
  dstart = Event[iev][1] + (dd.getTimezoneOffset() * 60);
  dend   = Event[iev][2] + (dd.getTimezoneOffset() * 60);
  rg     = Event[iev][3];
  sz     = Event[iev][4];
  if (dend<dstart) {
    t = dend;
    dend = dstart;
    dstart = t;
  }
  root = document.getElementById(Root);
  evtElt = document.getElementById('evt'+id);
  evtHeadElt = document.getElementById('evth'+id);
  evtFootElt = document.getElementById('evtf'+id);
  evtAbstractElt = document.getElementById('evta'+id);
  evtcElt = document.getElementById('evtc'+id);

  pstart = GetCoordFromDate(dstart);
  pend   = GetCoordFromDate(dend);

//   x = Math.round(pstart.x) + 2;
//   y = Math.round(pstart.y) + Ys;
//   h = Math.round(pend.y - pstart.y);
//   w = Math.round(Wevt);
  rw = Math.round(Wevt);
  //x = Math.round(pstart.x) + (rw);
  x = Math.round(pstart.x);
  y = Math.round(pstart.y) + Ys;
  h = Math.round(pend.y - pstart.y);
  w = Math.round(rw);
  //alert(' YS='+Ys+' x='+x+' y='+y+' h='+h+' w='+w)

  foot = 1;
  head = 5;
  content = h - foot - head;
	
  evtHeadElt.style.height = head+"px";
  evtFootElt.style.height = foot+"px";
  evtAbstractElt.style.height = content+"px";


  evtElt.style.top = y+"px";
  evtElt.style.left = x+"px";
  evtElt.style.width = w+"px";
  evtElt.style.height = h+"px";
  evtElt.style.position = 'absolute';
  evtElt.style.display = '';

  root.appendChild(evtElt);
  root.appendChild(evtcElt);
}

