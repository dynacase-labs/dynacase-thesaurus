
var flogWarningNeedClick = true; // true => click on zone is needed to close it
var flogDisplayDuration = 5000;   // milli second auto-close delay
var flogWarningColor = '#FF0000'; //
var flogInfosColor = '#00FF00';
var flogBorderStyle = '2px groove'; // border CSS syntax

 var flogZoneX = 0;
 var flogZoneY = 0;
 var flogZoneZ = 40000;
function displayWarningMsg(p) { __flogDisplayMsg(p, 'W'); }
  
function flogSetZonePos(x,y,z) {
  flogZoneX = parseInt(x);
  flogZoneY = parseInt(y);
  flogZoneZ = parseInt(z);
}

function flogSetZoneParam(wClick, delay) {
  flogWarningNeedClick = wClick;
  if (parseInt(delay)>5000) flogDisplayDuration = parseInt(delay); 
}
  
function flogSetZoneStyle(wColor, iColor, zBorderSize, zBorderStyle) {
  flogWarningColor = wColor; //
  flogInfosColor = iColor;
  flogBorderStyle = parseInt(zBorderSize)+'px '+zBorderStyle; // border CSS syntax
}

function flogSendRequest(url) {
  if (!url) return;
  if (url=='') return;

  if (window.XMLHttpRequest) sreq = new XMLHttpRequest();
  else sreq = new ActiveXObject("Microsoft.XMLHTTP");
  
  sreq.open("POST", url, false);
  sreq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  sreq.send('');
  if (sreq.status!=200) {
    alert('Url : ['+url+']\n'
	  +' http return code    : '+sreq.status+'\n'
	  +' Http return content : '+sreq.responseText);
        return false;
  } else {
    return sreq.responseText;
  }
}

function flogGetMsg(mtype) {
  flogMType = mtype;
  var url = UrlRoot+"&app=WGCAL&action=FGETMSG&mtype="+mtype;
  var res = flogSendRequest(url);
  return res;
}

function flogClearMsg(mtype) {
  var url = UrlRoot+"&app=WGCAL&action=FCLEARMSG&mtype="+mtype;
  var res = flogSendRequest(url);
  return;
}

function flogDisplayMsg(mtype) {

  if (!mtype) mtype='I';
  var res = flogGetMsg(mtype) 
  if (res=='') return;
  __flogDisplayMsg(res, mtype);

}

function __flogDisplayMsg(msg, mtype) {

  flogCloseMsgZoneTimeOut();

  if (!document.getElementById('__flogZone')) {
    var mz = document.createElement("div");
    mz.id        = '__flogZone';
    mz.name      = '__flogZone';   
    mz.position  = 'absolute';
    with (mz.style) {
      padding = '1em';
      cursor = 'pointer';
    }
    document.body.appendChild(mz);
  } else {
    var mz = document.getElementById('__flogZone');
  }
  
  var bordercolor, backgroundcolor;
  if (mtype=='W') bordercolor = flogWarningColor;
  else bordercolor = flogInfosColor;
  try {
    backgroundcolor = getAltern(bordercolor, '#000000', 240);
  } catch (e) {
    backgroundcolor = 'yellow';
  }
  with (mz) {
    style.backgroundColor = backgroundcolor;
    style.border = flogBorderStyle+' '+bordercolor;
    style.visibility = 'hidden';
    style.position = 'absolute';
    style.display = 'block';
    style.top  = parseInt(flogZoneX)+'px';
    style.left = parseInt(flogZoneY)+'px';
    style.zIndex = parseInt(flogZoneZ);
    innerHTML = msg;
    style.visibility = 'visible';
  }
  if (mtype=='W' && flogWarningNeedClick) {
    if (mz.addEventListener) { mz.addEventListener("click",flogCloseMsgZone,true); return true;   }
    else if (o.attachEvent) { return mz.attachEvent("onclick",flogCloseMsgZone); }
  } else {
    if (flogTempo!=0) clearTimeout(flogTempo);
    flogTempo = self.setTimeout("flogCloseMsgZoneTimeOut()", flogDisplayDuration);
  }
}

var flogTempo = 0;
var flogMType = 'W';
function flogCloseMsgZoneTimeOut() {
  if (flogTempo!=0) {
    clearTimeout(flogTempo);
    flogTempo = 0;    
    flogCloseMsgZone();
  }
}

function flogCloseMsgZone() {
  flogClearMsg(flogMType);
  if (document.getElementById('__flogZone')) {
    var mz = document.getElementById('__flogZone');
    mz.style.visibility = 'hidden';
  }
}
