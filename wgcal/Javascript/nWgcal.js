var evDisplayed = 0;
var evLoaded = new Array();

// Mouse pos
var posM = { x:0, y:0 };

function startEvDisplay(ev, pid) {
  if (evDisplayed!=pid) {
    posM.x = getX(ev);
    posM.y = getY(ev);
    evDisplayed = pid;
    showCalEvent(pid);
  }
}

function cancelEvDisplay(pid) {
  hideCalEvent(pid);
  evDisplayed = 0;
  posM.x = 0;
  posM.y = 0;
}



function getCalEvent(pid) {

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
      }
    }
  }
  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_VIEWEVENT&id="+pid;
  rq.open("GET", urlsend, true);
  rq.send(null);
}

var iif = 0;
function calInfo(t) {
  if (document.getElementById('textinfo')) {
    iif++;
    var nev = document.createElement('div');
    nev.innerHTML = '['+iif+'] '+t;
    document.getElementById('textinfo').appendChild(nev);
  }
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

  if (!document.getElementById(o)) return;
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
  if (document.getElementById(eid)) {
    return;
  }
  var ref = document.getElementById('eventlist');
  var nev = document.createElement('div');
  ref.appendChild(nev);
  with (nev) {
    id = 'EVTC'+pid;
    name = 'EVTC'+pid;
    innerHTML = '<div style="border:3px outset white; color:black; background-color:white"><img src="WGCAL/Images/fcal-wait.gif"> Waiting server...</div>';
    computeDivPosition('EVTC'+pid,20);
  }
  getCalEvent(pid);
  return;  
}

function showCalEvent(pid) {
  
  if (evLoaded[pid]) return;
  evLoaded[pid] = true;
  initCalEvent(pid);
  
  computeDivPosition('EVTC'+pid,20);
}

function hideCalEvent(pid) {
  if (document.getElementById('EVTC'+pid)) {
    evLoaded[pid] = false;
    document.getElementById('EVTC'+pid).style.display = 'none';
  }
}


function modifyEvent(pid) {
  alert('pid='+pid);
}

function wAddEvent(o, e, f, s) {
  calInfo(o,e,f);
  if (o.addEventListener){ o.addEventListener(e,f,s); return true;   } 
  else if (o.attachEvent) { return o.attachEvent("on"+e,f); } 
  else { return false; }
}
