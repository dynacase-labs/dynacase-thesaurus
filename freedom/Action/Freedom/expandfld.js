
  // --------------------------------------------------
function addbranch(fldtopnode, BeginnEntries) {
  // --------------------------------------------------


  
  // --------------------------------------------------
  // first part : compose the logical tree, set html object in current frame
  // --------------------------------------------------
   // find the leftside

   var images = fldtopnode.navObj.getElementsByTagName('img');   
    var leftSide="";
   for (i=0 ; i < images.length-2; i++)    { 
      leftSide  += '<img src="'+images[i].src+'" width=16 height=22>';
   }
   if (fldtopnode.isLastNode) {
     leftSide  += '<img src="FREEDOM/Images/ftv2blank.gif" width=16 height=22>';
   } else {
     leftSide  += '<img src="FREEDOM/Images/ftv2vertline.gif" width=16 height=22>';
   }
   

  // init the logical tree in parent.ffolder :: add a branch in fldtopnode node
  parent.ffolder.doc=document;
  parent.ffolder.nEntries = BeginnEntries;

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
  parent.ffolder.doc=parent.ffolder.document;


  // --------------------------------------------------
  // second part : copy html object in the initial frame
  // --------------------------------------------------
 
	
  var divs = document.getElementsByTagName("div");
 
  var ndiv=divs.length;

  if (ndiv > 1) {


    var divtoinsert = null;
    var flddiv = parent.ffolder.document.getElementById('folder'+parent.ffolder.fldidtoexpand);

    if (flddiv)
      divtoinsert=flddiv.nextSibling;


    //   alert('nch1:'+parent.ffolder.indexOfEntries[parent.ffolder.fldidtoexpand].nChildren);
    for (var i=1; i < ndiv; i++)  {
      
      //   alert(parent.ffolder.fldidtoexpand);
      
      h=  parent.ffolder.document.createElement("div");
      h.innerHTML= divs[i].innerHTML;
      h.id= divs[i].id;
      h.className= divs[i].className;

      var ne = BeginnEntries+i-1;

      
      parent.ffolder.document.getElementById('bodyid').insertBefore(h,divtoinsert);
      
      divs[i].style.backgroundColor='yellow';
      
      parent.ffolder.indexOfEntries[ne].navObj=h;  
      parent.ffolder.indexOfEntries[ne].iconImg=parent.ffolder.document.getElementById('folderIcon'+ne);  
      parent.ffolder.indexOfEntries[ne].nodeImg=parent.ffolder.document.getElementById('nodeIcon'+ne);  
      
      
    }
    
   
    


  }

  
}


// --------------------------------------------------
function copypopup( tdivpopup, BeginnEntries ) {
// --------------------------------------------------

    for (var i=1; i< tdivpopup['popfld'].length; i++) {
      parent.ffolder.tdiv['popfld'][i-1+BeginnEntries]=tdivpopup['popfld'][i];
      parent.ffolder.tdiv['poppaste'][i-1+BeginnEntries]=tdivpopup['poppaste'][i];
    }
}

     
     
