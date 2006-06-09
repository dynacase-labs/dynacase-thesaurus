var REQINSERTHTML; // the request

var INSERTINPROGRESS=false;

var THEINSERTCIBLE=false; // object where insert HTML code
var SYNCHRO=false; // send synchro mode

// send generic request
function requestUrlSend(cible,url) {
  if (INSERTINPROGRESS) alert('request aborted');
  if (INSERTINPROGRESS) return false; // one request only

  // branch for native XMLHttpRequest object
  if (window.XMLHttpRequest) {
    REQINSERTHTML = new XMLHttpRequest(); 
  } else if (window.ActiveXObject) {
    // branch for IE/Windows ActiveX version
    isIE = true;
    REQINSERTHTML = new ActiveXObject("Microsoft.XMLHTTP");
  }

  if (REQINSERTHTML) {
      if (! SYNCHRO) REQINSERTHTML.onreadystatechange = XmlInsertHtml;
      
      REQINSERTHTML.open("POST", url, (!SYNCHRO));     
      REQINSERTHTML.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
      //      REQINSERTHTML.setRequestHeader("Content-Length", "0");
      globalcursor('progress');
      THEINSERTCIBLE=cible;
     
 
      REQINSERTHTML.send('');

      if (SYNCHRO) {
	INSERTINPROGRESS=false;
	unglobalcursor();
	if (REQINSERTHTML.status == 200) {	   
	  if (REQINSERTHTML.responseXML) insertXMlResponse(REQINSERTHTML.responseXML)
	  else {
	    alert('no xml\n'+REQINSERTHTML.responseText);
	    return;
	  } 
	}
      } else {
	INSERTINPROGRESS=true;	
	globalcursor('progress');
	clipboardWait(cible);
	return true;
      }
    }
}

function XmlInsertHtml() {
  INSERTINPROGRESS=false; 
  //document.body.style.cursor='auto';
  unglobalcursor();
  if (REQINSERTHTML.readyState == 4) {
    // only if "OK"
    //dump('readyState\n');
    if (REQINSERTHTML.status == 200) {
      // ...processing statements go here...
      //  alert(REQINSERTHTML.responseText);
      if (REQINSERTHTML.responseXML) insertXMlResponse(REQINSERTHTML.responseXML)
      else {
	alert('no xml\n'+REQINSERTHTML.responseText);
	return;
      } 	  
    } else {
      alert("There was a problem retrieving the XML data:\n" +
	    REQINSERTHTML.statusText+' code :'+REQINSERTHTML.status);
      return;
    }
  } 
}

function insertXMlResponse(xmlres) {  
    var o=THEINSERTCIBLE;
    if (xmlres) {
      var elts = xmlres.getElementsByTagName("status");
      if (elts.length == 1) {
	  var elt=elts[0];
	  var code=elt.getAttribute("code");
	  var delay=elt.getAttribute("delay");
	  var c=elt.getAttribute("count");
	  var w=elt.getAttribute("warning");

	  if (w != '') alert(w);
	  if (code != 'OK') {
	    alert('code not OK\n'+REQINSERTHTML.responseText);
	    return;
	  }
	  elts = xmlres.getElementsByTagName("branch");
	  if (elts && (elts.length>0)) {
	    elt=elts[0].firstChild.nodeValue;
	    if (o) {
	      if (c > 0)       o.style.display='';
	      o.innerHTML=elt;
	    }
	  }
	  var actions=xmlres.getElementsByTagName("action");
	  if (actions.length >0) {
	    var actname=new Array();
	    var actdocid=new Array();
	    for (var i=0;i<actions.length;i++) {
	      actname[i]=actions[i].getAttribute("name");
	      actdocid[i]=actions[i].getAttribute("docid");
	    }
	    if (sendActionNotification) sendActionNotification(actname,actdocid);
	  }

	  if (! isNetscape) correctPNG();

	} else {
	  alert('no status\n'+REQINSERTHTML.responseText);
	  return;
	}
      }
}

function clipboardWait(o) {
  if (o) o.innerHTML='<table style="width:100%;height:100%"><tr><td align="center"><img style="width:48px"  src="Images/b_wait.gif"></tr></td></table>';
}
