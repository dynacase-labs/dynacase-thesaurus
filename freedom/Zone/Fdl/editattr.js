var INPROGRESSATTR=false;
var ATTRCIBLE=null;
var ATTRREADCIBLE=null; // the element  replaced by input
var INPUTINPROGRESS=false; // true when an input is already done
var ATTRREQ=null;
var DIVATTR=document.createElement("div");


function reqEditAttr() {
  INPROGRESSATTR=false; 
  document.body.style.cursor='auto';
  var o=ATTRCIBLE;
 
  if (ATTRREQ.readyState == 4) {
    // only if "OK"
    if (ATTRREQ.status == 200) {
      // ...processing statements go here...
      if (ATTRREQ.responseXML) {
	reqNotifyEditAttr(ATTRREQ.responseXML);

      } else {
	alert('no xml\n'+ATTRREQ.responseText);
	return;
      } 	  
    } else {
      alert("There was a problem retrieving the XML data:\n" +
	    ATTRREQ.statusText);
      return;
    }
  } 
}
function reqNotifyEditAttr(xmlres) {
  var o=ATTRCIBLE;
  if (xmlres) {
    var elts = xmlres.getElementsByTagName("status");

    if (elts.length == 1) {
      var elt=elts[0];
      var code=elt.getAttribute("code");
      var delay=elt.getAttribute("delay");
      var c=elt.getAttribute("count");
      var w=elt.getAttribute("warning");
      var f=elt.getAttribute("focus");

      if (w != '') alert(w);
      if (code != 'OK') {
	//	    alert('code not OK\n'+ATTRREQ.responseText);
	if (ATTRREADCIBLE) ATTRREADCIBLE.style.display='';	    
	if (o) o.style.display='none';
	return;
      }
      elts = xmlres.getElementsByTagName("branch");
      elt=elts[0].firstChild.nodeValue;
      // alert(elt);
      if (o) {
	if (ATTRREADCIBLE) ATTRREADCIBLE.style.display='none';
	if (c > 0) o.style.display='';
	o.style.left = 0;
	o.style.top  = 0;
	o.innerHTML=elt;
	elt=document.getElementById(f);
	if (elt) {
	  elt.focus();
	  INPUTINPROGRESS=true;
	} else {
	  INPUTINPROGRESS=false;	  
	}
      }	
      var actions=xmlres.getElementsByTagName("action");	  
      var actcode=new Array();
      var actarg=new Array();
      for (var i=0;i<actions.length;i++) {
	actcode[i]=actions[i].getAttribute("code");
	actarg[i]=actions[i].getAttribute("arg");
      }
      if (window.receiptActionNotification) window.receiptActionNotification(actcode,actarg);
      if (window.parent && window.parent.receiptActionNotification) window.parent.receiptActionNotification(actcode,actarg);
      if (window.opener && window.opener.receiptActionNotification) window.opener.receiptActionNotification(actcode,actarg);
      ATTRCIBLE=false;
      if (! INPUTINPROGRESS) ATTRREADCIBLE=false;
	  
    } else {
      alert('no status\n'+ATTRREQ.responseText);
      return;
    }
      
  } 
}


function attributeSendAsync(event,menuurl,cible,newval) {
  if (INPROGRESSATTR) return false; // one request only
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        ATTRREQ = new XMLHttpRequest(); 
    } else if (window.ActiveXObject) {
      // branch for IE/Windows ActiveX version
      ATTRREQ = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (ATTRREQ) {
        ATTRREQ.onreadystatechange = reqEditAttr ;

        ATTRREQ.open("POST", menuurl,true); 
	ATTRREQ.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
	ATTRCIBLE=cible;

	if (newval) ATTRREQ.send('value='+escape(newval));
	else ATTRREQ.send('');
	
	
	INPROGRESSATTR=true;
	document.body.style.cursor='progress';	
	return true;
    }    
}

function attributeSend(event,menuurl,cible,newval) {
  if (INPROGRESSATTR) return false; // one request only
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        ATTRREQ = new XMLHttpRequest(); 
    } else if (window.ActiveXObject) {
      // branch for IE/Windows ActiveX version
      ATTRREQ = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (ATTRREQ) {
      //ATTRREQ.onreadystatechange = reqEditAttr ;

        ATTRREQ.open("POST", menuurl,false); 
	ATTRREQ.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
	ATTRCIBLE=cible;

	if (newval) ATTRREQ.send('value='+escape(newval));
	else ATTRREQ.send('');
	
	
	INPROGRESSATTR=false;
	
	if(ATTRREQ.status == 200) {
	   
	  if (ATTRREQ.responseXML) reqNotifyEditAttr(ATTRREQ.responseXML);
	  else {
	    alert('no xml\n'+ATTRREQ.responseText);
	    return;
	  } 
	}
	
	return true;
    }    
}

function editattr(event,docid,attrid,cible) {

  var w,h;
  if (cible) {
    if (INPUTINPROGRESS) {
      if (ATTRREADCIBLE) ATTRREADCIBLE.style.display='';
    }
    cible.parentNode.insertBefore(DIVATTR,cible);
    ATTRREADCIBLE=cible;
    w=getObjectWidth(ATTRREADCIBLE);
    if (w < 200) w=200;
    DIVATTR.style.width=w;
    h=getObjectHeight(ATTRREADCIBLE);
    if (h < 20) h=20;
    
    DIVATTR.style.height=h;
    DIVATTR.innerHTML='progress...';
    ATTRREADCIBLE.style.display='none';
    DIVATTR.style.display='';
  }

  var menuurl='index.php?sole=Y&app=FDL&action=EDITATTRIBUTE&docid='+docid+'&attrid='+attrid;
  attributeSend(event,menuurl,DIVATTR);
}
function modattr(event,docid,attrid,newval) {

 
    DIVATTR.innerHTML='';
    DIVATTR.style.display='none';
  
    var menuurl='index.php?sole=Y&app=FDL&action=MODATTRIBUTE&docid='+docid+'&attrid='+attrid;

    attributeSend(event,menuurl,ATTRREADCIBLE,newval);
}
function cancelattr(event,docid,attrid) { 
    DIVATTR.innerHTML='';
    DIVATTR.style.display='none';
  
    var menuurl='index.php?sole=Y&app=FDL&action=MODATTRIBUTE&docid='+docid+'&attrid='+attrid;

  attributeSend(event,menuurl,ATTRREADCIBLE);
}

function textautovsize(event,o) {
  if (! event) event=window.event;

  var i=1;
  var hb=o.clientHeight;
  var hs=o.scrollHeight;

  if (hs > hb) {
    o.parentNode.style.height=hs;
  }
  
}
