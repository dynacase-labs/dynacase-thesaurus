// --------------------------------------------------------
function Fade(elt, size, css) {
  elt.width += size;  
  elt.height += size;
  elt.className = css;
}
function UnFade(elt, size, css) {
  elt.width -= size;  
  elt.height -= size;
  elt.className = css;
}


// --------------------------------------------------------
function  mynodereplacestr(n,s1,s2) {
  
  var kids=n.childNodes;
  var ka;
  var avalue;
  var regs1;
  var rs1;
  var tmp;
  var attnames = new Array('style', 'title', 'src' , 'onclick', 'href','onmousedown','onmouseout', 'onmouseover','id','name','onchange');
  // for regexp
  rs1 = s1.replace('[','\\[');
  rs1 = rs1.replace(']','\\]');
  regs1 = new RegExp(rs1,'g');
  
  for (var i=0; i< kids.length; i++) {     
    if (kids[i].nodeType==3) { 
      // Node.TEXT_NODE
      
	if (kids[i].data.search(rs1) != -1) {
	  tmp=kids[i].data; // need to copy to avoid recursive replace
	  
	  kids[i].data = tmp.replace(s1,s2);
	}
    } else if (kids[i].nodeType==1) { 
      // Node.ELEMENT_NODE
	
	// replace  attributes defined in attnames array
	  for (iatt in attnames) {
	    
	    attr = kids[i].getAttributeNode(attnames[iatt]);
	    if ((attr != null) && (attr.value != null) && (attr.value != 'null'))  {
	      
	      if (attr.value.search(rs1) != -1) {				
		avalue=attr.value.replace(regs1,s2);

		if (isNetscape) attr.value=avalue;
		else if ((attr.name == 'onclick') || (attr.name == 'onmousedown') || (attr.name == 'onmouseover')) kids[i][attr.name]=new Function(avalue); // special for IE5.5+
		else attr.value=avalue;
	      }
	    }
	  }
      mynodereplacestr(kids[i],s1,s2);
    } 
  }
}

// --------------------------------------------------------
function WGCalImgAltern(ev, eltId, img1, img2) {
  var elt = document.getElementById(eltId);
  var result;
  if (!elt) {
    window.status = "Element["+eltId+"] not found";
    return;
  }
  var sea = new String(elt.src);
  if (sea.indexOf(img1) != -1) {
    elt.src = img2;
  } else {
    elt.src = img1;
  }
}


function mytoto(name, value, target, taction)
 {
   usetparam(name, value, target, taction);
}

// --------------------------------------------------------
function usetparam(name, value, updatetarget, updateaction) 
{
  fset = document.getElementById('usetparam');
  fpname    = document.getElementById('pname');
  fpvalue   = document.getElementById('pvalue');
  taction = document.getElementById('taction');
  fpname.value = name;
  fpvalue.value = value;
  if (updatetarget=='') {
    updatetarget = 'wgcal_hidden';
    updateaction = '/what/index.php?sole=A&app=WGCAL&action=WGCAL_HIDDEN';
  }
  taction.value = updateaction;
  fset.target = updatetarget;
  fset.submit();
}


var isNetscape = navigator.appName=="Netscape";
