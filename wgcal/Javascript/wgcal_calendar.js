function WGCalResetSizes() {
  alert("resize");
  WGCalComputeCoord();
  FevRefreshAll();
}

var  Root = '';
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
var FevEvent = new Array();
var FevEventCount = -1;

// -----------------------------------------------------"private"---
function FevGetPxForMin() {
  return (Hzone / (Ydivision*60));  
}

// --------------------------------------------------------
function FevGetCoordFromDate(ts) {
 
   var evt = new Object();
   var evd = new Date();

   evd.setTime((ts*1000));
   
   day = evd.getDay();
   evt.x = (Wday * (day - 1)) + Xs;

   hmin = ((evd.getHours()-Ystart) * 60) + evd.getMinutes();
   ypx = FevGetPxForMin();
   evt.y = (ypx * hmin) + Ys;
    
   return evt;
}


// --------------------------------------------------------
function FevAddElement(id, elttype, content, style) {
  elt = document.createElement(elttype);
  elt.appendChild(document.createTextNode(content));
  elt.className = style;
  id.appendChild(elt);
  return elt;
}

// --------------------------------------------------------
function FevRefreshAll() {
  for (i=0; i<=FevEventCount; i++) {
    EvtDisplayEvent(i, false);
  }
}
  
// --------------------------------------------------------
function FevAddEvt(id, evtid, dstart, dend, owner, title, description, gico, rico, pico, tbcico, attendees, style, shl) {
  FevEventCount++;
  FevEvent[FevEventCount] = new Array();
  for (i=0; i<arguments.length; i++) {
    FevEvent[FevEventCount][i] = arguments[i];
  }
  FevDisplayEvent(FevEventCount, true);
}
  
function FevDisplayEvent(iev, newEvent) {

  id = FevEvent[iev][0];
  evtid = FevEvent[iev][1];
  dstart  = FevEvent[iev][2];
  dend  = FevEvent[iev][3];
  owner  = FevEvent[iev][4];
  title  = FevEvent[iev][5];
  description  = FevEvent[iev][6];
  gico  = FevEvent[iev][7];
  rico  = FevEvent[iev][8];
  pico  = FevEvent[iev][9];
  tbcico  = FevEvent[iev][10];
  attendees  = FevEvent[iev][11];
  style  = FevEvent[iev][12];
  shl = FevEvent[iev][13];
  
  if (dend<dstart) {
	t = dend;
	dend = dstart;
	dstart = t;
  }

  root = document.getElementById(id);

  if (newEvent) {
    descr = document.getElementById('evtdescr');
    ndescr = descr.cloneNode(true);
    ndescr.id = evtid;
    FevEvent[iev][0] = evtid;
  } else {
    ndescr = document.getElementById(id);
  }

  pstart = EvGetCoordFromDate(dstart);
  pend   = EvGetCoordFromDate(dend);

  x = Math.round(pstart.x) + (shl*Wshift);
  y = Math.round(pstart.y);
  h = Math.round(pend.y - pstart.y);
  w = Math.round(Wevt);

  ndescr.style.top = y+"px";
  ndescr.style.left = x+"px";
  ndescr.style.width = w+"px";
  ndescr.style.height = h+"px";

  if (newEvent) {
    ndescr.style.position = 'absolute';
    ndescr.className = "evt";
    ndescr.style.background = style;
    ndescr.style.display = '';
  
    if (gico+rico+pico+tbcico>0) {
      imgs = document.createElement('div');
      if (pico) {
          e = AddImage(EvGetIcon("private"), "Prive", Hicon, Wicon);
	  imgs.appendChild(e);
      }
      if (gico) {
        e = AddImage(EvGetIcon("group"), "Groupe", Hicon, Wicon);
        imgs.appendChild(e);
      }
      if (rico) {
     e = AddImage(EvGetIcon("repeat"), "Repetable", Hicon, Wicon);
     imgs.appendChild(e);
        }
        if (tbcico) {
     e = AddImage(EvGetIcon("tbd"), "A confirmer", Hicon, Wicon);
	     imgs.appendChild(e);
         }
      ndescr.appendChild(imgs);
    }
  
    content = document.createTextNode(title);
    ndescr.appendChild(content);
  
    root.appendChild(ndescr);

    ldescr = document.getElementById('evtlongdescr');
    lndescr = ldescr.cloneNode(true);
    lndescr.id = 'ldescr'+evtid;
    ix = AddElement(lndescr, 'div', EvTs2String(dstart)+" - "+EvTs2String(dend), ''); 
    ix = AddElement(lndescr, 'div', owner, ''); 
    ix = AddElement(lndescr, 'div', title, ''); 
    ix = AddElement(lndescr, 'div', description, ''); 
    ix = AddElement(lndescr, 'div', attendees, ''); 
    lndescr.className = "evtlongdescr";
    lndescr.style.width = "160px";
    lndescr.style.position = 'absolute';
  
    root.appendChild(lndescr);
  }

}



// --------------------------------------------------------
function RvEVTOnBlur(ev, id) {

}

// --------------------------------------------------------
function RvEVTOnMouseOver(ev, id) {
  elt = document.getElementById(id);
  elt.style.zIndex = 3;
  CalDisplayGroup(ev, 'ldescr'+id, 'r', 10,10,180,0);
}

// --------------------------------------------------------
function RvEVTOnMouseOut(ev, id) {
  elt = document.getElementById(id);
  elt.style.zIndex = 0;
  CalHideGroup(ev, 'ldescr'+id);
}
 
// --------------------------------------------------------
function RvEVTOnClick(ev, id) {
 alert('RvEVTOnClick');
}

// --------------------------------------------------------
function RvEVTOnDblClick(ev, id) {
 alert('Edition du bordel...');
}



// --------------------------------------------------------
function WGCalSetDate(calendar)
{  
      var ff = document.getElementById('fdatesel');
      var eltD = document.getElementById('indate');
  
      var y = calendar.date.getFullYear();
      var m = calendar.date.getMonth();     // integer, 0..11
      var d = calendar.date.getDate();
      var w = calendar.date.getWeekNumber();
      var ts = calendar.date.print("%s");
  
      if (calendar.dateClicked) {
       /*        alert("["+ts+"] Year : "+y+" Month : "+m+" Day : "+d+" Week : "+w); */
       eltD.value = ts;
       ff.submit();
      }
}


// --------------------------------------------------------
function WGCalAlternCss() {


}

// --------------------------------------------------------
function WGCalImgAltern(ev, eltId, img1, img2) {
  var elt = document.getElementById(eltId);
  var result;
  if (!elt) {
    window.status = "Element["+eltId+"] not found";
    return;
  }
  var sea = new String(elt.src);
  if (sea.indexOf(img1) != -1) {
    elt.src = img2;
  } else {
    elt.src = img1;
  }
}

// --------------------------------------------------------
function WGCalShowHideElt(ev, eltId) {
  var elt = document.getElementById(eltId);
  if (!elt) {
    window.status = "Element["+eltId+"] not found";
    return; 
  } 
  if ( elt.style.display == 'none' ) {
    elt.style.display = '';
  } else {
    elt.style.display = 'none';
  } 
}

// --------------------------------------------------------
function WGCalChangeClass(event, id, refclass, nclass)
{
  var elt = document.getElementById(id);
  if (!elt) return;
  if (elt.className!=refclass) elt.className = nclass;
}

// --------------------------------------------------------
function WGCUpdateDisplay(ev, calid, formid, color, classSelect, classOver, classOut) {
  var elt = document.getElementById(calid);
  var ff = document.getElementById(formid);
  ff.rid.value = -1;
  ff.sid.value = 0;
  if (!elt || !ff) return;
  if (elt.className == classOut || elt.className == classOver) {
    elt.className = classSelect;
    ff.rid.value = calid;
    ff.sid.value = 1;
  } else {
    elt.className = classOver;
    ff.rid.value = calid;
    ff.sid.value = 0;
  }
  ff.submit();
}


// ---------------------------------------------------
// REALLY USED
// ---------------------------------------------------

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
function ClickCalendarCell(ev, cell) {
  return;
}
// --------------------------------------------------------
function DblClickCalendarCell(ev, cell, urlroot, time) {
  //AddEvent(urlroot,time);
  AddNewEvent(ev, cell);
  
}
// --------------------------------------------------------
function OverCalendarCell(ev, elt, lref, cref) {
  elt.className = 'WGCAL_DayLineOver';
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
function WGCalViewInit(root, idstart, idend, xdiv, ydiv, ystart) {
  Root = root;	
  IdStart = idstart;
  IdEnd   = idend;	
  Xdivision = xdiv;
  Ydivision = ydiv;
  Ystart = ystart;
  WGCalComputeCoord();
}
// --------------------------------------------------------
function WGCalComputeCoord() {
  // compute area coord left/top (Xs,Ys) right/bottom (Xe,Ye)
  os = getAnchorPosition(IdStart);
  oe = getAnchorPosition(IdEnd);
  w = getObjectWidth(document.getElementById(IdEnd));
  h = getObjectHeight(document.getElementById(IdEnd));
  Xs = os.x;
  Ys = os.y;
  Xe = oe.x + w;
  Ye = oe.y + h;

  Hzone = Ye-Ys;
  Hhdiv = Math.round(Hzone / Ydivision)
  Wzone = Xe-Xs;
  Wday = Math.round(Wzone / Xdivision);
  Wevt = Math.round(Wday / 2);
  Wshift = Math.round(Wday / 20);
 
  text = ' W zone = '+Wzone+' W day = '+Wday+' W evt = '+Wevt+' Wshift = '+Wshift;  
  //  alert(text);
//   nText = document.createElement('div');
//   content = document.createTextNode(text);
//   nText.appendChild(content);
//   nText.style.position = 'absolute';
//   nText.style.left = Xs+"px";
//   nText.style.top = Ys+"px";
//   nText.style.width = Wzone+"px";
//   nText.style.height = Hzone+"px";
//   nText.style.border = '1px outset #afafaf';
//   document.getElementById(Root).appendChild(nText);
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
function EvTs2String(ts) {
   var d = new Date();
   d.setTime((ts*1000));
   var ms = [ 'Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jui', 'Jui', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec' ];
   return d.getDay()+'.'+ms[d.getMonth()]+'.'+d.getFullYear()+' '+d.getHours()+':'+d.getMinutes();
}

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

  cell = GetCurCell(ev);
  geo = getElementGeo(c);

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
