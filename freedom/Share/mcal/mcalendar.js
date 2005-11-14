// This source IS NOT DISTRIBUTED UNDER FREE LICENSE (like GPL or Artistic...)
// For any usage -commercial, private or other- you have to pay Marc Claverie.

function MCalendar(instance, serverAction, hmenu, style) 
{
  if (!document.__mcal) document.__mcal = new Array;

  if (document.__mcal[instance]) {
    
    this.CalRootElt = document.__mcal[instance].CalRootElt;
    this.CalDaysCount = document.__mcal[instance].CalDaysCount;
    this.CalHoursPerDay = document.__mcal[instance].CalHoursPerDay;
    this.CalOriginalTime = document.__mcal[instance].CalOriginalTime;
    this.CalDayStartHour = document.__mcal[instance].CalDayStartHour;
    this.CalShowWeekEnd = document.__mcal[instance].CalShowWeekEnd;
    this.CalHourHSize = document.__mcal[instance].CalHourHSize;
    this.CalHourWSize = document.__mcal[instance].CalHourWSize;
    this.CalCtrlKeyClick = document.__mcal[instance].CalCtrlKeyClick;
    this.CalKTitleHourW = document.__mcal[instance].CalKTitleHourW;
    this.CalKTitleDayH = document.__mcal[instance].CalKTitleDayH;
    this.showTitleBar = document.__mcal[instance].showTitleBar;
    this.showNavButton = document.__mcal[instance].showNavButton;
    this.Title = document.__mcal[instance].Title;

    this.serverMethod = document.__mcal[instance].serverMethod;

    this.dayCss = document.__mcal[instance].dayCss;
    this.dayCurrentCss = document.__mcal[instance].dayCurrentCss;
    this.dayWeekEndCss = document.__mcal[instance].dayWeekEndCss;
    this.daynhCss = document.__mcal[instance].daynhCss;
    this.dayTitleCss = document.__mcal[instance].dayTitleCss;
    this.Dim = document.__mcal[instance].Dim;
    this.CalPeriod = document.__mcal[instance].CalPeriod;
    this.CalRealDaysCount = document.__mcal[instance].CalRealDaysCount;
    this.CalZonePStart = document.__mcal[instance].CalZonePStart;
    this.CalZonePEnd = document.__mcal[instance].CalZonePEnd;
    this.CalPixelForMinute = document.__mcal[instance].CalPixelForMinute;
    this.CalHourHeight = document.__mcal[instance].CalHourHeight;
    this.CalHourWidth = document.__mcal[instance].CalHourWidth;
    this.xborder = document.__mcal[instance].xborder;
    this.yborder = document.__mcal[instance].yborder;
    this.TEvent = document.__mcal[instance].TEvent;
    this.TEventElt = document.__mcal[instance].TEventElt;
    this.EventTime = document.__mcal[instance].EventTime;
    this.Message = document.__mcal[instance].Message;
    this.menus = document.__mcal[instance].menus;
    this.isComputed = document.__mcal[instance].isComputed;
    this.debug = document.__mcal[instance].debug;
    this.lastGetDate = document.__mcal[instance].lastGetDate;
    this.lastRequest = document.__mcal[instance].lastRequest;
    this.hMenu = document.__mcal[instance].lastRequest;

  } else {

    this.CalRootElt = instance; // element where i am inserted
    this.CalDaysCount = 7;            // number of days displayed
    this.CalHoursPerDay = 10;         // number of time by day
    var cd = new Date();             
    this.CalOriginalTime = this.CalInitTime = cd.getTime();  // Init time = current time
    this.CalDayStartHour = 8;         // First hour for day
    this.CalShowWeekEnd = true;       // Show / hide week end
    this.CalHourHSize = 'auto';       // or px;
    this.CalHourWSize = 'auto';       // or H px;
    this.CalCtrlKeyClick = false;     // ctrl-click handler on calendar
    this.CalKTitleHourW = 35;         // Hours title width
    this.CalKTitleDayH = 20;          // Days title width
    this.showTitleBar = false;
    this.showNavButton = true;
    this.Title = 'MCalendar (c) Marc &lt;marc.claverie (@) gmail.com&gt;';
    
    this.serverMethod = Array();
    var ok = false;
    if (serverAction) {
      for (var ia=0; ia<serverAction.length; ia++) {
	this.serverMethod[serverAction[ia].id] = serverAction[ia].request;
      }
    }
    
    this.dayCss = '';
    this.dayCurrentCss = '';
    this.dayWeekEndCss = '';
    this.daynhCss = '';
    this.dayTitleCss = '';
    
    // Some properties computed
    this.Dim = { x:0, y:0, w:0, z:0 };
    this.CalPeriod = new Array();
    this.CalRealDaysCount = 0;
    this.CalZonePStart = '';
    this.CalZonePEnd = '';
    this.CalPixelForMinute = 0;
    this.CalHourHeight = 0;
    this.CalHourWidth = 0;
    
    this.xborder = 1;
    this.yborder = 1;
    
    this.TEvent = new Array();
    this.TEventElt = new Array();
    this.EventTime = 0;
    
    this.Message = new Array();
  
    this.menus = new Array();

    this.lastGetDate = 0;
    this.lastRequest = '';
    this.isComputed = false;
    this.debug = false; // true;

    this.hMenu = hmenu;

    if (style) this.style = style;
    else {
      this.style = new Object;
      this.style.fontSize = '9';
      this.style.fontFam  = 'Tahoma,Arial,Helvetica,sans-serif';
      this.style.background = 'white';
      this.style.foreground = 'black';
      this.style.currentDayBackground = '#F2FFDB';
      this.style.titleBackground = '#F1D998';
      this.style.titleBackgroundOver = 'orange';
      this.style.oddDayBackground = '#F8F1FB';
      this.style.evenDayBackground = '#F8F1FB';
      this.style.oddDayWEBackground = '#edeff7';
      this.style.evenDayWEBackground = '#eff1f9';
    }
  
  

    document.__mcal[this.CalRootElt] = this;
  }
}


MCalendar.prototype.__deleteElt = function(ev) {
  if (!this.TEvent[ev].mode) return false;
  var mode = this.TEvent[ev].mode;
  if (!this.TEventElt[mode]) return;
  for (var ielt=0; ielt<this.TEventElt[mode].length; ielt++) {
    if (this.TEventElt[mode][ielt].ref==ev && document.getElementById(this.TEventElt[mode][ielt].id)) {
      var e = document.getElementById(this.TEventElt[mode][ielt].id);
      e.parentNode.removeChild(e);
    }
  }
}

MCalendar.prototype.Delete = function() {
  if (document.getElementById(this.CalRootElt)) {
    var mc = document.getElementById(this.CalRootElt);
    var kids = mc.childNodes;
    var numkids = kids.length;
    for(var i=(kids.length-1); i >= 0; i--) {       // Loop through kids
      mc.removeChild(kids[i]);
    }    
  }
}

//
// Compute some properties according constant and parameters set by user or constructor
//
MCalendar.prototype.Compute = function()
{
  cD = new Date(this.CalInitTime);
  dS = new Date(cD.getFullYear(), cD.getMonth(), cD.getDate(), 0, 0, 0, 0);
  dE = new Date(cD.getFullYear(), cD.getMonth(), cD.getDate(), 23, 59, 59, 0);
  

  // First compte the days count
  this.CalDisplayedDaysCount = this.CalDaysCount;
  if (!this.CalShowWeekEnd) 
    {
      var ndays = this.CalDaysCount;
      var ida;
      for (ida=0; ida<this.CalDaysCount+1; ida++) 
	{
	  cday = new Date( dS.getFullYear(), dS.getMonth(), (dS.getDate()+ida), 0, 0, 0, 0);
	  dayOfWeek = cday.getDay( );
	  if (dayOfWeek==6 || dayOfWeek==0) this.CalDisplayedDaysCount--;
	}
    }

  this.CalZonePStart = 'd1h1';
  this.CalZonePEnd = 'd'+(this.CalDisplayedDaysCount)+'h'+(this.CalHoursPerDay+1);

  this.Dim = mcalGetZoneCoord(this.CalRootElt);

  if (this.CalHourHSize=='auto') 
    this.CalHourHeight = Math.floor( (this.Dim.h-this.CalKTitleDayH) / (this.CalHoursPerDay+2));
  else this.CalHourHeight = parseInt(this.CalHourHSize);
  this.CalPixelForMinute = this.CalHourHeight / 60;  
  if (this.CalHourWSize=='auto') this.CalHourWidth = Math.floor( (this.Dim.w-(this.CalKTitleHourW+(2*this.xborder))) / this.CalDisplayedDaysCount);
  else this.CalHourWidth = this.CalHourWSize;
  
  mcalDrawRectAbsolute('__MCal'+this.CalRootElt, 
			this.CalRootElt, 
			this.Dim.x, 
			this.Dim.y, 
			this.Dim.w , 
			this.Dim.h, 
			1000, '', false, '', false, false);
  // System menus
  var tmenu = [
    { id:'title', label:'Calendrier', type:0 },
    { id:'weekend', label:'show/hide we', desc:'display or undisplay week-end zone', status:2, type:1,
      icon:'mcalendar-showhidewe.png', onmouse:'', amode:3, aevent:0, 
      atarget:'', ascript:'document.__mcal.'+this.CalRootElt+'.ShowHideWeekEnd();', aevent:0 },
    { id:'sep0', type:2 },
    { id:'nextperiod', label:'Avancer', desc:'Afficher la période suivante', status:2, type:1,
      icon:'mcalendar-next.png', onmouse:'', amode:3, aevent:0, 
      atarget:'', ascript:'document.__mcal.'+this.CalRootElt+'.gotoNextPeriod();', aevent:0 },
    { id:'prevperiod', label:'Reculer', desc:'Afficher la période précédente', status:2, type:1,
      icon:'mcalendar-prev.png', onmouse:'', amode:3, aevent:0, 
      atarget:'', ascript:'document.__mcal.'+this.CalRootElt+'.gotoPrevPeriod();', aevent:0 },
    { id:'currentperiod', label:'Revenir', desc:'Afficher la période initiale', status:2, type:1,
      icon:'mcalendar-current.png', onmouse:'', amode:3, aevent:0, 
      atarget:'', ascript:'document.__mcal.'+this.CalRootElt+'.gotoCurrentPeriod();', aevent:0 },
    { id:'sep1', type:2 },
    { id:'resize', label:'resize', desc:'resize calendar', status:2, type:1,
      icon:'mcalendar-resize.png', onmouse:'', amode:3, aevent:0, 
      atarget:'', ascript:'document.__mcal.'+this.CalRootElt+'.Resize();', aevent:0 }
    ];
  var style = { bg:'#F8F1FB', fg:'black', tbg:'#F1D998', tfg:'black', abg:'#EAE9C1', afg:'black' };
  this.calmenu = new MCalMenu( '__gmenu', tmenu, style );
  this.hourmenu = false;
  if (this.hMenu) this.hourmenu = new MCalMenu('__ghmenu' , this.hMenu, style );

  this.isComputed = true;

  return;
}

MCalendar.prototype.__getEvents = function() 
{
  if (!this.serverMethod['getevents']) return;
  var rq;
  try {
    rq = new XMLHttpRequest();
  } catch (e) {
    rq = new ActiveXObject("Msxml2.XMLHTTP");
  }
  rq.instanceName = this.CalRootElt;
  
  rq.onreadystatechange =  function() {
  if (rq.readyState == 4) {
    var instance = document.__mcal[rq.instanceName];
    if (rq.responseXML && rq.status==200) {
	var xmlstr;
 	try {
 	  var s = new XMLSerializer();
 	  var d = rq.responseXML;
 	  xmlstr = s.serializeToString(d);
 	} catch (e) {
	  xmlstr = rq.responseXML.xml;
 	}
	
	// TODO some code to detect XML error : malformed, empty ...

//  var text = xmlstr;
//  var t2 = text.replace(/</g, '&lt;');
//  mcalShowTrace("<pre>" + t2.replace(/>/g, '&gt;') +"</pre>");

	var xmlDom = new XMLDoc(xmlstr, mcalShowError);
	var xmlDomTree = xmlDom.docNode;// Get all events
	var events = xmlDomTree.getElements("event");
	var menus = xmlDomTree.getElements("menu");
	var id;
	var rid;
	var evstatus;
	var dmode;
	var etime;
	var duration;
	var title;
	var item;
	var param;
        var vstyles = new Array;

	for (var ie=0; ie<menus.length; ie++) {
	  var mid = menus[ie].getAttribute("id");

	  var mst = menus[ie].getElements("style");
	  var size = fgcolor = bgcolor = afgcolor = abgcolor = tfgcolor = tbgcolor = '';
	  if (mst.length>0) {
	    font = (mst[0].getAttribute('font')?mst[0].getAttribute('font'):'');
	    size = (mst[0].getAttribute('size')?mst[0].getAttribute('size'):'');
	    fgcolor = (mst[0].getAttribute('fgcolor')?mst[0].getAttribute('fgcolor'):'');
	    bgcolor = (mst[0].getAttribute('bgcolor')?mst[0].getAttribute('bgcolor'):'');
	    afgcolor = (mst[0].getAttribute('afgcolor')?mst[0].getAttribute('afgcolor'):'');
	    abgcolor = (mst[0].getAttribute('abgcolor')?mst[0].getAttribute('abgcolor'):'');
	    tfgcolor = (mst[0].getAttribute('tfgcolor')?mst[0].getAttribute('tfgcolor'):'');
	    tbgcolor = (mst[0].getAttribute('tbgcolor')?mst[0].getAttribute('tbgcolor'):'');
	  }
	  var style = { sz:size, fg:fgcolor, bg:bgcolor, afg:afgcolor, abg:abgcolor, tfg:tfgcolor, tbg:tbgcolor };
	    
	  var tmenu = new Array();
	  var items = menus[ie].getElements("item");
	  var iid = -1;
	  var istatus = -1;
	  var itype = -1;
	  var iicon = '';
	  var ilabel = '';
	  for (var it=0; it<items.length; it++) {
	    iid = items[it].getAttribute("id");
	    istatus = items[it].getAttribute("status");
	    itype = items[it].getAttribute("type");
	    iicon = items[it].getAttribute("icon");
	    ilabel = items[it].getElements("label")[0].getText();
	    idescr = items[it].getElements("description")[0].getText();
	    var aid = -1;
	    var aonmouse = -1;
	    var amode = -1;
	    var aevent = -1;
	    var atarget = '';
	    var ascript = '';
	    var actions = items[it].getElements("action");
	    if (actions.length>00) {
	      if (actions[0].getAttribute("id")) aid = actions[0].getAttribute("id");
	      if (actions[0].getAttribute("onmouse")) aonmouse = actions[0].getAttribute("onmouse");
	      if (actions[0].getAttribute("amode")) amode = actions[0].getAttribute("amode");
	      if (actions[0].getAttribute("aevent")) aevent = actions[0].getAttribute("aevent");
	      if (actions[0].getAttribute("atarget")) atarget = actions[0].getAttribute("atarget");
	      if (actions[0].getAttribute("ascript")) ascript = actions[0].getAttribute("ascript");
	    }
	    if (!iid) {
	      mcalShowError('Invalid menu '+mid+' item ('+(it+1)+') : missing mandatory id attribute');
	    } else {
//  	      mcalShowTrace("id="+iid+" label="+ilabel+" desc="+idescr+" status="+istatus+" type="+itype+" icon="+iicon+" amode="+amode+" atarget="+atarget+" ascript="+ascript+" aevent="+aevent+"");
	      tmenu[it] = {
		id      : iid,
		label   : ilabel,
		desc    : idescr,
		status  : istatus,
		type    : itype,
		icon    : iicon,
		onmouse : aonmouse, 
		amode   : amode, 
		atarget : atarget, 
		ascript : ascript,
		aevent  : aevent
	      };
	      instance.menus[mid] = new MCalMenu( mid, tmenu, style );
	    }
	  }
	}


	for (var ie=0; ie<events.length; ie++) {

	  id = events[ie].getAttribute("id");
	  rid = events[ie].getAttribute("rid");
	  idcard = events[ie].getAttribute("cid");
	  status = events[ie].getAttribute("status"); // C change D delete 
	  dmode = events[ie].getAttribute("dmode");
	  etime = events[ie].getAttribute("time");
	  duration = events[ie].getAttribute("duration");
	  title = events[ie].getElements("title");

	  var menuref = events[ie].getElements("menuref");
	  var evmenu = null;
	  var ref = menuref[0].getAttribute('id');
	  if (!ref || !instance.menus[ref]) mcalShowError('Event['+(ie+1)+'], menu reference : attribute id empty or referenced menu '+(ref?ref:'')+' does not exist');
	  else {
	    var sref = new String(menuref[0].getAttribute('use'));
	    if (!sref) mcalShowError('Event['+(ie+1)+'], menu reference : use attribute missing or empty');
	    else {
	      evmenu = { ref:ref, use:sref.split(',') };
	    }
	  }

	  content = events[ie].getElements("content")[0];
	  tstyle = content.getElements('styleinfo')[0];
	  styles = tstyle.getElements('style');
	  vstyles = [];
	  for (var ist=0; ist<styles.length; ist++) {
	    vstyles[vstyles.length] = { id:styles[ist].getAttribute('id'), val:styles[ist].getAttribute('val') };
	  }
	  var hcontent = content.getElements('chtml')[0];
	  tcontent = hcontent.getUnderlyingXMLText();
	  var tcontent2 = hcontent.getElements();
	  scontent = '';
	  for (var iet=0; iet<tcontent2.length; iet++) {
	    scontent += tcontent2[iet].getUnderlyingXMLText();
	  }
          instance.AddEvent(id, idcard, rid, dmode, parseInt(etime*1000), parseInt(duration*1000), 
			    title[0].getText(), scontent, vstyles, evmenu);
	}
	instance.__hideMessage();
        if (instance.TEvent.length>0) instance.__displayEvents();
      } else {
        instance.__hideMessage();
        var zTs = Math.round(instance.CalPeriod[0].ds.getTime()/1000);
        var zTe = Math.round(instance.CalPeriod[(instance.CalPeriod.length-1)].de.getTime()/1000);
        mcalShowError("Can't get serveur response (XML datas) ["+rq.statusText+"]<br>Http request = "+this.serverMethod['getevents']);
      }
    }
  }
  var debugs = '(';
  var zTs = Math.round(this.CalPeriod[0].ds.getTime()/1000);
  var zTe = Math.round(this.CalPeriod[(this.CalPeriod.length-1)].de.getTime()/1000);
  var dld = new Date;
  var ldate = this.lastGetDate;
  if (this.serverMethod['getevents']!=this.lastRequest) ldate=0;
  var serverreq = mcalParseReq( this.serverMethod['getevents'], [ 'TS', 'TE', 'LR' ], [ zTs, zTe, ldate ]);
  this.lastRequest = this.serverMethod['getevents'];
  this.lastGetDate = Math.floor(dld.getTime()/1000);
  debugs += 'request=['+this.lastRequest+'] last date=['+ldate+'])';
  MCalendar.__waitingServer();
  rq.open("GET", serverreq, true);
  rq.send(null);
}


MCalendar.prototype.__drawTitleBar = function() 
{		  
  var eltn = '<img width="14" title="" src="mcalendar-showhidewe.png" title="Afficher/Cacher les week-ends" onclick="document.__mcal.'+this.CalRootElt+'.ShowHideWeekEnd();" style="border:0; cursor:pointer">';
  eltn += '<img width="14" title="" src="mcalendar-resize.png" title="Retailler" onclick="document.__mcal.'+this.CalRootElt+'.Resize();" style="border:0; cursor:pointer">';

  var tbW  = this.CalKTitleHourW;
  for (var ip=0; ip<this.CalPeriod.length; ip++) tbW += (this.CalPeriod[ip].hide ? 0 : (2*this.xborder)+this.CalHourWidth);

  mcalDrawRectAbsolute('__caltitle', this.CalRootElt, 1, 1, tbW, this.CalKTitleDayH, 2000, 'dayh', true, eltn+'&nbsp;'+this.Title, false, false);
  this.Dim.y += this.CalKTitleDayH;
}


MCalendar.__waitingServer = function() {
  MCalendar.__showMessage('<img style="vertical-align:middle; border:0; height:15" src="mcalendar-waitserver.gif">&nbsp;<span style="vertical-align:middle;">Interrogation du serveur...</span>');
}

MCalendar.__showMessage = function(info) {
  var style = [
     { id:'overflow', val:'hidden' },
     { id:'margin', val:'1px' },
     { id:'padding', val:'1px' },
     { id:'border', val:'3px ridge blue' },
     { id:'background-color', val:'white' },
     { id:'color', val:'blue' }
  ];
  mcalDrawRectAbsolute('__mcalendarinfo', '', 2, 2, 200, this.CalKTitleDayH-1, 2001, '', true, info, false, style);
}

MCalendar.prototype.__hideMessage = function(trace) { 
  if (this.debug) {
    var dt = new Date()
    stop = dt.getTime();
    mcalShowTrace("Dur&eacute;e d'interrogation du serveur : "+((stop-this.start)/1000)+" seconds");
  }
  document.getElementById('__mcalendarinfo').style.display='none'; 
};

// -----------------------------------------------------------------------------------
MCalendar.prototype.Display = function() {
  this.gotoCurrentPeriod();
}

// -----------------------------------------------------------------------------------
MCalendar.prototype.__display = function() {
  if (!document.getElementById(this.CalRootElt)) {
    mcalShowError('no such element '+this.CalRootElt);
    return;
  }
	
  if (!this.isComputed) this.Compute();
  

  var ip;
  var eltn = '';
  var cx, cy, cw, ch;
  var idh, ida;
  var dayXPos = 1;
  var dayOfWeek = -1;
  var totalW = 0; 
  var hide = false;
  var style = new Array;

  for (ida=0; ida<this.CalDaysCount+1; ida++) {
    if (ida>0) {
      ip = ida-1;
      this.CalPeriod[ip] = { ds:0, hs:0, he:0, de:0, hide:false };
      this.CalPeriod[ip].ds = new Date( dS.getFullYear(), dS.getMonth(), (dS.getDate()+ip), 
					0, 0, 0, 0);
      this.CalPeriod[ip].de = new Date( dS.getFullYear(), dS.getMonth(), (dS.getDate()+ip), 
					23, 59, 59, 999);
      this.CalPeriod[ip].hs = new Date( dS.getFullYear(), dS.getMonth(), (dS.getDate()+ip), 
					this.CalDayStartHour, 0, 0, 0);
      this.CalPeriod[ip].he = new Date( dS.getFullYear(), dS.getMonth(), (dS.getDate()+ip), 
					this.CalDayStartHour+this.CalHoursPerDay, 0, 0, 0);
      dayOfWeek = this.CalPeriod[ip].ds.getDay( );
      if (!this.CalShowWeekEnd && (dayOfWeek==6 || dayOfWeek==0)) this.CalPeriod[ip].hide = true;
      hide = this.CalPeriod[ip].hide;
      today = new Date();
    } else {
      hide = false;
      dayOfWeek = -1;
    }
    
    if (ida==0) cw = this.CalKTitleHourW;
    else cw = (hide?0:this.CalHourWidth);

    for (idh=0; idh<(this.CalHoursPerDay+3); idh++) {
      
      style = [ 
	  { id:'cursor', val:'pointer' },
	  { id:'font-size', val: parseInt(this.style.fontSize) },
	  { id:'font-family', val: this.style.fontFam },
	  { id:'position', val: 'absolute' },
	  { id:'border-style', val: 'outset' },
	  { id:'border-width', val: '1px' },
	  { id:'border-color', val: this.style.currentDayBackground },
	  ];
      idel = 'd'+ida+'h'+idh;
      eltn = '' ;
      if (this.showNavButton) {
	if (ida==0 && idh==0) eltn += '<img width="16" title="" src="mcalendar-gmenu.png">';
      }

      // Set style
      if (ida==0 || idh==0) {
	style[style.length] = { id:'background-color', val:this.style.titleBackground };
	style[style.length] = { id:'text-align', val:'center' };
	style[style.length] = { id:'font-weight', val:'bold' };
      } else {
	if (dayOfWeek==6 || dayOfWeek==0) {
	  if ((ida%2)==0) style[style.length] = { id:'background-color', val:this.style.evenDayWEBackground };
	  else style[style.length] = { id:'background-color', val:this.style.oddDayWEBackground };
	}  else if (ida>0 && (this.CalPeriod[ip].ds.toLocaleDateString() == today.toLocaleDateString())) {
	  style[style.length] = { id:'background-color', val:this.style.currentDayBackground };
	} else {
	  if ((ida%2)==0) style[style.length] = { id:'background-color', val:this.style.evenDayBackground };
	  else style[style.length] = { id:'background-color', val:this.style.oddDayBackground };
	}
      }
	
      if (ida>0 && idh==0) {
	  eltn += this.CalGetDayOfWeekLabel(this.CalPeriod[ip].ds.getDay())
	    +     ' ' + this.CalPeriod[ip].ds.getDate()
	    +     ' ' + this.CalGetMonthLabel(this.CalPeriod[ip].ds.getMonth());
      }
      
      if (ida==0 && idh>1 && idh<=this.CalHoursPerDay+1) eltn += this.CalDayStartHour + (idh-2)+ 'h00';    
      if (ida>0) {
	var title = this.CalPeriod[ip].ds.toLocaleDateString() 
	  + ', '+ (this.CalDayStartHour + (idh-2)) + 'h00 ' 
	  + (this.CalDayStartHour + (idh-1)) + 'h00';
      }
      
      if (idh==0) {
	cy = (this.showTitleBar?this.CalKTitleDayH+(2*this.yborder)+1:1);
	ch = this.CalKTitleDayH;
	if (ida>0 && (this.CalPeriod[(ida-1)].ds.toLocaleDateString() == today.toLocaleDateString())) {
	  style[style.length] = { id:'text-decoration', val:'underline' };
	} 
      } else {
	cy = (this.showTitleBar?this.CalKTitleDayH+(2*this.yborder):1) + (this.CalKTitleDayH+(2*this.yborder)) + ((idh-1) * (this.CalHourHeight+(2*this.yborder)));
	ch = this.CalHourHeight;
      }
      
      var attr = [ 
 	{ id:'onmouseover', val:"document.getElementById('d"+ida+"h"+idh+"').style.border = '1px inset "+this.style.currentDayBackground+"'; document.getElementById('d0h"+idh+"').style.backgroundColor = '"+this.style.titleBackgroundOver+"'; document.getElementById('d"+ida+"h0').style.backgroundColor = '"+this.style.titleBackgroundOver+"';" },
 	{ id:'onmouseout', val:"document.getElementById('d"+ida+"h"+idh+"').style.border = '1px outset "+this.style.currentDayBackground+"'; document.getElementById('d0h"+idh+"').style.backgroundColor = '"+this.style.titleBackground+"'; document.getElementById('d"+ida+"h0').style.backgroundColor = '"+this.style.titleBackground+"';" },
	];
      mcalDrawRectAbsolute(idel, this.CalRootElt, dayXPos,cy,cw,ch, 500, '', (hide?false:true), eltn, attr, style);
      if (ida==0 && idh==0) 
	this.calmenu.attachToElt( idel, [ 0 ], 'click', 'MCalendar.GHandler', [ this.CalRootElt, 0, 0 ]);
      else if (ida==0 || idh==0)
	this.calmenu.attachToElt( idel, false, 'contextmenu', 'MCalendar.GHandler', [ this.CalRootElt, 0, 0 ]);
      else 
	if (this.hourmenu) this.hourmenu.attachToElt( idel, false, 'contextmenu', 'MCalendar.GHandler', [ this.CalRootElt, 0, 0, (this.CalPeriod[ip].ds.getTime() + (idh-2)*3600*1000), (this.CalPeriod[ip].ds.getTime() + (idh-1)*3600*1000) ]);
    }
    if (!hide) dayXPos += cw;
  }  
  if (this.showTitleBar) this.__drawTitleBar();

  return;
}
      
// -----------------------------------------------------------------------------------
MCalendar.prototype.CalGetDayOfWeekLabel = function(d) {
  var tlab = [ 'Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi' ];
  return tlab[d];
}

// -----------------------------------------------------------------------------------
MCalendar.prototype.CalGetMonthLabel = function(d) {
  var tlab = [ 'Janv', 'F&eacute;v', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aou', 'Sep', 'Oct', 'Nov', 'D&eacute;c' ];
  return tlab[d];
}

      

// --------------------------------------------------------------
// Event manipulation
// --------------------------------------------------------------
MCalendar.prototype.__getEventById = function(idev) {
  for (var iev=0; iev<this.TEvent.length; iev++) {
    if (idev==this.TEvent[iev].id) return iev;
  }
  return false;
}

  MCalendar.prototype.AddEvent = function(id, idcard, rid, dmode, time, duration, title, content, style, menu) 
{ 
  var idx = this.__getEventById(id);
  if (!idx) idx = this.TEvent.length;
  this.TEvent[idx] = { id:id, 
		       idcard:idcard, 
		       rid:rid, 
		       mode:(dmode>=0||dmode<=2?dmode:1), 
		       time:time, 
		       duration:duration, 
		       title:title, 
		       content:content, 
		       style:style, 
		       menu:menu };
}

MCalendar.prototype.__printEvent = function(iev) 
{
  if (!this.TEvent[iev]) return;
  var pev = '';
  var de = new Date();
  de.setTime(this.TEvent[iev].time);
  pev = 'Event['+this.TEvent[iev].id+'::'+this.TEvent[iev].rid+'] = {<br>'
  +     '\t\tTitre = '+this.TEvent[iev].title+'<br>'
  +     '\t\tDate = '+this.sDT(de)+' ('+this.TEvent[iev].time+')<br>'
  +     '\t\tDuree = '+this.TEvent[iev].duration+'<br>'
  +     '\t\tMode d\'affichage = '+this.TEvent[iev].mode+'<br>';
  pev += '}';
  mcalShowTrace(pev);
}
  

MCalendar.prototype.__deleteEvent = function(iev, deleteEntry) {

  if (iev==-1) {
    // delete all
    for (var ie=0; ie<this.TEvent.length; ie++) {
      this.__deleteElt(ie);
    }
    if (deleteEntry) this.TEvent.splice(0);
    
  }  else if (iev>=this.TEvent.length) {
    return false;

  } else {
    // delete one !
    this.__deleteElt(iev);
    if (deleteEntry) this.TEvent.splice(iev, 1);
  }
  return true;
}


MCalendar.prototype.__displayEvents = function() {
  var ie=0;

  // reset event element
  this.TEventElt[0] = [];
  this.TEventElt[1] = [];
  this.TEventElt[2] = [];
  this.__computeRealElt();
  this.TEventElt[1].sort(sortRealElt);
  this.eventoffset();
  var trc = '';
  trc += '<table border="1" style="font-size:11; width:90%">';
  trc += '<tr><td>id</td><td>elt</td><td>x</td><td>w</td><td>y</td><td>h</td><td>y+h</td><td>day</td><td>off</td><td>div</td><td>woff</td></tr>'
  for (var ie=0; ie<this.TEventElt[1].length; ie++) {
    
    this.TEventElt[1][ie].co.x = parseInt(this.TEventElt[1][ie].co.x);
    this.TEventElt[1][ie].co.w = parseInt(this.TEventElt[1][ie].co.w);
    this.TEventElt[1][ie].co.y = parseInt(this.TEventElt[1][ie].co.y);
    this.TEventElt[1][ie].co.h = parseInt(this.TEventElt[1][ie].co.h);

    trc += "<tr><td>"+this.TEvent[this.TEventElt[1][ie].ref].id+"</td><td>"+this.TEventElt[1][ie].id+"</td><td>"+this.TEventElt[1][ie].co.x+"</td><td>"+this.TEventElt[1][ie].co.w+"</td><td>"+this.TEventElt[1][ie].co.y+"</td><td>"+this.TEventElt[1][ie].co.h+"</td><td>"+parseInt(this.TEventElt[1][ie].co.y+this.TEventElt[1][ie].co.h)+"</td><td>"+this.TEventElt[1][ie].co.day+"</td><td>"+this.TEventElt[1][ie].co.offset+"</td><td>"+this.TEventElt[1][ie].co.doffset+"</td><td>"+this.TEventElt[1][ie].co.woffset+"</td></tr>"; 
    

  }
  trc += "</table>";
  if (this.TEventElt[0]) {
    for (ie=0; ie<this.TEventElt[0].length; ie++) this.__displayEventElt(0, ie);
  }
  if (this.TEventElt[1]) {
    for (ie=0; ie<this.TEventElt[1].length; ie++) this.__displayEventElt(1, ie);
  }
  if (this.TEventElt[2]) {
    for (ie=0; ie<this.TEventElt[2].length; ie++) this.__displayEventElt(2, ie);
  }

//      mcalShowTrace(trc);
}


function in_array(e,t) {  
  for (var i=0;i<t.length;i++) {
    if (t[i]==e) return true;
  }
  return false;
}

MCalendar.prototype.__computeRealElt= function() {

  var start, duration, ostart, oend;
  var rctime, shour, smin, ehour, emin;
  var sday, eday;
  var rctime, sevent, eevent;
  var selt, eelt;
  var coorde, coords;
  var mode;
  var celt;
   

  for (var ie=0; ie<this.TEvent.length; ie++) {

    start = this.TEvent[ie].time;
    duration = this.TEvent[ie].duration;
    mode = this.TEvent[ie].mode;

    ostart = start;
    oend = start+duration;
  
    rctime = this.AdjustEventTime(ostart);
    start = rctime.time;
    sday = rctime.day;
    
    if (ostart==oend) {
      start = this.CalPeriod[sday].ds.getTime();
      end = this.CalPeriod[sday].hs.getTime() - 1;
      eday = sday;
    } else {
      rctime = this.AdjustEventTime(oend);
      end = rctime.time;
      eday = rctime.day;
    }
    
    for (var id=sday; id<=eday; id++) {
    if (this.CalPeriod[id].hide) continue;
      if (mode==0 || mode==1) {
	if (id>sday) istart = this.CalPeriod[id].ds.getTime();
	else istart = start;
	if (id<eday) iend = this.CalPeriod[id].de.getTime();
	else iend = end;
    
	rctime = this.AdjustEventTime(istart);
	istart = rctime.time;
	rctime = this.AdjustEventTime(iend);
	iend = rctime.time;
	
	sevent = new Date();
	sevent.setTime(istart);
	
	eevent = new Date();
	eevent.setTime(iend);
	
	shour = sevent.getHours();
	smin  = sevent.getMinutes();
	
	selt = 'd'+(id+1)+'h'+(shour-this.CalDayStartHour+2);
	coords = mcalGetZoneCoord(selt);
	coords.y = coords.y + (smin * this.CalPixelForMinute);
	
	ehour = eevent.getHours();
	emin  = eevent.getMinutes();
	
	eelt = 'd'+(id+1)+'h'+(ehour-this.CalDayStartHour+2);
	coorde = mcalGetZoneCoord(eelt);
	coorde.y = coorde.y + (emin * this.CalPixelForMinute);
	
	co = { x : parseInt(coords.x), 
	       y : parseInt(coords.y), 
	       w : parseInt(coords.w), 
	       h : parseInt(coorde.y - coords.y), 
	       z : (mode==0?1800:2000),
	       day : id,
	       offset : 0, // 6 eric
	       doffset : 0 }; // 7 eric
	

	if (!this.TEventElt[mode]) this.TEventElt[mode] = new Array;
	celt = this.TEventElt[mode].length;
	this.TEventElt[mode][celt] = { ref : ie, id : '__ev_'+ie+'_d_'+id, co:co };
      }
    }
  }
  return;
}

MCalendar.prototype.__eventEltExist = function(mode, elt) {
  if (!this.TEventElt[mode]) return false;
  for (var ie=0; ie<this.TEventElt[mode].length; ie++) {
    if (this.TEventElt[mode][ie].id == elt) return ie;
  }
}

MCalendar.prototype.AdjustEventTime = function(time) 
{
  var ctime = { day:-1, time:0 };
  if (time<this.CalPeriod[0].ds.getTime()) 
    {
      ctime.time = this.CalPeriod[0].ds.getTime();
      ctime.day = 0; 
    } 
  else if (time>this.CalPeriod[(this.CalPeriod.length-1)].de.getTime()) 
    {
      ctime.time = this.CalPeriod[(this.CalPeriod.length-1)].he.getTime();
      ctime.day = (this.CalPeriod.length-1);
    } 
  else 
    {
      ctime.day = -1;
      for (id=0; id<this.CalPeriod.length && ctime.day==-1; id++) 
	{
	if (time>=this.CalPeriod[id].ds.getTime() && time<=this.CalPeriod[id].de.getTime())   
	  {
	    ctime.day = id;
	    ctime.time = time;
	    if (time<(this.CalPeriod[id].hs.getTime()-(3600*1000))) 
	      {
		ctime.time = this.CalPeriod[id].hs.getTime()-(3600*1000);
	      } 
	    else if (time>=(this.CalPeriod[id].he.getTime()+(3600*1000))) 
	      {
		ctime.time = this.CalPeriod[id].he.getTime()+(3599*1000);
	      }
	  }
	}
    }
  return ctime;
}


MCalendar.prototype.__displayEventElt = function(mode, ie) 
{

  var evRef = this.TEventElt[mode][ie].ref;
  var content = this.TEvent[evRef].content;
  var id = this.TEvent[evRef].id;
  var rid = this.TEvent[evRef].rid;
  var idcard = this.TEvent[evRef].idcard;
  
  var evStyle = new Array;
  for (var is=0; is<this.TEvent[evRef].style.length; is++) {
    evStyle[evStyle.length] = this.TEvent[evRef].style[is];
  }
  evStyle[evStyle.length] = { id:'overflow', val:'hidden'};
  evStyle[evStyle.length] = { id:'cursor', val:'pointer' };



  var usemenu = false;
  var evAttr = new Array;
  evAttr = [
    { id:'onclick', val:'if (event.shiftKey) alert("shift"); else if (event.ctrlKey) alert("ctrl"); else document.__mcal.'+this.CalRootElt+'.__showDetail(event, \''+id+'\',\''+idcard+'\')' },
    { id:'title', val:this.TEvent[evRef].title+' (click to show details)' },
    { id:'onmouseover', val:'' },
    { id:'onmouseout', val:'' }
  ];
  if (this.TEvent[evRef].menu.ref && this.menus[this.TEvent[evRef].menu.ref]) usemenu = true;
  

  var calealacon = 2;
  x = this.TEventElt[mode][ie].co.x + (mode==0?1:calealacon);
  w = this.TEventElt[mode][ie].co.w - (mode==0?5:(2*calealacon)+3);
 
  if (this.TEventElt[mode][ie].co.doffset!=0) {
    var wdiv = Math.floor(w / (this.TEventElt[mode][ie].co.doffset+1));
    x = x + ((wdiv+1) * this.TEventElt[mode][ie].co.offset);
      w = Math.floor(w / (this.TEventElt[mode][ie].co.doffset+1));
//     if (this.TEventElt[mode][ie].co.woffset==0)
//       w = Math.floor(w / (this.TEventElt[mode][ie].co.doffset+1));
//     else 
//       w = wdiv * ((this.TEventElt[mode][ie].co.doffset+1) - this.TEventElt[mode][ie].co.offset);
  }

  mcalDrawRectAbsolute( this.TEventElt[mode][ie].id,
			this.CalRootElt, 
  			x, 
			this.TEventElt[mode][ie].co.y, 
			w,
			Math.floor(this.TEventElt[mode][ie].co.h), 
			this.TEventElt[mode][ie].co.z, 
			'',
			true, 
			content, 
			evAttr, evStyle);
  
  if (usemenu) {
    this.menus[this.TEvent[evRef].menu.ref].attachToElt( this.TEventElt[mode][ie].id,
							 this.TEvent[evRef].menu.use,
							 'contextmenu',
							 'MCalendar.GHandler',
							 [ this.CalRootElt, this.TEvent[evRef].id, this.TEvent[evRef].rid ] );
  }
  return;
}

// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------

var toffset=new Array();
MCalendar.prototype.eventoffset = function() {
  var po=0;
  var px=0;
  var pk=0;
  var k,kk,cl;
  var col=0;
  var ts=new Array();
  var sl;

  for (k=0;k<this.TEventElt[1].length;k++) {
    
    yi = this.TEventElt[1][k].co.y;
    hi = this.TEventElt[1][k].co.h;
    cl = this.TEventElt[1][k].co.day;
    this.TEventElt[1][k].co.offset=0;
    this.TEventElt[1][k].co.doffset=0;
    this.TEventElt[1][k].co.woffset=0;
//     this.TEventElt[1][k].co.pound = 100*(this.TEventElt[1][k].co.h + this.TEventElt[1][k].co.y) - this.TEventElt[1][k].co.y;
    if (cl > col) { // change column : reinit
      if (po > 0) this.initpo(pk,k,po);
      po=0;
      px=0;
      pk=k;
    }
    col=cl;

    sl=false; // same event group ?
    for (ki=pk;ki<k;ki++) {
      if (yi <= (this.TEventElt[1][ki].co.y + this.TEventElt[1][ki].co.h)) {
	sl=true;
	break; 
      }
    }
   if (sl) {      
     kk=0;
     var fk = false;
     ts = [];
     this.TEventElt[1][k].co.offset = po+1;
     for (ki=k-1;ki>=pk;ki--) {
       if (! in_array(this.TEventElt[1][ki].co.offset,ts) ) {
	 if (   yi >= (this.TEventElt[1][ki].co.y + this.TEventElt[1][ki].co.h) 
		&& this.TEventElt[1][k].co.offset>this.TEventElt[1][ki].co.offset) {
	   // try to place in highter subline
	   this.TEventElt[1][k].co.offset = this.TEventElt[1][ki].co.offset;//+0.001;
	   this.TEventElt[1][k].co.woffset = 1;
	   fk=true;
	   //  	      break;
	 }
	 ts.push(this.TEventElt[1][ki].co.offset);
       }
       kk++;
     }
     if (!fk) po++;

   } else {
     // new subline
     this.initpo(pk,k,po);
     po=0; 
     pk=k;
   }
   px=yi+hi;
  }
  if (po > 0) this.initpo(pk,k,po);
}

MCalendar.prototype.initpo = function(p1,p2,po) {
  for (var ki=p1;ki<p2;ki++) {
    this.TEventElt[1][ki].co.doffset=po;
  }
}

function sortRealElt(a,b) {
  if (a.co.x != b.co.x) return a.co.x - b.co.x;
  if (a.co.y < b.co.y) return -1;
  if (a.co.y==b.co.y) return b.co.h - a.co.h;
  return 1;
}



// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------

MCalendar.prototype.__showDetail = function(event, idev, ridev) {

  if (document.getElementById(ridev)) {
    var vev = document.getElementById(ridev);
    var evcoord = mcalEventXY(event);
    with (vev) {
      style.left = evcoord.x - 20;
      style.top = evcoord.y - 20;
      style.display = '';
    }
  } else {
    
    var rq;
    try {
      rq = new XMLHttpRequest();
    } catch (e) {
      rq = new ActiveXObject("Msxml2.XMLHTTP");
    }
    rq.instanceName = this.CalRootElt;
    rq.evcoord = mcalEventXY(event);
    rq.onreadystatechange =  function() {
      if (rq.readyState == 4) {
	var instance = document.__mcal[rq.instanceName];
	instance.__hideMessage();
	if (rq.status==200) {
	  if (rq.responseText) {
	    var istyle = [
		{ id:'padding', val:'5px' },
		{ id:'border', val:'3px groove orange' },
		{ id:'background-color', val:'white' },
		{ id:'cursor', val:'pointer' },	      
		];
	    var iattr = [
		{ id:'onmouseout', val:"setTimeout('document.getElementById(\\'"+ridev+"\\').style.display = \\'none\\'', 2000);" },
		{ id:'onclick', val:'document.getElementById(\''+ridev+'\').style.display=\'none\'' },
		{ id:'title', val:'click to close' }
	      ];
	    mcalDrawRectAbsolute(ridev, '', rq.evcoord.x-20, rq.evcoord.y-20, 'auto', 'auto', 20000, '', true, rq.responseText, iattr, istyle); 
	  }
	} else {
	  mcalShowError("Erreur de communication avec le serveur : "+req.statusText);
	}
      }
    }
    MCalendar.__waitingServer();
    var serverreq = mcalParseReq( this.serverMethod['eventcard'], [ 'EVID' ], [ idev ]);
    rq.open("GET", serverreq, true);
    rq.send(null);
  }
}


// Log messages
// -----------------------------------------------------------------------------------

MCalendar.prototype.mlog = function(s) { return MCalendar.AddMessage(s); };
MCalendar.prototype.AddMessage = function(s) 
{
  this.Message[this.Message.length] = s;
  return;
}
      
MCalendar.prototype.ViewMessage = function() 
{
  var i;
  var mm = '';
  for (i=0; i<this.Message.length; i++) {
    mm += this.Message[i]+'\n';
  }
  mm += '\n--------------oOo----------------';
  mm += '\n MCalendar (C) 2005 Marc '
  mm += '\n <marc.claverie (at) gmail.com>';
  mm += '\n--------------oOo----------------';
  if (!confirm(mm)) this.Message.splice(0);
  return;
}
      
MCalendar.prototype.sDT = function(d) 
{ 
  return d.toLocaleDateString()+' '+d.toLocaleTimeString(); 
}

MCalendar.prototype.sDTs = function(ts) 
{ 
  d = new Date(ts); 
  return this.sDT(d); 
}
      
      
// Inline event creation
// -----------------------------------------------------------------------------
      
MCalendar.prototype.createNewEvent = function(evt) 
{
  evt = (evt) ? evt : ((event) ? event : null );
  var cc = (evt.keyCode) ? evt.keyCode : evt.charCode;
  var evtitle = document.getElementById('evtitle');
  if ((cc == 13)) 
    {
      if (evtitle.value != '') 
	{
	  // Submit form or send xmlHTTPRequest...
	  var dd = new Date();
	  this.AddEvent(EventTime, 3600, 0, 'evrx', '', '' );
	  this.__displayEvents();
	  document.getElementById('inputzone').style.display = 'none';
	  return false;
	} 
      else 
	{
	  document.getElementById('inputzone').style.display = 'none';
	}
    }
  return true;
}
  
MCalendar.prototype.initNewEvent = function(e)  
{
  var start = mcalGetZoneCoord(this.CalZonePStart);
  var end = mcalGetZoneCoord(this.CalZonePEnd);
  GetXY(e);
	
  EventTime = this.GetTimeFromXY(e);

  var cc = this.GetXYForTime(EventTime);
  var ff = document.getElementById('inputzone');
  ff.style.top = cc.y;
  ff.style.left = cc.x;
  ff.style.width = this.CalHourWidth;
  ff.style.overflow = 'hidden';
  ff.style.display = '';
  
  document.getElementById('evtitle').style.background = '';
  document.getElementById('evtitle').value = '';
  document.getElementById('evtitle').focus();
  
  return;
}


// -------------------------------------------------
// Coordinates computation Time --> XY and XY to Time
// -------------------------------------------------
MCalendar.prototype.GetXYForTime = function(time) 
{
  var coord = { x:0, y:0 };
  var id;
  var day;
  
  if (time<this.CalPeriod[0].ds.getTime()) 
    {
      time = this.CalPeriod[0].ds.getTime();
      day = 1; 
    } 
  else if (time>this.CalPeriod[(this.CalPeriod.length-1)].de.getTime()) 
    {
      time = this.CalPeriod[(CalPeriod.length-1)].he.getTime();
      day = (this.CalPeriod.length-1);
    } 
  else 
    {
      day = -1;
      for (id=0; id<this.CalPeriod.length && day==-1; id++) {
	if (time>=this.CalPeriod[id].ds.getTime() && time<=this.CalPeriod[id].de.getTime())   {
	  day=id+1;
	}
      }
    }
	
  sevent = new Date();
  sevent.setTime(time + (sevent.getTimezoneOffset()*60*1000));
  shour = sevent.getHours();
  smin  = sevent.getMinutes();
	
  var selt = 'd'+day+'h'+(shour-this.CalDayStartHour+2);
  var coord = mcalGetZoneCoord(selt);

  return coord;
}

MCalendar.prototype.GetTimeFromXY = function(e) {
	
  var start = mcalGetZoneCoord(this.CalZonePStart);
  var end = mcalGetZoneCoord(this.CalZonePEnd);
  GetXY(e);
	
  // Compute the day
  var cd  = Math.floor((Xpos - start.x) / start.w);
  if (cd<0) cd = 0;
  if (cd>this.CalPeriod.length-1) cd = this.CalPeriod.length-1;
  
  // compute hour
  Ypos = (Ypos<start.y?start.y:Ypos);
  Ypos = (Ypos>(end.y + end.h)?(end.y + end.h):Ypos);
	
  var sec = Math.floor((this.CalPeriod[cd].he.getTime() - this.CalPeriod[cd].hs.getTime()) / (end.y + end.h - start.y));
  var dX = (sec * Ypos) + this.CalPeriod[cd].hs.getTime();
	
  rr = 15 * 60 * 1000;
  rdX = Math.floor(dX / rr) * rr;
  var rdP = new Date(rdX - 0*(this.CalPeriod[cd].hs.getTimezoneOffset()*60*1000));
    
  return rdP.getTime();
}
  



MCalendar.prototype.gotoPrevPeriod = function()
{
  cD = new Date(this.CalInitTime);
  dPrev = new Date(cD.getFullYear(), cD.getMonth(), (cD.getDate() - this.CalDaysCount), 0, 0, 0, 0);
  this.CalInitTime = dPrev.getTime();
  this.isComputed = false;
  this.__deleteEvent(-1, true);
  this.__display();
  this.__getEvents();
  return;
}

MCalendar.prototype.gotoNextPeriod = function()
{
  cD = new Date(this.CalInitTime);
  dNext = new Date(cD.getFullYear(), cD.getMonth(), (cD.getDate() + this.CalDaysCount), 0, 0, 0, 0);
  this.CalInitTime = dNext.getTime();
  this.isComputed = false;
  this.__deleteEvent(-1, true);
  this.__display();
  this.__getEvents();
  return;
}

MCalendar.prototype.gotoCurrentPeriod = function()
{
  cD = new Date(this.CalOriginalTime);
  this.CalInitTime = cD.getTime();
  this.isComputed = false;
  this.__deleteEvent(-1, true);
  this.__display();
  this.__getEvents();
  return;
}

MCalendar.prototype.ShowHideWeekEnd = function() 
{
  this.CalShowWeekEnd = (this.CalShowWeekEnd?false:true);
  this.isComputed = false;
  this.__deleteEvent(-1, false);
  this.__display();
  this.__displayEvents();
  return true;
}

MCalendar.prototype.Resize = function() 
{
  this.isComputed = false;
  this.__deleteEvent(-1, false);
  this.__display();
  this.__displayEvents();
  return true;
}



MCalendar.reloadEvent = function(event, calendar, eid) {
  alert('MCalendar.reloadEvent: event '+calendar+' '+eid);
}
MCalendar.reloadAllEvents = function(event, calendar) {
  alert('MCalendar.reloadAllEvents: event '+calendar);
}
MCalendar.deleteEvent = function(event, calendar, eid) {
  var cal = new MCalendar(calendar);
  if (cal.__deleteEvent && cal.__getEventById ) {
    var iev = cal.__getEventById(eid);
    if (iev) cal.__deleteEvent(iev, true);
  }
}

MCalendar.GHandler  = function(event, mode, type, action, target, hmode, hparam) {
    
  var calendar = hparam[0];
  var eid = hparam[1]
  var eidcard = hparam[2]

  var oparam = new Array;
  for (var ip=3; ip<hparam.length; ip++) oparam[oparam.length] = hparam[ip];

  MCalendar.UserHandler(event, calendar, eid, eidcard, mode, action, target, oparam);
  MCalendar.SystemHandler(event, calendar, eid, type);
  return;
}


MCalendar.SystemHandler  = function(event, calendar, eid, type ) {
  switch (parseInt(type)) {
    
    case 1: // reload event
    MCalendar.reloadEvent(event, calendar, eid);
    break;

    case 2: // reload all event
    MCalendar.reloadAllEvents(event, calendar);
    break;

    case 3: // delete event
    MCalendar.deleteEvent(event, calendar, eid);
    break;
    
  }
    
}

  MCalendar.UserHandler = function(event, calendar, eid, eidcard, type, action, target, oparam ) {
  if (!action && action=='') return false;
  var pscript = mcalParseReq(action, [ 'ECAL', 'EID', 'EICARD' ], [ calendar, eid, eidcard ] );
  switch (parseInt(type)) {

  case 0:
    var cal = new MCalendar(calendar);
    cal.__showDetail(event, eid, eidcard);   
    break;

  case 1: // Create new window....
    window.open( pscript, target);
    break;

  case 2: // JS function call with standard arguments : event, calendar and id
    eval(pscript)(event, calendar, eid, eidcard, oparam);
    break;

  case 3: // Full user spec JS function call
    eval(pscript);
    break;
    
  }
  return;
}

