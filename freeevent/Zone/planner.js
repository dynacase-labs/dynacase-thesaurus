var mstart=0;
var mend=0;
var mdelta=mend-mstart;
var tevents=new Array();
var zoomx=1;
var zoomy=1;
var maxw;
var maxh=300;
var dh=17;//width events
var mdh=6; //margin height between events
var bx;
var by;
var rw=70; // ressource column width
var oy=80; // margin offset in y
var eh=20; // offset event position
var minw=20; // minimum pixel width to see division
var xyby;
var oxgrid; // grid x reference
var oygrid; // grid y reference
var ocdday;
var isFixed=false;

function placeEvt(idx,line,subline,x,w,offset,doff) {
  var dw=maxw*zoomx;
  var dih=(dh+mdh)*zoomy;
  var oimg= document.getElementById('bar'+idx);

  var ores= document.getElementById('res'+idx);
  if (oimg) {
    oimg.style.left=rw+bx+(x-mstart)*dw/mdelta;
    h=eh+by+(subline*dih);
    if (h>maxh) maxh=h;
    if (doff==0) oimg.style.top=h;
    else oimg.style.top=h+(dh*zoomy/(doff+1))*offset;
    //oimg.width=(w)*dw/(mend-mstart);
    //oimg.height=dih;
    if (w < 0)  oimg.style.width=100; 
    else  oimg.style.width=(w)*dw/mdelta;
    oimg.style.height=dh*zoomy/(doff+1);
    oimg.style.display='';
    
    ores.style.top=h;
    ores.style.left=bx;
    ores.style.width=rw;
    ores.style.height=(dh*zoomy);
    ores.style.display='';

    onxgrid=oxgrid.cloneNode(true);
    
    onxgrid.style.top=h-mdh/2;   
    onxgrid.style.left=bx;
    onxgrid.id='gridx'+idx;
    onxgrid.style.width=maxw*zoomx+rw;
    onxgrid.style.height=1;
    onxgrid.style.display='';
    ocdday.appendChild(onxgrid);
  }
}



function placeEvents() {
  mdelta=mend-mstart;
  oxgrid=document.getElementById('xgrid');
  oygrid=document.getElementById('ygrid');
  ocdday=document.getElementById('cdday');
  xyby=getAnchorPosition('bgmilli');
  by=xyby.y+oy;
  bx=xyby.x;
  maxh=100;
  //  alert(getObjectWidth(document.getElementById('bgmilli'));
  mg=parseInt(getCssStyle(document.body, "marginLeft"));
  maxw=Math.max(getObjectWidth(document.getElementById('tcalhead')),400)-rw;
  

  // clear cloned division
  while (ocdday.childNodes.length>0) {
    ocdday.removeChild(ocdday.childNodes[0]);
  }
  for (i=0;i<tevents.length;i++) {
    placeEvt(tevents[i][0],tevents[i][1],tevents[i][2],tevents[i][3],tevents[i][4],tevents[i][6],tevents[i][7]);
  }
  dmilli=document.getElementById('bgmilli');
  if (dmilli) {
    dmilli.style.height=(maxh)-xyby.y+(dh*zoomy);
    dmilli.style.width=parseInt(maxw*zoomx)+rw;
  }
  placeDays();

  bi=document.getElementById('binter');
  if (bi) {
    bi.style.top=by-80;
    bi.style.left=bx;
    bi.style.width=rw;
    bi.style.zIndex=100;
    if (isFixed) bi.style.position='fixed';
    bi.style.display='';
  }
  
}
function placeDays() {
  var nbday=mdelta;
  var odday=document.getElementById('dday');
  var odhour=document.getElementById('dhour');
 
  var dx=0;

  var onday;
  var ndiv=0;
  var nWeek=0;
  var dw=(maxw/mdelta);
  var h=0;

  odday.style.display='';
  odhour.style.display='';
  pXMonth=parseInt(rw+bx); // precedent 
  pXYear=pXMonth;
  pXWeek=pXMonth;
  pDJWeek=0;
  pDJMonth=0;
  for (var i=0;i<nbday;i++) {
    dx=dw*i;
    if (weekDay(mstart+i)=='[TEXT:Monday]') {
      // begin week
      if ((dw*zoomx*7) > minw) { // mini 10 pixel
	
	onweek=odday.cloneNode(true);
	tjdstart[ndiv]=mstart+i-7;
	tjdend[ndiv]=mstart+i;
	onweek.id='DIV'+(ndiv++);
	nWeek=jdToWeekNumber(mstart+i-7);
	onweek.innerHTML=nWeek;
	onweek.title=jd_to_cal(mstart+i);
	onweek.style.width=parseInt(rw+bx+(dx*zoomx))-pXWeek;
	onweek.style.height=20;
	onweek.style.top=by-40;
	onweek.style.left=pXWeek;
	if (isFixed) onweek.style.position='fixed';
	if ((nWeek % 2) == 0) onweek.className='weekOdd';
	else onweek.className='weekEven';
	ocdday.appendChild(onweek);
	nWeek++;
	pDJWeek=i;
	pXWeek=parseInt(rw+bx+(dx*zoomx));

	 // grid x
      if ((dw*zoomx) <= minw) {
	
	onygrid=oygrid.cloneNode(true);
    
	onygrid.style.top=parseInt(onweek.style.top)+20;
	onygrid.style.left=onweek.style.left;
	onygrid.id='gridy'+ndiv;
	onygrid.style.width=1;
	h=maxh-xyby.y+(dh*zoomy)-60;	
	if (h< 0) h=10;
	onygrid.style.height=h;

	onygrid.style.display='';
	ocdday.appendChild(onygrid);
      }
      }
    }
    nDay=parseInt(jd_to_cal(mstart+i,'d'));
    
    if (nDay == 1) {
      nMonth=(jd_to_cal(mstart+i,'M'));
      
      if ((dw*zoomx*30) > minw) { // mini 10 pixel
	// begin month
	onmonth=odday.cloneNode(true);
	tjdiso[ndiv]=jd_to_cal(mstart+i-1,'Y')+'-'+(nMonth-1);
	onmonth.id='DIV'+(ndiv++);

	onmonth.innerHTML=month[(nMonth+10)%12];
	onmonth.style.width=parseInt(rw+bx+(dx*zoomx))-pXMonth;
	onmonth.style.height=20;
	onmonth.style.top=by-60;
	onmonth.style.left=pXMonth;
	if (isFixed) onmonth.style.position='fixed';
	if ((nMonth % 2) == 1) onmonth.className='monthOdd';
	else onmonth.className='monthEven';
	ocdday.appendChild(onmonth);	
	pXMonth=parseInt(rw+bx+(dx*zoomx));
	pDJMonth=i;	 
	// grid x
	if ((dw*zoomx*7) <= minw) {
	
	  onygrid=oygrid.cloneNode(true);
    
	  onygrid.style.top=parseInt(onmonth.style.top)+20;
	  onygrid.style.left=onmonth.style.left;
	  onygrid.id='gridy'+ndiv;
	  onygrid.style.width=1;
	  h=maxh-xyby.y+(dh*zoomy)-40;
	  if (h < 0) h=10;
	  onygrid.style.height=h;
	  onygrid.style.display='';
	  ocdday.appendChild(onygrid);
	}
      }
      if (nMonth == 1) {
	// happy new year
	onyear=odday.cloneNode(true);
	tjdiso[ndiv]=jd_to_cal(mstart+i-1,'Y');
	onyear.id='DIV'+(ndiv++);

	onyear.innerHTML=jd_to_cal(mstart+i-1,'Y');
	onyear.style.width=parseInt(rw+bx+(dx*zoomx))-pXYear;
	onyear.style.height=20;
	onyear.style.top=by-80;
	onyear.style.left=pXYear; 
	onyear.className='year';
	if (isFixed) onyear.style.position='fixed';
	ocdday.appendChild(onyear);	
	pXYear=parseInt(rw+bx+(dx*zoomx));
      }
    }
    if ((dw*zoomx) > minw) {
      onday=odday.cloneNode(true);
      tjdstart[ndiv]=mstart+i;
      tjdend[ndiv]=mstart+i+1;
      onday.id='DIV'+(ndiv++);
      onday.title=weekDay(mstart+i)+jd_to_cal(mstart+i);
      if ((dw*zoomx)>80) onday.innerHTML=weekDay(mstart+i)+' '+jd_to_cal(mstart+i,'d');
      else onday.innerHTML=jd_to_cal(mstart+i,'d');
      onday.style.top=by-20;
      
      onday.style.width=parseInt(dw*zoomx);
      if (isFixed) onday.style.position='fixed';
      //      onday.style.height=(maxh)-xyby.y+(dh*zoomy)-60;
      onday.style.height=20;
      onday.style.left=parseInt(rw+bx+(dx*zoomx));
      if ((i % 2) == 0) onday.className='dayOdd';
      else onday.className='dayEven';
      ocdday.appendChild(onday);
      
      // grid x
      if (true) {
	
	onygrid=oygrid.cloneNode(true);
    
	onygrid.style.top=parseInt(onday.style.top)+20;
	onygrid.style.left=onday.style.left;
	onygrid.id='gridy'+ndiv;
	onygrid.style.width=1;
	h=maxh-xyby.y+(dh*zoomy)-80;
	if (h<0) h=10;
	onygrid.style.height=h;
	onygrid.style.display='';
	ocdday.appendChild(onygrid);
      }
      if ((dw*zoomx) > 300) {
	// include hours
	onhour=odhour.cloneNode(true);
	onhour.id='DIV'+(ndiv++);
	onhour.title=weekDay(mstart+i)+jd_to_cal(mstart+i);
	
	onhour.style.top=by;
	
	onhour.style.width=parseInt(dw*zoomx);
	if (isFixed) onhour.style.position='fixed';
	onhour.style.height=20
	  onhour.style.left=parseInt(rw+bx+(dx*zoomx));
	if ((i % 2) == 0) onhour.className='dayOdd';
	else onhour.className='dayEven';
	ocdday.appendChild(onhour);
      }
    }
  }
  //last Week
  if ((dw*zoomx*7) > minw) { // mini 10 pixel) {
    onweek=odday.cloneNode(true);
    tjdstart[ndiv]=mstart+pDJWeek;
    tjdend[ndiv]=mstart+pDJWeek+7;
    onweek.id='DIV'+(ndiv++);

    onweek.innerHTML=jdToWeekNumber(mstart+pDJWeek);
    onweek.style.width=parseInt(rw+bx+((dw+dx)*zoomx))-pXWeek;
    onweek.style.height=20;
    onweek.style.top=by-40;
    onweek.style.left=pXWeek;
	if (isFixed) onweek.style.position='fixed';
    if ((nWeek % 2) == 0) onweek.className='weekOdd';
    else onweek.className='weekEven';
    ocdday.appendChild(onweek);
    if ((dw*zoomx) <= minw) {
	
      onygrid=oygrid.cloneNode(true);
    
      onygrid.style.top=parseInt(onweek.style.top)+20;
      onygrid.style.left=onweek.style.left;
      onygrid.id='gridy'+ndiv;
      onygrid.style.width=1;
      h=maxh-xyby.y+(dh*zoomy)-60;
      if (h<0) h=10;
      onygrid.style.height=h
      onygrid.style.display='';
      ocdday.appendChild(onygrid);
    }	
  }
  // last month
  if ((dw*zoomx*30) > minw) { // mini 10 pixel
    nMonth=parseInt(jd_to_cal(mstart+i-0.01,'M'));
    // begin month
    onmonth=odday.cloneNode(true);
    tjdiso[ndiv]=jd_to_cal(mstart+i-0.01,'Y')+'-'+nMonth;
    onmonth.id='DIV'+(ndiv++);
	
    onmonth.innerHTML=month[(nMonth+11)%12];
    onmonth.style.width=parseInt(rw+bx+((dw+dx)*zoomx))-pXMonth;
    onmonth.style.height=20;
    onmonth.style.top=by-60;
    onmonth.style.left=pXMonth;
    if (isFixed) onmonth.style.position='fixed';
    if ((nMonth % 2) == 0) onmonth.className='monthOdd';
    else onmonth.className='monthEven';
    ocdday.appendChild(onmonth);	
    pXMonth=parseInt(rw+bx+(dx*zoomx));	
    if ((dw*zoomx*7) <= minw) {
	
	  onygrid=oygrid.cloneNode(true);
    
	  onygrid.style.top=parseInt(onmonth.style.top)+20;
	  onygrid.style.left=onmonth.style.left;
	  onygrid.id='gridy'+ndiv;
	  onygrid.style.width=1;
	  h=maxh-xyby.y+(dh*zoomy)-40;
	  if (h<0) h=10;
	  onygrid.style.height=h;
	  onygrid.style.display='';
	  ocdday.appendChild(onygrid);
	}
  }
  // last year
	// happy new year
	onyear=odday.cloneNode(true);
	tjdiso[ndiv]=jd_to_cal(mstart+i-1,'Y');
	onyear.id='DIV'+(ndiv++);

	if (isFixed) onyear.style.position='fixed';
	onyear.innerHTML=jd_to_cal(mstart+i-0.3,'Y');
	onyear.style.width=parseInt(rw+bx+((dw+dx)*zoomx))-pXYear;
	onyear.style.height=20;
	onyear.style.top=by-80;
	onyear.style.left=pXYear; 
	onyear.className='year';
	ocdday.appendChild(onyear);	
	pXYear=parseInt(rw+bx+(dx*zoomx));
  
	// cache dday
	odday.style.display='none';
  odhour.style.display='none';
}

var tjdstart=new Array();
var tjdend=new Array();
var tjdiso=new Array();
function viewcal(oid) {
  var idx=oid.substr(3);
  var m1=tjdstart[idx];
  var m2=tjdend[idx];
  var miso=tjdiso[idx];

  if (m1 && m2) {
    document.location.href="[CORE_STANDURL]&app=FDL&action=FDL_CARD&zone="+document.zone+"&vid="+document.vid+document.moreurl+"&id="+document.docid+"&jdstart="+m1+'&jdend='+m2;
  }
  if (miso) {
    document.location.href="[CORE_STANDURL]&app=FDL&action=FDL_CARD&zone="+document.zone+"&vid="+document.vid+document.moreurl+"&id="+document.docid+"&isoperiod="+miso;
    
  }

}
function movecal(pc) {
  var d=mdelta*(pc/100);
  var m1=mstart+d;
  var m2=mend+d;


  if (m1 && m2) {
    document.location.href="[CORE_STANDURL]&app=FDL&action=FDL_CARD&zone="+document.zone+"&vid="+document.vid+document.moreurl+"&id="+document.docid+"&jdstart="+m1+'&jdend='+m2;
  }
}

function resizecal() {
  var onp=document.getElementById("nperiod");
  var m1=mstart;
  var m2=mend;
  if (onp && parseInt(onp.value)>0) {
    m2=m1+parseInt(onp.value);
    document.location.href="[CORE_STANDURL]&app=FDL&action=FDL_CARD&zone="+document.zone+"&vid="+document.vid+document.moreurl+"&id="+document.docid+"&jdstart="+m1+'&jdend='+m2;
  }
}
function allcal() {

    document.location.href="[CORE_STANDURL]&app=FDL&action=FDL_CARD&isoperiod=all&zone="+document.zone+"&vid="+document.vid+document.moreurl+"&id="+document.docid+"";
  
}

function viewdesc(event,idx) {
  var dd=document.getElementById('desc');
  if (dd) {
    if (dd.style.display=='none') {
      dd.style.top=0;
      dd.style.left=400;
      dd.style.width='auto';
      dd.style.display='';
      GetXY(event);
      //      dd.innerHTML=Xpos+'+'+Ypos;           
      dbar=document.getElementById('bar'+tevents[tidx[idx]][0]);
      if (dbar) {
	dd.style.backgroundColor=dbar.style.backgroundColor;
	dd.style.borderColor=dbar.style.backgroundColor;
	dd.innerHTML=tevents[tidx[idx]][5];
	w=getObjectWidth(dd);
	w2=getObjectWidth(document.body);

	if ((Xpos+w+20)>w2) Xpos=Xpos-w-40;
	if (Xpos<0) Xpos=0;
	h=getObjectHeight(dd);
	h2=getObjectHeight(document.body);
	//	alert(h2+','+Ypos+','+getFrameHeight());
	cy=(window.event)?window.event.clientY:event.clientY;
	hw=getFrameHeight();
	if ((cy+h+20)>hw) Ypos=Ypos-h-20;
      }
      dd.style.top=Ypos+10;
      dd.style.left=Xpos+10;
      if (getObjectWidth(dd) < 20) dd.style.width='200px'; // bug in mozilla when vscroll bar
    }
  }  
}


function unviewdesc(event) {
  var dd=document.getElementById('desc');
  if (dd) {
    if (dd.style.display!='none') {
      dd.style.display='none';
    }
  }  
}


function subwinevt(w,h,name,url) {  
  var ename=name;
  var wn=null;
  var ww=0;
  if (window.name == 'folio1') wn=windowExist('folio0');
  else if (window.name == 'folio0') wn=windowExist('folio1');

  if (wn != null) {
    ww=getFrameWidth(wn);
    if (ww > 10) wn.location.href=url;
    else subwindow(w,h,ename,url);
  } else { 
    subwindow(w,h,ename,url);
  }
}


function eventsort(a,b) {
  if (a[2] != b[2]) return a[2]-b[2];
  return a[3]-b[3];
}

var toffset=new Array();
var tidx=new Array(); // new index for event
function eventoffset() {
  var po=0;
  var px=0,pk=0;
  var k,kk,li;
  var line=1;
  var ts=new Array();
  
  tevents.sort(eventsort);

  for (k=0;k<tevents.length;k++) {
    tidx[parseInt(tevents[k][0])]=k;
    
    xi=tevents[k][3];
    wi=tevents[k][4];
    li=tevents[k][2];
    tevents[k][6]=0;
    tevents[k][7]=0;
    if (li > line) { // change line : reinit
      if (po > 0) {
	initpo(pk,k,po);
      }
      po=0;
      px=0;
      pk=k;
    }

    line=li;


    sl=false; // same event group ?
    for (ki=pk;ki<k;ki++) {
      if (xi < (tevents[ki][3]+tevents[ki][4])) {
	sl=true;
	break;
      }
    }

    if (sl) {      
	kk=0;
	fk=false;
	ts=[];
	tevents[k][6]=po+1;
	for (ki=k-1;ki>=pk;ki--) {
	  if (! in_array(tevents[ki][6],ts) ) {
	  if (xi >= (tevents[ki][3]+tevents[ki][4])) {
	    // try to place in highter subline
	    tevents[k][6]=tevents[ki][6];//+0.001;
	    fk=true;
	    //	    alert(ki);alert(ts);
	    break;
	  }
	  ts.push(tevents[ki][6]);
	  
	  }
	  kk++;
	}

	if (!fk) po++;
    } else {
      // new subline
      initpo(pk,k,po);
      po=0; 
      pk=k;
    }
    px=xi+wi;

  }
  if (po > 0) {
    initpo(pk,k,po);

  }
}

function initpo(p1,p2,po) {

  for (var ki=p1;ki<p2;ki++) {
    tevents[ki][7]=po;
    //     tevents[ki][5]=tevents[ki][5]+'<h1>'+ki+')PO:'+tevents[ki][6]+'-'+tevents[ki][7]+'['+p1+','+p2+']'+'</h1>';

  }

}
function initoffset() {
  var k;
  

  for (k=0;k<tevents.length;k++) {
    tidx[parseInt(tevents[k][0])]=k;    
    tevents[k][6]=0;
    tevents[k][7]=0;
  }
}
var tevtrot=['evtres','evttitle',''];
var ievtrot=0;
function showrot() {
  var n=tevtrot[ievtrot];

  if (ievtrot == 2) {
    // showall
    displaybyname(tevtrot[0],'');   
    displaybyname(tevtrot[1],'');
  } else if (ievtrot == 1) {
    // showall
    displaybyname(tevtrot[1],'');
    displaybyname(tevtrot[0],'none');
  } else if (ievtrot == 0) {
    // showall
    displaybyname(tevtrot[0],'');
    displaybyname(tevtrot[1],'none');
  }

  ievtrot = (ievtrot +1)%3;    
  
}

function displaybyname(n,d) {
  var le = document.getElementsByName(n);

  for (var i=0;i<le.length;i++) {
    le[i].style.display=d;
  }

}

function in_array(e,t) {  
  for (var i=0;i<t.length;i++) {
    if (t[i]==e) return true;
  }
  return false;
}
