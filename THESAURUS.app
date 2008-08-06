<?php
// ---------------------------------------------------------------
// $Id: THESAURUS.app,v 1.1 2008/08/06 15:11:52 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/thesaurus/THESAURUS.app,v $


$app_desc = array (
		   "name"	 =>"THESAURUS",		//Name
		   "short_name"	=>N_("Thesaurus"),    	//Short name
		   "description"=>N_("Manage thesaurus"),  //long description
		   "access_free"=>"N",			//Access free ? (Y,N)
		   "icon"	=>"thesaurus.png",	//Icon
		   "displayable"=>"Y",			//Should be displayed on an app list (Y,N)
		   "with_frame"	=>"Y",			//Use multiframe ? (Y,N)
		   "childof"	=>"ONEFAM"		// instance of ONEFAM application	
		   );

  


$action_desc = array (
  array( 
   "name"		=>"TH_EDITSKOSIMPORT",
   "short_name"		=>N_("interface import SKOS format"),
   "acl"		=>"ONEFAM_MASTER"),
  array( 
   "name"		=>"TH_SKOSIMPORT",
   "short_name"		=>N_("import SKOS format"),
   "acl"		=>"ONEFAM_MASTER"),
  array( 
   "name"		=>"INPUTTREE",
   "short_name"		=>N_("display tree of thesaurus"),
   "acl"		=>"ONEFAM_READ")
)

		
?>
