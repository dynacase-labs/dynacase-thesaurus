
var sFam = new Array;
var cHandler = "insertContact";
var cclass = 'WGCRessDefault';

function insertContact(domid, famid, id, title, iconsrc) {
  var col = 'transparent';
  var st = -1;
  if (famid==128) {
    col = 'red';
    st = 0;
  }
  addRessource(id, title, iconsrc, st, col, 'nouveau', true, false);
  if (document.getElementById(domid)) {
    var e = document.getElementById(domid);
    e.parentNode.removeChild(e);
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

function searchSFamilie(evt, force) {
  var result = document.getElementById('sfamres');
  result.innerHTML = '';
  result.style.display = 'none';
  if (sFam.length==0) return true;
  var stext = document.getElementById('sFamText');
  if (stext.value.length<2) return true;
  var smode = (document.getElementById('sMode').checked ? 'C' : 'B' );
  var fams = sFam.join("|");
  var url = "/freedom/index.php?sole=Y&app=WGCAL&action=WGCAL_SEARCHCONTACTS&sfam="+fams+"&stext="+stext.value+"&cmode=W&smode="+smode+"&cfunc="+cHandler+'&iclass='+cclass;

  evt = (evt) ? evt : ((event) ? event : null );
  var cc = (evt.keyCode) ? evt.keyCode : evt.charCode;

  if (cc==13 || force) {
    var fvl = document.getElementById('fgetiuser');
    var val = document.getElementById('itext');
    var fval = document.getElementById('ifam');
    val.value = stext.value;
    fval.value = fams;
    fvl.submit();
    if (stext) stext.value = '';
    return false;
  }

  var po = getAnchorPosition('sFamText');
  var rq;
  try {
    rq = new XMLHttpRequest();
  } catch (e) {
    rq = new ActiveXObject("Msxml2.XMLHTTP");
  }
  rq.onreadystatechange =  function() {
    if (rq.readyState == 4) {
      result.innerHTML = '';
      result.style.display = 'none';
      if (rq.responseText && rq.status==200) {
	if (rq.responseText.length>0) {
	  result.innerHTML = rq.responseText;
	  result.innerHTML += '<div align="right" style="border-style: solid none none none; border-width:1px; cursor:pointer;" onclick="this.parentNode.style.display=\'none\'"><img width="14px" src="Images/wm-hide.gif"></div>';
	  result.style.left = po.x; 
	  result.style.top = po.y + 20; 
	  result.style.display = 'block';
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
