function setwrvalert() {
  rf = document.getElementById('alertwrv');
  if (rf.checked) val = 1;
  else val = 0;
  fcalChangeUPref(-1, "WGCAL_U_WRVALERT", val);
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

// ------------------------------------------------------------
// Waiting Event display
// ------------------------------------------------------------

var rvWto = -1;

function composeWaitRvArea(rvl) {

  if (!(rvl.status && rvl.status==200)) return;

  eval(rvl.content);

  var rvlist = document.getElementById('vwaitrv');
  var rvcount = document.getElementById('rvcount');
  rvcount.innerHTML = waitrv.length;

  var iis = rvlist.childNodes.length - 1;
  for (var ii=iis; ii>=0; ii--) rvlist.removeChild(rvlist.childNodes[ii]);

  var wdiv;
  for (var i=0; i<waitrv.length; i++) {
    var id = waitrv[i].id;

    wdiv = document.createElement('div');
    wdiv.setAttribute('id', 'evt'+waitrv[i].id );
    wdiv.className = 'wgcalwaitrv';
    wdiv.innerHTML = ' &bull; <span class="wgcalwaitrvtitle" >'+waitrv[i].title+'</span> <span class="wgcalwaitrvdesc">'+waitrv[i].date+', '+waitrv[i].owner+'</span>';
    fcalAddEvent(wdiv, 'click', 
		 function foo(event) { 
		   viewmenu(event, UrlRoot+'&app=WGCAL&action=WGCAL_GETMENU&ctx=WRV&ue=f&id='+id); 
		   return false; } );
    rvlist.appendChild(wdiv);
  }
  
  var dat = new Date();
  var day = dat.getDate();
  var month = dat.getMonth()+1;
  var year = dat.getFullYear();
  var hour = dat.getHours();
  var min = dat.getMinutes();
  var dstr = pad(day,2,'0')+"/"+pad(month,2,'0')+"/"+year+" "+pad(hour,2,'0')+":"+pad(min,2,'0');
  document.getElementById('todaydate').innerHTML = dstr;

  var rf = document.getElementById('alertwrv');
  if (rf.checked && waitrv.length>0) {
    alert("Vous avez "+waitrv.length+" rendez-vous en attente.");
  }

  rvWto = setTimeout("getWaitingRv()", (5*60*1000));
}
 
function pad(s,l,p) {
  var str = String(s);
  if (str.length<l) str = pad(p+s, l, p);
  return str;
}

function getWaitingRv() {

  if (rvWto!=-1) {
    clearTimeout(rvWto);
    rvWto = -1;
  }

  var urlsend = UrlRoot+"app=WGCAL&action=WGCAL_WAITRV";
  fcalSendRequest(urlsend, true, composeWaitRvArea);

}
