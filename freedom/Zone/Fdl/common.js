var isNetscape = navigator.appName=="Netscape";

function viewornot(id) {
  var o=document.getElementById(id);
  if (o) {
    if (o.style.display=='none') o.style.display='';
    else o.style.display='none';
  }
}
