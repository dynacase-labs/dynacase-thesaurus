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

