<?php
/**
 * Freedom document manipulation Soap library
 *
 * @author Anakeen 2006
 * @version $Id: Lib.FreedomWSDoc.php,v 1.15 2006/11/02 15:02:42 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM-WEBSERVICES
 */
/**
 */
include_once('WHAT/Class.User.php');
include_once('WHAT/Class.Session.php');
include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.Doc.php");


$dbaccess = "user=anakeen dbname=freedom";


function runAction($appli, $act, $params) {

 $result = array( "status"    => -1,
		   "statusmsg" => "Unknown error",
		   "mime"      => "text/plain",
		   "content"   => base64_encode("$app | $action | ..... ") );

  include_once('Lib.Http.php');
  include_once('Class.User.php');
  include_once('Class.Session.php');

  global $action;
  //echo "Appli=$appli action=$act Param="; print_r2($params);

  $indexphp=basename($_SERVER["SCRIPT_NAME"]);
  $log=new Log("",$indexphp);
  $CoreNull = "";
  global $CORE_LOGLEVEL;
  
  $session = new Session();
  $core = new Application();
  $core->Set("CORE",$CoreNull,$session);
  $core->user = new User();
  $core->user->setLoginName($_SERVER["PHP_AUTH_USER"]);

  $app = new Application();
  $app->Set($appli, $core);
  $action = new Action();
  $action->Set($act, $app);

  // init for gettext
include_once("WHAT/Lib.Prefix.php");
//echo "pubdir=".$pubdir." DEFAULT_PUBDIR=".DEFAULT_PUBDIR."<br>";;
  setlocale(LC_MESSAGES,$action->Getparam("CORE_LANG"));  
  setlocale(LC_MONETARY, $action->Getparam("CORE_LANG"));
  setlocale(LC_TIME, $action->Getparam("CORE_LANG"));
  putenv ("LANG=".$action->Getparam("CORE_LANG")); // needed for old Linux kernel < 2.4
  bindtextdomain ("what", DEFAULT_PUBDIR."/locale");
  bind_textdomain_codeset("what", 'ISO-8859-15');
  textdomain ("what");

  foreach ($params as $k => $v) {
    SetHttpVar($v["pname"], $v["pvalue"]);
  }

    //print_r2($action);
  $err = $action->canExecute($act);
  if ($err=="") {

    //echo "<pre>avant</pre>";
    $body = $action->execute();
    //echo "<pre>apres</pre>";
    //echo "<pre>"; print_r2(htmlentities($body)) ; echo "</pre>";
    $result = array( "status"    => 1,
		   "statusmsg" => "It's OK",
		   "mime"      => "text/plain",
		   "content"   => base64_encode($body));
  } else {
     $result = array( "status"    => 0,
                 "statusmsg" => "User ".$action->user->login." can't execute action $act (application ".$action->parent->name.") [$err]",
                   "mime"      => "text/plain",
                   "content"   => base64_encode(""));
  }
  return $result;

}


/**
 * Read doc content : properties and attributes
 * @param string $docid
 * @param string $docrev 
 * @return docContent $doc
 */
function  docRead($docid="", $docrev="") {
  global $dbaccess;
  global $action;

  $uid = _getUserFid();

  $doc = new_Doc($dbaccess, $docid);
  if (isset($doc) && $doc->isAlive()) {
    if ($docrev!="") {
      $ldoc = $doc->GetRevisions("TABLE");
      foreach($ldoc as $k => $cdoc) {
	if ($docrev==$cdoc["revision"]) {
	  $ndoc = getDocObject($dbaccess, $cdoc);
	  continue;
	}
      }
    } else {
      $ndoc = &$doc;
    }
    if ($ndoc!==false) {
      $tdo[] = getTDoc($dbaccess, $ndoc->id);
      $doclist = _xmlDoclist($tdo);
    }
  }
  return base64_encode($doclist);
}


/**
 * Search for documents according filters
 * @param tQuery  $query array of ( attr | value )
 * @param integer $start [default = 0]
 * @param integer $slice or 0 for all documents [default = 0]
 * @param string  $famid "" or family identifier (logical name or number) [default = 0]
 * @param string  $state "" state filter [default = ""]
 * @param boolean $allrev set to true to retrieve all revision [default = false]
 * @param boolean $trash  set to true to search in trash [default = false]
 * @param string  $orderby order by attribute [default order by title]
 * @return docList
 */
function docQuery($query=array(), $start=0, $slice=0, $famid="", $state="", $allrev=false, $trash=false, $orderby="title" ) {
fwsLog("  docQuery D ".strftime("%X %x", time()), "I", __FILE__,__LINE__);
  global $dbaccess;
 
  $docs = array();

  $uid = _getUserFid();

  $filter = array();
  if (count($query) > 0) {
    $sl = "";
    foreach ($query as $kq => $vq) {
      $sl .= ($sl==""?"":" AND ") . "(".$vq.")";
    }
    $filter[] = $sl;
  }
  if ($state!="") $filter[] = "state = '".$state."'";
  $tdocs = getChildDoc($dbaccess, 0, $start, ($slice==0?"ALL":$slice),  
		       $filter, $uid, "TABLE", 
		       $famid, $allrev, $orderby, true, $trash);
  $doclist = _xmlDoclist($tdocs);

  return base64_encode($doclist);
}

/**
 * Returns doc history
 * @param string $docid
 * @return docHisto 
 */
function  docGetHistory($docid="") {
  global $dbaccess;

  $rel = array("release" => array());
  $doc = new_Doc($dbaccess, $docid);


  $ldoc = $doc->GetRevisions("TABLE");

  $trdoc= array();
  foreach($ldoc as $k=>$zdoc) {
    $rdoc=getDocObject($dbaccess,$zdoc);
    $owner = new User("", $rdoc->owner);
    $trdoc[$k]["owner"]= $owner->firstname." ".$owner->lastname;
    $trdoc[$k]["version"]= $rdoc->version;
    $state=$rdoc->getState();
    $trdoc[$k]["state"]= ($state=="")?"":(($rdoc->locked==-1)?_($state):_("current"));
    setlocale (LC_TIME, "fr_FR");
    $trdoc[$k]["date"]= strftime ("%a %d %b %Y %H:%M",$rdoc->revdate);
    $trdoc[$k]["vername"]= $tversion[$rdoc->version];

    $tc=$rdoc->getHisto();
    $tlc = array();
    $kc=0; // index comment
    foreach ($tc as $vc) {
      $stime=$vc["date"];
      $tlc[]=array("cdate"=>$stime,
                   "cauthor"=>$vc["uname"],
                   "ccomment"=>htmlentities($vc["comment"]));
    }
    $trdoc[$k]["comment"]=$tlc;
    $trdoc[$k]["id"]= $rdoc->id;
    $trdoc[$k]["divid"]= $k;
  }
//print_r2($trdoc);
  return array("id" => $rdoc->initid, "title" => $rdoc->getValue("in_title"), "release" => $trdoc);
}

/**
 * Returns Workflow for document : full workflow description and following states
 * @param string $docid
 * @return docWorkflow 
 */
function  docGetWorkflow($docid="") {
  global $dbaccess;

  $workflow = array();
  $doc = new_Doc($dbaccess, $docid);
  if (isset($doc) && $doc->isAlive()) $wkf = $doc->wid;
  if ($wkf!="" && is_numeric($wkf)) {
    $wdoc = new_Doc($dbaccess, $wkf);
    if (isset($wdoc) && $wdoc->isAlive()) {
      $twst = $wdoc->getStates();
      $wst = array();
      foreach ($twst as $k => $w) {
	$wst[] = array( "key" => $k, "value" => $w);
      }
      $fstates = $wdoc->GetFollowingStates();
      $fst = array();      
      foreach ($fstates as $ks => $vs) {
	$slab = (isset($twst[$vs]) ? $twst[$vs] : $vs);
	$fst[] = array( "key" => $vs, "value" => $slab);
      }
      $workflow = array( "descr" => $wdoc->getTitle(), 
			 "docid" => $wdoc->id,
			 "nextStates" => $fst,
			 "allStates" => $wst 
			 );
    }
  }
  return $workflow;
}


/* 
 * --------------------------------------------------------------------------------------------
 * Private function
 * --------------------------------------------------------------------------------------------
 */

function _xmlDoclist($tdocs) {
  global $dbaccess;
  global $action;
  $xml = new Layout("Layout/doclist.xml");

  $excluded = array ("values","attrids" );

  foreach ($tdocs as $k=>$v) {
    $tattr = array();
    foreach ($v as $ka => $va) {
          if (! in_array($ka,$excluded)) $tattr[] = array("attname" => $ka, "empty" => ($va!="" ? false : true), "attvalue"=>$va);
    }
    $xml->setBlockData("attr".$v["id"], $tattr);
  }
  $xml->setBlockData("docs", $tdocs);
  $xml_string = $xml->gen();
  return $xml_string;
}

  
  
  


function docAPIVersion() {
  @include_once("Lib.Install.php");
  return $version."-".$release;
}

function _getUserFid() {
  global $_SERVER;
  global $action;

  $CoreNull="";
  $core = new Application();
  $core->Set("CORE",$CoreNull);
  $core->session=new Session();
  $action = new Action();
  $action->Set("",$core);
  $action->user = new User(); //create user as admin  
  $action->user->setLoginName($_SERVER["PHP_AUTH_USER"]);
  return $action->user->id;
}


function _docObject2docContent($ndoc) {
  foreach ($ndoc as $k => $v)     $rattr[] = array( "key" => $k, "value" => $v );
  return array( "prop" => $propr, "attr" => $rattr) ;
}


?>
