
  // --------------------------------------------------
function addbranch(fldtopnode, BeginnEntries) {
  // --------------------------------------------------


  
    // --------------------------------------------------
    // first part : compose the logical tree, set html object in current frame
    // --------------------------------------------------
    // find the leftside

      var images = fldtopnode.navObj.getElementsByTagName('img');   
  var leftSide="";
   var ffolder = window.open('','ffolder','');

   for (i=0 ; i < images.length-2; i++)    { 
      leftSide  += '<img src="'+images[i].src+'" width=16 height=22>';
   }
   if (fldtopnode.isLastNode) {
     leftSide  += '<img src="FREEDOM/Images/ftv2blank.gif" width=16 height=22>';
   } else {
     leftSide  += '<img src="FREEDOM/Images/ftv2vertline.gif" width=16 height=22>';
   }
   

  // init the logical tree in ffolder :: add a branch in fldtopnode node
  ffolder.doc=document;
  ffolder.nEntries = BeginnEntries;

    var level=1;
    for (i=0 ; i < fldtopnode.nChildren; i++)    { 
      if (i == fldtopnode.nChildren-1) 
        fldtopnode.children[i].initialize(level, 1, leftSide) 
      else 
        fldtopnode.children[i].initialize(level, 0, leftSide) 
    } 
  
  fldtopnode.setState(false);
  fldtopnode.setState(true);


  // restore parameters
  ffolder.doc=ffolder.document;


  // --------------------------------------------------
  // second part : copy html object in the initial frame
  // --------------------------------------------------
 
	
  var divs = document.getElementsByTagName("div");
 
  var ndiv=divs.length;

  if (ndiv > 1) {


    var divtoinsert = null;
    var flddiv = ffolder.document.getElementById('folder'+ffolder.fldidtoexpand);

    if (flddiv)
      divtoinsert=flddiv.nextSibling;


    //   alert('nch1:'+ffolder.indexOfEntries[ffolder.fldidtoexpand].nChildren);
    for (var i=1; i < ndiv; i++)  {
      
      //   alert(ffolder.fldidtoexpand);
      
      h=  ffolder.document.createElement("div");
      h.innerHTML= divs[i].innerHTML;
      h.id= divs[i].id;
      h.className= divs[i].className;

      var ne = BeginnEntries+i-1;

      
      ffolder.document.getElementById('bodyid').insertBefore(h,divtoinsert);
      
      divs[i].style.backgroundColor='yellow';
      
      ffolder.indexOfEntries[ne].navObj=h;  
      ffolder.indexOfEntries[ne].iconImg=ffolder.document.getElementById('folderIcon'+ne);  
      ffolder.indexOfEntries[ne].nodeImg=ffolder.document.getElementById('nodeIcon'+ne);  
      
      
    }
    
   
    


  }

  
}


// --------------------------------------------------
function copypopup( tdivpopup, BeginnEntries ) {
// --------------------------------------------------

    var ffolder = window.open('','ffolder','');
    for (var i=1; i< tdivpopup['popfld'].length; i++) {
      ffolder.tdiv['popfld'][i-1+BeginnEntries]=tdivpopup['popfld'][i];
      ffolder.tdiv['poppaste'][i-1+BeginnEntries]=tdivpopup['poppaste'][i];
    }
}

     
     
