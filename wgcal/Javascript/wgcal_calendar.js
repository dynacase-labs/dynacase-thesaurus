function WGCalResetSizes() {
//  alert("resize");
//  WGCalComputeCoord();
//  WGCalRefreshAll();
}

var  Root = 'root';
var  IdStart = 0;
var  IdEnd   = 0;
var  XDays = 0;
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
  if (day==0) day = 7;
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
  for (id=0; id<XDays; id++) {
    for (i=0; i<Days[id].ev.length; i++) {
      evtc = document.getElementById('evtc'+Days[id].ev[i].id);
      evtc.style.display = 'none';
    }
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
  subwindow(400, 650, 'EditEvent'+time, urlroot+'&app=WGCAL&action=WGCAL_EDITEVENT&time='+time);	
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
function WGCalViewInit(idstart, idend, xdiv, ydiv, ystart) {
  IdStart   = idstart;
  IdEnd     = idend;	
  XDays = xdiv;
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
  Hhdiv = Hzone / Ydivision;
  Wzone = Xe-Xs;
  Wday = Wzone / XDays;
  Wevt = Wday - 4;
  Wshift = Wday / 5;
 
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

}

// --------------------------------------------------------
function WGCalAddEvent(evtid, dstart, dend, day) 
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

  if (evt.start<Days[day].vstart) evt.start = Days[day].vstart - 3600;
  if (evt.end>Days[day].vend) evt.end = Days[day].vend + 3600;

  pstart   = GetCoordFromDate(evt.start);
  pend     = GetCoordFromDate(evt.end);
  dstartt  = GetCoordFromDate(Days[day].vstart);
  dendt    = GetCoordFromDate(Days[day].vend);

  evt.x = pstart.x;
  evt.y = pstart.y + Ys;
  evt.h = pend.y - pstart.y;
  if (evt.y<dstartt.y) {
    evt.y = dstartt.y;
    evt.h = Math.round(pend.y - dstartt.y + (GetPxForMin()*60));
  }
  if ((evt.h+evt.y)>dendt.y) evt.h = dendt.y - pstart.y;

  evt.w = Wevt;
  evt.s = 0;

  Days[day].ev[cEv] = evt;
  return;
}
  

function WGCalSortEventInDay( e1, e2) {
  return e1.y - e2.y;
}
  

function WGCalComputeDay(day) {
  dEvent = Days[id].ev.sort(WGCalSortEventInDay);
  for (i=0; i<dEvent.length; i++) {
    var rs_x = dEvent[i].x;
    var rs_y = dEvent[i].y;
    var re_x = dEvent[i].x + dEvent[i].w;
    var re_y = dEvent[i].y + dEvent[i].h;
    for (j=1; j<dEvent.length; j++) {
      if (dEvent[i].id == dEvent[j].id) continue;
      var cs_x = dEvent[j].x;
      var cs_y = dEvent[j].y;
      var ce_x = dEvent[j].x + dEvent[j].w;
      var ce_y = dEvent[j].y + dEvent[j].h;
      if (PtInRect(rs_x,rs_y,re_x,re_y,cs_x,cs_y)) {
	dEvent[j].s++;
	if (dEvent[j].s > Days[id].col) {
	  Days[id].col = dEvent[j].s;
	}
      } 
    }
  }
  return dEvent;
}

function WGCalPrintAnEvent(ev) {
  s = '';
  s += ' -- Ev['+ev.id+'] (x1,y1),(x2,y2)=('+ ev.x + ',' + ev.y + '),(' + parseInt(ev.x+ev.w) + ',' + parseInt(ev.y+ev.w) + ') s='+ev.s;
  return s;

}
function WGCalPrintAllEvents() {
  for (id=0; id<XDays; id++) {
    s = 'Day '+id+'\n';
    for (i=0; i<Days[id].ev.length; i++) {
      s += WGCalPrintAnEvent(Days[id].ev[i]);
    }
    alert(s)
  }
}

      

function WGCalDisplayAllEvents() {

  var  root = document.getElementById(Root);
  
  foot = 1;
  head = 5;
  for (id=0; id<XDays; id++) {
    
    dEvent = WGCalComputeDay(id);
    cShift = Days[id].col;
    cWidth = (Wday/6) / cShift;
    
    for (i=0; i<dEvent.length; i++) {
      
      eE = document.getElementById('evt'+dEvent[i].id); // Event container
      eH = document.getElementById('evth'+dEvent[i].id); // Header
      eF = document.getElementById('evtf'+dEvent[i].id); // Footer
      eA = document.getElementById('evta'+dEvent[i].id); // Abstract
      eC = document.getElementById('evtc'+dEvent[i].id); // Comment
      content = dEvent[i].h - foot - head;
      
      eH.style.height = head+"px";
      eF.style.height = foot+"px";
      eA.style.height = content+"px";
      
      eE.style.top = dEvent[i].y+"px";
      eE.style.left = ((dEvent[i].s * cWidth) + dEvent[i].x)+"px";
      eE.style.width = (dEvent[i].w-(cShift*cWidth))+"px";
      eE.style.height = dEvent[i].h+"px";
      eE.style.position = 'absolute';
      eE.style.display = '';
      
      root.appendChild(eE);
      root.appendChild(eC);
    }
  }
  
}

function PtInRect(rx1, ry1, rx2, ry2, px, py) {
  st = false;
  if (px>=rx1 && px<rx2 && py>=ry1 && py<ry2) st = true;
  return st;
}
