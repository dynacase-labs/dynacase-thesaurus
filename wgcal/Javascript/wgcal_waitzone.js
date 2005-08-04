function setwrvalert(target, action) {
  rf = document.getElementById('alertwrv');
  if (rf.checked) val = 1;
  else val = 0;
  usetparam(-1, "WGCAL_U_WRVALERT", val,target, action) ;
}


function ViewEvent(urlroot, cevent) {
  subwindow(250, 350,'ViewEvent', urlroot+'&app=WGCAL&action=WGCAL_VIEWEVENT&cev='+cevent)
  return;
}

function SetEventState(cevent, state, target, action) {
  var frm = document.getElementById('feventstate');
  frm.cev.value = cevent;
  frm.st.value = state;
  frm.ra.value = action;
  frm.target = target;
  frm.submit();
  return;
}

  
 
