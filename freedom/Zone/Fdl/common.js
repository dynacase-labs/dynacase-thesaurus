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
// Utility function to add an event listener
function delEvent(o,e,f){
	if (o.removeEventListener){ o.removeEventListener(e,f,true); return true; }
	else if (o.detachEvent){ return o.detachEvent("on"+e,f); }
	else { return false; }
}



// return value in computed style
// o : the node HTML Object
// attribute name (marginLeft, top, backgroundColor)
function getCssStyle(o,a) {
  var result = 0;
  var sa='';
  var j=0;
  
  if (document.defaultView) {
    var style = document.defaultView;
    var cssDecl = style.getComputedStyle(o, "");
    for (var i=0;i<a.length;i++) {
	  if (a[i]<='Z') {
	    sa+='-';
	    sa+=a[i].toLowerCase();
	  } else {
	    sa+=a[i];
	  }

    } 
    result = cssDecl.getPropertyValue(sa);
  } else if (o.currentStyle) {
    result = o.currentStyle[a];
  } 
  return result;
}
    	
function copy_clip(meintext)
{

 if (window.clipboardData) 
   {
   
   // the IE-manier
   window.clipboardData.setData("Text", meintext);
   alert('copy :'+meintext);
   // waarschijnlijk niet de beste manier om Moz/NS te detecteren;
   // het is mij echter onbekend vanaf welke versie dit precies werkt:
   }
   else if (window.netscape) 
   { 
   
   // dit is belangrijk maar staat nergens duidelijk vermeld:
   // you have to sign the code to enable this, or see notes below 
   netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
   
   // maak een interface naar het clipboard
   var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
   if (!clip) return;
   
   // maak een transferable
   var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
   if (!trans) return;
   
   // specificeer wat voor soort data we op willen halen; text in dit geval
   trans.addDataFlavor('text/unicode');
   
   // om de data uit de transferable te halen hebben we 2 nieuwe objecten nodig   om het in op te slaan
   var str = new Object();
   var len = new Object();
   
   var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
   
   var copytext=meintext;
   
   str.data=copytext;
   
   trans.setTransferData("text/unicode",str,copytext.length*2);
   
   var clipid=Components.interfaces.nsIClipboard;
   
   if (!clip) return false;
   
   clip.setData(trans,null,clipid.kGlobalClipboard);
   
   }
   alert("Following info was copied to your clipboard:\n\n" + meintext);
   return false;
}


function trackMenuKey(event)
{
  var intKeyCode;
  if (isNetscape) {
    intKeyCode = event.keyCode;
    altKey = event.altKey
    ctrlKey = event.ctrlKey
   }  else {
    intKeyCode = window.event.keyCode;
    altKey = window.event.altKey;
    ctrlKey = window.event.ctrlKey
   }
  window.status=intKeyCode + ':'+altKey+ ':'+ctrlKey;

  if (((intKeyCode ==  93))) {
    // Ctrl-V
    openMenu(event,'popupcard',1);
    
    if (event.stopPropagation) event.stopPropagation();
    else event.cancelBubble=true;
    if (event.preventDefault) event.preventDefault();
    else event.returnValue=true;
    return false;
  }
  return true;
}
