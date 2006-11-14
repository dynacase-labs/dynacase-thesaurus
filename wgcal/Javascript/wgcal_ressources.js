function fcalUpdateCalendar(forcereload) {
  globalcursor('progress'); 
  var e;
  try {
    window.parent.wgcal_calendar.fcalReloadEvents();
  } catch(e) {
    alert('a marche pas... '+window.parent.id+' error='+e);
  };
  unglobalcursor();  
}

function fcalRessourceIsDisplayed(id) {
  for (var ii=0; ii<calRessources.length; ii++) {
    if (calRessources[ii].id==id) return calRessources[ii].displayed;
  }
  return false;
}

function fcalShowHideRessource(id) {
  for (var ii=0; ii<calRessources.length; ii++) {
    if (calRessources[ii].id==id) {
      calRessources[ii].displayed = (calRessources[ii].displayed?false:true);
      return ii;
    }
  }      
  return -1;
}

function fcalSetRessourceColor(id, color) {
  for (var ii=0; ii<calRessources.length; ii++) {
    if (calRessources[ii].id==id) {
      calRessources[ii].color = color;
      fcalSaveRessources();
      if (calRessources[ii].displayed) fcalUpdateCalendar();
      return true;
    }
  }
  return true;
}

function fcalGetRessource(id) {
  var ir = -1;
  for (var ii=0; ii<calRessources.length; ii++) {
    if (calRessources[ii].id==id) return ii;
  }
  return ir;
}

function fcalDeleteRessource(id) {
  var idp=fcalGetRessource(id);
  if (idp>-1) {
    var ud = calRessources[idp].displayed;
    if (calRessources[idp].adselected)  {
      document.getElementById('agd'+me.id).checked = true;
      calCurrentEdit = { id:me.id, title:me.title, color:document.getElementById('cp'+me.id).style.backgroundColor };
      fcalChangeUPref(-1, 'WGCAL_U_DCALEDIT', me.id);
    }
    calRessources.splice(idp, 1);
    fcalSaveRessources();
    if (ud) fcalUpdateCalendar();
  }
  return idp;
}

function fcalSaveRessources() {
  var rlist = '';
  for (var ii=0; ii<calRessources.length; ii++) {
    rlist += (rlist==''?'':'|') 
      + calRessources[ii].id+'%'+(calRessources[ii].displayed?1:0)+'%'+calRessources[ii].color;
  }
  fcalChangeUPref(-1, 'WGCAL_U_RESSDISPLAYED', rlist);
}
