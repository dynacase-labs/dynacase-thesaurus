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



function DrawRect(x,y,w,h,c) {
  text = '(x,y,w,h)=('+x+','+y+','+w+','+h+')';
  nText = document.createElement('div');
  content = document.createTextNode(text);
  nText.appendChild(content);
  nText.style.position = 'absolute';
  nText.style.background = c;
  nText.style.left = x+"px";
  nText.style.top = y+"px";
  nText.style.width = w+"px";
  nText.style.height = h+"px";
  nText.style.border = '0px';
  document.getElementById(Root).appendChild(nText);
}

// --------------------------------------------------------
function WGCalComputeCoord() {

  var gamma = 0; //0.25;

  // compute area coord left/top (Xs,Ys) right/bottom (Xe,Ye)
  var os = getAnchorPosition(IdStart);
  var hr = getObjectHeight(document.getElementById(IdStart));
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
  Wevt = Wday;
  Wshift = Wday / 5;
 
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
//   tmpd   = GetTimeInfoFromTs(evt.start);
//   tmpe   = GetTimeInfoFromTs(evt.end);
//   alert('Début day:'+tmpd.day+' h:'+tmpd.hours+' m:'+tmpd.minutes+'\nFin   day:'+tmpe.day+' h:'+tmpe.hours+' m:'+tmpe.minutes);

  pstart   = GetCoordFromDate(evt.start);
  pend     = GetCoordFromDate(evt.end);
  dstartt  = GetCoordFromDate(Days[day].vstart);
  dendt    = GetCoordFromDate(Days[day].vend);

  evt.x = pstart.x;
  evt.y = pstart.y;
  evt.h = pend.y - pstart.y;
  evt.w = Wevt;
  evt.s = 0;
  evt.clone = clone; // Clone number
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
    cWidth = (Wday/6) / (cShift);
    
    for (i=0; i<dEvent.length; i++) {

      if (dEvent[i].clone==0) {
     
	eE = document.getElementById('evt'+dEvent[i].id); // Event container
	eH = document.getElementById('evth'+dEvent[i].id); // Header
	eF = document.getElementById('evtf'+dEvent[i].id); // Footer
	eA = document.getElementById('evta'+dEvent[i].id); // Abstract
	eC = document.getElementById('evtc'+dEvent[i].id); // Comment
	content = dEvent[i].h - foot - head;
	root.appendChild(eC);
	
	eH.style.height = head+"px";
	eF.style.height = foot+"px";
	eA.style.height = content+"px";
	
      } else {

	etmp = document.getElementById('evt'+dEvent[i].id); 
	eE = etmp.cloneNode(true);
      }
      eE.style.top = dEvent[i].y+"px";
      eE.style.left = ((dEvent[i].s * cWidth) + dEvent[i].x + 2)+"px";
      eE.style.width = (dEvent[i].w-(cShift*cWidth))+"px";
      eE.style.height = dEvent[i].h+"px";
      eE.style.position = 'absolute';
      eE.style.display = '';
      
      root.appendChild(eE);
    }
  }
  
}

function PtInRect(rx1, ry1, rx2, ry2, px, py) {
  st = false;
  if (px>=rx1 && px<rx2 && py>=ry1 && py<ry2) st = true;
  return st;
}
