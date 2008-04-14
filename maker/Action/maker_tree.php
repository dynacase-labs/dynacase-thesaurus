<?php
/**
 * Folder tree for maker
 *
 * @author Anakeen 2008
 * @version $Id: maker_tree.php,v 1.1 2008/04/14 16:37:11 eric Exp $
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
		   "selecturl"=>"http://www.google.fr",
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
		   "target"=>"_blank",
		   "leaf"=>false);

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