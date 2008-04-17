include_js('FDL/Layout/popupdocmenu.js');
include_js('FDL/Layout/popupfunc.js');
include_js('FDL/Layout/prototree.js');
include_js('FDL/Layout/iframe.js');
function viewmakermenu(event,type,upobject) {
  var corestandurl=window.location.pathname+'?sole=Y&';
  var menuapp='MAKER';
  var menuaction='MAKER_MENU';
  var menuopt='&type='+type;

  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+menuopt;
  viewsubmenu(event,menuurl,upobject);
}


function viewprojecttree(event,where) {

  poptree(event,'[TEXT:the project tree]',10,20,300,500);
  //poptest(event,'[TEXT:the test]',315,20,800,500);
  var CORE_STANDURL=window.location.pathname+'?sole=Y&';
  var url= CORE_STANDURL+'app=MAKER&action=MAKER_TREE&type=top';
  reloadtree(event,'toptree_c',url);
}
addEvent(window, 'load', viewprojecttree);



// create popup for insert div after
function poptree(event,divtitle,x,y,w,h) {
  var dpopdiv = document.getElementById('toptree_s');
  var fpopdiv;
  if (! dpopdiv) {
    new popUp(x, y, w, h, 'toptree', 'A', '[CORE_BGCOLOR]', '[CORE_TEXTFGCOLOR]', '16pt serif', divtitle, '[COLOR_B5]', '[CORE_TEXTFGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', 'black', true, true, true, true, false, false,true);

    $('toptree_c').addClassName('prototree');
    
  } 
}

// create popup for insert div after
function poptest(event,divtitle,x,y,w,h) {
  var dpopdiv = document.getElementById('poptest_s');
  var fpopdiv;
  if (! dpopdiv) {
    new popUp(x, y, w, h, 'poptest', 'about:blank', '[CORE_BGCOLOR]', '[CORE_TEXTFGCOLOR]', '16pt serif', divtitle, '[COLOR_B5]', '[CORE_TEXTFGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', 'black',true, true, true, true, true, false, true);
   
  } 
}


function openSingleFrame(name,url,title) {
  var x=30;
  var y=30;
  var w=200;
  var h=100;
  var dpopdiv = document.getElementById(name+'_s');
  var fpopdiv;
  if (! dpopdiv) {
    new popUp(x, y, w, h, name, url, '[CORE_BGCOLOR]', '[CORE_TEXTFGCOLOR]', '16pt serif', title, '[COLOR_B5]', '[CORE_TEXTFGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', 'black',true, true, true, true, true, false, true);
  } else {
    changecontent(name,url);
  }
}


function openTabFrame(name,taburl,title) {
  var x=30;
  var y=30;
  var w=200;
  var h=100;
  var dpopdiv = document.getElementById(name+'_s');
  var fpopdiv;
  var text='<div id="menutabs'+name+'"></div><div class="windowstabs" id="windowstabs'+name+'"></div>';
  if (! dpopdiv) {
    new popUp(x, y, w, h, name, text, '[CORE_BGCOLOR]', '[CORE_TEXTFGCOLOR]', '16pt serif', title, '[COLOR_B5]', '[CORE_TEXTFGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', 'black',true, true, true, true, false, false, true);
  } else {
    changecontent(name,taburl);
  }
}


function addTabEntry(dpopdiv,mainframe,tabname,tabtitle) {
  var x=30;
  var y=30;
  var w=200;
  var h=100;
  var menu = $('menutabs'+mainframe);

  if (! menu) {
    alert('no menu '+name);
  } else {
    var idf='menuentry'+tabname;
    var tab=$(idf);
    if (tab) {
    } else {

      var nf=document.createElement('div');
      nf.id=idf;
      //      nf.name=idf;
      nf.className='entrytab';
      nf.innerHTML=tabtitle;
      nf.observe('click',function (event) {displaytab(tabname); });
      menu.appendChild(nf); 
    }
  }
}

function openFrameInTabFrame(mainframe,tabname,taburl,tabtitle) {
  var x=30;
  var y=30;
  var w=200;
  var h=100;
  var dpopdiv = $('windowstabs'+mainframe);

  if (! dpopdiv) {
    alert('no frame '+name);
  } else {
    var idf='tab'+tabname;
    var tab=$(idf);
    if (tab) {
      displaytab(tabname);
    } else {
      var nf=document.createElement('iframe');
      nf.id=idf;
      //      nf.name=idf;
      nf.className='windowtab';

      nf.src=taburl;
      undisplaytabs(dpopdiv);
      dpopdiv.appendChild(nf); 
      addTabEntry(dpopdiv,mainframe,tabname,tabtitle);
      displaytab(tabname);
    }
  }
}

function undisplaytabs(main) {
  var tabs=$A(main.getElementsByClassName('windowtab'));//$$('iframe.windowtab','div.windowtab');
   //var tabs=$$('iframe.windowtab','div.windowtab');
  tabs.each(function(tab){
      tab.hide();
    });
  tabs=$A(main.getElementsByClassName('entrytab'));
  tabs.each(function(tab){
      tab.addClassName('unselect');
      tab.removeClassName('select');
    });  
}
function displaytab(tabname) { 
  //undisplaytabs(main);
  var idf='tab'+tabname;
  var tab=$(idf);
  if (tab) {
    undisplaytabs(tab.parentNode.parentNode);
    tab.show();
  }

  var ih=tab.parentNode.parentNode.getHeight() - tab.positionedOffset().top;
  //  alert(ih);
  tab.style.height=(ih-8)+'px';
  idf='menuentry'+tabname;
  tab=$(idf);
  if (tab) {    
      tab.addClassName('select');
      tab.removeClassName('unselect');
  }
  
}


function openDivInTabFrame(mainframe,tabname,taburl,tabtitle) {
  var x=30;
  var y=30;
  var w=200;
  var h=100;
  var dpopdiv = $('windowstabs'+mainframe);

  if (! dpopdiv) {
    alert('no frame '+name);
  } else {
    var idf='tab'+tabname;
    var tab=$(idf);
    if (tab) {
      displaytab(tabname);
    } else {
      var nf=document.createElement('div');
      nf.id=idf;
      //      nf.name=idf;
      nf.className='windowtab';
      nf.innerHTML=getUrlContent(taburl);

      undisplaytabs(dpopdiv);
      dpopdiv.appendChild(nf); 
      addTabEntry(dpopdiv,mainframe,tabname,tabtitle);
      displaytab(tabname);
    }
  }
}



function getUrlContent(aurl){
    var temp;
    new Ajax.Request(aurl, {
      method: 'get',
      asynchronous:false,
      onComplete: function(transport) {        
        temp = transport.responseText;
      }
    });
    return temp;
}
