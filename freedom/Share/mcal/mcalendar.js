// This source IS NOT DISTRIBUTED UNDER FREE LICENSE (like GPL or Artistic...)
// For any usage -commercial, private or other- you have to pay Marc Claverie.

function MCalendar(instance, server, tsparam, teparam) 
{
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
  this.showTitleBar = true;
  this.showNavButton = true;
  this.Title = 'MCalendar (c) Marc &lt;marc.claverie (@) gmail.com&gt;';

  
  this.serverMethod = Array();
  this.serverMethod['allevents'] = server[0];
  this.serverMethod['evdetail'] = server[1];

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
  this.EventTime = 0;
      
  this.Message = new Array();
  
  if (!document.__mcal) document.__mcal = new Array;
  document.__mcal[this.CalRootElt] = this;

  this.menus = new Array();
  
  this.isComputed = false;
  this.debug = false; // true;
}


MCalendar.prototype.__deleteElt = function(elt) {
  if (!document.getElementById(elt)) return;
  var e = document.getElementById(elt);
  e.parentNode.removeChild(e);
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
  
  var aattr = [ 
    { id:'onclick', val:"if (event.shiftKey) alert('la grosse edition'); else if (event.ctrlKey) document.__mcal["+this.CalRootElt+"].ViewMessage(); else document.__mcal["+this.CalRootElt+"].initNewEvent(event)" } ];
  var astyle = [
    { id:'background-color', val:'' },
    { id:'display', val:'' }  ];
	  	
  mcalDrawRectAbsolute('__MCal'+this.CalRootElt, 
			this.CalRootElt, 
			this.Dim.x, 
			this.Dim.y, 
			this.Dim.w , 
			this.Dim.h, 
			1000, '', false, '', aattr, astyle);
  this.isComputed = true;

  return;
}

MCalendar.prototype.__getEvents = function() 
{
  if (!this.serverMethod['allevents']) return;
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
	  dmode = events[ie].getAttribute("dmode");
	  etime = events[ie].getAttribute("time");
	  duration = events[ie].getAttribute("duration");
	  title = events[ie].getElements("title");

	  var menuref = events[ie].getElements("menuref");
	  var evmenu = null;
	  var ref = menuref[0].getAttribute('id');
	  if (!ref || !instance.menus[ref]) mcalShowError('Event['+(ie+1)+'], menu reference : attribute id empty or referenced menu '+(ref?ref:'')+' does not exist');
	  else {
	    var sref = new String(menuref[0].getAttribute('id'));
	    if (!sref) mcalShowError('Event['+(ie+1)+'], menu reference : use attribute missing or empty');
	    else {
	      evmenu = { ref:ref, use:sref.split(',') };
	    }
	  }

	  content = events[ie].getElements("content")[0];
	  tstyle = content.getElements('styleinfo')[0];
	  styles = tstyle.getElements('style');
	  for (var ist=0; ist<styles.length; ist++) {
	    vstyles[vstyles.length] = { id:styles[ist].getAttribute('id'), val:styles[ist].getAttribute('val') };
	  }
	  var hcontent = content.getElements('chtml')[0];
	  tcontent = hcontent.getUnderlyingXMLText();

          instance.AddEvent(id, idcard, rid, dmode, parseInt(etime*1000), parseInt(duration*1000), title[0].getText(), tcontent, vstyles, evmenu);
	}
	instance.__hideMessage();
        if (instance.TEvent.length>0) instance.__displayEvents();
      } else {
        instance.__hideMessage();
        var zTs = Math.round(instance.CalPeriod[0].ds.getTime()/1000);
        var zTe = Math.round(instance.CalPeriod[(instance.CalPeriod.length-1)].de.getTime()/1000);
        mcalShowError("Can't get serveur response (XML datas) ["+rq.statusText+"]<br>Http request = "+this.serverMethod['allevents']);
      }
    }
  }
  this.__showMessage("Interrogation du serveur...");
  var zTs = Math.round(this.CalPeriod[0].ds.getTime()/1000);
  var zTe = Math.round(this.CalPeriod[(this.CalPeriod.length-1)].de.getTime()/1000);
  var serverreq = mcalParseReq( this.serverMethod['allevents'], [ 'TS', 'TE'], [ zTs, zTe ]);
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


MCalendar.prototype.__showMessage = function(info) {
  if (this.debug) {
    var dt = new Date()
    this.start = dt.getTime();
  }
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
  var dclass = '';
  var eltn = '';
  var cx, cy, cw, ch;
  var idh, ida;
  var dayXPos = 0;
  var dayOfWeek = -1;
  var totalW = 0; 
  var hide = false;
 
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
    
    if (ida==0) {
      dayXPos = (hide?0:1);
      cw = (hide?0:this.CalKTitleHourW);
    } else if (ida==1) {
      dayXPos += (hide?0:this.CalKTitleHourW+(2*this.xborder));
      cw = (hide?0:this.CalHourWidth);
    } else {
      dayXPos += (hide?0:this.CalHourWidth + (2*this.xborder));
      cw = (hide?0:this.CalHourWidth);
    }

    for (idh=0; idh<(this.CalHoursPerDay+3); idh++) {
      dclass = this.dayBaseCss;
      if (ida>0 && (idh==1 || idh==(this.CalHoursPerDay+2))) 
	dclass += " "+this.daynhCss;
      else if (ida==0 || idh==0) 
	dclass += " "+this.dayTitleCss;
      else if (ida>0 && (this.CalPeriod[ip].ds.toLocaleDateString() == today.toLocaleDateString())) 
	dclass += " "+this.dayCurrentCss;
      else if ((dayOfWeek==6 || dayOfWeek==0) && ida!=0)  
	dclass += " "+this.dayWeekEndCss[(ida%2)];
      else 
	dclass += ' '+this.dayCss[(ida%2)];
      
      idel = 'd'+ida+'h'+idh;
      eltn = '';
      if (ida==0 && idh==0) {
	if (this.showNavButton) {
	  eltn = '<img width="10" title="" src="mcalendar-prev.png" onclick="document.__mcal.'+this.CalRootElt+'.gotoPrevPeriod();" style="border:0; cursor:pointer">';
	  eltn += '<img width="10" title="" src="mcalendar-current.png" onclick="document.__mcal.'+this.CalRootElt+'.gotoCurrentPeriod();" style="border:0; cursor:pointer">';
	  eltn += '<img width="10" title="" src="mcalendar-next.png" onclick="document.__mcal.'+this.CalRootElt+'.gotoNextPeriod();" style="border:0; cursor:pointer">';
	} else {
	  eltn = '';
	}
      }
      if (ida>0 && idh==0) {
	  eltn += this.CalGetDayOfWeekLabel(this.CalPeriod[ip].ds.getDay())
	    +     ' ' + this.CalPeriod[ip].ds.getDate()
	    +     ' ' + this.CalGetMonthLabel(this.CalPeriod[ip].ds.getMonth())
	    }
      if (ida==0 && idh>1 && idh<=this.CalHoursPerDay+1) eltn += this.CalDayStartHour + (idh-2)+ 'h00';    
      if (ida>0) {
	var title = this.CalPeriod[ip].ds.toLocaleDateString() 
	  + ', '+ (this.CalDayStartHour + (idh-2)) + 'h00 ' 
	  + (this.CalDayStartHour + (idh-1)) + 'h00';
      }
      
      if (idh==0) {
	cy = (this.showTitleBar?this.CalKTitleDayH+(2*this.yborder)+1:0);
	ch = this.CalKTitleDayH;
      } else {
	cy = (this.showTitleBar?this.CalKTitleDayH+(2*this.yborder):0) + (this.CalKTitleDayH+(2*this.yborder)) + ((idh-1) * (this.CalHourHeight+(2*this.yborder)));
	ch = this.CalHourHeight;
      }
      
      var attr = [ { id:'title', val:title } ];
      mcalDrawRectAbsolute(idel, this.CalRootElt, dayXPos,cy,cw,ch, 500, dclass, (hide?false:true), eltn, attr);
    }
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
  this.TEvent[idx] = { id:id, idcard:idcard, rid:rid, mode:dmode, time:time, duration:duration, 
		       title:title, content:content, style:style, menu:menu };
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
  // var menu = this.TEvent[iev].a;
  // for (var ix=0; ix<menu.length; ix++) {
            //maccesst[maccesst.length] = { def:defaultv, typ:type, lab:label, tar:target, act:action };
    // pev += '\t\t [menu='+menu[ix].lab+' ['+menu[ix].def+'] '+menu[ix].act+' ('+menu[ix].type+') --> '+menu[ix].tar+']\n';
  // } 
  pev += '}';
  mcalShowTrace(pev);
}
  

MCalendar.prototype.__deleteEvent = function(iev, deleteEntry) {
  if (iev==-1) {
    for (var ie=0; ie<this.TEvent.length; ie++) {
      for (var ielt=0; ielt<this.TEvent[ie].evelt.length; ielt++) {     
	this.__deleteElt(this.TEvent[ie].evelt[ielt]);
      }
    }
    if (deleteEntry) this.TEvent.splice(0);
  }  else if (iev>=this.TEvent.length) {
    return false;
  } else {
    for (var ielt=0; ielt<this.TEvent[ie].evelt.length; ielt++) {     
      this.__deleteElt(this.TEvent[i].evelt[ielt]);
    }
    if (deleteEntry) this.TEvent.splice(iev, 1);
  }
  return true;
}

MCalendar.prototype.__displayEvents = function() {
  var ie=0;
  for (ie=0; ie<this.TEvent.length; ie++) {
    this.__showEvent(ie);
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

MCalendar.prototype.__showEvent = function(ie) 
{
  var day;
  var istart = iend = 0;
  var rctime = { day:-1, time:0 };
  
  if (!this.TEvent[ie].evelt) this.TEvent[ie].evelt = new Array;
  
  var start = this.TEvent[ie].time;
  var duration = this.TEvent[ie].duration;
  var content = this.TEvent[ie].content;
  var id = this.TEvent[ie].id;
  var rid = this.TEvent[ie].rid;
  var idcard = this.TEvent[ie].idcard;
  var mode = this.TEvent[ie].mode;
  
  var evStyle = new Array;
  for (var is=0; is<this.TEvent[ie].style.length; is++) {
    evStyle[evStyle.length] = this.TEvent[ie].style[is];
  }
  evStyle[evStyle.length] = { id:'overflow', val:'hidden'};
  evStyle[evStyle.length] = { id:'cursor', val:'pointer' };



  var usemenu = false;
  var evAttr = new Array;
  evAttr = [
      { id:'onclick', 	val:'if (event.shiftKey) alert("shift"); else if (event.ctrlKey) alert("ctrl"); else document.__mcal.'+this.CalRootElt+'.__showDetail(event, \''+id+'\',\''+idcard+'\')' },
      { id:'title', val:'click to show details' }
  ];
  if (this.TEvent[ie].menu.ref && this.menus[this.TEvent[ie].menu.ref]) usemenu = true;
  
  var ostart = start;
  var oend = start+duration;
  
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

  for (id=sday; id<=eday; id++) {
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
      
      var selt = 'd'+(id+1)+'h'+(shour-this.CalDayStartHour+2);
      var coords = mcalGetZoneCoord(selt);
      coords.y = coords.y + (smin * this.CalPixelForMinute);
      
      ehour = eevent.getHours();
      emin  = eevent.getMinutes();
      
      var eelt = 'd'+(id+1)+'h'+(ehour-this.CalDayStartHour+2);
      var coorde = mcalGetZoneCoord(eelt);
      coorde.y = coorde.y + (emin * this.CalPixelForMinute);
      
      var H = coorde.y - coords.y;
      
      this.TEvent[ie].evelt[this.TEvent[ie].evelt.length] = '__ev_'+ie+'_d_'+id;
      mcalDrawRectAbsolute( '__ev_'+ie+'_d_'+id,
			    this.CalRootElt, 
			    coords.x+(mode==0?1:4), 
			    coords.y,
			    coords.w-(mode==0?4:10), 
			    H-2, 
			    2000, 
			    '',
			    true, 
			    content, 
			    evAttr, evStyle);
      
      if (usemenu) {
	if (!document.getElementById(this.TEvent[ie].menu.ref)) this.menus[this.TEvent[ie].menu.ref].create();
	this.menus[this.TEvent[ie].menu.ref].attachToElt( '__ev_'+ie+'_d_'+id, 
							  this.CalRootElt, 
							  this.TEvent[ie].id, 
							  'contextmenu',
							  "document.__mcal."+this.CalRootElt+".__showDetail(event, "+this.TEvent[ie].id+", '"+this.TEvent[ie].rid+"')" );
      }

    }
  }
  return;
}

MCalendar.prototype.__showDetail = function(event, idev, ridev) {
  
  //   if (!document.__mcal[cal]) {
  //     mcalShowError('MCalendar.showDetail:: No such calendar '+cal);
  //     return;
  //   }
  
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
    this.__showMessage("Interrogation du serveur...");
    var serverreq = mcalParseReq( this.serverMethod['evdetail'], [ 'EVID' ], [ idev ]);
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



