var cp = new ColorPicker('window');

var curRessource = -1;

function pickColor(color) {
  if (curRessource!=-1) {
    idx = getRessourcePos(curRessource);
    if (idx==-1) return;
    ressourceList[idx][1] = color;
    document.getElementById('cp'+curRessource).style.background = color;
    saveRessources();
  }
}
   
function showColorPicker(event, idress) {
  curRessource = idress;
  cp.show('cp'+idress);
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
  tdiv['resspopup'][rid]=[1,1,1,1];
  saveRessources();
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
    removeRessource(rid);
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
  document.getElementById('cp'+rid).style.background = rcolor;
  document.getElementById(rid).className = rstyle;
  storeRessource(rid, rcolor, rstate, ricon, rdescr, rstyle);
}

var CRessId = -1;
var CRessText = '';

function vuvRessource() {
  if (CRessId==-1) {
    alert('[TEXT: invalid ressource id]');
    return;
  }
  idx = getRessourcePos(CRessId);
  if (idx==-1) return;
  if (ressourceList[idx][2] == 1) {
    ressourceList[idx][2] = 0;
    rstyle = 'WGCRessDefault';
  } else {
    ressourceList[idx][2] = 1;
    rstyle = 'WGCRessSelected';
  }
  document.getElementById(CRessId).className = rstyle;
  saveRessources();
  return;
}
function removeRessource() {
  var eltRess;
  if (CRessId==-1) {
    alert('[TEXT: invalid ressource id]');
    return;
  }
  idx = getRessourcePos(CRessId);
//   alert ('ress '+CRessId+' id='+idx);
  if (idx!=-1) {
    ressourceList[idx][0] = -1;
    eltRess = document.getElementById('tr'+CRessId);
    if (!eltRess) return;
    eltRess.parentNode.deleteRow(eltRess.sectionRowIndex);
    saveRessources();
  }
}

   

function saveRessources() {
  var rlist= "";
  for (i=0; i<ressourceList.length;i++) {
    if (ressourceList[i][0] != -1 ) 
      rlist += ressourceList[i][0]+"%"+ressourceList[i][2]+"%"+ressourceList[i][1]+"|";
  }
  usetparam("WGCAL_U_RESSDISPLAYED", rlist, 'wgcal_calendar', 'WGCAL_CALENDAR');
  return;
}


// --------------------------------------------------------

function WGCalChangeVisibility(tool) {
  el = document.getElementById(tool);
  if (el.style.display=='') {
    el.style.display = 'none';
  } else {
    el.style.display = '';
  }
  WGCalSaveToolsVisibility();
  return;
}

function WGCalSaveToolsVisibility() {
  var s='';
  var i=0;
  for (i=0; i<toolList.length; i++) {
    el = document.getElementById(toolList[i]);
    v = (el.style.display == '' ? 1 : 0 );
    s += (s==''?'':'|');
    s +=  toolList[i]+'%'+v;
  }
  usetparam('WGCAL_U_TOOLSSTATE', s, 'wgcal_hidden', 'WGCAL_HIDDEN');
}
                                                                                                                   
 

   

function setwrvalert() {
  rf = document.getElementById('alertwrv');
  if (rf.checked) val = 1;
  else val = 0;
  usetparam("WGCAL_U_WRVALERT", val, '', '');
}
 

function ViewEvent(urlroot, cevent) {
  subwindow(250, 350,'ViewEvent', urlroot+'&app=WGCAL&action=WGCAL_VIEWEVENT&cev='+cevent)
  return;
}

function SetEventState(cevent, state) {
  frm = document.getElementById('eventstate');
  seeev = document.getElementById('evt'+cevent);
  cevid = document.getElementById('cev');
  cevid.value = cevent;
  evst = document.getElementById('st');
  evst.value = state;
  frm.submit();
//   seeev.style.display = 'none';
  document.location.reload(true);
  return;
}

  
 
