//**************************************************************** 
// Keep this copyright notice: 
// This copy of the script is the property of the owner of the 
// particular web site you were visiting.
// Do not download the script's files from there.
// For a free download and full instructions go to: 
// http://www.geocities.com/marcelino_martins/foldertree.html
//
// Author: Marcelino Alves Martins (http://www.mmartins.com) 
// 1997--2001. 
//**************************************************************** 
 
// Log of changes: 
//       10 Aug 01 - Support for Netscape 6
//
//       17 Feb 98 - Fix initialization flashing problem with Netscape
//       
//       27 Jan 98 - Root folder starts open; support for USETEXTLINKS; 
//                   make the ftien4 a js file 
 
 
// Definition of class Folder 
// ***************************************************************** 
if (! document.wstyle)  document.wstyle="FREEDOM";

function Folder(folderDescription, hreference, refid, ftype, hasChild) //constructor 
{ 
  //constant data 
  this.desc = folderDescription 
  this.hreference = hreference 
  this.id = -1   
  this.navObj = 0  
  this.iconImg = 0  
  this.nodeImg = 0  
  this.isLastNode = 0 
  this.refid =  refid; // external reference
  this.ftype =  ftype; // external reference
 
  //dynamic data 
  this.isOpen = true 
    this.isLoaded = (! hasChild)
  this.iconSrc = "FREEDOM/Images/ftv2folderopen.gif"   
  this.children = new Array 
    this.nChildren = 0;   
 
  //methods 
  this.initialize = initializeFolder 
  this.setState = setStateFolder 
  this.addChild = addChild 
  this.createIndex = createEntryIndex 
  this.escondeBlock = escondeBlock
  this.esconde = escondeFolder 
  this.mostra = mostra 
  this.renderOb = drawFolder 
  this.totalHeight = totalHeight 
  this.subEntries = folderSubEntries 
  this.outputLink = outputFolderLink 
  this.blockStart = blockStart
  this.blockEnd = blockEnd
} 
 
function initializeFolder(level, lastNode, leftSide) 
{ 
  var j=0 ;
  var i=0 ;
  var numberOfFolders ;
  var numberOfDocs ;
  var nc ;
      
  nc = this.nChildren ;
    
  this.createIndex();
 
 
  if (level>0) 
    if (lastNode) { 
      //the last child in the children array 
    
	if (! this.isLoaded)  this.renderOb(leftSide  + "<img onMouseDown='clickOnNode("+this.id+");return false' name='nodeIcon" + this.id + "' id='nodeIcon" + this.id + "' src='FREEDOM/Images/ftv2loadlastnode.gif' width=16 height=22 border=0>");
	else if (nc > 0)      this.renderOb(leftSide  + "<img onMouseDown='clickOnNode("+this.id+");return false' name='nodeIcon" + this.id + "' id='nodeIcon" + this.id + "' src='FREEDOM/Images/ftv2mlastnode.gif' width=16 height=22 border=0>");
	else this.renderOb(leftSide +   "<img name='nodeIcon" + this.id + "' id='nodeIcon" + this.id + "' src='FREEDOM/Images/ftv2lastnode.gif' width=16 height=22 border=0>");

      leftSide = leftSide + "<img src='FREEDOM/Images/ftv2blank.gif' width=16 height=22>"  
      this.isLastNode = 1 ;
    }     else     { 
      if (! this.isLoaded) this.renderOb(leftSide  + "<img onMouseDown='clickOnNode("+this.id+");return false' name='nodeIcon" + this.id + "' id='nodeIcon" + this.id + "' src='FREEDOM/Images/ftv2loadnode.gif' width=16 height=22 border=0>") ;
      else if (nc > 0)      this.renderOb(leftSide  + "<img onMouseDown='clickOnNode("+this.id+");return false' name='nodeIcon" + this.id + "' id='nodeIcon" + this.id + "' src='FREEDOM/Images/ftv2mnode.gif' width=16 height=22 border=0>") ;
      else       this.renderOb(leftSide  + "<img name='nodeIcon" + this.id + "' id='nodeIcon" + this.id + "' src='FREEDOM/Images/ftv2node.gif' width=16 height=22 border=0>") ;

      leftSide = leftSide + "<img src='FREEDOM/Images/ftv2vertline.gif' width=16 height=22>" ;
      this.isLastNode = 0 ;
    } 
  else 
    this.renderOb("") 
   
  if (nc > 0) 
  { 
    level = level + 1 
    for (i=0 ; i < this.nChildren; i++)  
    { 
      if (i == this.nChildren-1) 
        this.children[i].initialize(level, 1, leftSide) 
      else 
        this.children[i].initialize(level, 0, leftSide) 
      } 
  } 
} 
 
function setStateFolder(isOpen) 
{ 
  var subEntries 
  var totalHeight 
  var fIt = 0 
  var i=0 
 
  if (isOpen == this.isOpen) {
    return 
  }
 
 
  this.isOpen = isOpen 
  propagateChangesInState(this) 
} 
 
function propagateChangesInState(folder) 
{   
  var i=0 
 
  if (folder.isOpen) 
  { 
    if (folder.nodeImg)  if (folder.isLastNode)  folder.nodeImg.src = "FREEDOM/Images/ftv2mlastnode.gif" 
                         else  folder.nodeImg.src = "FREEDOM/Images/ftv2mnode.gif" 
    folder.iconImg.src = document.wstyle+"/Images/ftv2folderopen"+folder.ftype+".gif" 
    for (i=0; i<folder.nChildren; i++) 
      folder.children[i].mostra() 
  } 
  else 
  { 
    if (folder.nodeImg) 
      if (folder.isLastNode) 
        if (! folder.isLoaded )   folder.nodeImg.src = "FREEDOM/Images/ftv2loadlastnode.gif" 
	else if (folder.nChildren > 0)   folder.nodeImg.src = "FREEDOM/Images/ftv2plastnode.gif" 
	else folder.nodeImg.src = "FREEDOM/Images/ftv2lastnode.gif" 
      else 
        if (! folder.isLoaded )   folder.nodeImg.src = "FREEDOM/Images/ftv2loadnode.gif" 
	else if (folder.nChildren > 0)  folder.nodeImg.src = "FREEDOM/Images/ftv2pnode.gif" 
        else     folder.nodeImg.src = "FREEDOM/Images/ftv2node.gif" 

    folder.iconImg.src = document.wstyle+"/Images/ftv2folderclosed"+folder.ftype+".gif" 

      // alert('esconde:'+folder.id+':'+folder.nChildren);
    for (i=0; i<folder.nChildren; i++) 
      folder.children[i].esconde() 
  }  
} 
 
function escondeFolder() 
{ 
  this.escondeBlock()
   
  this.setState(0) 
} 
 
function drawFolder(leftSide) 
{ 
  var idParam = "id='folder" + this.id + "'"


  this.blockStart("folder")

  doc.write("<tr><td class=\"fld\">") 
  doc.write(leftSide) 
    //  this.outputLink() 
  doc.write("<img id='folderIcon" + this.id + "' name='folderIcon" + this.id + "' src='" + this.iconSrc+"' border=0 style=\"cursor:crosshair\"");



  if (this.hreference == "#") {

      doc.write("onMouseDown=\"if (drag == 0) {selectFolder("+this.id+","+this.refid+");if (buttonNumber(event) == 1) parent.flist.location.href='"+actionviewfile+"&dirid="+this.refid+"'}\"") 
      doc.write("onMouseOver=\"if (drag == 1) clickOnFolder("+this.id+")\"") 
      doc.write("onContextMenu=\"openMenu(event,'popfld',"+this.id+");return false\"") 

      doc.write("onMouseUp=\"if (drag == 1) {dirid="+this.refid+";selid="+this.id+";openMenu(event,'poppaste',"+this.id+")};return false;\"") 
	} else {
	  doc.write("onMouseDown=\"parent.flist.location.href='"+this.hreference+"'\" "); 
	}
  doc.write(">") 
  doc.write("</td><td class=\"fld\" valign=middle nowrap>") 
    doc.write("<span class=\"urltext\" id='text"+this.id+"'")
      doc.write("onMouseDown=\"if (drag == 0) {selectFolder("+this.id+","+this.refid+");parent.flist.location.href='"+actionviewfile+"&dirid="+this.refid+"'}\"") 
      doc.write("onMouseOver=\"if (drag == 0) this.className='urltextsel'\"") 
      doc.write("onMouseOut=\"if (drag == 0) this.className='urltext'\"") 
  doc.write(">")  
  doc.write(this.desc) 
  doc.write("</span>")  
  doc.write("</td>")  

  this.blockEnd()
 
 
    this.navObj = doc.getElementById("folder"+this.id)
    this.iconImg = doc.getElementById("folderIcon"+this.id) 
    this.nodeImg = doc.getElementById("nodeIcon"+this.id)
  
} 
 
var selObj=0;
var dirid=0; 

function selectFolder(id, refid) {
  closeMenu('popfld');
  if (selObj)  {
    selObj.className='';
  }
  selObj = doc.getElementById("folder"+id);
    selObj.className='select';

    selid=id; // selected folder
    window.status="select:"+selid;
  dirid=refid;
  fldidtoexpand=id;

}
function outputFolderLink() 
{ 

  if (this.hreference) 
  { 
    doc.write("<a href='" + this.hreference + "' TARGET=\"basefrm\" ") 
    if (browserVersion > 0) 
      doc.write("onClick='javascript:clickOnFolder("+this.id+"): return false'") 
    doc.write(">") 
  } 
  else 
    doc.write("<a>")  
} 
 
function addChild(childNode) 
{ 
  this.children[this.nChildren] = childNode 
  this.nChildren++ 
  return childNode 
} 
 
function folderSubEntries() 
{ 
  var i = 0 
  var se = this.nChildren 
 
  for (i=0; i < this.nChildren; i++){ 
    if (this.children[i].children) //is a folder 
      se = se + this.children[i].subEntries() 
  } 
 
  return se 
} 
 


 
// Definition of class Item (a document or link inside a Folder) 
// ************************************************************* 
 
function Item(itemDescription, itemLink, refid, icon) // Constructor 
{ 
  // constant data 
  this.desc = itemDescription 
  this.link = itemLink 
  this.refid = refid 
  this.id = -1 //initialized in initalize() 
  this.navObj = 0 //initialized in render() 
  this.iconImg = 0 //initialized in render() 
  this.iconSrc = "FREEDOM/Images/ftv2doc.gif" 
 
  // methods 
  this.initialize = initializeItem 
  this.createIndex = createEntryIndex 
  this.esconde = escondeBlock
  this.mostra = mostra 
  this.renderOb = drawItem 
  this.totalHeight = totalHeight 
  this.blockStart = blockStart
  this.blockEnd = blockEnd
} 
 
function initializeItem(level, lastNode, leftSide) 
{  
  this.createIndex() 
 
  if (level>0) 
    if (lastNode) //the last 'brother' in the children array 
    { 
      this.renderOb(leftSide + "<img src='FREEDOM/Images/ftv2lastnode.gif' width=16 height=22>") 
      leftSide = leftSide + "<img src='FREEDOM/Images/ftv2blank.gif' width=16 height=22>"  
    } 
    else 
    { 
      this.renderOb(leftSide + "<img src='FREEDOM/Images/ftv2node.gif' width=16 height=22>") 
      leftSide = leftSide + "<img src='FREEDOM/Images/ftv2vertline.gif' width=16 height=22>" 
    } 
  else 
    this.renderOb("")   
} 

function drawItem(leftSide) 
{ 
  this.blockStart("item")

  doc.write("<tr><td class=\"fld\">") 
  doc.write(leftSide) 
  doc.write("<a href=" + this.link + ">") 
  doc.write("<img id='itemIcon"+this.id+"' ") 
  doc.write("src='"+this.iconSrc+"' border=0>") 
  doc.write("</a>") 
  doc.write("</td><td class=\"fld\" valign=middle nowrap>") 
  if (USETEXTLINKS) 
    doc.write("<a href=" + this.link + ">" + this.desc + "</a>") 
  else 
    doc.write(this.desc) 

  this.blockEnd()
 
  if (browserVersion == 1) { 
    this.navObj = doc.all["item"+this.id] 
    this.iconImg = doc.all["itemIcon"+this.id] 
  } else if (browserVersion == 2) { 
    this.navObj = doc.layers["item"+this.id] 
    this.iconImg = this.navObj.document.images["itemIcon"+this.id] 
    doc.yPos=doc.yPos+this.navObj.clip.height 
  } else if (browserVersion == 3) { 
    this.navObj = doc.getElementById("item"+this.id)
    this.iconImg = doc.getElementById("itemIcon"+this.id)
  } 
} 
 
// Methods common to both objects (pseudo-inheritance) 
// ******************************************************** 
 
function mostra() 
{ 
  // alert('mostra'+this.id);
  if (browserVersion == 1 || browserVersion == 3) { 
     var str = new String(doc.links[0])
     if (str.slice(16,20) != "ins.")
	    return
  }

  if (browserVersion == 1 || browserVersion == 3) 
    this.navObj.style.display = "block" 
  else 
    this.navObj.visibility = "show" 
} 

function escondeBlock() 
{ 
  // alert('escondeBlock'+this.id);
  if (browserVersion == 1 || browserVersion == 3) { 
    if (this.navObj.style.display == "none") 
      return 
    this.navObj.style.display = "none" 
  } else { 
    if (this.navObj.visibility == "hiden") 
      return 
    this.navObj.visibility = "hiden" 
  }     
} 
 
function blockStart(idprefix) {
  var idParam = "id='" + idprefix + this.id + "'";

  doc.write("<div class='folder' " + idParam +">");
  
     
  doc.write("<table border=0 cellspacing=0 cellpadding=0 ");

  
  doc.write(">");
}

function blockEnd() {
  doc.write("</table>");
   
  doc.write("</div>");
}
 
function createEntryIndex() 
{ 
  this.id = nEntries 
  indexOfEntries[nEntries] = this 
  nEntries++ 
} 
 
// total height of subEntries open 
function totalHeight() //used with browserVersion == 2 
{ 
  var h = this.navObj.clip.height 
  var i = 0 
   
  if (this.isOpen) //is a folder and _is_ open 
    for (i=0 ; i < this.nChildren; i++)  
      h = h + this.children[i].totalHeight() 
 
  return h 
} 

 
// Events 
// ********************************************************* 
 
function clickOnFolder(folderId) 
{ 
  var clicked = indexOfEntries[folderId] 
 
    if (clicked.nChildren == 0) return;
  if (!clicked.isOpen) 
    clickOnNode(folderId) 
 
  return  
 
  if (clicked.isSelected) 
    return 
} 
 
function clickOnNode(folderId) 
{ 
  var clickedFolder = 0 
  var state = 0 

  clickedFolder = indexOfEntries[folderId] 
  state = clickedFolder.isOpen 
 
    clickedFolder.setState(!state) //open<->close  

    if (allinitialized && (! clickedFolder.isLoaded) ) {
      fldidtoexpand=folderId;

      top.fexpand.document.location.href=actionexpfld+clickedFolder.refid;
           
      
      clickedFolder.isLoaded=true;
    }

} 
 

// Auxiliary Functions for Folder-Tree backward compatibility 
// *********************************************************** 
 
function gFld(description, hreference, refid, ftype, hasChild) 
{ 
  folder = new Folder(description, hreference, refid, ftype, hasChild) 
  return folder 
} 
 
function gLnk(target, description, linkData, refid) 
{ 
  fullLink = "" 
 
  if (target==0) 
  { 
    fullLink = "'"+linkData+"' target=\"info\"" 
  } 
  else 
  { 
    if (target==1) 
       fullLink = "'http://"+linkData+"' target=_blank" 
    else
       fullLink = "'http://"+linkData+"' target=\"info\"" 
  } 
 
  linkItem = new Item(description, fullLink, refid)   
  return linkItem 
} 
 
function insFld(parentFolder, childFolder) 
{ 
  return parentFolder.addChild(childFolder) 
} 
 
function insDoc(parentFolder, document) 
{ 
  parentFolder.addChild(document) 
} 
 

// Global variables 
// **************** 
 
//These two variables are overwriten on defineMyTree.js if needed be
USETEXTLINKS = 0 
STARTALLOPEN = 0
var indexOfEntries = new Array 
var doc = document 
var browserVersion = 0 
var selectedFolder=0
var allinitialized=false;
var BeginnEntries = 0 
var nEntries = BeginnEntries
var fldidtoexpand=0;
// Main function
// ************* 

// This function uses an object (navigator) defined in
// ua.js, imported in the main html page (left frame).
function initializeDocument() 
{ 
  BeginnEntries = indexOfEntries.length;
  nEntries = BeginnEntries
  switch(navigator.family)
  {
    case 'ie4':
      browserVersion = 1 //IE4   
      break;
    case 'nn4':
      browserVersion = 2 //NS4 
      break;
    case 'gecko':
      browserVersion = 3 //NS6
      break;
	default:
	  browserVersion = 0 //other 
	  break;
  }      

      browserVersion = 3 //NS6
  //foldersTree (with the site's data) is created in an external .js 
  fldtop.initialize(0, 1, "") 
  
 

  //The tree starts in full display 
  if (!STARTALLOPEN)
	  if (browserVersion > 0) {
		// close the whole tree 
		clickOnNode(BeginnEntries) 
		// open the root folder 
		clickOnNode(BeginnEntries) 
	  } 

 

  
   allinitialized=true;
} 
 
