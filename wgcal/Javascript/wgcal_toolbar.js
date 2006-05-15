// Color selection
var curRessource = -1;
function pickColor(color) {
  if (curRessource!=-1) {
    fcalSetRessourceColor(curRessource, color);
    document.getElementById('cp'+curRessource).style.background = color;
  }
}
   

// Ressource picker 
var picker = null;
window.onunload = killwins;
function killwins() {
  if (picker != null) picker.close();
}



function fcalToolbarSetEvState(event,idp,state) {
  fcalSetEventState(event,idp,state, true);
}

// ----------------------------------------------
var ressListChg = true;

 
function addRessource(rid, rtitle, ricon, rstate, rcolor, rselect, ro, agd) {
  var idx = fcalGetRessource(rid);
  if (idx!=-1) return;
  fcalDrawRessource( rtitle, rid, ricon, '#00FFFF', 'WGCRessDefault', false, ro, agd, false);
  fcalSaveRessources();
}

function storeRessource(id, color, display, icon, descr, style, romode, adhave, adselected) {
  calRessources[calRessources.length] = {
    id         : id,
    color      : color,
    displayed  : display,
    icon       : icon,
    label      : descr,
    readonly   : romode,
    adhave     : adhave,
    adselected : adselected
  };
}

function fcalDrawRessource( rdescr, rid, ricon, rcolor, rstyle, rstate, romode, adhave, adselected ) {
  var nTr;
  var tab;

  removeRessource(rid);

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
  if (romode) document.getElementById('imro'+rid).style.display = '';
  else document.getElementById('imro'+rid).style.display = 'none';
  storeRessource(rid, rcolor, rstate, ricon, rdescr, rstyle, romode, adhave, adselected );

  // delegation
  if (adhave) document.getElementById('agd'+rid).style.display='block';
  else document.getElementById('agd'+rid).style.display='none';
  if (adselected) document.getElementById('agd'+rid).checked = true;
  else document.getElementById('agd'+rid).checked = false;
}

function vuvRessource(rid) {
  var rstyle = '';
  fcalShowHideRessource(rid);
  var isDisplayed = fcalRessourceIsDisplayed(rid);
  if (isDisplayed) rstyle = 'WGCRessSelected';
  else rstyle = 'WGCRessDefault';
  document.getElementById(rid).className = rstyle;
  fcalSaveRessources();
  fcalUpdateCalendar();
  return;
}

function showHideAllRess(show) {
  var ir;
  var haveChanged = false;
  for (ir=0; ir<calRessources.length; ir++) {
    if (show==-2) {  // Reverse all
      if (calRessources[ir].display) {
	document.getElementById(calRessources[ir].id).className = 'WGCRessDefault';
	calRessources[ir].displayed = false;
	haveChanged = true;
      } else {
	document.getElementById(calRessources[ir].id).className = 'WGCRessSelected';
	calRessources[ir].displayed = true;
	haveChanged = true;
      }
    } else if (show==-1) {
      document.getElementById(calRessources[ir].id).className = 'WGCRessDefault';
      calRessources[ir].displayed = false;
      haveChanged = true;
    } else if (show==0) {
      document.getElementById(calRessources[ir].id).className = 'WGCRessSelected';
      calRessources[ir].displayed = true;
      haveChanged = true;
    } else if (show>0) {
      if (calRessources[ir].id == show) {
	document.getElementById(calRessources[ir].id).className = 'WGCRessSelected';
	calRessources[ir].displayed = true;
 	haveChanged = true;
      } else {
	document.getElementById(calRessources[ir].id).className = 'WGCRessDefault';
	calRessources[ir].displayed = false;
 	haveChanged = true;
      }
    }
  }
  fcalSaveRessources();
  if (haveChanged) fcalUpdateCalendar();  
  return;
}

function removeRessource(rid) {
  fcalDeleteRessource(rid);
  if (document.getElementById('tr'+rid)) document.getElementById('tr'+rid).parentNode.deleteRow(document.getElementById('tr'+rid).sectionRowIndex);
}

function SaveFrameWidth() {
  var w=getFrameWidth(window);
  usetparam(-1, "WGCAL_U_TOOLBARSZ", w, 'wgcal_hidden', 'WGCAL_HIDDEN');
  return;
}
