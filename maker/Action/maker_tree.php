<?php
/**
 * Folder tree for maker
 *
 * @author Anakeen 2008
 * @version $Id: maker_tree.php,v 1.2 2008/04/16 07:20:25 eric Exp $
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
    $projectdir=$action->getParam('MAKER_PROJECTDIR');
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
		   "selectjs"=>"openTabFrame( 'Documents$project' , 'http://www.anakeen.com','".sprintf(_("Families of %s project"),$project)."')",
		   "target"=>"_blank",
		   "leaf"=>false);

     $tree[]=array("label"=>_("Create Main Frame"),
		   "icon"=>$action->getImageUrl("document.png"),
		   "tooltip"=>false,
		   "selectjs"=>"openTabFrame( 'Documents$project' , 'http://www.anakeen.com','".sprintf(_("Main %s project"),$project)."')",
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
		   "selectjs"=>"openDivInTabFrame( 'Documents$project' , 'TabSecond$project', 'http://chewbacca.tlse.anakeen.com/freedom/?sole=Y&&app=FDL&action=IMPCARD&zone=FDL:VIEWBODYCARD:S&id=137026','".sprintf(_("Third %s project"),$project)."')",
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

  


  prototree($action,$tree);
}


?>