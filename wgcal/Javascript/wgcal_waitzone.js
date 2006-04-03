function setwrvalert(tg,act) {
  rf = document.getElementById('alertwrv');
  if (rf.checked) val = 1;
  else val = 0;
  usetparam(-1, "WGCAL_U_WRVALERT", val, tg, act);
}


function ViewEvent(urlroot, cevent) {
  subwindow(250, 350,'ViewEvent', urlroot+'&app=FDL&action=IMPCARD&id='+cevent)
  return;
}

function SetEventState(cevent, state) {
  var frm = document.getElementById('feventstate');
  frm.cev.value = cevent;
  frm.st.value = state;
  frm.submit();
  return;
}

  
 
