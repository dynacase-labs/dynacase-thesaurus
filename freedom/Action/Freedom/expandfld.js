
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
   

  // init the logical tree in parent.list :: add a branch in fldtopnode node
  parent.list.doc=document;
  parent.list.nEntries = BeginnEntries;

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
  parent.list.doc=parent.list.document;


  // --------------------------------------------------
  // second part : copy html object in the initial frame
  // --------------------------------------------------
 
	
  var divs = document.getElementsByTagName("div");
 
  var ndiv=divs.length;

  if (ndiv > 1) {


    var divtoinsert = null;
    var flddiv = parent.list.document.getElementById('folder'+parent.list.fldidtoexpand);

    if (flddiv)
      divtoinsert=flddiv.nextSibling;


    //   alert('nch1:'+parent.list.indexOfEntries[parent.list.fldidtoexpand].nChildren);
    for (var i=1; i < ndiv; i++)  {
      
      //   alert(parent.list.fldidtoexpand);
      
      h=  parent.list.document.createElement("div");
      h.innerHTML= divs[i].innerHTML;
      h.id= divs[i].id;
      h.className= divs[i].className;

      var ne = BeginnEntries+i-1;

      
      parent.list.document.getElementById('bodyid').insertBefore(h,divtoinsert);
      
      divs[i].style.backgroundColor='yellow';
      
      parent.list.indexOfEntries[ne].navObj=h;  
      parent.list.indexOfEntries[ne].iconImg=parent.list.document.getElementById('folderIcon'+ne);  
      parent.list.indexOfEntries[ne].nodeImg=parent.list.document.getElementById('nodeIcon'+ne);  
      
      
    }
    
   
    


  }

  
}


// --------------------------------------------------
function copypopup( tdivpopup, BeginnEntries ) {
// --------------------------------------------------

    for (var i=1; i< tdivpopup['popfld'].length; i++) {
      parent.list.tdiv['popfld'][i-1+BeginnEntries]=tdivpopup['popfld'][i];
      parent.list.tdiv['poppaste'][i-1+BeginnEntries]=tdivpopup['poppaste'][i];
    }
}

     
     
