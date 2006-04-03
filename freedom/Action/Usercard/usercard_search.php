<?php
/**
 * progress bar tool
 *
 * @author Anakeen 2000
 * @version $Id: usercard_search.php,v 1.7 2006/04/03 14:56:26 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */
                                                             

include_once("FDL/Class.WDoc.php"); 
include_once("FDL/freedom_util.php");
include_once("FDL/Class.DocAttribute.php");            
                                                                                
function usercard_search(&$action) { 
 $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");    

 $dbaccess = $action->GetParam("FREEDOM_DB");

 //enum function
 $user=createDoc ($dbaccess,getFamIdFromName($dbaccess,"USER"),false); 
 $auser = $user->getAttribute("US_TYPE");
 $function = $auser->getEnum();
 $func=array();
 $func[]=array("function"=>" ","idfunction"=>"");
 foreach ($function as $k=>$v){
   $func[]=array("function"=>$function[$k],
		 "idfunction"=>$k);

 }
 $action->lay->setBlockData("FUNC",$func);


 //enum catg
 $soc=createDoc ($dbaccess,getFamIdFromName($dbaccess,"SOCIETY"),false);
 $asoc = $soc->getAttribute("SI_CATG");
 $categorie = $asoc->getEnum();
 $catg=array();
 $catg[]=array("catg"=>" ","idcatg"=>"");
 foreach ($categorie as $k=>$v){
   $catg[]=array("catg"=>$function[$k],
		 "idcatg"=>$k);
 }

 $action->lay->setBlockData("CATG",$catg);

}
?>