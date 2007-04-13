function popdoc(event,url) {

  if (event) event.cancelBubble=true;     
  if (ctrlPushed(event)) {
    subwindow([FDL_HD2SIZE],[FDL_VD2SIZE],'_blank',url);
  } else {

    var dpopdoc = document.getElementById('POPDOC_s');
    var fpopdoc;
    if (! dpopdoc) {
      new popUp([mgeox], [mgeoy], [mgeow], [mgeoh], 'POPDOC', url, 'white', '#00385c', '16pt serif', '[TEXT:mini view]', '[COLOR_B5]', '[CORE_TEXTBGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', '[CORE_BGCOLORALTERN]', true, true, true, true, true, false);
    
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


// create popup for insert div after
function newPopdiv(event,divtitle,x,y,w,h) {

  if (event) event.cancelBubble=true;     
    
    GetXY(event); 
  if (!x) x=Xpos;
  if (!y) y=Ypos;
  if (!w) w=[mgeow];
  if (!h) h=[mgeoh];

    var dpopdiv = document.getElementById('POPDIV_s');
    var fpopdiv;
    if (! dpopdiv) {
      new popUp(x, y, w, h, 'POPDIV', 'zou', '[CORE_BGCOLOR]', '[CORE_TEXTFGCOLOR]', '16pt serif', divtitle, '[COLOR_B5]', '[CORE_TEXTFGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', 'black', true, true, true, true, false, false,true);
    
    } else {
      if ((getObjectTop(dpopdiv) < document.body.scrollTop) || 
	  (getObjectTop(dpopdiv) > (getInsideWindowHeight() +document.body.scrollTop))	){
	// popup is not visible in scrolled window => move to visible part
	movePopup('POPDIV' ,[mgeox], [mgeoy]+document.body.scrollTop);
      } 
      showbox( 'POPDIV');    
  }
    return document.getElementById('POPDIV_c');
}


function postit(url,x,y,w,h) {
		      
  if (!x) x=150;
  if (!y) y=110;
  if (!w) w=300;
  if (!h) h=200;
  var dpostit = document.getElementById('POSTIT_s');
  if (! dpostit) {
    new popUp(x, y, w, h, 'POSTIT', url, '#faff77', '#00385c', '16pt serif', '[TEXT:post it]', 'yellow', '[CORE_BGCOLORALTERN]', 'yellow', 'transparent', '#faff77', true, true, true, true, true, false,true);
    
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
