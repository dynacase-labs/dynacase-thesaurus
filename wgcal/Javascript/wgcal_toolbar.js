var cp = new ColorPicker('window');

var curRessource = -1;

function pickColor(color) {
  if (curRessource!=-1) {
    idx = getRessourcePos(curRessource);
    ressourceList[idx][1] = color;
    document.getElementById('cp'+curRessource).style.background = color;
    document.getElementById('cp'+curRessource).style.background = color;
    saveTmpRessources();
  } else {
    document.getElementById('cpmycolor').style.background = color;
    usetparam("WGCAL_U_MYCOLOR", color, '', '');
 }
}
   
function showColorPicker(event, id, idress) {
  curRessource = idress;
  cp.show(id);
}



var picker = null;

window.onunload = killwins;
function killwins() {
  if (picker != null) picker.close();
}



// ----------------------------------------------
var ressourceList = new Array();

function getRessourcePos(rid) {
  var idx = -1;
  for (i=0; i<ressourceList.length && idx==-1; i++) {
    if (ressourceList[i][0] == rid) idx = i;
  }
  return idx;
}
 
function addRessource(rid, rtitle, ricon, rstate) {
  idx = getRessourcePos(rid);
  if (idx!=-1) return;
  InsertRessource( rtitle, rid, ricon, '#00FFFF', 'WGCRessDefault', 0 );
  saveTmpRessources();
}

function storeRessource(id, color, display, icon, descr, style) {
  idx = getRessourcePos(id);
  if (idx==-1)  {
    idx = ressourceList.length;
    ressourceList[idx] = new Array();
  }
  ressourceList[idx][0] = id;
  ressourceList[idx][1] = color;
  ressourceList[idx][2] = display;
  ressourceList[idx][3] = icon;
  ressourceList[idx][4] = descr;
  ressourceList[idx][5] = style;
}

function deleteRessource(rid) {
  var eltRess;
  idx = getRessourcePos(rid);
  if (idx!=-1) ressourceList[idx][0] = -1;
  eltRess = document.getElementById('tr'+rid);
  if (!eltRess) return;
  eltRess.parentNode.deleteRow(eltRess.sectionRowIndex);
  saveTmpRessources();
}

function showAllRessource() {
  var tt = '';
  for (i=0; i<ressourceList.length;i++) {
    tt += printRessource(i);
  }
  alert(tt);
}
  
function printRessource(i) {
  var m = "";
  if (ressourceList[i][0] != -1 ) {
    m = 'Ressource #'+i+' == ';
    m += ressourceList[i][0]+":"+ressourceList[i][1]+":"+ressourceList[i][2]+":"+ressourceList[i][3]+":"+ressourceList[i][4]+":"+ressourceList[i][5]+"\n";
  }
  return m;
}

function InsertRessource( rdescr, rid, ricon, rcolor, rstyle, rstate ) {
  var nTr;
  var tab;

  idx = getRessourcePos(rid);
  if (idx!=-1) {
    deleteRessource(rid);
  }
  tab = document.getElementById('tabress');
  if (!tab) {
    alert('tabress not defined');
    return;
  }
  with (document.getElementById('trsample')) {
    style.display = '';
    nTr = cloneNode(true);
    style.display = 'none';
  }
  nTr.id = 'tr'+rid;
  mynodereplacestr(nTr, '%RID%', rid);
  mynodereplacestr(nTr, '%RICON%', ricon);
  mynodereplacestr(nTr, '%RDESCR%', rdescr);
  nTr.style.display = '';
  tab.appendChild(nTr);
  storeRessource(rid, rcolor, rstate, ricon, rdescr, rstyle);
  document.getElementById(rid).className = rstyle;
  document.getElementById('cp'+rid).style.background = rcolor;
}

function setRessourceState(rid, setStyle, unsetStyle, memo) {
  if (rid==-1) {
    alert('[TEXT: invalid ressource id]');
    return;
  }
  idx = getRessourcePos(rid);
  if (ressourceList[idx][2] == 1) {
    ressourceList[idx][2] = 0;
    rstyle = unsetStyle;
  } else {
    ressourceList[idx][2] = 1;
    rstyle = setStyle;
  }
  document.getElementById(rid).className = rstyle;
  saveTmpRessources();
  return;
}
   

function saveTmpRessources() {
  var rlist= "";
  for (i=0; i<ressourceList.length;i++) {
    if (ressourceList[i][0] != -1 ) 
      rlist += ressourceList[i][0]+"%"+ressourceList[i][2]+"%"+ressourceList[i][1]+"|";
  }
  usetparam("WGCAL_U_RESSTMPLIST", rlist, 'wgcal_calendar', 'WGCAL_CALENDAR');
  return;
}

function saveRessources() {
  var rlist= "";
  for (i=0; i<ressourceList.length;i++) {
    if (ressourceList[i][0] != -1 ) 
      rlist += ressourceList[i][0]+"%"+ressourceList[i][2]+"%"+ressourceList[i][1]+"|";
  }
  usetparam("WGCAL_U_RESSDISPLAYED", rlist, '', '');
  return;
}

// --------------------------------------------------------
 
function WGCalChangeVisibility(tool, eid) {
  el = document.getElementById(eid);
  if (el.style.display=='') {
    el.style.display = 'none';
    toolIsVisible[tool] = 0;
  } else {
    el.style.display = '';
    toolIsVisible[tool] = 1;
  }
  WGCalSaveToolsVisibility();
  return;
}

function WGCalSaveToolsVisibility() {
  var s='';
  for (i=0; i<countTools; i++) {
    s += (s==''?'':'|');
    s +=  i+'%'+toolIsVisible[i];
  }
  usetparam('WGCAL_U_TOOLSSTATE', s, 'wgcal_hidden', 'WGCAL_HIDDEN');
}
                                                                                                                   
 

   

function setwrvalert() {
  rf = document.getElementById('alertwrv');
  if (rf.checked) val = 1;
  else val = 0;
  usetparam("WGCAL_U_WRVALERT", val, '', '');
}
 

function SetEventState(cevent, state) {
  frm = document.getElementById('eventstate');
  seeev = document.getElementById('evt'+cevent);
  cevid = document.getElementById('cev');
  cevid.value = cevent;
  evst = document.getElementById('st');
  evst.value = state;
  frm.submit();
  seeev.style.display = 'none';
  return;
}

  
  
