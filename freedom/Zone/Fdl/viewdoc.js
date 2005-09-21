function popdoc(event,url) {

  if (event) event.cancelBubble=true;     
  if (ctrlPushed(event)) {
    subwindow([FDL_HD2SIZE],[FDL_VD2SIZE],'wpopdoc',url);
  } else {

    var dpopdoc = document.getElementById('POPDOC_s');
    var fpopdoc;
    if (! dpopdoc) {
      new popUp([mgeox], [mgeoy], [mgeow], [mgeoh], 'POPDOC', url, 'white', '#00385c', '16pt serif', '[TEXT:mini view]', '[CORE_FGCOLOR]', '[CORE_TEXTBGCOLOR]', '[CORE_BGCOLORALTERN]', '[CORE_BGCOLORALTERN]', '[CORE_BGCOLORALTERN]', true, true, true, true, true, false);
    
    } else {
      if ((getObjectTop(dpopdoc) < document.body.scrollTop) || 
	  (getObjectTop(dpopdoc) > (getInsideWindowHeight() +document.body.scrollTop))	){
	// popup is not visible in scrolled window => move to visible part
	movePopup('POPDOC' ,[mgeox], [mgeoy]+document.body.scrollTop);
      } 
      changecontent( 'POPDOC' , url );
      showbox( 'POPDOC');

    }
  }
}

function postit(url,x,y,w,h) {
		      
  if (!x) x=150;
  if (!y) y=110;
  if (!w) w=300;
  if (!h) h=200;
  var dpostit = document.getElementById('POSTIT_s');
  if (! dpostit) {
    new popUp(x, y, w, h, 'POSTIT', url, '#faff77', '#00385c', '16pt serif', '[TEXT:post it]', 'yellow', '[CORE_BGCOLORALTERN]', 'transparent', 'transparent', '#faff77', true, true, true, true, true, false,true);
    
  } else {
    if ((getObjectTop(dpostit) < document.body.scrollTop) || 
	(getObjectTop(dpostit) > (getInsideWindowHeight() +document.body.scrollTop))	){
      // popup is not visible in scrolled window => move to visible part
      movePopup('POSTIT' ,250, 210+document.body.scrollTop);
    } 
    changecontent( 'POSTIT' , url );
    showbox( 'POSTIT');
  }
}
function centerError() {
  CenterDiv('error');
}

function refreshParentWindows() {
  if (window.opener && window.opener.document.needreload) window.opener.location.reload();
  else if (parent.flist) parent.flist.location.reload();
  else if (parent.fvfolder) parent.fvfolder.location.reload();
  else if (parent.ffoliolist){
    parent.ffoliolist.location.reload();
    if (parent.ffoliotab) parent.ffoliotab.location.reload();
  }
  
}
function updatePopDocTitle() {

  if (window.parent) {
    var fpopdoc_t= window.parent.document.getElementById('POPDOC_ti');
    if (fpopdoc_t) {
      if (window.document && (window.document.title!="")) {
	fpopdoc_t.innerHTML=window.document.title;
      } else {
	fpopdoc_t.innerHTML="mini vue 3";
      }
    }
  }
}

addEvent(window,"load",updatePopDocTitle);
