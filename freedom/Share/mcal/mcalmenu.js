
// This source IS NOT DISTRIBUTED UNDER FREE LICENSE (like GPL or Artistic...)
// For any usage -commercial, private or other- you have to pay Marc Claverie.


// --------------------------------------------------------------------------------------
// Initialize menu 'mid', menu contains item description
//
// mid : alphnum string
// menu : array of object { label, status, type, icon, onmouse, amode, atarget, ascript, aevent }
//
// label    : item displayed in menu
// desc     : description displayed wher mouse is over item (like html title attribute)
// status   : 0: hidden, 1:Inactif, 2:Actif
// type     : 0 : title 1 : menu item 2 : separator
// icon     : icon relative path 
// onmouse  : 0 : none 1 onclick 2 on shiftclick 3 : on ctrl-click
//            used for attach menu to an element
// amode    : 0=http 1=javascript
// atarget  : target for http (like target for <a>)
// ascript  : url (http) or fonction (javascript)
// aevent   : 0 : none, 1 :reload event; 2 : delete event, 3 reload calendar

function MCalMenu( mid, menu, style ) {

  if (document.getElementById(mid)) {
    alert(mid+' : this menu already exist !');
    return false;
  }

  // param
  this.zIndex = 500;
  this.menuReactivity = 500;
  this.setIcons = true;
  this.menuWidth = (this.setIcons?90:70);

  this.bgTitleColor = (style.tbg!='' ? style.tbg : "#d2d74d");
  this.bgColor =      (style.bg!=''  ? style.bg  : "yellow");
  this.bgAltColor =   (style.abg!='' ? style.abg : "orange");
  this.color =        (style.fg!=''  ? style.fg  : "green");
  this.titleColor =   (style.tfg!='' ? style.tfg : "#188418");
  this.altColor =     (style.afg!='' ? style.afg : "white");

  this.itemFont = (!style.font || style.font=='' ? 'Tahoma,Arial,Helvetica,sans-serif' : style.font );

  this.itemFontSize = (!style.sz || style.sz=='' ? 9 : style.sz );
  this.menuHeight = 16 + (this.itemFontSize - 9);

  // Computed...
  this.menuItem = menu;

  this.xBorder = this.yBorder = this.border = 1;
  this.id = mid;
  this.menuId = '_mcalmenu_'+mid;

  this.status = 0; // O : hidden 1 : inactive 2 : active

  this.param = '';
}


// --------------------------------------------------------------------------------------
// Set menu color 
// fg : foreground, bg : background
// afg : altern foreground, abg : alter background (color set on pointer over item)
// tfg : title foreground, tbg : title background (item title)

MCalMenu.prototype.setColor = function(fg, bg, afg, abg, tfg, tbg ) {

  this.bgColor = (bg!=''?bg:this.bgColor);
  this.color = (fg!=''?fg:this.color);

  this.bgAltColor = (abg!=''?abg:this.bgAltColor);
  this.altColor = (afg!=''?afg:this.altColor);

  this.bgTitleColor = (tbg!=''?tbg:this.bgTitleColor);
  this.titleColor = (tfg!=''?tfg:this.titleColor);

}

// --------------------------------------------------------------------------------------
// Set Item size 
MCalMenu.prototype.setItemSize = function(h, w) {
  this.menuWidth = w;
  this.menuHeight = h;
  this.itemFontSize = 9 + (this.menuHeight - 16);
}

// --------------------------------------------------------------------------------------
// Show / Hide Icons
MCalMenu.prototype.showIcons = function() { this.setIcons = true; }
MCalMenu.prototype.hideIcons = function() { this.setIcons = false; }
 
// --------------------------------------------------------------------------------------
// Set the reactivity (milli second delay) to close menu on ouse out
MCalMenu.prototype.setSensitivity = function(x) {
  if (parseInt(x)) this.menuReactivity = parseInt(x);
}
 

// --------------------------------------------------------------------------------------
// Values parameter send whith request (http get or js fonction argument)
MCalMenu.prototype.setParam = function(p) {
  this.param = p;
}

// --------------------------------------------------------------------------------------
// Create the menu
MCalMenu.prototype.create = function() {

  if (!document.getElementById(this.menuId)) {
    
    var x = 0;
    var y = 0;
    var w = this.menuWidth + (2*this.xBorder);
    var h = (this.menuHeight + (2*this.yBorder)) * this.menuItem.length;
    
    var mstyle = [ 
      { id:'display', val:'none' }, 
      { id:'margin', val:'0px' }, 
      { id:'padding', val:'0px' }, 
      { id:'font-family', val:this.itemFont }, 
      { id:'font-size', val:this.itemFontSize+'px' }, 
      { id:'border', val:'0px none' } 
      ];
    
    // Draw root element for menu
    mcalDrawRectAbsolute(this.menuId, '', x, y, w, h, this.zIndex-1, '', false, this.menuId, false, mstyle); 
    
    if (!document.__mcalmenus) document.__mcalmenus = new Array;
    document.__mcalmenus[this.menuId] = this.menuItem;
    
    this.addItems();
  }
  return true;  
}
  
// --------------------------------------------------------------------------------------
// Hide menu
MCalMenu.hideMenu = function() {
  if (!document.getElementById(CMi)) return false;
  document.getElementById(CMi).style.display = 'none';
  return true;
}

// --------------------------------------------------------------------------------------
// Display menu mid at pointer position 
MCalMenu.HandlerCtx = '';
MCalMenu.HandlerFunc = '';
MCalMenu.HandlerArgs = new Array;
MCalMenu.showMenu = function(e, mid, handlercontext, hfunction, hargs) {
  if (!document.getElementById(mid)) return false;
  MCalMenu.HandlerCtx = handlercontext;
  MCalMenu.HandlerFunc = hfunction;
  MCalMenu.HandlerArgs = hargs;
  MCalMenu.stopTempo(mid);
  var evcoord = mcalEventXY(e);
  document.getElementById(mid).style.left = parseInt(evcoord.x-25);
  document.getElementById(mid).style.top = parseInt(evcoord.y-5);
  document.getElementById(mid).style.display = '';
  mcalCancelEvent(e);
}

// --------------------------------------------------------------------------------------
// Activate the menu default action
MCalMenu.defaultMenu = function(ev, menuid) {
  if (document.__mcalmenus[menuid]) {
    var cm = document.__mcalmenus[menuid];
    var found = false;
    for (var num=0; num<cm.length && !found; num++ ) {
      if (cm[num].onmouse == 3 && ev.ctrlKey) {
	MCalMenu.activateItem(menuid, num);
	found = true;
      }
      if (cm[num].onmouse == 2 && ev.shiftKey) {
        MCalMenu.activateItem(menuid, num);
	found = true;
      }
      if (cm[num].onmouse == 1) {
        MCalMenu.activateItem(menuid, num);
	found = true;
      }
    }
    mcalCancelEvent(ev);
  }
}

// --------------------------------------------------------------------------------------
// Activate an item
MCalMenu.activateItem = function(event, mid, iid) {
  if (document.__mcalmenus[mid]) {
    var cm = document.__mcalmenus[mid];
    if (!cm[iid]) alert('Pas d\item '+iid+' dans le menu '+mid);
    else {

      eval(MCalMenu.HandlerFunc)(event, 
				 parseInt(cm[iid].amode),
				 parseInt(cm[iid].aevent),
				 cm[iid].ascript,
				 cm[iid].atarget,
				 MCalMenu.HandlerCtx, 
				 MCalMenu.HandlerArgs );

    }
  } else {
    var ret = false;
    alert('Pas de menu '+mid);
  }
  MCalMenu.HandlerCtx = '';
  MCalMenu.HandlerFunc = '';
  MCalMenu.HandlerArgs = [];
  MCalMenu.hideMenu();
  return ret;
}
  
   
// --------------------------------------------------------------------------------------
// Attach menu to element
//   MCalMenu.prototype.attachToElt = function(elt, cal, id, evhandler, viewhandler) {
  MCalMenu.prototype.attachToElt = function(elt, handmode, handlerFunction, handlerArgs) {

  var thismenu = this.menuId;
  var targs = new Array;
 
  if (document.getElementById(elt)) {
    var elti = document.getElementById(elt);
    switch (handmode) {
      
    case 'click' : 
      break;
      
    default: //case 'contextmenu' :
      mcalAddEvent( elti, 
		    'contextmenu', 
		    function cev(e) { 
		      var lmenu = thismenu; 
		      var hmode = handmode; 
		      var hfunction = handlerFunction;
		      var hargs = handlerArgs;
		      MCalMenu.showMenu(e, lmenu, hmode, hfunction, hargs); }, 
		    true);
    }

  } else {
    mcalShowError('MCalMenu.attachToElt:: no such element '+elt);
  }
  
  return;
}

// --------------------------------------------------------------------------------------
// Create menu items
MCalMenu.prototype.addItems = function() {
    
    var x = 0;
    var y = 0;
    var w = this.menuWidth - (2*this.yBorder);
    var h = 0;

    for (var num=0; num<this.menuItem.length; num++ ) {
     
	var m = this.menuItem[num];

	var itext = '';
	if (m.type==2) itext = '';
	else {
	    itext = '<span style="vertical-align:middle">'+m.label+'</span>';
	    var itico = '';
	    if (this.setIcons && m.type>0) {
		if (m.icon && m.icon!='') itico = '<img src="' + m.icon + '" style="vertical-align:middle; border:0; width:16; height:16">&nbsp;';
		else itico = '<span style="padding-left:16">&nbsp;</span>';
	    }
	    itext = itico + itext;
	}

	var normalTopEffect = this.border+'px '+(num==0?'outset':'solid')+' '+this.bgColor;
	var normalBottomEffect = this.border+'px '+(num==(this.menuItem.length-1)?'outset':'solid')+' '+this.bgColor;
	var normalLeftEffect = this.border+'px outset '+this.bgColor;
	var normalRightEffect = this.border+'px outset '+this.bgColor;
	var overEffect = this.border+'px outset '+this.bgAltColor;

	if (m.status==0) {
	    h = 0;
	    y += 0;
	} else {
	    h = (m.type==2?0:this.menuHeight);
	}
  
	var mistyle = [ 
	    { id:'z-index', val:this.zIndex },
	    { id:'cursor', val:'pointer' },
	    { id:'border-top', val:normalTopEffect },
	    { id:'border-bottom', val:normalBottomEffect },
	    { id:'border-left', val:normalLeftEffect },
	    { id:'border-right', val:normalRightEffect },
	    ];
	if (m.type==0) {
	    mistyle[mistyle.length] = { id:'text-align', val:'center' };
	    mistyle[mistyle.length] = { id:'font-weight', val:'bold' };
	    mistyle[mistyle.length] = { id:'background-color', val:this.bgTitleColor };
	    mistyle[mistyle.length] = { id:'color', val:this.titleColor };
	    mistyle[mistyle.length] = { id:'border-bottom', val:this.border+'px ridge '+this.titleColor };
	} else if (m.type==2) {
 	    mistyle[mistyle.length] = { id:'background-color', val:this.bgColor };
	    mistyle[mistyle.length] = { id:'border-bottom', val:this.border+'px dotted '+this.color };
	} else {
	    mistyle[mistyle.length] = { id:'background-color', val:this.bgColor };
	    mistyle[mistyle.length] = { id:'color', val:this.color };
	    mistyle[mistyle.length] = { id:'font-style', val:(m.status==1 ? 'italic' : '' )} ;
	}
	
  
	var mclick = mover = mout = '';
	if (m.type==1 && m.status==2) {
	    mover = "this.style.color='"+this.altColor+"'; this.style.background = '"+this.bgAltColor+"'; this.style.border='"+ overEffect +"'";
	    mout = "this.style.color='"+this.color+"'; this.style.background = '"+this.bgColor+"'; this.style.borderTop='" + normalTopEffect +"'; this.style.borderBottom='" + normalBottomEffect +"'; this.style.borderLeft='" + normalLeftEffect +"'; this.style.borderRight='" + normalRightEffect +"';"; 
	    mclick = "MCalMenu.activateItem(event, '"+this.menuId+"', "+num+");";
	}
	var miattr = [ 
          { id:'title', val:(m.desc?m.desc:m.label) },
	  { id:'onmouseover', val:"MCalMenu.stopTempo('"+this.menuId+"'); "+mover },
	  { id:'onmouseout', val:mout+"; MCalMenu.startTempo('"+this.menuId+"')"   },
	  { id:'onclick', val:mclick } ];
	
	// Draw menu in this.menuName element father...
	mcalDrawRectAbsolute(this.menuId+'_item'+num, this.menuId, x, y, w, h, this.zIndex, '', true, itext, miattr, mistyle); 
 	y += (m.status==0?0:h + (2*this.yBorder));

    }
  return;
}
  
  
  var CMt = 0;
  var CMi = 0;
  MCalMenu.startTempo = function(idm) {
    CMi = idm;
    CMt = setTimeout('MCalMenu.hideMenu()', 500);
  }
  MCalMenu.stopTempo = function(idm) {
    if (idm==CMi) clearTimeout(CMt);
  }

  
