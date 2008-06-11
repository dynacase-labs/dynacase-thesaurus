<?php
/**
 * Folder tree for maker
 *
 * @author Anakeen 2008
 * @version $Id: maker_tree.php,v 1.7 2008/06/11 16:18:48 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage MAKER
 */
 /**
 */


include_once("FDL/prototree.php");

function maker_tree(&$action) {
 
  $type = GetHttpVars("type");
  $project = GetHttpVars("project");
  $application = GetHttpVars("application");

  // -------------------- Tree ------------------

  $surl=$action->getParam("CORE_STANDURL");
  $projectdir=$action->getParam('MAKER_PROJECTDIR');
  $tree=array();

  if ($type=='test') {    
    for ($i=0;$i<10;$i++) {
    $tree[]=array("label"=>_("New project $i"),
		  "icon"=>$action->getImageUrl("folder.png"),
		  "tooltip"=>"false",
		  "expandurl"=>"$surl&app=MAKER&action=MAKER_TREE&type=top",
		  "selecturl"=>"http://www.google.fr",
		  "target"=>"_blank",
		  "leaf"=>false);
    }
  }

  if ($type=='top') {    
    if ($handle = opendir($projectdir)) {
      while (false !== ($dir = readdir($handle))) {       
	if (is_file("$projectdir/$dir/project.xml")) {
	  $tree[]=array("label"=>_("$dir"),
		    "icon"=>$action->getImageUrl("folder.png"),
		    "tooltip"=>false,
		    "expandurl"=>"$surl&app=MAKER&action=MAKER_TREE&type=first&project=$dir",
		    "selecturl"=>"http://www.google.fr",
		    "target"=>"_blank",
		    "leaf"=>false);
	}	
      }    
      closedir($handle);
    }
  } 

  if ($type=='first') {    
     $tree[]=array("label"=>_("Applications"),
		   "icon"=>$action->getImageUrl("folder.png"),
		   "tooltip"=>false,
		   "expandurl"=>"$surl&app=MAKER&action=MAKER_TREE&type=application&project=$project",
		   "selectjs"=>"changecontent( 'poptest' , 'http://www.google.fr')",
		   "target"=>"_blank",
		   "leaf"=>false);

     $tree[]=array("label"=>_("Families"),
		   "icon"=>$action->getImageUrl("folder.png"),
		   "tooltip"=>false,
		   "expandurl"=>"$surl&app=MAKER&action=MAKER_TREE&type=families&project=$project",
		   "selectjs"=>"openSingleFrame( 'Families$project' , 'http://www.anakeen.com','".sprintf(_("Families of %s project"),$project)."')",
		   "target"=>"_blank",
		   "leaf"=>false);

     $tree[]=array("label"=>_("Profil"),
		   "icon"=>$action->getImageUrl("profil.gif"),
		   "tooltip"=>false,
		   "expandurl"=>"$surl&app=MAKER&action=MAKER_TREE&type=profil&project=$project",
		   "target"=>"_blank",
		   "leaf"=>false);

     $tree[]=array("label"=>_("Documents"),
		   "icon"=>$action->getImageUrl("document.png"),
		   "tooltip"=>false,
		   "expandurl"=>"$surl&app=MAKER&action=MAKER_TREE&type=document&project=$project",
		   "selectjs"=>"openTabFrame( 'Documents$project' , '".sprintf(_("Families of %s project"),$project)."')",
		   "target"=>"_blank",
		   "leaf"=>false);

     $tree[]=array("label"=>_("Create Main Frame"),
		   "icon"=>$action->getImageUrl("document.png"),
		   "tooltip"=>false,
		   "selectjs"=>"openTabFrame( 'Documents$project' , '".sprintf(_("Main %s project"),$project)."')",
		   "target"=>"_blank",
		   "leaf"=>true);

     $tree[]=array("label"=>_("Create First Tab Frame"),
		   "icon"=>$action->getImageUrl("document.png"),
		   "tooltip"=>false,
		   "selectjs"=>"openFrameInTabFrame( 'Documents$project' , 'TabFirst$project', 'http://www.frdom.org','".sprintf(_("Frdom %s project"),$project)."')",
		   "target"=>"_blank",
		   "leaf"=>true);

     $tree[]=array("label"=>_("Create Second Tab Frame"),
		   "icon"=>$action->getImageUrl("document.png"),
		   "tooltip"=>false,
		   "selectjs"=>"openFrameInTabFrame( 'Documents$project' , 'Tab2$project', 'http://www.google.fr','".sprintf(_("Google %s project"),$project)."')",
		   "target"=>"_blank",
		   "leaf"=>true);
     $tree[]=array("label"=>_("Create Third Tab Frame"),
		   "icon"=>$action->getImageUrl("document.png"),
		   "tooltip"=>false,
		   "selectjs"=>"openFrameInTabFrame( 'Documents$project' , 'Tab3$project', 'http://www.apple.fr','".sprintf(_("Apple %s project"),$project)."')",
		   "target"=>"_blank",
		   "leaf"=>true);

     $tree[]=array("label"=>_("Create First Tab Div"),
		   "icon"=>$action->getImageUrl("document.png"),
		   "tooltip"=>false,
		   "selectjs"=>"openDivInTabFrame( 'Documents$project' , 'TabSecond$project', '/freedom/?sole=Y&&app=FDL&action=IMPCARD&zone=FDL:VIEWBODYCARD:S&id=9','".sprintf(_("Third %s project"),$project)."')",
		   "target"=>"_blank",
		   "leaf"=>true);

     $tree[]=array("label"=>_("Other files"),
		   "icon"=>$action->getImageUrl("folder.png"),
		   "tooltip"=>false,
		   "expandurl"=>"$surl&app=MAKER&action=MAKER_TREE&type=other&project=$project",
		   "selecturl"=>"",
		   "target"=>"_blank",
		   "leaf"=>false);
    
  }

  if ($type=='other') { 
    include_once("Lib.FileMime.php");
    $filesdir=$projectdir."/$project/files";
    if ($handle = @opendir($filesdir)) {
      while (false !== ($file = readdir($handle))) {       
	if ($file[0]!='.') {
	  $tree[]=array("label"=>_("$file"),
			"icon"=>getIconFile($filesdir.'/'.$file),
			"tooltip"=>false, 
			"selectjs"=>"openDivInTabFrame( 'Documents$project' , '$file', '?sole=Y&&app=MAKER&action=MAKER_FILEEDIT&project=$project&file=files/$file','".$file."')",
			"target"=>"_blank",
			"leaf"=>true);
	}
		
      }    
      closedir($handle);
    }
  }   
    // list all files in files sub-directory
    
  


  prototree($action,$tree);
}

function getIconFile($filename) {
  $ext=substr($filename,strrpos($filename,'.')+1);
  
  if ($ext=="php") {
    $mime="text/x-php";
  }
  else $mime=getSysMimeFile($filename);
  $icon=getIconMimeFile($mime);
  return 'Images/'.$icon;
}

?>