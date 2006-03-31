var evDisplayed = 0;
var evLoaded = new Array();

// Mouse pos
var posM = { x:0, y:0 };

function startEvDisplay(ev, pid) {
  if (document.getElementById('evt'+pid)) {
    var eir = document.getElementById('evt'+pid);
    eir.style.background  = 'red';
    wAddEvent(eir, 'click', "modifyEvent("+pid+")", true);
  } else {
    calInfo('evt'+pid+' pas trouvé!');
  }
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
  var urlsend = "http://sn.marc.i-cesam.com/freedom/index.php?sole=Y&app=WGCAL&action=WGCAL_VIEWEVENT&id="+pid;
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
    innerHTML = '<span style="color:white; background-color:red">Waiting server...</span>';
    style.display = 'block';
    style.position = 'absolute';
  }
  getCalEvent(pid);
  return;  
}

function showCalEvent(pid) {
  
  if (evLoaded[pid]) return;
  evLoaded[pid] = true;
  initCalEvent(pid);

  var ref = document.getElementById('EVTC'+pid);

  var ww = getFrameWidth();
  var wh = getFrameHeight();
  var h = getObjectHeight(ref);
  var w = getObjectWidth(ref);

  var x = posM.x + 10;
  var y = posM.y + 10;

  ref.style.display = 'block';
  ref.style.position = 'absolute';

//    calInfo("Frame w="+ww+",h="+wh+" Mouse x="+posM.x+",y="+posM.y+"   Elt w="+w+",h="+h);

  ref.style.left = x+'px';
  ref.style.top = y+'px';
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
