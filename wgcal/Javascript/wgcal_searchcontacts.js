
var sFam = new Array;
var cHandler = "insertContact";
var cclass = 'WGCRessDefault';
var STimeOut = 400;
var SResultPosLeftShift = 20;
var SResultPosTopShift = 40;
var SearchMinLength = 2;
var rq = false;

function insertContact(domid, isInter, id, title, iconsrc) {
  var col = 'transparent';
  var st = -1;
  if (isInter) {
    col = 'red';
    st = 0;
  }
  addRessource(id, title, iconsrc, st, 'nouveau', col,  true, false);
  if (document.getElementById(domid)) {
    var e = document.getElementById(domid);
    var pe = e.parentNode;
    pe.removeChild(e);
    var ic = 0;
    for (var ip=0; ip<pe.childNodes.length; ip++) {
      if (pe.childNodes[ip].nodeName=='DIV') ic++;
    }
    if (ic==1) pe.style.display='none';
  }
}

function addSFamilie(idf) {
  for (var ix=0; ix<sFam.length; ix++) {
    if (sFam[ix]==idf) return true;
  }
  sFam[sFam.length] = idf;
  return true;
}

function removeSFamilie(idf) {
  if (sFam.length==1) return false;
  for (var ix=0; ix<sFam.length; ix++) {
    if (sFam[ix]==idf) {
      sFam.splice(ix,1);
      return true;
    }
  }
  return true;
}

var sfTimer = -1;

function searchSFamilie(evt, bUrl, force) {

  if (sfTimer!=-1) clearTimeout(sfTimer);
  sfTimer = -1;

  evt = (evt) ? evt : ((event) ? event : null );
  var cc = (evt.keyCode) ? evt.keyCode : evt.charCode;
  if (cc<48||cc>90) return true;

  var result = document.getElementById('sfamres');
  result.innerHTML = '';
  result.style.display = 'none';
  
  if (sFam.length==0) return true;

  var stext = document.getElementById('sFamText');
  if (stext.value.length<SearchMinLength) return true;


  if (cc==13 || force) {
    var fvl = document.getElementById('fgetiuser');
    var val = document.getElementById('itext');
    var fval = document.getElementById('ifam');
    val.value = stext.value;
    fval.value = sFam.join("|");
    fvl.submit();
    if (stext) stext.value = '';
    return false;
  }

  sfText = stext.value;
  sfMode = (document.getElementById('sMode').checked ? 'C' : 'B' );
  sfFams = sFam.join("|");

  sfTimer = setTimeout("runSearchSFamilie('"+bUrl+"','"+sfFams+"','"+sfText+"','"+sfMode+"')", STimeOut);
  return true;
}  

function runSearchSFamilie(burl, f, t, m) {

  var url = burl + "app=WGCAL&action=WGCAL_SEARCHCONTACTS&sfam="+f+"&stext="+t+"&cmode=W&smode="+m+"&cfunc="+cHandler+'&iclass='+cclass;
//    alert('Go('+sfTimer+'):url='+url); return;
  var result = document.getElementById('sfamres');
  result.innerHTML = '';
  result.style.display = 'none';
  
  if (!rq) {
    try {
      rq = new XMLHttpRequest();
    } catch (e) {
      rq = new ActiveXObject("Msxml2.XMLHTTP");
    }
  } else {
    rq.abort();
  }

  if (f=='' || t=='' || m=='') return;


  var po = getAnchorPosition('sFamText');
  var pwi = getObjectWidth(document.getElementById('sFamText'));

  rq.onreadystatechange =  function() {
    if (rq.readyState == 4) {
      result.innerHTML = '';
      result.style.display = 'none';
      if (rq.responseText && rq.status==200) {
	if (rq.responseText.length>1) {
	  result.style.display = '';
	  result.innerHTML = rq.responseText;
	  result.style.left = parseInt(po.x + pwi); //(po.x + SResultPosLeftShift); 
	  result.style.top = parseInt(po.y); //(po.y + SResultPosTopShift); 
	} else {
	  result.style.display = 'none';
	}
      }
    }
  }

  rq.open("GET", url, true);
  rq.send(null);
 
  return true;

}
