var isNetscape = navigator.appName=="Netscape";

function viewornot(id) {
  var o=document.getElementById(id);
  if (o) {
    if (o.style.display=='none') o.style.display='';
    else o.style.display='none';
  }
}

// serach element in array
// return index found (-1 if not)
function array_search(elt,ar) {
  for (var i=0;i<ar.length;i++) {
    if (ar[i]==elt) return i;
  }
  return -1;
}

// only for mozilla
function moz_unfade(dvid) {
  var f;
  var dv=document.getElementById(dvid);  
  if (dv && dv.style.MozOpacity) {
    f=parseFloat(dv.style.MozOpacity);
    if (f < 1) {
      dv.style.MozOpacity=f+0.02;
      
      setTimeout('moz_unfade(\''+dvid+'\')',10);
    } 
  }
}

// Utility function to add an event listener
function addEvent(o,e,f){
	if (o.addEventListener){ o.addEventListener(e,f,true); return true; }
	else if (o.attachEvent){ return o.attachEvent("on"+e,f); }
	else { return false; }
}
